<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/SettingsController.php';
require_once BASE_PATH . 'includes/flash.php';

// Sécurité
if (!isset($_SESSION['user_id']) || !isset($pdo)) {
    header('Location: /login.php');
    exit;
}

$controller = new SettingsController($pdo);
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $type = $_POST['type'] ?? '';

    // --- MISE A JOUR THEME ---
    if ($type === 'display') {
        $theme = $_POST['theme'] ?? 'light';
        $result = $controller->updateDisplaySettings($userId, $theme);

        if ($result === true) {
            setFlash('success', 'Thème mis à jour avec succès.');
        } else {
            setFlash('danger', $result);
        }
    }

    // --- MISE A JOUR DEVISE ---
    elseif ($type === 'preferences') {
        $currency = $_POST['currency'] ?? 'MAD';
        $result = $controller->updateInputPreferences($userId, $currency);

        if ($result === true) {
            setFlash('success', 'Devise par défaut enregistrée.');
        } else {
            setFlash('danger', $result);
        }
    }
}

// IMPORTANT : On redirige vers le DISPATCHER principal.
// Il renverra automatiquement l'admin vers admin/settings.php, l'employé vers employe/settings.php, etc.
header('Location: ' . BASE_URL . 'views/settings_manager.php');
exit;
?>