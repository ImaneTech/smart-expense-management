<?php
// Fichier : controllers/update_demande.php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'Controllers/DemandeController.php';
require_once BASE_PATH . 'includes/flash.php';
// L'inclusion de la classe FileHandler
require_once BASE_PATH . 'includes/file_handler.php'; 

// Contrôle d'accès (Employé uniquement)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employe' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit();
}

$demande_id = (int)($_POST['demande_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];
$errors = [];

// 1. Validation de base des données de la demande
$objet_mission = trim($_POST['objet_mission'] ?? '');
$lieu_deplacement = trim($_POST['lieu_deplacement'] ?? '');
$date_depart = $_POST['date_depart'] ?? '';
$date_retour = $_POST['date_retour'] ?? '';
$details_data = $_POST['details'] ?? [];
$details_to_delete = $_POST['details_to_delete'] ?? [];

if (empty($demande_id)) {
    $errors[] = "ID de demande manquant.";
}
if (empty($objet_mission)) {
    $errors[] = "L'objet de la mission est obligatoire.";
}
if (empty($date_depart) || empty($date_retour)) {
    $errors[] = "Les dates de départ et de retour sont obligatoires.";
}
if (strtotime($date_depart) > strtotime($date_retour)) {
    $errors[] = "La date de départ ne peut pas être après la date de retour.";
}
if (empty($details_data) && empty($details_to_delete)) {
     $errors[] = "La demande doit contenir au moins une dépense.";
}


// 2. Validation et traitement des détails (fichiers inclus)
$processed_details = [];
$upload_dir_relative = 'uploads/justificatifs/';
$fileHandler = new FileHandler($upload_dir_relative); 


foreach ($details_data as $key => $detail) {
    // Validation des champs requis pour chaque détail
    if (empty($detail['date_depense']) || empty($detail['categorie_id']) || empty($detail['montant'])) {
        $errors[] = "Tous les champs des dépenses (Date, Catégorie, Montant) sont obligatoires.";
        continue;
    }
    if ((float)$detail['montant'] <= 0) {
        $errors[] = "Le montant de la dépense doit être supérieur à zéro.";
        continue;
    }

    $justificatif_path = $detail['justificatif_path_old'] ?? null; // Chemin existant
    $is_new_file_uploaded = false;
    
    $file_keys = []; // Clés pour le handler.
    $uploaded_file_name = ''; // Pour vérifier la présence du fichier

    if (isset($detail['id_detail_frais'])) {
        // C'est une ligne existante : on cherche un 'justificatif_new'
        $file_keys = [$key, 'justificatif_new'];
        $uploaded_file_name = $_FILES['details']['name'][$key]['justificatif_new'] ?? null;
    } else {
        // C'est une nouvelle ligne : on cherche un 'justificatif'
        $file_keys = [$key, 'justificatif'];
        $uploaded_file_name = $_FILES['details']['name'][$key]['justificatif'] ?? null;
    }
    
    // Tentative de téléchargement du nouveau fichier
    if (!empty($uploaded_file_name)) {
        try {
            $new_file_path = $fileHandler->handleUpload($_FILES, "details", $file_keys);
            
            // Si l'upload réussit, on met à jour le chemin
            $justificatif_path = $new_file_path; 
            $is_new_file_uploaded = true;
        } catch (Exception $e) {
            $errors[] = "Erreur de téléchargement pour le justificatif : " . $e->getMessage();
            continue;
        }
    }
    
    // Validation: justificatif is only required for NEW details that don't have a file uploaded
    if (!isset($detail['id_detail_frais'])) {
        // This is a new detail - justificatif is required
        if (!$is_new_file_uploaded && empty($justificatif_path)) {
            $errors[] = "Le justificatif est obligatoire pour la dépense.";
            continue;
        }
    }
    // For existing details, justificatif_path_old will be preserved if no new file is uploaded


    $processed_details[] = [
        'id_detail_frais' => $detail['id_detail_frais'] ?? null, // Null si nouveau détail
        'date_depense' => $detail['date_depense'],
        'id_categorie_frais' => (int)$detail['categorie_id'],
        'montant' => (float)$detail['montant'],
        'description' => $detail['description'] ?? '',
        'justificatif_path' => $justificatif_path, // Chemin final (ancien ou nouveau)
        'is_new_file' => $is_new_file_uploaded, // Info pour le controller de domaine (supprimer l'ancien fichier)
    ];
}

// 3. Traitement
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header('Location: ' . BASE_URL . 'views/employe/edit_demande.php?id=' . $demande_id);
    exit();
}

try {
    // Note : La connexion PDO doit être initialisée (on suppose qu'elle vient de config.php)
    $demandeController = new DemandeController($pdo); 
    
    // Les données principales de la demande
    $demande_data = [
        'objet_mission' => $objet_mission,
        'lieu_deplacement' => $lieu_deplacement,
        'date_depart' => $date_depart,
        'date_retour' => $date_retour,
    ];
    
    // Passer l'instance de FileHandler à updateDemande 
    // pour que le contrôleur de domaine puisse gérer la suppression des anciens fichiers.
    $success = $demandeController->updateDemande($demande_id, $user_id, $demande_data, $processed_details, $details_to_delete, $fileHandler);

    if ($success) {
        setFlash('success', '✅ La demande de frais a été **mise à jour** avec succès et soumise pour validation !');
        header('Location: ' . BASE_URL . 'views/employe/details_demande.php?id=' . $demande_id);
        exit();
    } else {
        // En cas de succès = false sans exception levée (ce qui ne devrait pas arriver avec la logique actuelle)
        throw new Exception("Erreur inconnue lors de la mise à jour de la demande.");
    }

} catch (Exception $e) {
    setFlash('danger', '❌ Échec de la mise à jour : ' . $e->getMessage());
    header('Location: ' . BASE_URL . 'views/employe/edit_demande.php?id=' . $demande_id);
    exit();
}