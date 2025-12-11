<?php
// Models/AdminModel.php

class AdminModel {
    private PDO $pdo;
    private string $demandeTable = 'demande_frais';
    private string $userTable = 'users';
    private string $detailsTable = 'details_frais';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllStats(): array {
        $stats = [];
        
        $queries = [
            'validees_manager' => "SELECT COUNT(*) FROM {$this->demandeTable} WHERE statut IN ('Validée Manager', 'Approuvée Compta', 'Payée')",
            'en_attente' => "SELECT COUNT(*) FROM {$this->demandeTable} WHERE statut = 'En attente'",
            'rejetees' => "SELECT COUNT(*) FROM {$this->demandeTable} WHERE statut = 'Rejetée Manager'"
        ];

        foreach ($queries as $key => $sql) {
            $stmt = $this->pdo->query($sql);
            $stats[$key] = (int)$stmt->fetchColumn();
        }

        return $stats;
    }

    public function getAllDemandes(?string $statut = null, string $column = 'statut'): array {
        $sql = "SELECT d.id, d.user_id, d.objet_mission, d.date_depart, d.date_retour, d.statut, d.statut_final,
                       d.created_at, d.montant_total,
                       u.first_name, u.last_name, u.email,
                       CONCAT(u.first_name, ' ', u.last_name) as utilisateur_nom
                FROM {$this->demandeTable} d
                JOIN {$this->userTable} u ON d.user_id = u.id";
        
        $params = [];
        if ($statut && $statut !== 'all') {
            // Allow filtering by statut or statut_final
            $allowedColumns = ['statut', 'statut_final'];
            if (!in_array($column, $allowedColumns)) {
                $column = 'statut';
            }
            $sql .= " WHERE d.$column = ?";
            $params[] = $statut;
        }
        
        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDemandeById(int $id): ?array {
        $sql = "SELECT d.*, u.first_name, u.last_name, u.email, u.department, u.role
                FROM {$this->demandeTable} d
                JOIN {$this->userTable} u ON d.user_id = u.id
                WHERE d.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deleteDemande(int $id): bool {
        // Deleting a demand should cascade to details if foreign keys are set up, 
        // but let's be safe and delete details first if needed, or rely on cascade.
        // Assuming cascade or manual deletion.
        $this->pdo->beginTransaction();
        try {
            // Delete details first
            $stmtDetails = $this->pdo->prepare("DELETE FROM {$this->detailsTable} WHERE demande_id = ?");
            $stmtDetails->execute([$id]);

            // Delete demand
            $stmtDemande = $this->pdo->prepare("DELETE FROM {$this->demandeTable} WHERE id = ?");
            $stmtDemande->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting demande: " . $e->getMessage());
            return false;
        }
    }
}
