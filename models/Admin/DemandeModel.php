<?php
// --- Déclaration du Namespace ---
namespace Models\Admin;

use PDO;
use PDOException;

class DemandeModel
{
    private PDO $db;
    private string $table = 'demande_frais';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Calcule le montant total d'une demande à partir des détails de frais.
     */
    public function calculerMontantTotal(int $demandeId): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(montant), 0) FROM details_frais WHERE demande_id = ?");
        $stmt->execute([$demandeId]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Récupère toutes les demandes, filtrées par statut si spécifié, avec les noms des utilisateurs.
     */
    public function getAll(?string $statut = null): array
    {
        $sql = "
            SELECT df.*, u.first_name, u.last_name, 
                   CONCAT(u.first_name, ' ', u.last_name) AS utilisateur_nom
            FROM {$this->table} df
            JOIN users u ON df.user_id = u.id
        ";
        $params = [];

        if ($statut) {
            $sql .= " WHERE df.statut = ?";
            $params[] = $statut;
        }

        $sql .= " ORDER BY df.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($demandes as &$demande) {
                $demande['montant_total'] = $this->calculerMontantTotal($demande['id']);
                $demande['user_nom'] = $demande['utilisateur_nom'];
            }
            return $demandes;
        } catch (PDOException $e) {
            error_log("Erreur getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les données d'une demande par ID.
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour une demande existante.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'user_id', 'objet_mission', 'lieu_deplacement', 'date_depart', 'date_retour', 
            'statut', 'manager_id', 'manager_id_validation', 'date_traitement', 
            'commentaire_manager', 'montant_total'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                if (in_array($field, ['manager_id', 'manager_id_validation']) && empty($data[$field])) {
                    $params[] = null;
                } else {
                     $params[] = ($field === 'date_traitement' || $field === 'commentaire_manager') && empty($data[$field]) ? null : $data[$field];
                }
            }
        }

        if (empty($fields)) {
             error_log("Tentative de mise à jour sans données pour ID: " . $id);
             return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = $id;

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur SQL lors de l'update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée une nouvelle demande.
     */
    public function create(array $data): int|bool
    {
        $fields = [
            'user_id', 'objet_mission', 'lieu_deplacement', 'date_depart', 
            'date_retour', 'statut', 'manager_id'
        ];
        $sqlFields = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        
        $params = [];
        foreach ($fields as $field) {
            $value = $data[$field] ?? null;
            if ($field === 'manager_id' && empty($value)) {
                $params[] = null;
            } else {
                $params[] = $value;
            }
        }
        
        $sql = "INSERT INTO {$this->table} ({$sqlFields}, created_at) VALUES ({$placeholders}, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($params)) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur SQL lors de la création: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une demande et les détails de frais associés.
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $stmtDetails = $this->db->prepare("DELETE FROM details_frais WHERE demande_id = ?");
            $stmtDetails->execute([$id]);

            $stmtDemande = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $ok = $stmtDemande->execute([$id]);

            $this->db->commit();
            return $ok;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erreur suppression: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les statistiques de la page.
     */
    public function getStats(): array
    {
        $rows = $this->db->query("SELECT statut, COUNT(*) AS total FROM {$this->table} GROUP BY statut")->fetchAll(PDO::FETCH_ASSOC);
        $out = ['validees_manager' => 0, 'en_attente' => 0, 'rejetees' => 0];
        
        foreach ($rows as $r) {
            $s = $r['statut'];
            $t = (int)$r['total'];
            if ($s === 'Validée Manager' || $s === 'Approuvée Compta' || $s === 'Payée') {
                $out['validees_manager'] += $t;
            } elseif ($s === 'En attente') {
                $out['en_attente'] += $t;
            } elseif ($s === 'Rejetée Manager') {
                $out['rejetees'] += $t;
            }
        }
        return $out;
    }
}