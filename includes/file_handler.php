<?php
// Fichier : includes/file_handler.php
// Rôle : Encapsuler les fonctions d'upload et de suppression pour une utilisation orientée objet.
class FileHandler {

    private $upload_dir_absolute;
    private $upload_dir_relative_base; // Stocke le chemin relatif passé au constructeur
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 Mo

    public function __construct(string $relativeUploadDir) {
        // Assure que le chemin relatif ne commence pas par '/' pour éviter des problèmes de concaténation
        $relativeUploadDir = trim($relativeUploadDir, '/');
        
        // Le chemin absolu pour les opérations de fichier
        $this->upload_dir_absolute = BASE_PATH . $relativeUploadDir . '/';
        
        if (!is_dir($this->upload_dir_absolute)) {
            // Crée le dossier si nécessaire avec les permissions 0755
            if (!mkdir($this->upload_dir_absolute, 0755, true)) {
                throw new \Exception("Échec de la création du répertoire d'upload : {$this->upload_dir_absolute}");
            }
        }
        $this->upload_dir_relative_base = $relativeUploadDir . '/';
    }

    /**
     * Gère l'upload d'un fichier à partir de la superglobale $_FILES.
     *
     * @param array $files La superglobale $_FILES.
     * @param string $inputName Le nom de l'input principal ('details').
     * @param array $indexKeys Les clés pour naviguer dans la structure de $_FILES (Ex: ['new-0', 'justificatif']).
     * @return string Le chemin RELATIF du fichier sauvegardé (Ex: uploads/justificatifs/nom_du_fichier.jpg).
     * @throws \Exception Si le téléchargement échoue ou si la validation échoue.
     */
    public function handleUpload(array $files, string $inputName, array $indexKeys): string {
        
        $keyIndex = $indexKeys[0]; // Ex: 'new-0' ou 'existing-123'
        $fieldName = $indexKeys[1]; // Ex: 'justificatif' ou 'justificatif_new'

        // 1. Extraction et vérification de la présence du fichier dans la structure $_FILES
        if (!isset($files[$inputName]['tmp_name'][$keyIndex][$fieldName])) {
             // Cette exception ne devrait pas être levée si le contrôleur a bien vérifié la présence
             throw new \Exception("Le fichier n'est pas présent dans la structure \$files.");
        }

        $filesData = $files[$inputName];
        $tmpName = $filesData['tmp_name'][$keyIndex][$fieldName];
        $fileSize = $filesData['size'][$keyIndex][$fieldName];
        $fileError = $filesData['error'][$keyIndex][$fieldName];

        // --- 1. Vérification des erreurs PHP UPLOAD_ERR ---
        if ($fileError !== UPLOAD_ERR_OK) {
            $phpErrors = [
                UPLOAD_ERR_INI_SIZE   => "La taille du fichier dépasse la limite autorisée par le serveur (php.ini).",
                UPLOAD_ERR_FORM_SIZE  => "La taille du fichier dépasse la limite spécifiée dans le formulaire HTML.",
                UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléchargé.",
                UPLOAD_ERR_NO_FILE    => "Aucun fichier n'a été téléchargé.",
                UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
                UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
                UPLOAD_ERR_EXTENSION  => "Une extension PHP a arrêté l'upload du fichier.",
            ];
            throw new \Exception($phpErrors[$fileError] ?? "Erreur d'upload inconnue ({$fileError}).");
        }
        
        // Sécurité : Vérifier si c'est bien un fichier soumis via HTTP POST
        if (!is_uploaded_file($tmpName)) {
             throw new \Exception("Fichier temporaire invalide ou upload illégal.");
        }

        // --- 2. Vérification du type MIME (sécurité) ---
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf'
        ];
        
        if (!function_exists('mime_content_type')) {
             throw new \Exception("La fonction mime_content_type n'est pas disponible. Vérifiez la configuration PHP.");
        }
        
        $mimeType = mime_content_type($tmpName);
        if (!isset($allowedMimeTypes[$mimeType])) {
            throw new \Exception("Type de fichier non autorisé : {$mimeType}. Seuls JPG, PNG et PDF sont acceptés.");
        }

        // --- 3. Vérification de la taille ---
        if ($fileSize > self::MAX_FILE_SIZE) {
            throw new \Exception("Le fichier est trop volumineux. Max 5 Mo.");
        }

        // --- 4. Création du nom unique et du chemin ---
        $extension = $allowedMimeTypes[$mimeType];
        $fileName = uniqid('justif_', true) . '.' . $extension;

        $destinationPathAbsolute = $this->upload_dir_absolute . $fileName;
        $destinationPathRelative = $this->upload_dir_relative_base . $fileName;
        
        // --- 5. Déplacement du fichier ---
        if (move_uploaded_file($tmpName, $destinationPathAbsolute)) {
            // Retourne le chemin relatif (Ex: uploads/justificatifs/...)
            return $destinationPathRelative; 
        } else {
            throw new \Exception("Échec du déplacement du fichier vers la destination.");
        }
    }
    
    /**
     * Supprime un fichier physique en utilisant son chemin relatif (BDD).
     *
     * @param string $relativePath Le chemin du fichier stocké en BDD (Ex: uploads/justificatifs/...).
     * @return bool True si le fichier est supprimé ou n'existe pas.
     */
    public function deleteFile(string $relativePath): bool {
        if (empty($relativePath)) {
            return true;
        }
        
        // Correction/Renforcement de la sécurité des chemins
        $absolute_path = BASE_PATH . ltrim($relativePath, '/');
        
        // Vérification de sécurité : s'assurer que le fichier est bien dans le répertoire d'upload prévu.
        $real_absolute_path = realpath($absolute_path);
        $real_upload_dir = realpath($this->upload_dir_absolute);

        // Si realpath échoue (fichier inexistant) ou si le fichier n'est pas dans le dossier d'upload
        if (!$real_absolute_path || strpos($real_absolute_path, $real_upload_dir) !== 0) {
            // Si le fichier n'existe pas, on considère que l'opération de suppression est réussie
            return !file_exists($absolute_path);
        }
        
        if (file_exists($absolute_path)) {
            return unlink($absolute_path);
        }
        
        return true;
    }
}