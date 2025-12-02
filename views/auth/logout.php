<?php
// Configuration
$redirect_url = 'login.php';
$redirect_delay = 20; 

session_start();

// 1. On vide et détruit la session actuelle (déconnexion réelle)
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 2. On démarre une NOUVELLE session vide pour stocker le message flash
session_start();

// 3. On inclut le système de flash et on définit le message
require_once __DIR__ . '/../../includes/flash.php';
setFlash('success', 'Vous avez été déconnecté en toute sécurité.');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - GoTrackr</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="../../assets/css/login.css" rel="stylesheet"> 
    <link href="../../assets/css/style.css" rel="stylesheet">
    
    <style>
        /* ===== STYLE POPUP FLASH ===== */
        /* Ceci transforme l'alerte standard en notification flottante */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999; /* Au-dessus de tout */
            min-width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            animation: slideInRight 0.5s ease-out;
            border: none;
            border-left: 5px solid #198754; /* Bordure verte success */
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Ajustement du bouton btn-login */
        a.btn-login {
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }
    </style>
</head>

<body class="login-page">

    <?php displayFlash(); ?>

    <div class="login-container">
        
        <div class="left-section">
            <div class="logo-section">
                <img src="../../assets/img/logo.png" alt="ExpenseTrack Logo" class="logo-icon">
                <span class="logo-text">GoTrackr</span>
            </div>

            <h2 class="form-title">Déconnexion réussie</h2>
            <p class="form-subtitle">À bientôt sur votre espace.</p>

            <div class="logout-content" style="margin-top: 2rem;">
                
                <p class="logout-message">
                    Redirection automatique dans <strong id="countdown"><?= $redirect_delay ?></strong> secondes...
                </p>

                <a href="<?= $redirect_url ?>" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Se reconnecter
                </a>
                
                <div class="signup-link" style="margin-top: 20px;">
                    <a href="#">Besoin d'aide ?</a>
                </div>
            </div>
        </div>

        <div class="right-section">
            <div class="illustration-container">
                <h2 class="illustration-tagline">Merci de votre visite</h2>
                <p class="illustration-subtitle">Vos données sont synchronisées.</p>

                <img src="../../assets/img/logout.png" alt="Logout Illustration" class="illustration-image">

                <div class="feature-badges">
                    <div class="feature-badge"><i class="fas fa-shield-alt"></i> Sécurisé</div>
                    <div class="feature-badge"><i class="fas fa-check"></i> Sauvegardé</div>
                    <div class="feature-badge"><i class="fas fa-clock"></i> Déconnecté</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const redirectDelay = <?= $redirect_delay ?>;
        const redirectUrl = "<?= $redirect_url ?>";
        
        let timeLeft = redirectDelay;
        const countdownElement = document.getElementById('countdown');

        // Compte à rebours
        const interval = setInterval(() => {
            timeLeft--;
            if (countdownElement) {
                countdownElement.textContent = timeLeft;
            }

            if (timeLeft <= 0) {
                clearInterval(interval);
                window.location.href = redirectUrl;
            }
        }, 1000);

        // Disparition automatique du flash après 4 secondes (optionnel)
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 4000);
    </script>
</body>
</html>