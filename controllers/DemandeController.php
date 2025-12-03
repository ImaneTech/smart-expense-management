<?php
// controllers/DemandeController.php (CORRIGÉ)

require_once __DIR__ . '/../models/DemandeModel.php'; 
require_once BASE_PATH . 'controllers/TeamController.php'; 

class DemandeController {

    private $model;
    private $db;
    private $managerId; 
    private $userId;    

    public function __construct($db) {
        $this->db = $db;
        $this->model = new DemandeModel($this->db);
        $this->checkAuth();
        
        $this->userId = (int)($_SESSION['user_id'] ?? 0); 
        $this->managerId = $this->userId; 
    }
    
    private function checkAuth(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'manager') {
            $redirectUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'views/auth/login.php';
            header('Location: ' . $redirectUrl); 
            exit;
        }
    }

    // =========================================================
    // SECTION 1: DATA PROVIDERS
    // =========================================================

    public function getDashboardData(): array {
        $stats = $this->model->getDashboardStats($this->managerId);

        $teamController = new TeamController($this->db, $this->managerId);
        $allTeamMembers = $teamController->getAllTeamMembers();
        
        // CORRECTION: Utiliser la méthode simple pour le tableau de bord
        $latestDemandes = $this->model->getDemandesByStatus($this->managerId, 'En attente'); 
        $latestDemandes = array_slice($latestDemandes, 0, 5); 

        $teamMembers = array_slice($allTeamMembers, 0, 5);

        return [
            'stats' => $stats,
            'latest' => $latestDemandes, 
            'team' => $teamMembers,     
        ];
    }

    public function getAllPendingDemandes(): array {
        return $this->model->getDemandesByStatus($this->managerId, 'En attente');
    }
    
    public function getDemandeDetails($demandeId): ?array {
        $demande_info = $this->model->getDemandeById((int)$demandeId, $this->managerId);
        
        if (!$demande_info) { return null; }

        $demande_info['statut'] = $demande_info['statut'] ?? 'En attente'; 
        
        $lignes_frais = $this->model->getDetailsFrais((int)$demandeId);
        $demande_info['lignes_frais'] = $lignes_frais;
    
        return $demande_info; 
    }

    public function getDemandesList(?string $statut = null): array {
        if (strtolower($statut) === 'toutes') {
            $statut = null;
        }
        return $this->model->getAllDemandesForManager($this->managerId, $statut);
    }
    
    /**
     * Récupère l'historique des demandes traitées pour le manager.
     */
 // controllers/DemandeController.php

// controllers/DemandeController.php

// REMPLACEZ LA MÉTHODE getHistorique() actuelle par celle-ci:
// Pour l'Historique
public function getHistorique(): array {
    $statuts_historique = ['Validée Manager', 'Rejetée Manager'];
    return $this->model->getDemandesByStatuses($this->managerId, $statuts_historique); // SANS le 'true'
}

// Pour la Liste des demandes
public function getAllDemandesForManager(int $managerId, ?string $statut = null): array {
     $statuses = $statut !== null && strtolower($statut) !== 'toutes' 
         ? [$statut] 
         : ['En attente', 'Validée Manager', 'Rejetée Manager'];
     return $this->model->getDemandesByStatuses($managerId, $statuses); // SANS le 'false'
}
    // ... (Le reste du contrôleur est inchangé)
    public function faireUneRecherche(array $searchParams): array {
        
        $employeId = (int)($searchParams['employe'] ?? 0);
        $statut    = trim($searchParams['statut'] ?? '');
        $dateDebut = trim($searchParams['date_debut'] ?? '');
        $dateFin   = trim($searchParams['date_fin'] ?? '');
        
        return $this->model->rechercheAvancee(
            $this->managerId, 
            $employeId, 
            $statut, 
            $dateDebut, 
            $dateFin
        );
    }
    
    public function getManagerId(): int {
        return $this->managerId;
    }
    
    public function traiterDemandeAction($postData): void {
        if (isset($postData['action'], $postData['demande_id'])) {
            
            $id = (int) $postData['demande_id'];
            $action = $postData['action'];
            $motif = $postData['commentaire_manager'] ?? null; 

            $demandeActuelle = $this->model->getDemandeById($id, $this->managerId);

            if (!$demandeActuelle || $demandeActuelle['statut'] !== 'En attente') {
                $_SESSION['error_message'] = "Erreur: La demande n'est pas 'En attente' ou vous n'êtes pas le manager responsable.";
                header('Location: details_demande.php?id=' . $id);
                exit;
            }
            
            $nouveauStatut = ($action === 'valider') ? 'Validée Manager' : 'Rejetée Manager'; 
            
            if ($action === 'rejeter' && empty(trim($motif))) {
                $_SESSION['error_message'] = "Le motif de rejet est obligatoire.";
                header('Location: details_demande.php?id=' . $id);
                exit;
            }

            if ($this->model->updateStatut($id, $nouveauStatut, $this->managerId, $this->userId, $motif)) {
                
                $_SESSION['message'] = "Demande (ID: {$id}) traitée et statut mis à jour à '{$nouveauStatut}'.";
                
                header('Location: details_demande.php?id=' . $id);
                exit;

            } else {
                $_SESSION['error_message'] = "Erreur lors de la mise à jour (Modèle), vérifiez les journaux.";
                header('Location: details_demande.php?id=' . $id);
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Données d'action POST incomplètes.";
            header('Location: demandes_liste.php'); 
            exit;
        }
    }
}