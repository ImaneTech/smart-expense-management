<?php

// 1. On vérifie juste que la config a été chargée AVANT par la page principale
if (!defined('BASE_URL') || !defined('BASE_PATH')) {
    // Si on arrive ici, c'est que tu as oublié d'inclure config.php dans ta page dashboard.php
    die("Erreur critique : config.php n'est pas chargé. Inclus-le au début de ta page.");
}

// 2. Démarrage de la session (Sécurité anti-doublon)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Sécurité Connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "views/auth/login.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?php echo $page_title ?? 'GoTrackr'; ?></title>

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/stylee.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebarr.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo3.png">
</head>
<body>

    <?php include BASE_PATH . 'includes/sidebar.php'; ?>

    <section class="home-section">
        <div class="main-content">