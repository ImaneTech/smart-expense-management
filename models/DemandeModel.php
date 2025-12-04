<?php
// models/DemandeModel.php (VERSION CORRIGÉE ET HARMONISÉE)

class DemandeModel {

    private PDO $pdo;
    private string $userTable = 'users';
    private string $demandeTable = 'demande_frais';
    private string $detailsTable = 'details_frais';
    private string $historyTable = 'historique_statuts'; 

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // =========================================================
    // SECTION 1: DASHBOARD STATISTICS (KPIs)
    // =========================================================
    public function getDashboardStats(int $managerId): array {
        $stats = [];
        $baseSqlFilter = "
            FROM {$this->demandeTable} d
            JOIN {$this->userTable} u ON d.user_id = u.id
            WHERE u.manager_id = :managerId
        ";

        $queries = [
            'pending' => "SELECT COUNT(*) $baseSqlFilter AND d.statut = 'En attente'",
            'validated' => "SELECT COUNT(*) $baseSqlFilter AND d.statut IN ('Validée Manager','Approuvée Compta','Payée')",
            'rejected' => "SELECT COUNT(*) $baseSqlFilter AND d.statut = 'Rejetée Manager'",
            'amount_pending' => "
                SELECT SUM(df.montant)
                FROM {$this->detailsTable} df
                JOIN {$this->demandeTable} d ON df.demande_id = d.id
                JOIN {$this->userTable} u ON d.user_id = u.id
                WHERE d.statut = 'En attente' AND u.manager_id = :managerId
            ",
            'team_size' => "SELECT COUNT(*) FROM {$this->userTable} WHERE manager_id = :managerId"
        ];

        foreach ($queries as $key => $sql) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':managerId' => $managerId]);
            $value = $stmt->fetchColumn();
            $stats[$key] = $key === 'amount_pending' ? (float)($value ?? 0.00) : (int)$value;
        }

        return $stats;
    }

    // =========================================================
    // SECTION 2: READ OPERATIONS
    // =========================================================
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

    public function getDemandesByStatuses(int $managerId, array $statuses): array {
        if (empty($statuses)) return [];

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $sql = "
            SELECT d.id, d.user_id, d.objet_mission, d.date_depart, d.statut, 
                   d.date_traitement AS date_validation,
                   u.first_name, u.last_name, u.email,
                   (SELECT SUM(montant) FROM {$this->detailsTable} WHERE demande_id = d.id) AS total_calcule
            FROM {$this->demandeTable} d
            JOIN {$this->userTable} u ON d.user_id = u.id
            WHERE u.manager_id = ? AND d.statut IN ($placeholders)
            ORDER BY d.date_traitement DESC, d.created_at DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_merge([$managerId], $statuses));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans getDemandesByStatuses : " . $e->getMessage());
            return [];
        }
    }

    public function rechercheAvancee(int $managerId, int $employeId = 0, string $statut = '', string $dateDebut = '', string $dateFin = ''): array {
        $sql = "
            SELECT d.*, u.first_name, u.last_name, u.email, d.lieu_deplacement,
                   (SELECT SUM(montant) FROM {$this->detailsTable} WHERE demande_id = d.id) AS total_calcule
            FROM {$this->demandeTable} d
            JOIN {$this->userTable} u ON d.user_id = u.id
            WHERE u.manager_id = :managerId
        ";
        $params = [':managerId' => $managerId];

        if ($employeId > 0) {
            $sql .= " AND d.user_id = :employeId";
            $params[':employeId'] = $employeId;
        }
        if (!empty($statut)) {
            $sql .= " AND d.statut = :statut";
            $params[':statut'] = $statut;
        }
        if (!empty($dateDebut)) {
            $sql .= " AND d.date_depart >= :dateDebut";
            $params[':dateDebut'] = $dateDebut;
        }
        if (!empty($dateFin)) {
            $sql .= " AND d.date_depart <= :dateFin";
            $params[':dateFin'] = $dateFin;
        }

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

    public function getDemandeById(int $id, int $managerId): ?array {
        $sql = "SELECT d.*, u.first_name, u.last_name, u.email
                FROM {$this->demandeTable} d
                JOIN {$this->userTable} u ON d.user_id = u.id
                WHERE d.id = ? AND (u.manager_id = ? OR d.manager_id_validation = ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $managerId, $managerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getDetailsFrais(int $demandeId): array {
        $sql = "SELECT df.*, cf.nom AS nom_categorie
                FROM {$this->detailsTable} df
                JOIN categories_frais cf ON df.categorie_id = cf.id
                WHERE df.demande_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$demandeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDemandesForManager(int $managerId, ?string $statut = null): array {
        $statuses = $statut !== null ? [$statut] : ['En attente','Validée Manager','Rejetée Manager'];
        return $this->getDemandesByStatuses($managerId, $statuses);
    }

    // =========================================================
    // SECTION 3: WRITE OPERATIONS
    // =========================================================
    public function updateStatut(int $id, string $nouveauStatut, int $managerId, int $userIdAction, ?string $motif = null): bool {
        $this->pdo->beginTransaction();
        try {
            $ancienStatut = $this->getStatutActuel($id, $managerId);
            if (!$ancienStatut) {
                $this->pdo->rollBack();
                return false;
            }

            $sqlDemande = "UPDATE {$this->demandeTable} d
                           JOIN {$this->userTable} u ON d.user_id = u.id
                           SET d.statut = ?, d.commentaire_manager = ?, d.date_traitement = NOW(), d.manager_id_validation = ?
                           WHERE d.id = ? AND (u.manager_id = ? OR d.manager_id_validation = ?)";
            $stmtDemande = $this->pdo->prepare($sqlDemande);
            if (!$stmtDemande->execute([$nouveauStatut, $motif, $managerId, $id, $managerId, $managerId]) || $stmtDemande->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $sqlHistory = "INSERT INTO {$this->historyTable} (demande_id, user_id, ancien_statut, nouveau_statut, commentaire)
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
            error_log("Erreur de transaction updateStatut : " . $e->getMessage());
            return false;
        }
    }

    private function getStatutActuel(int $id, int $managerId): ?string {
        $sql = "SELECT d.statut
                FROM {$this->demandeTable} d
                JOIN {$this->userTable} u ON d.user_id = u.id
                WHERE d.id = ? AND (u.manager_id = ? OR d.manager_id_validation = ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $managerId, $managerId]);
        return $stmt->fetchColumn() ?: null;
    }
}
