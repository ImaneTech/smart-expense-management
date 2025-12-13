<?php
// models/TeamModel.php

class TeamModel
{
    private $pdo;
    private $userTable = 'users'; 


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère TOUS les membres assignés à un manager spécifique.
     * Utilise la colonne users.manager_id.
     */
    public function findAllTeamMembers(int $managerId): array {
        try {
            $sql = "
                SELECT id, first_name, last_name, email, role 
                FROM {$this->userTable}
                WHERE manager_id = :managerId
                ORDER BY last_name ASC
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':managerId', $managerId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("SQL Error (findAllTeamMembers): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les employés disponibles pour l'ajout (ceux qui n'ont pas encore de manager).
     */
    public function findAvailableEmployees(int $managerId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, first_name, last_name, email, role 
                FROM {$this->userTable} 
                WHERE manager_id IS NULL
                AND id != :managerIdSelf
                AND role = 'employe'
                ORDER BY last_name ASC
            ");
            $stmt->bindParam(':managerIdSelf', $managerId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error (findAvailableEmployees): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajoute les membres à l'équipe en mettant à jour leur manager_id dans la table users.
     */
    public function addMembersToTeam(int $managerId, array $memberIds): bool
    {
        if (empty($memberIds)) return true;

        $updatePlaceholders = [];
        $paramsUpdate = [':managerIdUpdate' => $managerId];
        $k = 0;
        
        foreach ($memberIds as $memberId) {
            $updatePlaceholders[] = ":updId{$k}";
            $paramsUpdate[":updId{$k}"] = (int) $memberId;
            $k++;
        }

        $updateSql = "
            UPDATE {$this->userTable} 
            SET manager_id = :managerIdUpdate 
            WHERE id IN (" . implode(', ', $updatePlaceholders) . ")
            AND manager_id IS NULL 
        ";
        
        try {
            $stmtUpdate = $this->pdo->prepare($updateSql);
            
            foreach ($paramsUpdate as $key => $value) {
                $stmtUpdate->bindValue($key, $value, PDO::PARAM_INT);
            }
            
            return $stmtUpdate->execute();

        } catch (PDOException $e) {
            error_log("SQL Error (addMembersToTeam): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retire un membre de l'équipe en remettant son manager_id à NULL dans la table users.
     */
    public function removeMemberFromTeam(int $managerId, int $memberId): bool {
        try {
            $stmtUpdate = $this->pdo->prepare("
                UPDATE {$this->userTable}
                SET manager_id = NULL
                WHERE id = :memberId 
                AND manager_id = :managerId -- Sécurité : s'assurer que c'est bien CE manager qui le retire
            ");
            $stmtUpdate->bindParam(':managerId', $managerId, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':memberId', $memberId, PDO::PARAM_INT);
            return $stmtUpdate->execute();
            
        } catch (PDOException $e) {
            error_log("SQL Error (removeMemberFromTeam): " . $e->getMessage());
            return false;
        }
    }
}