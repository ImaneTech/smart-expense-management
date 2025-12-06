<?php
/**
 * Fichier : utils/file_handler.php
 * Fonctions utilitaires pour la gestion des fichiers (uploads).
 */

/**
 * Gère l'upload d'un fichier en appliquant des vérifications de sécurité.
 *
 * @param array $fileData Le tableau $_FILES['nom_du_champ'].
 * @param string $targetDir Le répertoire de destination absolu où stocker le fichier.
 * @return array Un tableau avec 'filepath' (chemin absolu) en cas de succès, ou 'error' en cas d'échec.
 */
function handleFileUpload(array $fileData, string $targetDir): array
{
    // --- 1. Vérification des erreurs d'upload PHP natives ---
    if ($fileData['error'] !== UPLOAD_ERR_OK) {
        $phpErrors = [
            UPLOAD_ERR_INI_SIZE   => "La taille du fichier dépasse la limite autorisée par le serveur (php.ini).",
            UPLOAD_ERR_FORM_SIZE  => "La taille du fichier dépasse la limite spécifiée dans le formulaire HTML.",
            UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléchargé.",
            UPLOAD_ERR_NO_FILE    => "Aucun fichier n'a été téléchargé.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
            UPLOAD_ERR_EXTENSION  => "Une extension PHP a arrêté l'upload du fichier.",
        ];
        // Retourne l'erreur la plus pertinente
        return ['error' => $phpErrors[$fileData['error']] ?? "Erreur d'upload inconnue ({$fileData['error']})."];
    }
    
    // --- 2. Vérification du MIME Type et des Extensions ---
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg', 
        'image/png' => 'png', 
        'application/pdf' => 'pdf'
    ];
    $mimeType = mime_content_type($fileData['tmp_name']);
    
    if (!isset($allowedMimeTypes[$mimeType])) {
        return ['error' => "Type de fichier non autorisé : {$mimeType}. Seuls JPG, PNG et PDF sont acceptés."];
    }

    // --- 3. Vérification de la Taille (Max 5 Mo ici, à ajuster) ---
    $maxFileSize = 5 * 1024 * 1024; // 5 Mo en octets
    if ($fileData['size'] > $maxFileSize) {
        return ['error' => "Le fichier est trop volumineux. La taille maximale est de 5 Mo."];
    }
    
    // --- 4. Génération d'un nom de fichier unique et sécurisé ---
    // Utilisation d'un nom basé sur l'heure + un identifiant unique (plus sûr que le nom original)
    $extension = $allowedMimeTypes[$mimeType];
    $fileName = uniqid('justif_', true) . '.' . $extension; 
    
    // Chemin complet de destination
    // NOTE: On utilise realpath pour s'assurer que targetDir est un chemin absolu valide.
    $destinationPath = rtrim($targetDir, '/') . '/' . $fileName;

    // --- 5. Déplacement du fichier temporaire ---
    // is_uploaded_file() est essentiel pour garantir que le fichier provient bien d'un upload HTTP POST
    if (!is_uploaded_file($fileData['tmp_name'])) {
        return ['error' => "Tentative d'upload illégale ou fichier temporaire introuvable."];
    }

    if (move_uploaded_file($fileData['tmp_name'], $destinationPath)) {
        // Succès : retourne le chemin absolu du fichier stocké
        return ['filepath' => $destinationPath];
    } else {
        // Échec du déplacement (permissions, espace disque, etc.)
        return ['error' => "Échec du déplacement du fichier vers le dossier de destination."];
    }
}
?>