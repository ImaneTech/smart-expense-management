<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test 1 : Est-ce qu'on trouve la config ?
if (!file_exists(__DIR__ . '/../config.php')) {
    die("ERREUR CRITIQUE : Je ne trouve pas config.php dans " . realpath(__DIR__ . '/../'));
}
require_once __DIR__ . '/../config.php'; 

// Test 2 : Est-ce que BASE_PATH est défini ?
if (!defined('BASE_PATH')) {
    die("ERREUR : BASE_PATH n'est pas défini dans config.php");
}

// Test 3 : Est-ce qu'on trouve le header ?
if (!file_exists(BASE_PATH . 'includes/header.php')) {
    die("ERREUR : Je ne trouve pas header.php ici : " . BASE_PATH . 'includes/header.php');
}
require_once BASE_PATH . 'includes/header.php'; 
// ...




// 1. CHARGEMENT CONFIG (C'est le seul endroit avec ../)
require_once __DIR__ . '/../config.php'; 

// 2. HEADER (Session démarre ici)
require_once BASE_PATH . 'includes/header.php'; 

// 3. RECUPERATION ROLE
$role = $_SESSION['role'] ?? 'employe';

// 4. ROUTAGE DYNAMIQUE
// On utilise BASE_PATH pour inclure les vues "filles"
switch ($role) {
    case 'admin':
        include BASE_PATH . 'views/admin/dashboard_admin.php';
        break;

    case 'manager':
        // C'est ici que dashboard_manager.php sera injecté
        include BASE_PATH . 'views/manager/dashboard_manager.php';
        break;

    case 'employe':
    default:
        include BASE_PATH . 'views/employe/dashboard_employe.php';
        break;
}

// 5. FOOTER
require_once BASE_PATH . 'includes/footer.php'; 
?>