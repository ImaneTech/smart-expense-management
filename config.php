<?php
// config.php

// Simple Database Configuration
$host = "localhost";
$user = "root";
$pass = "";       
$dbname = "gestion_frais_db";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    
    die("Erreur de connexion : " . $e->getMessage());
}

// Configuration email
$emailConfig = [
    'use_real_email' => false, 
    'smtp_host' => 'smtp.gmail.com', // Serveur SMTP
    'smtp_port' => 587,
    'smtp_user' => 'iman.zn01@gmail.com',
    'smtp_pass' => 'lkai etee cfyi eeda', 
    'from_email' => 'iman.zn01@gmail.com',
    'from_name' => 'GoTrackr'
];

// 1. Pour PHP (Chemin fichier disque dur)
define('BASE_PATH', __DIR__ . '/'); 

// 2. Pour le HTML (Lien navigateur)
define('BASE_URL', 'http://localhost/smart-expense-management/');
