<?php
// views/settings.php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

// 1. Sécurité
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

// 2. Redirection intelligente selon le rôle
$role = $_SESSION['role'];

switch ($role) {
    case 'admin':
        header('Location: ' . BASE_URL . 'views/admin/settings_admin.php');
        break;
        
    case 'manager':
        header('Location: ' . BASE_URL . 'views/manager/settings_manager.php');
        break;
        
    case 'employe':
        header('Location: ' . BASE_URL . 'views/employe/settings_employe.php');
        break;
        
    default:
        header('Location: ' . BASE_URL . 'views/dashboard.php');
        break;
}
exit;
?>