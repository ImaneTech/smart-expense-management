<?php
// includes/header.php

// 1. Vérification que la config est chargée
if (!defined('BASE_URL') || !defined('BASE_PATH')) {
    die("Erreur critique : config.php n'est pas chargé.");
}

// 2. Démarrage session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Sécurité Connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "views/auth/login.php"); 
    exit();
}

// 4. Gestion du Thème Sombre (Lecture du Cookie)
$themeClass = '';
if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') {
    $themeClass = 'dark';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?php echo $page_title ?? 'GoTrackr'; ?></title>

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
    
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo3.png">
</head>

<body class="<?= $themeClass ?>">

    <?php include BASE_PATH . 'includes/sidebar.php'; ?>
    
    <?php
    require_once BASE_PATH . 'includes/flash.php';
    displayFlash();
    ?>

    <section class="home-section">
        <div class="main-content">
