<?php
declare(strict_types=1);

require_once __DIR__ . '/cabit-cms/bootstrap.php';
require_once __DIR__ . '/cabit-cms/mailer.php';

header('X-Robots-Tag: noindex, nofollow', true);
header('Cache-Control: no-store, max-age=0', true);

$base = cms_web_base();
$redirect = ($base ?: '') . '/?audit=error#audit';
$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
    || str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json');

function audit_response(bool $ok, string $message, string $redirect, bool $ajax, int $status = 200, ?int $requestId = null): never
{
    if ($ajax) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => $ok, 'message' => $message, 'request_id' => $requestId], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    header('Location: ' . $redirect, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    audit_response(false, 'Metoda de trimitere nu este validă.', $redirect, $isAjax, 405);
}

try {
    if (trim((string) ($_POST['company_website'] ?? '')) !== '') {
        audit_response(true, 'Solicitarea a fost înregistrată.', ($base ?: '') . '/?audit=success#audit', $isAjax);
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $websiteUrl = trim((string) ($_POST['website_url'] ?? ''));
    if ($websiteUrl !== '' && !preg_match('~^https?://~i', $websiteUrl)) {
        $websiteUrl = 'https://' . $websiteUrl;
    }

    $requestId = cms_add_audit_request($name, $email, $phone, $websiteUrl, $_SERVER['REMOTE_ADDR'] ?? '');
    $safeName = cms_e($name);
    $safeEmail = cms_e($email);
    $safePhone = cms_e($phone !== '' ? $phone : '—');
    $safeWebsite = cms_e($websiteUrl);
    $receivedAt = date('d.m.Y H:i');

    try {
        cabit_send_message(
            'Audit gratuit nou #' . $requestId . ' — ' . (string) parse_url($websiteUrl, PHP_URL_HOST),
            '<h1>Solicitare nouă de audit gratuit</h1>' .
            '<p><strong>Termen promis:</strong> maximum 30 de minute de la primire.</p>' .
            '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#d0d5dd">' .
            '<tr><th align="left">ID</th><td>#' . $requestId . '</td></tr>' .
            '<tr><th align="left">Nume</th><td>' . $safeName . '</td></tr>' .
            '<tr><th align="left">Email</th><td><a href="mailto:' . $safeEmail . '">' . $safeEmail . '</a></td></tr>' .
            '<tr><th align="left">Telefon</th><td>' . $safePhone . '</td></tr>' .
            '<tr><th align="left">Website</th><td><a href="' . $safeWebsite . '">' . $safeWebsite . '</a></td></tr>' .
            '<tr><th align="left">Primit</th><td>' . $receivedAt . '</td></tr>' .
            '</table><p>Solicitarea este salvată și în panoul de administrare.</p>',
            $email,
            $name
        );
    } catch (Throwable $mailError) {
        $note = 'Notificarea email nu a putut fi trimisă la ' . date('c') . ': ' . mb_substr($mailError->getMessage(), 0, 300);
        $statement = cms_db()->prepare('UPDATE audit_requests SET notes = ?, updated_at = ? WHERE id = ?');
        $statement->execute([$note, date('c'), $requestId]);
    }

    audit_response(true, 'Solicitarea a fost înregistrată. Auditul va fi livrat pe email în maximum 30 de minute și este 100% gratuit.', ($base ?: '') . '/?audit=success#audit', $isAjax, 200, $requestId);
} catch (InvalidArgumentException $error) {
    audit_response(false, $error->getMessage(), $redirect, $isAjax, 422);
} catch (Throwable) {
    audit_response(false, 'Solicitarea nu a putut fi înregistrată. Verifică datele și încearcă din nou.', $redirect, $isAjax, 500);
}
