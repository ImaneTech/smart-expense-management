<?php
// Simple Database Configuration
$host = "localhost";
$user = "root";
$pass = "root";       
$dbname = "gestion_frais_db";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Configuration email
$emailConfig = [
    'use_real_email' => false, // Changez à true quand vous aurez un serveur SMTP configuré
    'smtp_host' => 'smtp.gmail.com', // Serveur SMTP
    'smtp_port' => 587,
    'smtp_user' => 'iman.zn01@gmail.com',
    'smtp_pass' => 'lkai etee cfyi eeda', 
    'from_email' => 'iman.zn01@gmail.com',
    'from_name' => 'GoTrackr'
];

?>
