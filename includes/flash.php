<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Fonction pour ajouter un message
 */
if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

/**
 * Fonction d'affichage (Avec bouton 'X' et sans minuteur)
 */
if (!function_exists('displayFlash')) {
    function displayFlash() {
        
        // 1. Format "Unique" (Profil)
        if (isset($_SESSION['flash_message'])) {
            $msg = $_SESSION['flash_message'];
            $type = $_SESSION['flash_type'];
            
            if ($type === 'danger') {
                $swalType = 'error';
                $bgColor = '#fdecea';
                $textColor = '#721c24';
            } else {
                $swalType = 'success';
                $bgColor = '#e8f5e9';
                $textColor = '#1b5e20';
            }
            
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            
            // CSS pour la marge du haut
            echo "<style>
                div.swal2-container.swal2-top-end {
                    margin-top: 25px !important; 
                }
                /* Couleur du bouton X adaptée au texte */
                .swal2-close {
                    color: $textColor !important; 
                }
            </style>";

            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        position: 'top-end',
                        icon: '$swalType',
                        title: '" . addslashes($msg) . "',
                        
                        // --- MODIFICATIONS ICI ---
                        showConfirmButton: false, // Pas de bouton 'OK' en bas
                        showCloseButton: true,    // Affiche le 'X' pour fermer
                        timer: null,              // Désactive la fermeture automatique
                        // -------------------------
                        
                        toast: true,
                        background: '$bgColor',
                        color: '$textColor'
                    });
                });
            </script>";

            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            unset($_SESSION['flash_messages']);
            return;
        }

        // 2. Format "Tableau" (Login)
        if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            
            echo "<style>
                div.swal2-container.swal2-top-end {
                    margin-top: 25px !important; 
                }
            </style>";
            
            foreach ($_SESSION['flash_messages'] as $flash) {
                $type = ($flash['type'] === 'danger') ? 'error' : $flash['type'];
                
                if ($type === 'error') {
                    $bgColor = '#fdecea';
                    $textColor = '#721c24';
                } else {
                    $bgColor = '#e8f5e9';
                    $textColor = '#1b5e20';
                }

                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            position: 'top-end',
                            icon: '$type',
                            title: '" . addslashes($flash['message']) . "',
                            
                            // --- MODIFICATIONS ICI ---
                            showConfirmButton: false,
                            showCloseButton: true, 
                            timer: null, 
                            // -------------------------

                            toast: true,
                            background: '$bgColor',
                            color: '$textColor'
                        });
                    });
                </script>";
            }
            unset($_SESSION['flash_messages']);
        }
    }
}
?>