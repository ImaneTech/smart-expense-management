<?php
// controllers/DemandeController.php

require_once __DIR__ . '/../models/DemandeModel.php'; 
// Assurez-vous que BASE_PATH est correctement défini dans votre environnement
require_once BASE_PATH . 'controllers/TeamController.php'; 
// Assurez-vous que BASE_URL est défini dans votre config.php

class DemandeController {

    private $model;
    private $db;
    private $managerId; // L'ID du manager connecté
    private $userId;    // L'ID générique de l'utilisateur connecté

    public function __construct($db) {
        $this->db = $db;
        $this->model = new DemandeModel($this->db);
        $this->checkAuth();
        
        // Assurer que l'ID est un entier après l'authentification
        $this->userId = (int)($_SESSION['user_id'] ?? 0); 
        $this->managerId = $this->userId; 
    }
    
    /**
     * Vérifie l'état d'authentification et le rôle 'manager'.
     */
    private function checkAuth() {
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

    public function getDashboardData() {
        $stats = $this->model->getDashboardStats($this->managerId);

        $teamController = new TeamController($this->db, $this->managerId);
        $allTeamMembers = $teamController->getAllTeamMembers();
        
        $latestDemandes = $this->model->getDemandesByStatus($this->managerId, 'En attente', 5);
        $teamMembers = array_slice($allTeamMembers, 0, 5);

        return [
            'stats' => $stats,
            'latest' => $latestDemandes, 
            'team' => $teamMembers,     
        ];
    }

    public function getAllPendingDemandes() {
        return $this->model->getDemandesByStatus($this->managerId, 'En attente');
    }

    public function getDemandeDetails($demandeId) {
        $demande_info = $this->model->getDemandeById((int)$demandeId, $this->managerId);
        
        if (!$demande_info) { return null; }

        $demande_info['statut'] = $demande_info['statut'] ?? 'En attente'; 
        
        $lignes_frais = $this->model->getDetailsFrais((int)$demandeId);
        $demande_info['lignes_frais'] = $lignes_frais;
    
        return $demande_info; 
    }

    public function getDemandesList(?string $statut = null) {
        if (strtolower($statut) === 'toutes') {
            $statut = null;
        }
        return $this->model->getAllDemandesForManager($this->managerId, $statut);
    }
    
    /**
     * Récupère l'historique des demandes traitées pour le manager.
     */
    public function getHistorique() {
        $statuts_historique = ['Validée Manager', 'Rejetée Manager', 'Approuvée Compta', 'Payée'];
        return $this->model->getDemandesByStatuses($this->managerId, $statuts_historique);
    }
    
    /**
     * AJOUTÉ: Effectue une recherche avancée sur les demandes gérées par le manager.
     */
    public function faireUneRecherche(array $searchParams): array {
        
        // Préparation des paramètres pour le modèle
        $employeId = (int)($searchParams['employe'] ?? 0);
        $statut    = trim($searchParams['statut'] ?? '');
        $dateDebut = trim($searchParams['date_debut'] ?? '');
        $dateFin   = trim($searchParams['date_fin'] ?? '');
        
        // Appel du modèle (qui gère la logique SQL)
        return $this->model->rechercheAvancee(
            $this->managerId, 
            $employeId, 
            $statut, 
            $dateDebut, 
            $dateFin
        );
    }
    
    /**
     * Méthode utilitaire pour obtenir l'ID du Manager (pour TeamController)
     */
    public function getManagerId(): int {
        return $this->managerId;
    }
    
// =========================================================
// SECTION 2: ACTIONS POST (Mise à jour avec PRG pattern)
// =========================================================

/**
 * Traite l'action POST de validation ou de rejet d'une demande.
 */
public function traiterDemandeAction($postData) {
    if (isset($postData['action'], $postData['demande_id'])) {
        
        $id = (int) $postData['demande_id'];
        $action = $postData['action'];
        $motif = $postData['commentaire_manager'] ?? null; 

        // 🚨 NOUVEAU: Vérifier si la demande existe et est 'En attente'
        // Nous réutilisons la méthode existante pour garantir que le manager a le droit de la voir
        $demandeActuelle = $this->model->getDemandeById($id, $this->managerId);

        if (!$demandeActuelle || $demandeActuelle['statut'] !== 'En attente') {
            $_SESSION['error_message'] = "Erreur: La demande n'est pas 'En attente' ou vous n'êtes pas le manager responsable.";
            header('Location: details_demande.php?id=' . $id);
            exit;
        }
        
        // --- Le reste de la logique ---

        $nouveauStatut = ($action === 'valider') ? 'Validée Manager' : 'Rejetée Manager'; 
        
        if ($action === 'rejeter' && empty(trim($motif))) {
            $_SESSION['error_message'] = "Le motif de rejet est obligatoire.";
            // Redirection ici, car le return ferait planter le PRG pattern sans redirection
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
         header('Location: demandes_liste.php'); // Rediriger vers la liste si les données POST sont incomplètes
         exit;
    }
}
}