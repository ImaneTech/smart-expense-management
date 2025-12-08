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

// 1. Pour PHP (Chemin fichier disque dur)
// __DIR__ donne le dossier où est config.php (donc la racine du projet)
define('BASE_PATH', __DIR__ . '/'); 

// 2. Pour le HTML (Lien navigateur)
// Adapte le nom du dossier si ce n'est pas exactement celui-là
define('BASE_URL', 'http://localhost/smart-expense-management/');
?>
