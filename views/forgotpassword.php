<?php
session_start();

// Affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../includes/flash.php'; // <-- Ajout flash.php

// Gestion du formulaire de mot de passe oublié
$controller = new UserController($pdo);

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email = trim($_POST['email']);
    $result = $controller->sendResetPassword($email);

    // Utiliser flash directement
    if (isset($result['type']) && isset($result['message'])) {
        setFlash($result['type'], $result['message']);
        header("Location: forgotpassword.php"); // Redirection pour éviter la soumission multiple
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoTrackr - Mot de passe oublié</title>

    <!-- Bootstrap / Icons / CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet"> <!-- CSS popups flash -->
    <script src="../assets/js/forgotpassword.js" defer></script>
</head>

<body class="forgot-page">

    <!-- Affichage des popups flash -->
    <?php displayFlash(); ?>

    <div class="login-container">

        <!-- SECTION GAUCHE : FORMULAIRE -->
        <div class="left-section">

            <!-- Logo -->
            <div class="logo-section">
                <img src="../assets/img/logo.png" alt="ExpenseTrack Logo" class="logo-icon">
                <span class="logo-text">GoTrackr</span>
            </div>

            <h2 class="form-title">Mot de passe oublié</h2>
            <p class="form-subtitle">Entrez votre adresse e-mail pour recevoir un lien de réinitialisation.</p>

            <!-- Formulaire -->
            <form id="forgotPasswordForm" action="forgotpassword.php" method="POST" novalidate>
                <div class="form-group mb-3">
                    <label class="form-label">Adresse email</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="exemple@domaine.ma" 
                        required>
                </div>

                <button type="submit" class="btn-login">Envoyer le lien</button>
            </form>

            <div class="signup-link mt-3">
                Vous vous souvenez de votre mot de passe ?
                <a href="login.php">Se connecter</a>
            </div>
        </div>

        <!-- SECTION DROITE : ILLUSTRATION -->
        <div class="right-section">
            <div class="illustration-container">
                <h2 class="illustration-tagline">Récupération de mot de passe</h2>
                <p class="illustration-subtitle">Nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
                <img src="../assets/img/forgot-password.png" 
                     alt="Illustration de mot de passe oublié" 
                     class="illustration-image">
            </div>
        </div>
    </div>
</body>
</html>
