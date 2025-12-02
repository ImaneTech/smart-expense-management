<?php
session_start();

// Affichage des erreurs (A désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusion unique des fichiers
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/flash.php';
require_once __DIR__ . '/../../controllers/UserController.php';

// Gestion du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation simple
    if (empty($email) || empty($password)) {
        setFlash('danger', 'Veuillez remplir tous les champs.');
    } else {
        // Tentative de connexion
        $userController = new UserController($pdo);
        $result = $userController->login($email, $password);

        // $result est maintenant GARANTI d'être un tableau grâce à la modif du controller
        if ($result['success']) {
            // Mise en session
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['role'] = $result['user']['role'];
            $_SESSION['email'] = $result['user']['email'];
            $_SESSION['first_name'] = $result['user']['first_name'];

            // Gestion du "Se souvenir de moi"
            if (isset($_POST['remember'])) {
                // Créer un cookie 'remember_email' qui dure 30 jours (86400 sec * 30)
                // Le paramètre true à la fin active 'HttpOnly' pour la sécurité (empêche l'accès via JS)
                setcookie('remember_email', $email, time() + (86400 * 30), "/", "", false, true);
            } else {
                // Si l'utilisateur décoche la case, on supprime le cookie (temps dans le passé)
                if (isset($_COOKIE['remember_email'])) {
                    setcookie('remember_email', "", time() - 3600, "/");
                }
            }

            // Message et redirection
            setFlash('success', $result['message']);
            header('Location: ../dashboard.php');
            exit();
        } else {
            // Erreur
            setFlash('danger', $result['message']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpenseTrack - Connexion</title>
    <!-- Bootstrap / Icons / CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="../../assets/css/login.css" rel="stylesheet">
   <script src="../../assets/js/login.js" defer></script>
   <script src="../../assets/js/signup.js" defer></script>
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>




<body class="login-page">
    <?php
    require_once __DIR__ . '/../../includes/flash.php';
    displayFlash();
    ?>
    <div class="login-container">
        <!-- Left Section - Formulaire -->
        <div class="left-section">
            <!-- Logo -->
            <div class="logo-section">
                <img src="../../assets/img/logo.png" alt="ExpenseTrack Logo" class="logo-icon">
                <span class="logo-text">GoTrackr</span>
            </div>
            <!-- Formulaire -->
            <h2 class="form-title">Connexion</h2>
            <p class="form-subtitle">Bienvenue ! Connectez‑vous à votre compte.</p>

            <!-- Affichage des erreurs et messages de succès -->
            <?php displayFlash(); ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <!-- Formulaire de connexion -->
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="mohamed@example.ma" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="far fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-group">
                        <input type="checkbox" id="remember" name="remember"
                            <?php echo isset($_COOKIE['remember_email']) ? 'checked' : ''; ?>>
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="forgotpassword.php" class="forgot-link">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-login">Se connecter</button>

                <div class="signup-link">
                    Vous n'avez pas de compte ? <a href="signup.php">S'inscrire</a>
                </div>
            </form>
        </div>

        <!-- Right Section - Illustration -->
        <div class="right-section">
            <div class="illustration-container">
                <!-- Tagline -->
                <h2 class="illustration-tagline">Bienvenue chez GoTrackr</h2>
                <p class="illustration-subtitle">Gérez vos dépenses simplement et efficacement</p>

                <!-- Illustration Image -->
                <img src="../../assets/img/illustration.png" alt="Login Illustration" class="illustration-image">

                <!-- Feature Badges -->
                <div class="feature-badges">
                    <div class="feature-badge">
                        <i class="fas fa-lock"></i> Connexion sécurisée
                    </div>
                    <div class="feature-badge">
                        <i class="fas fa-mobile-alt"></i> Multi‑appareils
                    </div>
                    <div class="feature-badge">
                        <i class="fas fa-clock"></i> Accès 24/7
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>