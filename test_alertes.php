<?php
// ---------------------------------------------------------
// 1. PHP LOGIC (This usually goes in your functions file)
// ---------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to add a flash message
function setFlash(string $type, string $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

// Handle Form Submissions to trigger alerts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['test_success'])) {
        setFlash('success', 'Succès ! Votre compte a été créé avec succès.');
    }
    if (isset($_POST['test_danger'])) {
        setFlash('danger', 'Erreur : Cet email est déjà utilisé.');
    }
    if (isset($_POST['test_info'])) {
        setFlash('info', 'Info : Une mise à jour est disponible.');
    }
    if (isset($_POST['test_warning'])) {
        setFlash('warning', 'Attention : Votre session va bientôt expirer.');
    }
    if (isset($_POST['test_all'])) {
        setFlash('success', 'Action effectuée !');
        setFlash('danger', 'Erreur critique détectée.');
        setFlash('info', 'Vérifiez vos paramètres.');
    }
    
    // Redirect to avoid resubmission and to show the flash
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des Alertes Flash</title>
    <style>
        /* ---------------------------------------------------------
           2. YOUR CSS (Pasted exactly as provided)
           --------------------------------------------------------- */
        
        /* Layout Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f5f7fa; color: #2d3748; min-height: 100vh; }

        /* Container pour les popups (TOP RIGHT) */
        .flash-container {
            position: fixed;
            top: 20px;
            right: 20px;
            left: auto;
            transform: none;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        /* Style des popups */
        .flash-popup {
            display: flex;
            align-items: center;
            width: 100%;
            min-width: 320px;
            max-width: 500px;
            padding: 14px 18px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15); /* Slightly lighter shadow for better look */
            position: relative;
            /* Animation: Slide In From Right */
            animation: slideInRight 0.4s ease-out;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100%); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Taille des icônes */
        .flash-popup svg.bi {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            margin-right: 15px;
            fill: currentColor;
        }

        /* Bouton de fermeture */
        .flash-popup .btn-close {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 15px;
            background: transparent;
            border: none;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            color: inherit;
            opacity: 0.6;
            padding: 0;
        }
        .flash-popup .btn-close:hover { opacity: 1; }

        /* Couleurs */
        .flash-popup.alert-info {
            background-color: #cff4fc;
            color: #055160;
            border: 1px solid #b6effb;
        }
        .flash-popup.alert-info svg { fill: #055160; }

        .flash-popup.alert-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .flash-popup.alert-warning {
            background-color: #fef9c3;
            color: #854d0e;
            border: 1px solid #fde047;
        }

        .flash-popup.alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* Simple styling for the test page content (Buttons) */
        .test-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            gap: 20px;
        }
        .btn {
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            color: white;
            transition: transform 0.2s;
        }
        .btn:active { transform: scale(0.98); }
        .btn-green { background-color: #15803d; }
        .btn-red { background-color: #b91c1c; }
        .btn-blue { background-color: #0284c7; }
        .btn-yellow { background-color: #ca8a04; }
        .btn-dark { background-color: #333; }
    </style>
</head>
<body>

    <div class="test-container">
        <h1>Test des Notifications</h1>
        <p>Cliquez sur les boutons pour générer des alertes en haut à droite.</p>
        
        <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
            <button type="submit" name="test_success" class="btn btn-green">Test Success</button>
            <button type="submit" name="test_danger" class="btn btn-red">Test Error</button>
            <button type="submit" name="test_info" class="btn btn-blue">Test Info</button>
            <button type="submit" name="test_warning" class="btn btn-yellow">Test Warning</button>
            <button type="submit" name="test_all" class="btn btn-dark">Afficher tout (Stack)</button>
        </form>
    </div>

    <?php
    if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])):
    ?>
        <div class="flash-container">
            <svg xmlns="http://www.w3.org/2000/svg" class="d-none" style="display:none;">
                <symbol id="check-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </symbol>
                <symbol id="info-fill" viewBox="0 0 16 16">
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                </symbol>
                <symbol id="exclamation-triangle-fill" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </symbol>
            </svg>

            <?php
            foreach ($_SESSION['flash_messages'] as $flash):
                $type = $flash['type'] ?? 'info';
                $message = $flash['message'] ?? '';
                $icon = 'info-fill';
                if ($type === 'success') $icon = 'check-circle-fill';
                if ($type === 'warning' || $type === 'danger') $icon = 'exclamation-triangle-fill';
            ?>
                <div class="flash-popup alert-<?= htmlspecialchars($type) ?>">
                    <svg class="bi flex-shrink-0 me-2" role="img" aria-label="<?= htmlspecialchars(ucfirst($type)) ?>:">
                        <use xlink:href="#<?= $icon ?>"/>
                    </svg>
                    <div><?= htmlspecialchars($message) ?></div>
                    <button type="button" class="btn-close" aria-label="Close">&times;</button>
                </div>
            <?php
            endforeach;
            unset($_SESSION['flash_messages']);
            ?>
        </div>

        <script>
            // Fermer la popup au clic sur le bouton ×
            document.addEventListener("DOMContentLoaded", function() {
                const closeButtons = document.querySelectorAll('.flash-popup .btn-close');
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const popup = this.closest('.flash-popup');
                        if (popup) {
                            // Optionnel: Ajouter un effet de sortie
                            popup.style.opacity = '0';
                            popup.style.transform = 'translateX(100%)';
                            setTimeout(() => popup.remove(), 300);
                        }
                    });
                });
                
                // Optionnel : Auto-remove après 5 secondes
                /*
                setTimeout(() => {
                    const popups = document.querySelectorAll('.flash-popup');
                    popups.forEach(p => {
                        p.style.opacity = '0';
                        p.style.transform = 'translateX(100%)';
                        setTimeout(() => p.remove(), 300);
                    });
                }, 5000);
                */
            });
        </script>
    <?php endif; ?>

</body>
</html>