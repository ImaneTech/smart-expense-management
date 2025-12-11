<?php
// Controllers/traitement_demande.php

// 1. Initialisation
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DemandeController.php';

// 2. Vérification de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

// 3. Instanciation du contrôleur
try {
    $controller = new DemandeController($pdo);
    
    // 4. Appel de la méthode de traitement
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->traiterDemandeAction($_POST);
    } else {
        // Accès direct interdit
        header('Location: ' . BASE_URL . 'views/manager/demandes_liste.php');
        exit;
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur système : " . $e->getMessage();
    header('Location: ' . BASE_URL . 'views/manager/demandes_liste.php');
    exit;
}
