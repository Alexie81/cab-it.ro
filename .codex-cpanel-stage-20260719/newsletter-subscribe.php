<?php
declare(strict_types=1);

require_once __DIR__ . '/cabit-cms/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow', true);
header('Cache-Control: no-store, max-age=0', true);

$base = cms_web_base();
$status = 'invalid';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            $status = 'success';
        } else {
            $added = cms_add_subscriber((string) ($_POST['email'] ?? ''), (string) ($_POST['source'] ?? 'footer'), $_SERVER['REMOTE_ADDR'] ?? '');
            $status = $added ? 'success' : 'exists';
        }
    } catch (Throwable) {
        $status = 'invalid';
    }
}

header('Location: ' . ($base ?: '') . '/?newsletter=' . rawurlencode($status) . '#newsletter');
exit;
