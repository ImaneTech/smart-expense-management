<?php
// controllers/TeamController.php

require_once BASE_PATH . 'models/TeamModel.php';

class TeamController {
    private $model;
    private $currentManagerId;

    public function __construct($pdo, int $managerId) {
        $this->model = new TeamModel($pdo);
        $this->currentManagerId = $managerId; 
    }

    /**
     * Récupère la liste des membres actuels de l'équipe (Utilisé par equipe.php et Dashboard).
     */
    public function getAllTeamMembers() {
        return $this->model->findAllTeamMembers($this->currentManagerId);
    }
    
    /**
     * Récupère la liste des employés disponibles (Utilisé par ajouter_membre.php).
     */
    public function getAvailableEmployees() {
        return $this->model->findAvailableEmployees($this->currentManagerId);
    }
    
    /**
     * Gère l'association des membres à l'équipe.
     */
    public function addMembersToTeam(array $memberIds) {
        if (empty($memberIds)) {
            return ['error' => 'Veuillez sélectionner au moins un membre.'];
        }
        
        $success = $this->model->addMembersToTeam($this->currentManagerId, $memberIds);

        if (!$success) {
            return ['error' => 'Erreur lors de l\'ajout des membres à l\'équipe.'];
        }

        return ['success' => true];
    }

 
 /* Gère le retrait d'un membre de l'équipe du manager actuel.
 */
public function removeMemberFromTeam(int $memberId) {
    // L'ID du manager est déjà stocké dans $this->currentManagerId
    $success = $this->model->removeMemberFromTeam($this->currentManagerId, $memberId);
    
    if (!$success) {
        return ['error' => 'Erreur lors du retrait du membre. L\'entrée n\'a pas pu être supprimée.'];
    }
    return ['success' => true];
}
}