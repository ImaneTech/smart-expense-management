<?php
// models/TeamModel.php

class TeamModel
{
    private $pdo;
    private $userTable = 'users';
    private $teamTable = 'manager_team'; // Table de liaison

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère TOUS les membres assignés à un manager spécifique.
     * Cette méthode est la source de vérité unique pour la liste d'équipe.
     * Utilisé par TeamController::getAllTeamMembers().
     */
  public function findAllTeamMembers(int $managerId): array {
    try {
        $sql = "
            SELECT u.id, u.first_name, u.last_name, u.email, u.role 
            FROM {$this->userTable} u
            -- REVENIR AU JOIN pour n'afficher que les utilisateurs existants
            JOIN {$this->teamTable} mt ON mt.member_id = u.id
            WHERE mt.manager_id = :managerId
            ORDER BY u.last_name ASC
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
     * Récupère les employés disponibles pour l'ajout (non encore assignés au manager).
     * Utilisé par TeamController::getAvailableEmployees().
     */
    public function findAvailableEmployees(int $managerId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, first_name, last_name, email, role 
                FROM {$this->userTable} 
                WHERE id NOT IN (
                    SELECT member_id FROM {$this->teamTable} WHERE manager_id = :managerId
                )
                AND id != :managerIdSelf
                AND role = 'employe'
                ORDER BY last_name ASC
            ");
            $stmt->bindParam(':managerId', $managerId, PDO::PARAM_INT);
            $stmt->bindParam(':managerIdSelf', $managerId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error (findAvailableEmployees): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajoute les IDs des membres à la table de liaison manager_team.
     * Utilisé par TeamController::addMembersToTeam().
     */
    public function addMembersToTeam(int $managerId, array $memberIds): bool
    {
        if (empty($memberIds)) return true;

        $placeholders = [];
        $values = [];
        $i = 0;
        foreach ($memberIds as $memberId) {
            $placeholders[] = "(:managerId, :memberId{$i})";
            $values[":memberId{$i}"] = (int) $memberId;
            $i++;
        }

        $sql = "INSERT IGNORE INTO {$this->teamTable} (manager_id, member_id) VALUES " . implode(', ', $placeholders);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':managerId', $managerId, PDO::PARAM_INT);

            foreach ($values as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("SQL Error (addMembersToTeam): " . $e->getMessage());
            return false;
        }
    }
    /**
 * Supprime l'association entre le manager et un membre de l'équipe.
 */
public function removeMemberFromTeam(int $managerId, int $memberId): bool {
    try {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->teamTable}
            WHERE manager_id = :managerId AND member_id = :memberId
        ");
        $stmt->bindParam(':managerId', $managerId, PDO::PARAM_INT);
        $stmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("SQL Error (removeMemberFromTeam): " . $e->getMessage());
        return false;
    }
}
}
