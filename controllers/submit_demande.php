<?php
// Fichier : controllers/submit_demande.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();

// Le fichier de configuration DOIT être inclus en premier
require_once __DIR__ . '/../config.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'views/employe/create_demande.php');
    exit;
}

// --- 2. Initialisation du Contrôleur ---
require_once BASE_PATH . 'controllers/EmployeController.php'; 
// $pdo est supposé être défini dans config.php !
$employeController = new EmployeController($pdo);
$userId = (int)$_SESSION['user_id'];

// --- 3. Appel de la Logique Métier ---
$result = $employeController->submitNewDemande($_POST, $_FILES, $userId);

// --- 4. Gestion de la Redirection ---
if ($result['success']) {
    $demandeId = $result['demande_id'];
    $_SESSION['feedback_message'] = "Votre demande a été soumise avec succès (ID: $demandeId).";
    $_SESSION['feedback_type'] = 'success';
    // Rediriger vers la liste de suivi de l'employé
    header('Location: ' . BASE_URL . 'views/employe/demande_liste.php'); 
    exit;
} else {
    // Gestion des erreurs
    if (isset($result['validation_errors'])) {
        $_SESSION['form_errors'] = $result['validation_errors'];
        $feedback = 'Erreur(s) de validation du formulaire.';
    } else {
        $feedback = $result['error_message'] ?? 'Erreur critique lors de la soumission.';
        $_SESSION['feedback_message'] = $feedback;
        $_SESSION['feedback_type'] = 'danger';
    }
    
    // Rediriger vers le formulaire de création
    header('Location: ' . BASE_URL . 'views/employe/create_demande.php');
    exit;
}
?>