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
        // -------------------------
        // Config SMTP
        // -------------------------
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'iman.zn01@gmail.com'; 
        $mail->Password   = 'lkai etee cfyi eeda'; // mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // -------------------------
        // Expéditeur
        // -------------------------
        $mail->setFrom('iman.zn01@gmail.com', 'GoTrackr');

        // -------------------------
        // Destinataire
        // -------------------------
        $mail->addAddress($to);

        // -------------------------
        // Contenu HTML
        // -------------------------
        $mail->isHTML(true); 
        $mail->Subject = $subject;

        // Exemple de corps HTML pro pour GoTrackr
        $mail->Body = "
  <!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>{$subject}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; color: #333; margin:0; padding:0; }
        .email-container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #2566A1; color: #fff; text-align: center; padding: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .content h2 { color: #2566A1; font-size: 20px; margin-top: 0; }
        .content p { font-size: 16px; line-height: 1.5; }
        /* Bouton avec fond blanc et texte dark blue */
      .btn-reset { 
    background: #76BD46; /* vert */
    color: white;         /* texte blanc */
    display: inline-block; 
    padding: 12px 25px; 
    margin: 20px 0;  
    border: 2px solid #76BD46; 
    text-decoration: none; 
    border-radius: 6px; 
    font-weight: bold; 
}
      
        .footer { font-size:12px; color:#888; padding:20px; text-align:center; background:#f4f6f8; }
        .footer a { color:#2566A1; text-decoration:none; }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'><h1>GoTrackr</h1></div>
        <div class='content'>
            <h2>Réinitialisation de votre mot de passe</h2>
            <p>Bonjour,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte GoTrackr, l'application de gestion des frais de déplacement de votre entreprise.</p>
            <p>Pour créer un nouveau mot de passe, cliquez sur le bouton ci-dessous :</p>
           <p style='text-align:center;'>
    <a href='{$body}' 
       style='background-color:#76BD46; color:white; padding:12px 25px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>
       Réinitialiser mon mot de passe
    </a>
</p>
            <p>Ce lien est valable pendant 1 heure. Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
            <p>Merci,<br>L'équipe GoTrackr</p>
        </div>
        <div class='footer'>
            &copy; <?= date('Y') ?> GoTrackr. Tous droits réservés.<br>
            Pour assistance: <a href='mailto:support@gotrackr.com'>support@gotrackr.com</a>
        </div>
    </div>
</body>
</html>

        ";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Erreur envoi email: " . $mail->ErrorInfo);
        return false;
    }
}
?>
