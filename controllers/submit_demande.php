<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'includes/flash.php';
require_once BASE_PATH . 'Controllers/Employe/EmployeController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'views/employe/create_demande.php');
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0); 
if ($userId === 0) {
    setFlash('danger', 'Veuillez vous connecter pour soumettre une demande.');
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

global $pdo;
$employeController = new \Controllers\Employe\EmployeController($pdo, $userId, BASE_PATH);

$result = $employeController->submitNewDemande($_POST, $_FILES);

if ($result['success']) {
    setFlash('success', "Votre demande de frais a été soumise avec succès (ID: {$result['demande_id']}).");
    header('Location: ' . BASE_URL . 'views/employe/employe_demandes.php'); 
    exit;
} else {
    if (!empty($result['validation_errors'])) {
        $_SESSION['form_errors'] = $result['validation_errors'];
        setFlash('danger', 'Le formulaire contient des erreurs. Veuillez les corriger.');
    } else {
        $errorMessage = $result['error_message'] ?? 'Erreur critique lors de la soumission.';
        setFlash('danger', $errorMessage);
    }
    header('Location: ' . BASE_URL . 'views/employe/create_demande.php');
    exit;
}
