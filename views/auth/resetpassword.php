<?php
session_start();

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../includes/flash.php'; 

// Initialiser le contrôleur utilisateur
$controller = new UserController($pdo);

// Vérifier la présence du token dans l'URL
if (!isset($_GET['token'])) {
    die("Token manquant.");
}
$token = $_GET['token'];

// Gestion du formulaire de réinitialisation du mot de passe
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $p1 = trim($_POST['password']);
    $p2 = trim($_POST['password_confirm']);

    if ($p1 !== $p2) {
        setFlash('error', "Les mots de passe ne correspondent pas.");
        header("Location: resetpassword.php?token=$token");
        exit();
    } else {
        $result = $controller->resetPassword($token, $p1);

        if ($result === true) {
            // Le message flash est déjà défini dans le contrôleur
            header("Location: login.php"); // Redirection vers login après succès
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoTrackr - Réinitialiser le mot de passe</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/login.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet"> 
    <script src="../../assets/js/signup.js" defer></script>
    <script src="../../assets/js/resetpassword.js" defer></script>
</head>

<body class="reset-page">

    <!-- Affichage des popups flash -->
    <?php displayFlash(); ?>

    <div class="login-container">
        <!-- Left Section - Formulaire -->
        <div class="left-section">
            <div class="logo-section">
                <img src="../../assets/img/logo.png" alt="GoTrackr Logo" class="logo-icon">
                <span class="logo-text">GoTrackr</span>
            </div>
            
            <h2 class="form-title">Nouveau mot de passe</h2>
            <p class="form-subtitle">Créez un nouveau mot de passe sécurisé pour votre compte.</p>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="far fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirm')">
                            <i class="far fa-eye" id="password_confirm-icon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">Réinitialiser le mot de passe</button>
                
                <div class="signup-link">
                    Vous vous souvenez de votre mot de passe ? <a href="login.php">Se connecter</a>
                </div>
            </form>
        </div>
        
        <!-- Right Section - Illustration -->
        <div class="right-section">
            <div class="illustration-container">
                <h2 class="illustration-tagline">Sécurité renforcée</h2>
                <p class="illustration-subtitle">Créez un mot de passe fort pour protéger votre compte</p>
                <img src="../../assets/img/reset_pass_pic.svg" alt="Reset Password Illustration" class="illustration-image">
                <div class="feature-badges">
                    <div class="feature-badge"><i class="fas fa-shield-alt"></i> Sécurisé</div>
                    <div class="feature-badge"><i class="fas fa-key"></i> Crypté</div>
                    <div class="feature-badge"><i class="fas fa-check-circle"></i> Fiable</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
