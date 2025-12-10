<?php

namespace Controllers\Employe;

use Exception;
use PDO;
use UserModel;
use Models\Employe\DemandeFraisModel;
use FileHandler;

// --- INCLUSIONS ---
require_once __DIR__ . '/../../Models/Employe/DemandeFraisModel.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../includes/file_handler.php';

class EmployeController
{
    private PDO $pdo;
    private int $employeId;
    private DemandeFraisModel $demandeFraisModel;
    private UserModel $userModel;
    
    // Constante pour la limite du tableau de bord
    private const DASHBOARD_LIMIT = 6;

    public function __construct(\PDO $pdo, int $employeId, string $basePath)
    {
        $this->pdo = $pdo;
        $this->employeId = $employeId;

        // NOTE: \UserModel sans 'use' nécessite l'anti-slash si la classe est dans le namespace global
        $this->userModel = new \UserModel($pdo);
        $this->demandeFraisModel = new DemandeFraisModel($pdo, $basePath);
    }
    
    // =============================================================
    // =================== LOGIQUE DE RÉCUPÉRATION (UNIFIÉE) =====================
    // =============================================================

    /**
     * Récupère la liste des demandes de frais pour l'employé, avec une limite optionnelle.
     * Cette méthode est l'unique point d'accès pour les listes.
     * * @param int|null $limit La limite de lignes (null pour la liste complète).
     * @return array Liste des demandes.
     */
    public function getDemandesByEmploye(?int $limit = null): array
    {
        // Utilise la méthode du modèle qui calcule le montant total
        return $this->demandeFraisModel->getDemandesByEmployeId($this->employeId, $limit);
    }

    /**
     * Récupère les statistiques de demandes (Attente, Validée, Rejetée) pour les cartes.
     * @return array Les statistiques des demandes : ['en_attente' => N1, 'validee' => N2, 'rejetees' => N3]
     */
    public function getDashboardStats(): array
    {
        return $this->demandeFraisModel->getDemandeStatsByEmploye($this->employeId);
    }

    /**
     * Récupère la demande principale et les lignes de détails associées.
     * Utilisée par views/employe/details_demande.php.
     * * @param int $demandeId ID de la demande.
     * @param int $userId ID de l'employé (pour vérifier l'autorisation).
     * @return array|null Tableau contenant 'demande_frais' et 'details_frais', ou null.
     */
    public function getDemandeDetailsById(int $demandeId, int $userId): ?array
    {
        // Appel au modèle pour récupérer les données structurées pour la page de détails
        return $this->demandeFraisModel->getDemandeDetailsWithLines($demandeId, $userId);
    }

    /**
     * Récupère les données complètes pour le tableau de bord (stats + liste récente).
     * @return array Structure de données complète pour le dashboard.
     */
    public function getEmployeDashboardData(): array
    {
        // Utilise la nouvelle méthode pour les stats
        $stats = $this->getDashboardStats();
        $totalReimbursed = $this->demandeFraisModel->getTotalReimbursedAmount($this->employeId);

        // Utilisation de la constante DASHBOARD_LIMIT = 6
        $latest = $this->getDemandesByEmploye(self::DASHBOARD_LIMIT);

        return [
            'stats' => array_merge($stats, ['total_reimbursed_amount' => $totalReimbursed]),
            'latest' => $latest // CLÉ ATTENDUE PAR LE JS POUR LE TABLEAU
        ];
    }
    
    // =============================================================
    // =================== GESTION DES REQUÊTES API =====================
    // =============================================================

    /**
     * Gère toutes les requêtes API entrantes (GET et POST).
     */
    public function handleApiRequest(string $action, array $requestData, array $requestFiles = []): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($action === 'getDashboardData') {
            // Renvoie les stats et la liste récente (LIMIT 6)
            echo json_encode(['success' => true, 'data' => $this->getEmployeDashboardData()]);
        } elseif ($action === 'getDemandes') {
            // Renvoie la liste complète (LIMIT null) pour la page "Mes Demandes de Frais"
            $demandes = $this->getDemandesByEmploye(null);
            echo json_encode(['success' => true, 'demandes' => $demandes]);
        } elseif ($action === 'getRecentDemandes') {
            // Renvoie la liste limitée (LIMIT 6) pour le tableau de bord (si l'API l'appelle séparément)
            $demandes = $this->getDemandesByEmploye(self::DASHBOARD_LIMIT);
            echo json_encode(['success' => true, 'demandes' => $demandes]);

            // Gère l'appel spécifique du JS pour les cartes
        } elseif ($action === 'getDemandeStats') {
            $stats = $this->getDashboardStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
        } else if ($action === 'submitDemande') {
            $result = $this->submitNewDemande($requestData, $requestFiles);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Action invalide']);
        }
    }


    // =============================================================
    // =================== SOUMISSION DEMANDE ======================
    // =============================================================

    // NOTE: La fonction rollbackFiles n'est plus nécessaire si on utilise FileHandler->deleteFile dans le catch.
    
    public function submitNewDemande(array $postData, array $filesData): array
    {
        $errors = [];
        $detailsArray = [];

        // --- Validation formulaire principal ---
        $objet = trim($postData['objet_mission'] ?? '');
        $lieu = trim($postData['lieu_deplacement'] ?? '');
        $dateDepart = $postData['date_depart'] ?? '';
        $dateRetour = $postData['date_retour'] ?? '';

        if (!$objet) $errors[] = "L'objet de la mission est requis.";
        if (!$dateDepart) $errors[] = "La date de départ est requise.";
        if (!$dateRetour) $errors[] = "La date de retour est requise.";

        // --- Validation des détails ---
        if (!isset($postData['details']) || !is_array($postData['details']) || count($postData['details']) === 0) {
            $errors[] = "Au moins une dépense doit être ajoutée.";
        } else {
            foreach ($postData['details'] as $i => $detail) {
                $line = $i + 1;
                if (empty($detail['categorie_id'])) $errors[] = "La Catégorie pour la ligne #$line est requise ou invalide.";
                if (empty($detail['date_depense'])) $errors[] = "La Date de dépense pour la ligne #$line est requise.";
                if (empty($detail['montant'])) $errors[] = "Le Montant pour la ligne #$line est requis.";
                // Note: La validation du Justificatif manquant doit être faite ici si elle est obligatoire.
                
                $detailsArray[] = [
                    'categorie_id' => $detail['categorie_id'] ?? null,
                    'date_depense' => $detail['date_depense'] ?? null,
                    'montant' => $detail['montant'] ?? 0,
                    'description' => $detail['description'] ?? '',
                ];
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'validation_errors' => $errors];
        }

        // ⭐ ÉTAPE 1 : Instanciation du FileHandler
        $uploadDirRelative = 'uploads/justificatifs/';
        try {
            // Utilisation de la classe importée via use \FileHandler;
            $fileHandler = new FileHandler($uploadDirRelative);
        } catch (\Exception $e) {
            // Erreur si le dossier n'est pas accessible ou inexistant
            return ['success' => false, 'error_message' => "Erreur de configuration du dossier d'upload : " . $e->getMessage()];
        }


        // --- Insertion BDD ---
        $uploadedFiles = [];
        try {
            $this->pdo->beginTransaction();

            // 1️⃣ Table principale : demande_frais
            $stmt = $this->pdo->prepare("
                INSERT INTO demande_frais 
                    (user_id, objet_mission, lieu_deplacement, date_depart, date_retour, statut, created_at)
                VALUES (?, ?, ?, ?, ?, 'En attente', NOW())
            ");
            $stmt->execute([$this->employeId, $objet, $lieu, $dateDepart, $dateRetour]);
            $demandeId = $this->pdo->lastInsertId();

            // 2️⃣ Table des détails : details_frais
            $stmtDetail = $this->pdo->prepare("
                INSERT INTO details_frais
                    (demande_id, categorie_id, date_depense, montant, description, justificatif_path)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($detailsArray as $index => $d) {
                $justificatifPath = null;
                $fileKey = (string)$index; 

                if (
                    isset($filesData['details']['tmp_name'][$fileKey]['justificatif'])
                    && $filesData['details']['tmp_name'][$fileKey]['justificatif'] !== ''
                ) {
                    //  ÉTAPE 2 : Appel à la méthode handleUpload de l'objet (POO)
                    try {
                        $justificatifPath = $fileHandler->handleUpload(
                            $filesData, 
                            'details', 
                            [$fileKey, 'justificatif'] // Clés pour naviguer
                        );
                        $uploadedFiles[] = $justificatifPath; // Chemin relatif pour le rollback

                    } catch (\Exception $e) {
                        // Si l'upload échoue, on annule la transaction et on lève l'exception
                        throw new \Exception("Erreur upload ligne #".($index + 1)." : " . $e->getMessage());
                    }
                }

                $stmtDetail->execute([
                    $demandeId,
                    $d['categorie_id'],
                    $d['date_depense'],
                    $d['montant'],
                    $d['description'],
                    $justificatifPath
                ]);
            }


            $this->pdo->commit();
            return ['success' => true, 'demande_id' => $demandeId];
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            
            // ⭐ ÉTAPE 3 : Utilisation du FileHandler pour la suppression sécurisée en cas d'échec BDD
            if (isset($fileHandler)) {
                foreach ($uploadedFiles as $filepath) {
                    $fileHandler->deleteFile($filepath);
                }
            }
            
            return ['success' => false, 'error_message' => "Erreur BDD: " . $e->getMessage()];
        }
    }


    /**
 * Supprime une demande de frais et ses fichiers justificatifs si elle est en "En attente"
 * @param int $demandeId ID de la demande
 * @return bool True si suppression réussie, False sinon
 */
public function deleteDemande(int $demandeId): bool
{
    try {
        // 1️⃣ Vérifier que la demande appartient à l'employé et est "En attente"
        $stmt = $this->pdo->prepare("
            SELECT df.id AS demande_id, df.statut, d.justificatif_path
            FROM demande_frais df
            LEFT JOIN details_frais d ON d.demande_id = df.id
            WHERE df.id = :id AND df.user_id = :user
        ");
        $stmt->execute([
            'id' => $demandeId,
            'user' => $this->employeId
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) return false; // Demande introuvable
        if ($rows[0]['statut'] !== 'En attente') return false; // Statut interdit

        // 2️⃣ Collecter tous les fichiers justificatifs pour suppression
        $filesToDelete = [];
        foreach ($rows as $row) {
            if (!empty($row['justificatif_path'])) {
                $filesToDelete[] = $row['justificatif_path'];
            }
        }

        // 3️⃣ Supprimer la demande et les détails dans une transaction
        $this->pdo->beginTransaction();

        // Supprimer les détails
        $stmtDelDetails = $this->pdo->prepare("DELETE FROM details_frais WHERE demande_id = :id");
        $stmtDelDetails->execute(['id' => $demandeId]);

        // Supprimer la demande
        $stmtDelDemande = $this->pdo->prepare("DELETE FROM demande_frais WHERE id = :id AND user_id = :user");
        $stmtDelDemande->execute(['id' => $demandeId, 'user' => $this->employeId]);

        $this->pdo->commit();

        // 4️⃣ Supprimer physiquement les fichiers après commit
        $fileHandler = new FileHandler('uploads/justificatifs/'); // adapte si nécessaire
        foreach ($filesToDelete as $f) {
            $fileHandler->deleteFile($f);
        }

        return true;

    } catch (Exception $e) {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        return false;
    }
}

}