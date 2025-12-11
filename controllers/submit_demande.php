<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'includes/flash.php';
require_once BASE_PATH . 'Controllers/DemandeController.php';

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
$demandeController = new DemandeController($pdo);

// Prepare data for creerDemandeAction
$demandeData = [
    'objet_mission' => $_POST['objet_mission'] ?? '',
    'lieu_deplacement' => $_POST['lieu_deplacement'] ?? '',
    'date_depart' => $_POST['date_depart'] ?? '',
    'date_retour' => $_POST['date_retour'] ?? ''
];

$details = [];
// Parse details from POST data and handle file uploads
if (isset($_POST['details']) && is_array($_POST['details'])) {
    // Create uploads directory if it doesn't exist
    $uploadsDir = BASE_PATH . 'uploads/justificatifs/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    
    foreach ($_POST['details'] as $index => $detail) {
        $justificatifPath = '';
        
        // Handle file upload for this detail
        // Form sends files as: details[index][justificatif]
        // PHP reorganizes this into: $_FILES['details']['name'][index]['justificatif']
        
        if (isset($_FILES['details']['name'][$index]['justificatif']) && 
            $_FILES['details']['error'][$index]['justificatif'] === UPLOAD_ERR_OK) {
            
            $fileTmpPath = $_FILES['details']['tmp_name'][$index]['justificatif'];
            $fileName    = $_FILES['details']['name'][$index]['justificatif'];
            $fileSize    = $_FILES['details']['size'][$index]['justificatif'];
            $fileType    = $_FILES['details']['type'][$index]['justificatif'];
            
            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                // Generate unique filename
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueName = uniqid('justif_' . $userId . '_') . '.' . $extension;
                $destination = $uploadsDir . $uniqueName;
                
                // Move uploaded file
                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $justificatifPath = 'uploads/justificatifs/' . $uniqueName;
                }
            }
        }
        
        $details[] = [
            'date_depense' => $detail['date_depense'] ?? date('Y-m-d'),
            'categorie_id' => $detail['categorie_id'] ?? 0,
            'montant' => $detail['montant'] ?? 0,
            'description' => $detail['description'] ?? '',
            'justificatif_path' => $justificatifPath
        ];
    }
}

// Debug logging
error_log("POST details: " . print_r($_POST['details'] ?? 'NOT SET', true));
error_log("Prepared details array: " . print_r($details, true));
error_log("Details count: " . count($details));

// Call the controller method (it handles redirects and session messages internally)
try {
    $demandeController->creerDemandeAction($demandeData, $details, $_FILES);
    // If we reach here, the method didn't redirect (shouldn't happen)
    exit;
} catch (\Exception $e) {
    setFlash('danger', 'Erreur: ' . $e->getMessage());
    header('Location: ' . BASE_URL . 'views/employe/create_demande.php');
    exit;
}
