<?php
// ==========================================================
// CONFIGURATION ET LOGIQUE PHP DE DÉCONNEXION SÉCURISÉE
// ==========================================================

// Définitions de la redirection
$redirect_url = 'login.php'; // La page vers laquelle l'utilisateur sera redirigé
$redirect_delay = 20;        // Le temps d'attente en secondes avant la redirection

// 1. Démarrer la session
// Nécessaire pour accéder et manipuler les variables de session.
session_start();

// 2. Vider le tableau de session
// Ceci supprime toutes les variables de session (ex: $_SESSION['user_id']).
$_SESSION = array();

// 3. Détruire le cookie de session côté client (si existant)
// Ceci est crucial pour assurer que la session n'est pas réutilisée.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Détruire la session côté serveur
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="refresh" content="<?= $redirect_delay ?>;url=<?= $redirect_url ?>">
    <link rel="stylesheet" href="../../assets/css/logout.css"> 
    </head>
<body>
   <div class="page-container">
    <div class="logout-box">
        <div class="progress-bar-container">
            <div class="progress-bar-fill" style="display: none;"></div>
            <div class="icon">✅</div> 
            <h2>Déconnexion Réussie !</h2>
            <p>
                Vous avez été déconnecté(e) de votre compte. 
                <br>
                Merci d'avoir utilisé notre application.
            </p>
            
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="animation-duration: <?= $redirect_delay ?>s;"></div>
            </div>

            <p class="redirect-info">
                Redirection vers la <a href="<?= $redirect_url ?>">page de connexion</a>...
            </p>
        </div>
    </div>
</body>
</html>
<?php
exit;
?>