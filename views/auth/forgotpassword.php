<?php
session_start();

// Affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclusion des fichiers nécessaires
// NOTE: Ensure your path to config.php is correct. If config.php is in the root, and this file is in views/auth/,
// the path should be '../../config.php' or using __DIR__ as shown below.
require_once __DIR__ . '/../../config.php'; // Corrected path assumption
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../includes/flash.php'; // Corrected path assumption

// Gestion du formulaire de mot de passe oublié
$controller = new UserController($pdo);

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email = trim($_POST['email']);
    $result = $controller->sendResetPassword($email);

    // Utiliser flash directement
    if (isset($result['type']) && isset($result['message'])) {
        setFlash($result['type'], $result['message']);
        // Use BASE_URL for redirection
        header("Location: " . BASE_URL . "views/auth/forgotpassword.php"); 
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

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/login.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet"> 
    <script src="../../assets/js/signup.js" defer></script>
    <script src="<?= BASE_URL ?>assets/js/forgotpassword.js" defer></script>
</head>

<body class="forgot-page">

    <?php displayFlash(); ?>

    <div class="login-container">

        <div class="left-section">

            <div class="logo-section">
                <img src="<?= BASE_URL ?>assets/img/logo.png" alt="ExpenseTrack Logo" class="logo-icon">
                <span class="logo-text">GoTrackr</span>
            </div>

            <h2 class="form-title">Mot de passe oublié</h2>
            <p class="form-subtitle">Entrez votre adresse e-mail pour recevoir un lien de réinitialisation.</p>

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
                <a href="<?= BASE_URL ?>views/auth/login.php">Se connecter</a>
            </div>
        </div>

        <div class="right-section">
            <div class="illustration-container">
                <h2 class="illustration-tagline">Récupération de mot de passe</h2>
                <p class="illustration-subtitle">Nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
                <img src="<?= BASE_URL ?>assets/img/forgot-password.png" 
                    alt="Illustration de mot de passe oublié" 
                    class="illustration-image">
            </div>
        </div>
    </div>
</body>
</html>