<?php
// Configuration
$redirect_url = 'login.php';
$redirect_delay = 6;

// --- DÉCONNEXION ---

// 1. Démarre la session EXISTANTE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Vide et détruit la session actuelle (déconnexion réelle)
$_SESSION = array();
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
);
session_destroy();

// 3. Démarre une NOUVELLE session vide pour stocker le message flash
// Ceci est NÉCESSAIRE car session_destroy() arrête la session courante.
// Si le message flash doit être affiché sur la page de destination (login.php), 
// il faut le mettre dans la nouvelle session AVANT la redirection.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. On inclut le système de flash et on définit le message
require_once __DIR__ . '/../../includes/flash.php';
setFlash('success', 'Vous avez été déconnecté en toute sécurité.');

// Note: Une redirection immédiate avec `header("Location: $redirect_url"); exit();`
// est souvent préférable pour une déconnexion, car elle est plus rapide et
// le message flash s'affiche sur la page de connexion.
// Cependant, comme vous avez implémenté un compte à rebours, on garde le code ci-dessous.

// Envoie l'en-tête de rafraîchissement pour la redirection automatique côté serveur
header("Refresh: $redirect_delay; url=$redirect_url");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?= $redirect_delay ?>;url=<?= $redirect_url ?>">
    <title>Déconnexion - GoTrackr</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/login.css" rel="stylesheet"> 
    <link href="../../assets/css/logout.css" rel="stylesheet"> 
    
    <style>
        /* ===== STYLE POPUP FLASH (Laisser ici ou dans style.css/flash.css) ===== */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999; 
            min-width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            animation: slideInRight 0.5s ease-out;
            border: none;
            border-left: 5px solid #198754; 
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        


        /* ⚠️ Ces styles sont MIEUX placés dans logout.css */
        .logout-page .illustration-image {
            transform: none !important; 
            max-width: 100%;
            height: auto;
            margin: 0; 
        }
        .logout-page .right-section {
            padding: 40px 60px;
        }
        /* --- LOGO SECTION --- */
.logo-section {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 15px;
  margin-bottom: 40px;
  width: auto;
  padding: 5px 0;
}

.logo-icon {
  width: 60px;
  height: auto;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  cursor: pointer;
  transform: scale(1.9);
  filter: drop-shadow(0 4px 8px rgba(124, 179, 66, 0.15));
}

.logo-icon:hover {
  transform: scale(2.0) rotate(5deg);
  filter: drop-shadow(0 6px 12px rgba(124, 179, 66, 0.3));
}

.logo-text {
  font-size: 39px;
  font-weight: 900;
  margin-left: 30px;
  background: linear-gradient(135deg, #7cb342 0%, #558b2f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -1px;
  position: relative;
  transition: all 0.3s ease;
}

.logo-text:hover {
  letter-spacing: 0px;
}





.btn-login {
    /* Styles conservés pour la pleine largeur et l'apparence de base */
    width: 100%;
    max-width: none !important;
    width: 100% !important;

    /* 1. Centrage du texte et de l'icône horizontalement */
    text-align: center; /* Centrage du texte principal */
    
    /* 2. Utilisation de Flexbox pour le centrage vertical et le contrôle des éléments internes */
    display: flex; /* Permet d'aligner l'icône et le texte */
    justify-content: center; /* Centre le contenu (icône + texte) horizontalement */
    align-items: center; /* Centre le contenu verticalement */
    text-decoration: none; /* S'assurer qu'il n'y a pas de soulignement (car c'est un <a>) */

    /* 3. Padding et taille pour correspondre au bouton de connexion */
    padding: 16px 18px; /* Augmentation du padding vertical pour plus d'espace */
    font-size: 17px;
    
    /* Styles de couleur/bordure */
    background: linear-gradient(135deg, #7cb342 0%, #689f38 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 5px;
}

.btn-login i {
    /* Assure que l'icône est correctement espacée du texte */
    margin-right: 8px;
}

.btn-login:hover {
    background: linear-gradient(135deg, #689f38 0%, #558b2f 100%);
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(124, 179, 66, 0.3);
}

.logout-page .illustration-image {
  width: 150%;            /* Increased from 90% */
  max-width: 700px;       /* Increased from 500px */
  height: auto;
  margin: 20px 0;
  transform: none !important;
}


    </style>
</head>

<body class="login-page logout-page"> <?php displayFlash(); ?>

    <div class="login-container">
        
        <div class="left-section">
            <div class="logo-section">
                <img src="../../assets/img/logo.png" alt="GoTrackr Logo" class="logo-icon">
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
        // Utilisation des variables PHP
        const redirectDelay = <?= $redirect_delay ?>;
        const redirectUrl = "<?= $redirect_url ?>";
        
        let timeLeft = redirectDelay;
        const countdownElement = document.getElementById('countdown');

        // Compte à rebours uniquement visuel, car la redirection est gérée par l'en-tête HTML `meta http-equiv="refresh"`
        if (countdownElement) {
            const interval = setInterval(() => {
                timeLeft--;
                countdownElement.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    // La redirection se fait via la balise meta/header, 
                    // mais on garde le JS en secours au cas où l'en-tête serait ignoré.
                    // window.location.href = redirectUrl; 
                }
            }, 1000);
        }

        // Disparition automatique du flash après 4 secondes (optionnel)
        // Note: Assurez-vous que Bootstrap est chargé pour utiliser `new bootstrap.Alert`
        setTimeout(() => {
            const alertElement = document.querySelector('.alert');
            if(alertElement) {
                // Utilise la fonction de fermeture de Bootstrap si elle existe
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    const bsAlert = new bootstrap.Alert(alertElement);
                    bsAlert.close();
                } else {
                    alertElement.style.display = 'none'; // Fermeture simple si Bootstrap n'est pas prêt
                }
            }
        }, 4000);
    </script>
</body>
</html>