<?php
// Fichier : controllers/submit_demande.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) session_start();

// --- 1. Inclusion des Fichiers de Base ---
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'includes/flash.php'; // Permet d'utiliser setFlash() et d'accéder aux variables de session flash


// --- 2. Vérification de la Méthode et de l'Authentification ---

// Refuser si la méthode n'est pas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'views/employe/create_demande.php');
    exit;
}

// Récupérer l'ID utilisateur et vérifier l'authentification
$userId = (int)($_SESSION['user_id'] ?? 0); 
if ($userId === 0) {
    setFlash('danger', 'Veuillez vous connecter pour soumettre une demande.');
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}


// --- 3. Initialisation du Contrôleur ---
require_once BASE_PATH . 'controllers/EmployeController.php'; 
// $pdo est supposé être défini dans config.php
global $pdo; 
$employeController = new EmployeController($pdo);


// --- 4. Appel de la Logique Métier ---
// La méthode submitNewDemande doit retourner un tableau ['success' => bool, 'demande_id' => int|null, 'validation_errors' => array|null, 'error_message' => string|null]
$result = $employeController->submitNewDemande($_POST, $_FILES, $userId);


// --- 5. Gestion de la Redirection et du Feedback ---
if ($result['success']) {
    // --- SUCCÈS ---
    $demandeId = $result['demande_id'];
    
    // Utilisation de la fonction setFlash pour le message SweetAlert2
    setFlash('success', "Votre demande de frais a été soumise avec succès (ID: $demandeId).");
    
    // Rediriger vers la liste de suivi de l'employé
    header('Location: ' . BASE_URL . 'views/employe/employe_demandes.php'); 
    exit;
} else {
    // --- ERREUR ---
    
    // a) Erreurs de Validation (Si l'employeController a détecté des problèmes de formulaire)
    if (isset($result['validation_errors']) && !empty($result['validation_errors'])) {
        
        // Stocke la liste détaillée des erreurs. Cette liste est lue directement par create_demande.php
        $_SESSION['form_errors'] = $result['validation_errors'];
        
        // Stocke un message général d'erreur de type danger pour la bannière SweetAlert2
        setFlash('danger', 'Le formulaire contient des erreurs. Veuillez les corriger.');

    } else {
        // b) Erreurs Critiques/Génériques (Ex: échec DB, échec du téléchargement)
        $errorMessage = $result['error_message'] ?? 'Erreur critique non spécifiée lors de la soumission.';
        setFlash('danger', $errorMessage);
    }
    
    // Rediriger vers le formulaire pour correction ou re-essai
    header('Location: ' . BASE_URL . 'views/employe/create_demande.php');
    exit;
}
?>