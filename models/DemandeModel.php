<?php
// models/DemandeModel.php (Source unique de la logique de base de données)

class DemandeModel {
    
    private $pdo;
    private $userTable = 'users';
    private $demandeTable = 'demande_frais';
    private $detailsTable = 'details_frais';
    private $historyTable = 'historique_statuts'; // Nouvelle table pour l'historique

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* ============================================================
     * SECTION 1: DASHBOARD STATISTICS (KPIs)
     * ============================================================ */
    public function getDashboardStats(int $managerId): array {
        $stats = [];

        // Note: La jointure par u.manager_id est correcte pour filtrer par équipe.
        $baseSqlFilter = "
            FROM {$this->demandeTable} d
            JOIN {$this->userTable} u ON d.user_id = u.id
            WHERE u.manager_id = :managerId
        ";
        
        // Les statistiques restent inchangées, elles sont déjà optimisées.
        // 1. Count Pending
        $sql = "SELECT COUNT(*) " . $baseSqlFilter . " AND d.statut = 'En attente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['pending'] = (int) $stmt->fetchColumn(); 

        // 2. Count Validated/Approved (statuts finaux Manager/Admin)
        $sql = "SELECT COUNT(*) " . $baseSqlFilter . " AND d.statut IN ('Validée Manager', 'Approuvée Compta', 'Payée')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['validated'] = (int) $stmt->fetchColumn();

        // 3. Count Rejected
        $sql = "SELECT COUNT(*) " . $baseSqlFilter . " AND d.statut = 'Rejetée Manager'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['rejected'] = (int) $stmt->fetchColumn();

        // 4. Total Amount Pending
        $sql = "SELECT SUM(df.montant) 
                 FROM {$this->detailsTable} df
                 JOIN {$this->demandeTable} d ON df.demande_id = d.id
                 JOIN {$this->userTable} u ON d.user_id = u.id
                 WHERE d.statut = 'En attente' AND u.manager_id = :managerId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        // Utiliser ?? 0.00 pour s'assurer d'avoir un float en cas de NULL
        $stats['amount_pending'] = (float) ($stmt->fetchColumn() ?? 0.00); 
        
        // 5. Team Size
        $sql = "SELECT COUNT(*) FROM {$this->userTable} WHERE manager_id = :managerId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['team_size'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    //--------------------------------------------------------------------------
    
    /* ============================================================
     * SECTION 2: READ OPERATIONS
     * ============================================================ */

    /**
     * Récupère toutes les demandes gérées par un manager, filtrées par statut.
     * Inclut le total calculé par Sous-Requête.
     */
    public function getDemandesByStatus(int $managerId, string $statut, ?int $limit = null): array {
        $sql = "SELECT d.*, u.first_name, u.last_name, u.email,
                (SELECT SUM(montant) FROM {$this->detailsTable} WHERE demande_id = d.id) as total_calcule
                FROM {$this->demandeTable} d
                JOIN {$this->userTable} u ON d.user_id = u.id
                WHERE d.statut = ? AND u.manager_id = ?
                ORDER BY d.date_depart DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit); 
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut, $managerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les détails d'une demande spécifique, en vérifiant les droits du manager.
     */
    public function getDemandeById(int $id, int $managerId): ?array { 
        $sql = "SELECT 
                    d.*,
                    d.statut AS current_statut, 
                    u.first_name, 
                    u.last_name, 
                    u.email 
                FROM {$this->demandeTable} d 
                JOIN {$this->userTable} u ON d.user_id = u.id 
                WHERE d.id = ? AND u.manager_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $managerId]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si la demande n'existe pas ou le manager n'est pas responsable
        return $demande ?: null; 
    }
    
    /**
     * Récupère les lignes de frais (détails) pour une demande donnée.
     */
    public function getDetailsFrais(int $demandeId): array {
        $sql = "SELECT df.*, cf.nom AS nom_categorie 
                FROM {$this->detailsTable} df 
                JOIN categories_frais cf ON df.categorie_id = cf.id
                WHERE df.demande_id = ?";
        
        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute([$demandeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les demandes gérées par un manager, avec filtre optionnel par statut.
     * (Méthode de liste complète, utile pour la page "Toutes les demandes").
     */
    public function getAllDemandesForManager(int $managerId, ?string $statut = null): array {
        $sql = "SELECT d.*, u.first_name, u.last_name, u.email,
                (SELECT SUM(montant) FROM {$this->detailsTable} WHERE demande_id = d.id) as total_calcule
                FROM {$this->demandeTable} d
                JOIN {$this->userTable} u ON d.user_id = u.id
                WHERE u.manager_id = :managerId";
        
        $params = [':managerId' => $managerId];

        if ($statut !== null) {
            $sql .= " AND d.statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $sql .= " ORDER BY d.date_depart DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //--------------------------------------------------------------------------

    /* ============================================================
     * SECTION 3: WRITE OPERATIONS
     * ============================================================ */

    /**
     * Met à jour le statut d'une demande ET enregistre l'action dans l'historique.
     * Cette méthode doit être appelée dans une TRANSACTION si possible.
     */
    public function updateStatut(int $id, string $nouveauStatut, int $managerId, int $userIdAction, ?string $motif = null): bool {
        
        // 1. Récupérer l'ancien statut (nécessaire pour l'historique)
        $ancienStatut = $this->getStatutActuel($id, $managerId);
        if (!$ancienStatut) {
            return false; // Demande non trouvée ou manager non responsable
        }

        // 2. Mettre à jour la demande principale (demande_frais)
        $sqlDemande = "UPDATE {$this->demandeTable} d
                       JOIN {$this->userTable} u ON d.user_id = u.id
                       SET d.statut = ?, 
                           d.commentaire_manager = ?, 
                           d.date_traitement = NOW(), 
                           d.manager_id_validation = ?
                       WHERE d.id = ? AND u.manager_id = ?";
        
        $stmtDemande = $this->pdo->prepare($sqlDemande);
        
        $successDemande = $stmtDemande->execute([$nouveauStatut, $motif, $managerId, $id, $managerId]);
        
        if (!$successDemande) {
            return false;
        }

        // 3. Enregistrer l'action dans la table historique_statuts
        $sqlHistory = "INSERT INTO {$this->historyTable} 
                       (demande_id, user_id, ancien_statut, nouveau_statut, commentaire)
                       VALUES (?, ?, ?, ?, ?)";
        
        $stmtHistory = $this->pdo->prepare($sqlHistory);
        $successHistory = $stmtHistory->execute([$id, $userIdAction, $ancienStatut, $nouveauStatut, $motif]);

        return $successHistory; // Retourne TRUE si les deux opérations (UPDATE et INSERT) réussissent.
    }
    
    /**
     * Récupère uniquement le statut actuel d'une demande pour l'historique.
     */
    private function getStatutActuel(int $id, int $managerId): ?string {
        $sql = "SELECT d.statut 
                FROM {$this->demandeTable} d 
                JOIN {$this->userTable} u ON d.user_id = u.id
                WHERE d.id = ? AND u.manager_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $managerId]);
        $statut = $stmt->fetchColumn();
        
        return $statut ?: null;
    }
    
    //-------------------------------------------
}
?>