<?php
session_start();
// Affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusion des fichiers nécessaires
require __DIR__ . '/../../config.php';
require_once(__DIR__ . '/../../models/User.php');
require __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../includes/flash.php';


// Initialisation du contrôleur utilisateur
$controller = new UserController($pdo);

// Gestion du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $result = $controller->register($_POST);

    // Vérifier que $result est bien un tableau
    if (is_array($result) && isset($result['type'], $result['message'])) {
        setFlash($result['type'], $result['message']);

        // Redirection après succès
        if ($result['type'] === 'success') {
            header("Location: login.php");
            exit;
        }
   } else {
    if ($result !== false) {
        setFlash('danger', 'Une erreur inattendue est survenue.');
    }
}
}


?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoTrackr - Sign up</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/signupp.css" rel="stylesheet">
    <script src="../../assets/js/signup.js" defer></script>
    <link href="../../assets/css/stylee.css" rel="stylesheet">
    
</head>

<body class="signup-page">

    <!-- Affichage des popups flash -->
    <?php displayFlash(); ?>

    <div class="signup-container">
        <!-- Left Section - Formulaire de inscription -->
        <div class="left-section">
            <div class="logo-section">
                <img src="../../assets/img/logo3.png" alt="ExpenseTrack Logo" class="logo-icon">
                <span class="logo-text">GoTrackr</span>
            </div>
            <h2 class="form-title">Inscription</h2>

            <form id="signupForm" action="" method="POST" novalidate>
                <div class="row-fields">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="first_name" class="form-control" placeholder="Mohamed" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="last_name" class="form-control" placeholder="El Amrani" required>
                    </div>
                </div>

                <div class="row-fields">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="mohamed@example.ma" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="0611223344" required>
                    </div>
                </div>

                <div class="row-fields">
                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
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
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="far fa-eye" id="confirm_password-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row-fields">
                    <div class="form-group">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-control" required>
                            <option value="" disabled selected>Sélectionner un rôle</option>
                            <option value="employe">Employé</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Service</label>
                        <select name="department" class="form-control" required>
                            <option value="" disabled selected>Sélectionner un service</option>
                            <option value="HR">RH</option>
                            <option value="Finance">Finance</option>
                            <option value="Logistics">Logistique</option>
                            <option value="IT">IT</option>
                            <option value="Sales">Ventes</option>
                        </select>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required checked>
                    <label for="terms">J'accepte les conditions générales</label>
                </div>
                <button type="submit" class="btn-signup">S'inscrire</button>
                <div class="login-link"> Vous avez déjà un compte ? <a href="login.php">Se connecter</a> </div>
            </form>
        </div>

        <!-- Right Section - Illustration -->
        <div class="right-section">
            <div class="illustration-container">
                <h2 class="illustration-tagline">Track Every Expense, Simplify Your Life</h2>
                <p class="illustration-subtitle">Join thousands of users managing their finances smarter</p>
                <img src="../../assets/img/illustration.png" alt="Expense Management Illustration" class="illustration-image">
                <div class="feature-badges">
                    <div class="feature-badge"><i class="fas fa-check-circle"></i> Easy to Use</div>
                    <div class="feature-badge"><i class="fas fa-shield-alt"></i> Secure</div>
                    <div class="feature-badge"><i class="fas fa-chart-line"></i> Real-time Tracking</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
