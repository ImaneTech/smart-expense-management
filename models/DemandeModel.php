<?php
// models/DemandeModel.php (CORRIGÉ)

class DemandeModel {
    
    private $pdo;
    private $userTable = 'users';
    private $demandeTable = 'demande_frais';
    private $detailsTable = 'details_frais';
    private $historyTable = 'historique_statuts'; 

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* ============================================================
     * SECTION 1: DASHBOARD STATISTICS (KPIs) - OK
     * ============================================================ */
    public function getDashboardStats(int $managerId): array {
        $stats = [];
        $baseSqlFilter = "
             FROM {$this->demandeTable} d
             JOIN {$this->userTable} u ON d.user_id = u.id
             WHERE u.manager_id = :managerId
        ";
        
        // 1. Count Pending
        $sql = "SELECT COUNT(*) " . $baseSqlFilter . " AND d.statut = 'En attente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['pending'] = (int) $stmt->fetchColumn(); 

        // 2. Count Validated/Approved
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
     * SECTION 2: READ OPERATIONS (AJOUTS: getDemandesByStatuses, rechercheAvancee)
     * ============================================================ */

    /**
     * Récupère toutes les demandes gérées par un manager, filtrées par statut.
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
     * NOUVEAU: Récupère les demandes d'un manager qui correspondent à une liste de statuts (Historique).
     * CORRIGÉ: ORDER BY
     */
    public function getDemandesByStatuses(int $managerId, array $statuses): array {
        if (empty($statuses)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));

        $sql = "
            SELECT 
                d.id, d.user_id, d.objet_mission, d.date_depart, d.statut, 
                d.date_traitement AS date_validation,
                u.first_name, u.last_name, u.email,
                (SELECT SUM(l.montant) FROM {$this->detailsTable} l WHERE l.demande_id = d.id) AS total_calcule
            FROM 
                {$this->demandeTable} d
            JOIN 
                {$this->userTable} u ON d.user_id = u.id
            WHERE 
                u.manager_id = :manager_id
            AND 
                d.statut IN ({$placeholders})
            ORDER BY 
                d.date_traitement DESC, d.created_at DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
            $i = 1;
            foreach ($statuses as $statut) {
                $stmt->bindValue($i++, $statut, PDO::PARAM_STR); 
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans getDemandesByStatuses : " . $e->getMessage());
            return [];
        }
    }

    /**
     * NOUVEAU: Exécute une recherche avancée avec des filtres optionnels.
     * CORRIGÉ: Structure de la requête SQL et ORDER BY
     */
    public function rechercheAvancee(
        int $managerId, 
        int $employeId = 0, 
        string $statut = '', 
        string $dateDebut = '', 
        string $dateFin = ''
    ): array {
        
        $sql = "
            SELECT 
                d.*,
                u.first_name, u.last_name, u.email, d.lieu_deplacement,
                (SELECT SUM(l.montant) FROM {$this->detailsTable} l WHERE l.demande_id = d.id) AS total_calcule
            FROM 
                {$this->demandeTable} d
            JOIN 
                {$this->userTable} u ON d.user_id = u.id
            WHERE 
                u.manager_id = :managerId
        ";

        $params = [':managerId' => $managerId];

        // Filtre par employé (UserID)
        if ($employeId > 0) {
            $sql .= " AND d.user_id = :employeId";
            $params[':employeId'] = $employeId;
        }
        
        // Filtre par statut
        if (!empty($statut)) {
            $sql .= " AND d.statut = :statut";
            $params[':statut'] = $statut;
        }

        // Filtre par date de début (>= date_debut)
        if (!empty($dateDebut)) {
            $sql .= " AND d.date_depart >= :dateDebut";
            $params[':dateDebut'] = $dateDebut;
        }

        // Filtre par date de fin (<= date_fin)
        if (!empty($dateFin)) {
            $sql .= " AND d.date_depart <= :dateFin";
            $params[':dateFin'] = $dateFin;
        }
        
        // ORDER BY final avec la bonne colonne
        $sql .= " ORDER BY d.date_depart DESC, d.created_at DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erreur PDO dans rechercheAvancee : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les détails d'une demande spécifique, en vérifiant les droits du manager.
     */
    public function getDemandeById(int $id, int $managerId): ?array { 
        $sql = "SELECT 
                    d.*,
                    u.first_name, 
                    u.last_name, 
                    u.email 
                FROM {$this->demandeTable} d 
                JOIN {$this->userTable} u ON d.user_id = u.id 
                WHERE d.id = ? AND u.manager_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $managerId]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        
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
     * SECTION 3: WRITE OPERATIONS (CORRECTION: Ajout des transactions PDO)
     * ============================================================ */

    /**
     * Met à jour le statut d'une demande ET enregistre l'action dans l'historique.
     * Utilisation d'une TRANSACTION pour garantir l'atomicité.
     */
    public function updateStatut(int $id, string $nouveauStatut, int $managerId, int $userIdAction, ?string $motif = null): bool {
        
        $this->pdo->beginTransaction(); // ⬅️ Démarrer la transaction

        try {
            // 1. Récupérer l'ancien statut
            $ancienStatut = $this->getStatutActuel($id, $managerId);
            if (!$ancienStatut) {
                $this->pdo->rollBack(); 
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
            
            // Exécuter et vérifier l'impact (doit affecter au moins 1 ligne)
            if (!$stmtDemande->execute([$nouveauStatut, $motif, $managerId, $id, $managerId]) || $stmtDemande->rowCount() === 0) {
                 $this->pdo->rollBack(); 
                 return false;
            }

            // 3. Enregistrer l'action dans la table historique_statuts
            $sqlHistory = "INSERT INTO {$this->historyTable} 
                           (demande_id, user_id, ancien_statut, nouveau_statut, commentaire)
                           VALUES (?, ?, ?, ?, ?)";
            
            $stmtHistory = $this->pdo->prepare($sqlHistory);
            
            if (!$stmtHistory->execute([$id, $userIdAction, $ancienStatut, $nouveauStatut, $motif])) {
                $this->pdo->rollBack(); 
                return false;
            }

            $this->pdo->commit(); // ⬅️ Confirmer toutes les opérations
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack(); // ⬅️ Annuler en cas d'exception
            error_log("Erreur de transaction lors de la mise à jour du statut: " . $e->getMessage());
            return false;
        }
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