<?php
// =============================================================
// ================= TEAM CONTROLLER ==========================
// Fichier : controllers/TeamController.php
// Gère la gestion des équipes d’un manager
// =============================================================

require_once BASE_PATH . 'models/TeamModel.php';

class TeamController {

    private $model;
    private $currentManagerId;

    // =============================================================
    // =================== CONSTRUCTEUR ===========================
    // Initialise le modèle et stocke l’ID du manager courant
    // =============================================================
    public function __construct($pdo, int $managerId) {
        $this->model = new TeamModel($pdo);
        $this->currentManagerId = $managerId; 
    }

    // =============================================================
    // =================== LISTE DES MEMBRES ======================
    // =============================================================

    /**
     * Récupère tous les membres de l'équipe du manager courant.
     * Utilisé par l’équipe et le dashboard.
     * @return array Liste des membres
     */
    public function getAllTeamMembers(): array {
        return $this->model->findAllTeamMembers($this->currentManagerId);
    }
    
    /**
     * Récupère les employés disponibles pour être ajoutés à l'équipe.
     * Utilisé par ajouter_membre.php
     * @return array Liste des employés disponibles
     */
    public function getAvailableEmployees(): array {
        return $this->model->findAvailableEmployees($this->currentManagerId);
    }

    // =============================================================
    // =================== AJOUT DE MEMBRES ========================
    // =============================================================
    /**
     * Ajoute des membres à l'équipe du manager.
     * @param array $memberIds Liste des IDs des employés à ajouter
     * @return array ['success' => true] ou ['error' => 'message']
     */
    public function addMembersToTeam(array $memberIds): array {
        if (empty($memberIds)) {
            return ['error' => 'Veuillez sélectionner au moins un membre.'];
        }
        
        $success = $this->model->addMembersToTeam($this->currentManagerId, $memberIds);

        if (!$success) {
            return ['error' => 'Erreur lors de l\'ajout des membres à l\'équipe.'];
        }

        return ['success' => true];
    }

    // =============================================================
    // =================== SUPPRESSION DE MEMBRE ==================
    // =============================================================
    /**
     * Retire un membre de l'équipe du manager courant.
     * @param int $memberId ID du membre à retirer
     * @return array ['success' => true] ou ['error' => 'message']
     */
    public function removeMemberFromTeam(int $memberId): array {
        $success = $this->model->removeMemberFromTeam($this->currentManagerId, $memberId);
        
        if (!$success) {
            return ['error' => 'Erreur lors du retrait du membre. L\'entrée n\'a pas pu être supprimée.'];
        }
        return ['success' => true];
    }
}
?>
