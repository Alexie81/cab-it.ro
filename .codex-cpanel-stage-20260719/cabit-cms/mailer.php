<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

function cabit_send_message(string $subject, string $html, string $replyEmail = '', string $replyName = ''): void
{
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = CABIT_SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = CABIT_SMTP_USERNAME;
    $mail->Password = CABIT_SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = CABIT_SMTP_PORT;
    $mail->SMTPDebug = 0;
    $mail->setFrom(CABIT_SMTP_USERNAME, 'Website CAB-IT Expert');
    $mail->addAddress(CABIT_CONTACT_EMAIL, 'CAB-IT Expert');
    if (filter_var($replyEmail, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($replyEmail, $replyName !== '' ? $replyName : $replyEmail);
    }
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $html;
    $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $mail->send();
}
