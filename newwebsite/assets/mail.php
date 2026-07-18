<?php
declare(strict_types=1);

require_once __DIR__ . '/../cabit-cms/bootstrap.php';
require_once __DIR__ . '/../cabit-cms/mailer.php';

header('X-Robots-Tag: noindex, nofollow', true);
header('Cache-Control: no-store, max-age=0', true);

$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

function contact_reply(bool $success, bool $ajax): never
{
    if ($ajax) {
        http_response_code($success ? 200 : 422);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $success ? 'Mesaj trimis cu succes! Revenim cu un răspuns cât mai curând.' : 'Verifică datele formularului și încearcă din nou.';
        exit;
    }
    header('Location: /?contact=' . ($success ? 'success' : 'error') . '#contact-rapid', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    contact_reply(false, $isAjax);
}

try {
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        contact_reply(true, $isAjax);
    }
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $company = trim((string) ($_POST['company'] ?? ''));
    $service = trim((string) ($_POST['service'] ?? $_POST['subject'] ?? 'Solicitare generală'));
    $message = trim((string) ($_POST['message'] ?? ''));

    if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($message) < 5) {
        throw new InvalidArgumentException('Date de contact incomplete.');
    }

    cabit_send_message(
        'Mesaj nou din website — ' . mb_substr($service, 0, 80),
        '<h1>Mesaj nou din website</h1><table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#d0d5dd">' .
        '<tr><th align="left">Nume</th><td>' . cms_e($name) . '</td></tr>' .
        '<tr><th align="left">Email</th><td>' . cms_e($email) . '</td></tr>' .
        '<tr><th align="left">Telefon</th><td>' . cms_e($phone !== '' ? $phone : '—') . '</td></tr>' .
        '<tr><th align="left">Companie</th><td>' . cms_e($company !== '' ? $company : '—') . '</td></tr>' .
        '<tr><th align="left">Serviciu</th><td>' . cms_e($service) . '</td></tr>' .
        '</table><h2>Mesaj</h2><p>' . nl2br(cms_e($message)) . '</p>',
        $email,
        $name
    );
    contact_reply(true, $isAjax);
} catch (Throwable) {
    contact_reply(false, $isAjax);
}
