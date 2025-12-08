<?php

namespace Models\Employe;

// AJOUT DE L'ANTISLASH DANS L'IMPORTATION pour référencer la classe globale
use \PDO; 
use \Exception;

class DemandeFraisModel {
    private PDO $pdo; // Correct grâce à use \PDO
    private string $uploadDir;

    // Constructeur : reçoit l'objet PDO et le chemin d'upload
    public function __construct(PDO $pdo, string $basePath) {
        $this->pdo = $pdo;
        // Définir le répertoire d'upload (ajustez le chemin si nécessaire)
        $this->uploadDir = $basePath . 'public/uploads/justificatifs/'; 

        // S'assurer que le dossier d'upload existe
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /**
     * Récupère toutes les demandes de frais pour un utilisateur donné.
     */
    public function getAllUserDemandes(int $userId): array {
        $sql = "SELECT 
                    d.*,
                    (SELECT SUM(df.montant) 
                     FROM details_frais df 
                     WHERE df.demande_id = d.id) as montant_total,
                    (SELECT df.justificatif_path 
                     FROM details_frais df 
                     WHERE df.demande_id = d.id 
                     LIMIT 1) as justificatif
                FROM demande_frais d 
                WHERE d.user_id = :user_id 
                ORDER BY d.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les N dernières demandes de frais pour un utilisateur donné.
     */
    public function getUserRecentDemandes(int $userId, int $limit = 3): array {
        $sql = "SELECT 
                    d.*,
                    (SELECT SUM(df.montant) FROM details_frais df WHERE df.demande_id = d.id) as montant_total,
                    (SELECT df.justificatif_path FROM details_frais df WHERE df.demande_id = d.id LIMIT 1) as justificatif
                FROM demande_frais d 
                WHERE d.user_id = :user_id 
                ORDER BY d.created_at DESC 
                LIMIT :limite";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule les statistiques de statut (En attente, Validées, Rejetées) pour un utilisateur.
     */
    public function getUserStats(int $userId): array {
        $sql = "SELECT 
                    SUM(CASE WHEN statut = 'En attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut IN ('Validée Manager', 'Approuvée Compta', 'Payée') THEN 1 ELSE 0 END) as validees,
                    SUM(CASE WHEN statut = 'Rejetée Manager' THEN 1 ELSE 0 END) as rejetees
                FROM demande_frais 
                WHERE user_id = :user_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $stats = $stmt->fetch() ?: [];

        // Conversion en entier pour garantir le format JSON correct
        return [
            'en_attente' => (int)($stats['en_attente'] ?? 0),
            'validees' => (int)($stats['validees'] ?? 0),
            'rejetees' => (int)($stats['rejetees'] ?? 0),
        ];
    }
    
    /**
     * Logique d'ajout d'une nouvelle demande (add_demande).
     * Retourne l'ID de la demande créée.
     */
    public function addDemande(int $userId, array $data, array $file): int {
        $justificatif_path = $this->handleFileUpload($file);

        // --- 3. Transaction d'Insertion ---
        $this->pdo->beginTransaction();
        
        try {
            // Insérer la demande de frais (Header)
            $sqlDemande = "INSERT INTO demande_frais 
                            (user_id, objet_mission, lieu_deplacement, date_depart, date_retour, statut, created_at) 
                            VALUES (?, ?, ?, ?, ?, 'En attente', NOW())";
            
            $stmtDemande = $this->pdo->prepare($sqlDemande);
            $stmtDemande->execute([
                $userId,
                $data['objet_mission'],
                $data['lieu_deplacement'],
                $data['date_depart'],
                $data['date_retour']
            ]);
            
            $demandeId = $this->pdo->lastInsertId();
            
            // Insérer le détail des frais 
            $sqlDetail = "INSERT INTO details_frais 
                            (demande_id, categorie_id, montant, description, justificatif_path) 
                            VALUES (?, ?, ?, ?, ?)";
            
            $stmtDetail = $this->pdo->prepare($sqlDetail);
            $stmtDetail->execute([
                $demandeId,
                $data['categorie_id'], 
                floatval($data['montant']),
                $data['description'] ?? null,
                $justificatif_path
            ]);
            
            $this->pdo->commit();
            return $demandeId;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            
            // Supprimer le fichier uploadé si la transaction échoue
            if ($justificatif_path && file_exists($this->uploadDir . $justificatif_path)) {
                unlink($this->uploadDir . $justificatif_path);
            }
            throw $e;
        }
    }

    /**
     * Logique interne pour la gestion de l'upload du fichier justificatif.
     */
    private function handleFileUpload(array $file): ?string {
        if (!isset($file['justificatif']) || $file['justificatif']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Vérification de la taille (5MB max)
        if ($file['justificatif']['size'] > 5 * 1024 * 1024) {
            throw new Exception('Le fichier ne doit pas dépasser 5MB');
        }
        
        // Vérification de l'extension
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file['justificatif']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            throw new Exception('Format de fichier non autorisé');
        }

        // Générer un nom unique
        $file_name = 'justif_' . uniqid() . '_' . time() . '.' . $file_ext;
        $file_path = $this->uploadDir . $file_name;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['justificatif']['tmp_name'], $file_path)) {
            return $file_name;
        } else {
            throw new Exception('Erreur lors de l\'upload du fichier');
        }
    }
}