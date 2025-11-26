<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclusion des fichiers PHPMailer 
require __DIR__ . '/../phpmailer/PHPMailer.php';
require __DIR__ . '/../phpmailer/SMTP.php';
require __DIR__ . '/../phpmailer/Exception.php';

function sendMail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // Config SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'iman.zn01@gmail.com'; 
        $mail->Password = 'lkai etee cfyi eeda'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;  // Port SMTP
        // Expéditeur
        $mail->setFrom('iman.zn01@gmail.com', 'GoTrackr');

        // Destinataire
        $mail->addAddress($to);

        // Contenu
        $mail->isHTML(false); 
        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
