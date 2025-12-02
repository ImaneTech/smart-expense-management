<?php
// ======================================================
// 1. Charger la configuration
// ======================================================
require_once __DIR__ . '/../config.php';

// ======================================================
// 2. Démarrer la session (si pas déjà faite)
// ======================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ======================================================
// 3. Sécurité : Rediriger si non connecté
// ======================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

// ======================================================
// 4. Charger le header (HTML + assets + sidebar)
// ======================================================
require_once BASE_PATH . 'includes/header.php';

// ======================================================
// 5. Charger le bon tableau de bord selon le rôle
// ======================================================
$role = $_SESSION['role'] ?? 'employe';



switch ($role) {
    case 'admin':
        include BASE_PATH . 'views/admin/dashboard_admin.php';
        break;

    case 'manager':
        include BASE_PATH . 'views/manager/dashboard_manager.php';
        break;

    case 'employe':
    default:
        include BASE_PATH . 'views/employe/dashboard_employe.php';
        break;
}

echo '</div></section>';

// ======================================================
// 6. Footer (scripts JS)
// ======================================================

require_once BASE_PATH . 'includes/footer.php';
