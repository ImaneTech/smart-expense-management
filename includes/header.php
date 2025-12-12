<?php
// includes/header.php
require_once __DIR__ . '/../config.php'; 

// 1. Vérification que la config est chargée
if (!defined('BASE_URL') || !defined('BASE_PATH')) {
    die("Erreur critique : config.php n'est pas chargé.");
}

// 2. Démarrage session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Sécurité Connexion
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect and STOP execution.
    header("Location: " . BASE_URL . "views/auth/login.php"); 
    exit();
}

// --- If the script reaches here, the user IS logged in ---

// Récupération des données utilisateur de la session
$user_id = (int)$_SESSION['user_id']; // Cast to INT to satisfy UserController argument type
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$role = $_SESSION['role'] ?? 'employe';

// 4. Gestion du Thème Sombre (Lecture du Cookie)
$themeClass = '';
if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') {
    $themeClass = 'dark';
}

// *****************************************************************
// --- LOGIQUE DE DEVISE DYNAMIQUE (CENTRALISÉE) ---
// *****************************************************************

/**
 * Retourne le symbole de devise correspondant au code.
 */
// Définir la fonction dans le contexte global si elle n'existe pas (pour éviter les conflits)
if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol(string $code): string {
        return match (strtoupper($code)) {
            'EUR' => '€',
            'USD' => '$',
            'MAD' => 'Dhs', // Ajout de la devise marocaine
            'GBP' => '£',
            default => '€', // Devise par défaut
        };
    }
}

// Class definition MUST be included before the class is used
require_once BASE_PATH . 'Controllers/UserController.php';
// Assurez-vous que $pdo est disponible globalement (normalement via config.php)

// Instantiate controller and fetch data using the guaranteed $user_id
try {
    // L'instanciation de UserController nécessite l'objet PDO
    $userController = new UserController($pdo); 
    $preferredCurrencyCode = $userController->getPreferredCurrency($user_id); 
} catch (\Exception $e) {
    // En cas d'erreur BDD ou autre, utiliser une valeur par défaut
    $preferredCurrencyCode = 'EUR';
    // Vous pouvez logger l'erreur ici : error_log("Erreur devise: " . $e->getMessage());
}

$currencySymbol = getCurrencySymbol($preferredCurrencyCode);

// *****************************************************************
// --- FIN LOGIQUE DE DEVISE DYNAMIQUE ---
// *****************************************************************
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?php echo $page_title ?? 'GoTrackr'; ?></title>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">


    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
    
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo.png">
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        const CURRENCY_SYMBOL = "<?= $currencySymbol ?>";
    </script>
</head>

<body class="<?= $themeClass ?>">

    <?php include BASE_PATH . 'includes/sidebar.php'; ?>
    
    <?php
    require_once BASE_PATH . 'includes/flash.php';
    displayFlash();
    ?>

    <section class="home-section">
        <div class="main-content">