<?php
// =============================================================
// ================= EMPLOYE CONTROLLER ========================
// Fichier : controllers/EmployeController.php
// Gère toutes les actions de l'Employé (Dashboard, CRUD Demandes)
// =============================================================

require_once BASE_PATH . 'models/UserModel.php';
require_once BASE_PATH . 'models/DemandeModel.php';
// IMPORTANT : Assurez-vous que ce chemin est correct (includes/file_handler.php)
require_once BASE_PATH . 'includes/file_handler.php'; 
// Assurez-vous d'inclure CategorieModel si vous l'utilisez
// require_once BASE_PATH . 'models/CategorieModel.php'; 

class EmployeController
{
    private PDO $pdo;
    private UserModel $userModel;
    private DemandeModel $demandeModel;
    // private CategorieModel $categorieModel; // Décommentez si nécessaire

    // =================== CONSTRUCTEUR ===========================
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new UserModel($pdo);
        $this->demandeModel = new DemandeModel($pdo);
        // $this->categorieModel = new CategorieModel($pdo); // Décommentez si nécessaire
    }

    // =============================================================
    // =================== DASHBOARD & LISTES ======================
    // =============================================================
    
    /**
     * Récupère les statistiques et les dernières demandes pour le dashboard de l'employé.
     * @param int $employe_id L'ID de l'employé connecté.
     * @return array ['stats' => [...], 'latest' => [...]]
     */
    public function getEmployeDashboardData(int $employe_id): array 
    {
        $stats = $this->demandeModel->getDemandeStatsByEmploye($employe_id);
        $total_reimbursed_amount = $this->demandeModel->getTotalReimbursedAmount($employe_id);
        // Utilise la méthode du modèle pour les 5 dernières (limite 5)
        $latest = $this->demandeModel->getDemandesByEmployeId($employe_id, 5); 
        
        return [
            'stats' => array_merge($stats, ['total_reimbursed_amount' => $total_reimbursed_amount]),
            'latest' => $latest
        ];
    }
    
    /**
     * CORRECTION: Méthode publique pour récupérer la liste complète des demandes.
     * La vue appelle cette méthode pour accéder aux données du modèle.
     * @param int $employeId L'ID de l'employé connecté.
     * @return array Liste complète des demandes de l'employé.
     */
    public function getEmployeDemandesList(int $employeId): array 
    {
        // Utilise la méthode du modèle sans limite de résultats (null)
        return $this->demandeModel->getDemandesByEmployeId($employeId, null);
    }

    // =============================================================
    // =================== LOGIQUE DE SOUMISSION ===================
    // =============================================================
    
    /**
     * Supprime les fichiers uploadés en cas d'erreur (rollback).
     *
     * @param array $files Liste de chemins absolus.
     */
    private function rollbackFiles(array $files): void
    {
        foreach ($files as $filepath) {
            @unlink($filepath);
        }
    }

    /**
     * Traite la soumission d'une nouvelle demande de frais.
     * @param array $postData Données POST du formulaire.
     * @param array $fileData Données FILES des justificatifs.
     * @param int $userId ID de l'employé.
     * @return array Résultat du traitement.
     */
    public function submitNewDemande(array $postData, array $fileData, int $userId): array
    {
     // --- Initialisation Correcte ---
$errors = [];
$demandeData = [];       
$detailsArray = [];      
$filesUploaded = [];

        // --- 1. Validation des données de la mission (mission_frais) ---
        $missionKeys = ['objet_mission', 'lieu_deplacement', 'date_depart', 'date_retour'];

        foreach ($missionKeys as $key) {
            if (empty($postData[$key]) && in_array($key, ['objet_mission', 'date_depart', 'date_retour'])) {
                $errors[] = "Le champ '{$key}' est requis.";
            }
            $demandeData[$key] = filter_var($postData[$key] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        if (
            !empty($demandeData['date_depart']) &&
            !empty($demandeData['date_retour']) &&
            $demandeData['date_depart'] > $demandeData['date_retour']
        ) {
            $errors[] = "La date de retour ne peut pas être antérieure à la date de départ.";
        }
        
        // --- 2. Validation des détails des dépenses (details_frais) ---
        if (empty($postData['details'])) {
            $errors[] = "Au moins une ligne de dépense doit être ajoutée.";
        } else {
            foreach ($postData['details'] as $index => $detail) {
                if (
                    empty($detail['categorie_id']) ||
                    empty($detail['date_depense']) ||
                    empty($detail['montant'])
                ) {
                    $errors[] = "Les champs Catégorie, Date et Montant sont requis pour la ligne #" . ($index + 1) . ".";
                    continue;
                }

                $montant = filter_var($detail['montant'], FILTER_VALIDATE_FLOAT);
                if ($montant === false || $montant <= 0) {
                    $errors[] = "Le montant pour la ligne #" . ($index + 1) . " doit être un nombre positif.";
                    continue;
                }

                $detailsArray[$index] = [
                    'categorie_id' => (int)$detail['categorie_id'],
                    'date_depense' => $detail['date_depense'],
                    'montant' => $montant,
                    'description' => filter_var($detail['description'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                    'justificatif_path' => null
                ];
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'validation_errors' => $errors];
        }
        
        // --- 3. Upload des justificatifs ---
        $uploadDir = BASE_PATH . 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($detailsArray as $index => &$detail) {
            $fileKey = "justificatif_file_" . $index;

            if (isset($fileData[$fileKey]) && $fileData[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $result = handleFileUpload($fileData[$fileKey], $uploadDir);

                if (!empty($result['error'])) {
                    $errors[] = "Erreur d'upload pour la ligne #" . ($index + 1) . ": " . $result['error'];
                } else {
                    // Stocke le chemin relatif à la racine du projet
                    $detail['justificatif_path'] = str_replace(BASE_PATH, '', $result['filepath']);
                    // Stocke le chemin absolu pour le rollback en cas d'échec BDD
                    $filesUploaded[] = $result['filepath'];
                }
            }
        }
        unset($detail);

        if (!empty($errors)) {
            $this->rollbackFiles($filesUploaded);
            return ['success' => false, 'validation_errors' => $errors];
        }

        // --- 4. Enregistrement en BDD (Transaction) ---
        $this->pdo->beginTransaction();

        try {
            $demandeId = $this->demandeModel->createDemande($userId, $demandeData);

            if (!$demandeId) {
                throw new \Exception("Échec de création de la demande."); 
            }

            foreach ($detailsArray as $detail) {
                if (!$this->demandeModel->addDetailFrais($demandeId, $detail)) {
                    throw new \Exception("Échec d'insertion d'un détail de frais.");
                }
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'demande_id' => $demandeId
            ];

        } catch (\Exception $e) { 
            $this->pdo->rollBack();
            error_log("Transaction failed: " . $e->getMessage());
            $this->rollbackFiles($filesUploaded);

            return [
                'success' => false,
                'error_message' => "Erreur critique lors de la soumission de la demande."
            ];
        }
    }
}