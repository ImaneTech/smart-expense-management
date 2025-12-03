<?php
// models/DemandeModel.php (VERSION FINALE, NETTOYÉE ET CORRIGÉE)

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
     * SECTION 1: DASHBOARD STATISTICS (KPIs)
     * ============================================================ */
    public function getDashboardStats(int $managerId): array {
        // ... (Logique inchangée, est correcte) ...
        $stats = [];
        $baseSqlFilter = "
             FROM {$this->demandeTable} d
             JOIN {$this->userTable} u ON d.user_id = u.id
             WHERE u.manager_id = :managerId
        ";
        
        $sql = "SELECT COUNT(*) " . $baseSqlFilter . " AND d.statut = 'En attente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['pending'] = (int) $stmt->fetchColumn(); 

        $sql = "SELECT COUNT(*) " . $baseSqlFilter . " AND d.statut IN ('Validée Manager', 'Approuvée Compta', 'Payée')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['validated'] = (int) $stmt->fetchColumn();

        $sql = "SELECT COUNT(*) " . $baseSqlFilter . " AND d.statut = 'Rejetée Manager'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['rejected'] = (int) $stmt->fetchColumn();

        $sql = "SELECT SUM(df.montant) 
                     FROM {$this->detailsTable} df
                     JOIN {$this->demandeTable} d ON df.demande_id = d.id
                     JOIN {$this->userTable} u ON d.user_id = u.id
                     WHERE d.statut = 'En attente' AND u.manager_id = :managerId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':managerId' => $managerId]);
        $stats['amount_pending'] = (float) ($stmt->fetchColumn() ?? 0.00); 
        
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
     * Récupère toutes les demandes gérées par un manager, filtrées par un SEUL statut.
     * Utilisé pour le Dashboard (dernières 5 en attente).
     */
    public function getDemandesByStatus(int $managerId, string $statut, ?int $limit = null): array {
        $sql = "SELECT d.*, u.first_name, u.last_name, u.email,
                 (SELECT SUM(montant) FROM {$this->detailsTable} WHERE demande_id = d.id) as total_calcule
                 FROM {$this->demandeTable} d
                 JOIN {$this->userTable} u ON d.user_id = u.id
                 WHERE d.statut = ? AND u.manager_id = ?
                 ORDER BY d.date_depart DESC";

        $params = [$statut, $managerId];

        if ($limit !== null) {
            $sql .= " LIMIT ?"; 
            $params[] = $limit; 
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les demandes d'un manager qui correspondent à une liste de statuts (Historique/Liste).
     * CORRIGÉ: Filtre par d.manager_id_validation ou u.manager_id selon le besoin.
     */
   // Dans models/DemandeModel.php, méthode getDemandesByStatuses

public function getDemandesByStatuses(int $managerId, array $statuses): array {
     if (empty($statuses)) {
         return [];
     }

     $placeholders = implode(',', array_fill(0, count($statuses), '?'));
     
     // ATTENTION : La condition est TOUJOURS basée sur u.manager_id
     $whereCondition = "u.manager_id = :manager_id"; 

     $sql = "
         SELECT 
             d.id, d.user_id, d.objet_mission, d.date_depart, d.statut, 
             d.date_traitement AS date_validation,
             u.first_name, u.last_name, u.email,
             (SELECT SUM(l.montant) FROM details_frais l WHERE l.demande_id = d.id) AS total_calcule
         FROM 
             demande_frais d
         JOIN 
             users u ON d.user_id = u.id
         WHERE 
             {$whereCondition} /* C'est ICI qu'on filtre par l'équipe */
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
     * Exécute une recherche avancée avec des filtres optionnels.
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
        // ... (Logique de recherche avancée inchangée)
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
        
        // ORDER BY final
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
                WHERE d.id = ? AND (u.manager_id = ? OR d.manager_id_validation = ?)"; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $managerId, $managerId]);
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
         // Si $statut est fourni, nous le transformons en tableau pour getDemandesByStatuses
         $statuses = $statut !== null ? [$statut] : ['En attente', 'Validée Manager', 'Rejetée Manager'];
         
         // Nous utilisons getDemandesByStatuses sans filtre par validateur (filterByValidator = false)
         return $this->getDemandesByStatuses($managerId, $statuses, false);
    }


    //--------------------------------------------------------------------------

    /* ============================================================
     * SECTION 3: WRITE OPERATIONS
     * ============================================================ */

    /**
     * Met à jour le statut d'une demande ET enregistre l'action dans l'historique.
     */
    public function updateStatut(int $id, string $nouveauStatut, int $managerId, int $userIdAction, ?string $motif = null): bool {
        
        $this->pdo->beginTransaction(); 

        try {
            // 1. Récupérer l'ancien statut
            $ancienStatut = $this->getStatutActuel($id, $managerId);
           
            if (!$ancienStatut) {
                 error_log("DEBUG-UPDATE-STATUT: ERREUR 1: Demande ID={$id} NON trouvée pour Manager ID={$managerId}. Accès refusé ou statut déjà finalisé.");
                 $this->pdo->rollBack(); 
                 return false; 
            } else {
                 error_log("DEBUG-UPDATE-STATUT: Demande ID={$id} trouvée. Ancien Statut: {$ancienStatut}");
            }

            // 2. Mettre à jour la demande principale (demande_frais)
            $sqlDemande = "UPDATE {$this->demandeTable} d
                           JOIN {$this->userTable} u ON d.user_id = u.id
                           SET d.statut = ?, 
                               d.commentaire_manager = ?, 
                               d.date_traitement = NOW(), 
                               d.manager_id_validation = ?
                           WHERE d.id = ? AND (u.manager_id = ? OR d.manager_id_validation = ?)"; 
            
            $stmtDemande = $this->pdo->prepare($sqlDemande);
            
            if (!$stmtDemande->execute([$nouveauStatut, $motif, $managerId, $id, $managerId, $managerId]) || $stmtDemande->rowCount() === 0) {
                 $message = "ECHEC MAJEUR DANS UPDATE STATUT. Demande ID={$id}, ManagerID={$managerId}. ";
                 $message .= "Statut Tenté: {$nouveauStatut}. RowCount: " . $stmtDemande->rowCount();
                 error_log($message); 
                 
                 $this->pdo->rollBack(); 
                 return false;
            } else {
                error_log("DEBUG-UPDATE-STATUT: Succès UPDATE Demande ID={$id}. Nouveau Statut: {$nouveauStatut}. Lignes affectées: " . $stmtDemande->rowCount());
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

            $this->pdo->commit(); 
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack(); 
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
                 WHERE d.id = ? AND (u.manager_id = ? OR d.manager_id_validation = ?)"; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $managerId, $managerId]);
        $statut = $stmt->fetchColumn();
        
        return $statut ?: null;
    }
    
    //-------------------------------------------
}