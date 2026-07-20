<?php
declare(strict_types=1);

header('X-Robots-Tag: noindex, nofollow', true);
header('Cache-Control: no-store, max-age=0', true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact/', true, 303);
    exit;
}

if (trim((string) ($_POST['website'] ?? '')) !== '') {
    header('Location: /?contact=success#contact-rapid', true, 303);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$service = trim((string) ($_POST['service'] ?? $_POST['subject'] ?? 'Solicitare generală'));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $message === '') {
    header('Location: /contact/?contact=error#formular', true, 303);
    exit;
}

$lines = [
    'Bună! Am completat formularul de pe cab-it.ro și doresc mai multe informații.',
    '',
    'Nume: ' . $name,
    'Serviciu: ' . ($service !== '' ? $service : 'Nespecificat'),
];
if ($company !== '') {
    $lines[] = 'Companie: ' . $company;
}
if ($email !== '') {
    $lines[] = 'Email: ' . $email;
}
if ($phone !== '') {
    $lines[] = 'Telefon: ' . $phone;
}
$lines[] = '';
$lines[] = 'Mesaj: ' . $message;

header('Location: https://wa.me/40771532949?text=' . rawurlencode(implode("\n", $lines)), true, 303);
exit;
