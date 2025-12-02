<?php
// flash.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ajouter un message flash
function setFlash(string $type, string $message)
{
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

// Fonction pour afficher les messages flash
function displayFlash() {
    if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])):
        ?>
        <style>
            .flash-container {
                position: fixed;
                top: 20px;
                left: 20px; /* C'est ici qu'on définit le côté GAUCHE */
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                min-width: 300px;
                max-width: 400px;
            }

            .flash-popup {
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInLeft 0.5s ease-out; /* Animation venant de la gauche */
                background-color: white;
                border-left: 5px solid transparent;
                position: relative;
            }

            /* Couleurs Bootstrap personnalisées pour plus de clarté */
            .alert-success { background-color: #d1e7dd; color: #0f5132; border-color: #198754; }
            .alert-danger  { background-color: #f8d7da; color: #842029; border-color: #dc3545; }
            .alert-warning { background-color: #fff3cd; color: #664d03; border-color: #ffc107; }
            .alert-info    { background-color: #cff4fc; color: #055160; border-color: #0dcaf0; }

            /* Animation d'entrée depuis la gauche */
            @keyframes slideInLeft {
                from { transform: translateX(-100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            /* Bouton de fermeture */
            .flash-popup .btn-close {
                margin-left: auto;
                background: none;
                border: none;
                font-size: 1.2rem;
                cursor: pointer;
                opacity: 0.5;
            }
            .flash-popup .btn-close:hover { opacity: 1; }
        </style>

        <div class="flash-container">
            <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
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
                <div class="flash-popup alert-<?= htmlspecialchars($type) ?> d-flex align-items-center">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="<?= htmlspecialchars(ucfirst($type)) ?>:">
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
            document.addEventListener("DOMContentLoaded", function() {
                // Gestion de la fermeture au clic
                const closeButtons = document.querySelectorAll('.flash-popup .btn-close');
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const popup = this.closest('.flash-popup');
                        if (popup) {
                            popup.style.opacity = '0'; // Petite transition CSS
                            setTimeout(() => popup.remove(), 300);
                        }
                    });
                });

                // (Optionnel) Disparition automatique après 5 secondes
                setTimeout(() => {
                    const popups = document.querySelectorAll('.flash-popup');
                    popups.forEach(p => {
                        p.style.transition = 'opacity 0.5s ease';
                        p.style.opacity = '0';
                        setTimeout(() => p.remove(), 500);
                    });
                }, 5000);
            });
        </script>
<?php
    endif;
}
?>