<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/settings.php';

function send_mail(PDO $pdo, string $to, string $subject, string $body): bool {
    $mail = new PHPMailer(true);
    try {
        $host = get_setting($pdo, 'smtp_host', '');
        $port = get_setting($pdo, 'smtp_port', '');
        $user = get_setting($pdo, 'smtp_user', '');
        $pass = get_setting($pdo, 'smtp_pass', '');
        $secure = get_setting($pdo, 'smtp_secure', '');
        $from = get_setting($pdo, 'smtp_from', $user);
        $fromName = get_setting($pdo, 'smtp_from_name', $from);
        if(!$host || !$port) return false;
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = (int)$port;
        if($secure){
            $mail->SMTPSecure = $secure;
        }
        if($user){
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
        }
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        return $mail->send();
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        return false;
    }
}
