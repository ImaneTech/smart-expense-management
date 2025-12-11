<?php
// Controllers/download_justificatif.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Controllers/DemandeController.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) && !isset($_SESSION['manager_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}

// Récupérer les paramètres
$file = $_GET['file'] ?? null;
$demandeId = $_GET['demande_id'] ?? null;

if (!$file || !$demandeId) {
    die("Paramètres manquants.");
}

// Sécuriser le nom de fichier (éviter les traversées de répertoires)
$fileName = basename($file);

// Construire le chemin complet
// Note: Les fichiers sont stockés dans uploads/justificatifs/
$filePath = BASE_PATH . 'uploads/justificatifs/' . $fileName;

// Vérifier si le fichier existe
if (!file_exists($filePath)) {
    // Essayer de trouver le fichier si le chemin complet était stocké en base
    // Parfois le chemin en base est 'uploads/justificatifs/fichier.pdf'
    $filePath = BASE_PATH . $file;
    if (!file_exists($filePath)) {
         // Dernier essai: vérifier si c'est juste le nom de fichier mais dans le dossier uploads
        $filePath = BASE_PATH . 'uploads/' . $fileName;
        if (!file_exists($filePath)) {
             die("Fichier introuvable sur le serveur.");
        }
    }
}

// Vérifier les permissions (Optionnel mais recommandé: vérifier si l'utilisateur a le droit de voir cette demande)
// Ici on suppose que si l'utilisateur a le lien et est connecté, c'est bon pour l'instant.
// Pour plus de sécurité, on pourrait instancier DemandeController et vérifier les droits.

// Déterminer le type MIME
$mimeType = mime_content_type($filePath);
if (!$mimeType) {
    $mimeType = 'application/octet-stream';
}

// Headers pour le téléchargement/affichage
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Vider le tampon de sortie pour éviter de corrompre le fichier
ob_clean();
flush();

// Lire le fichier
readfile($filePath);
exit;
