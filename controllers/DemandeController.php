<?php
// controllers/DemandeController.php

require_once __DIR__ . '/../models/DemandeModel.php'; 
// Assurez-vous que BASE_PATH est correctement défini dans votre environnement
require_once BASE_PATH . 'controllers/TeamController.php'; 

class DemandeController {

    private $model;
    private $db;
    private $managerId; // L'ID du manager connecté
    private $userId;    // L'ID générique de l'utilisateur connecté

    public function __construct($db) {
        $this->db = $db;
        $this->model = new DemandeModel($this->db);
        $this->checkAuth();
        
        // Initialiser les IDs après l'authentification
        $this->userId = $_SESSION['user_id'];
        $this->managerId = $this->userId; // Pour le contrôleur Manager, userId = managerId
    }
    
    /**
     * Vérifie l'état d'authentification et le rôle 'manager'.
     */
    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'manager') {
            // Utiliser exit après header pour s'assurer que le script s'arrête
            header('Location: ' . BASE_URL . 'views/auth/login.php'); 
            exit;
        }
    }

// --- Les méthodes getDashboardData, getAllPendingDemandes, getDemandeDetails, getDemandesList 
// --- sont conservées telles quelles car elles sont correctement implémentées.

    // =========================================================
    // SECTION 1: DATA PROVIDERS (Aucun changement nécessaire)
    // =========================================================

    public function getDashboardData() {
        // ... (Code inchangé)
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
    
    // =========================================================
    // SECTION 2: ACTIONS POST (Mise à jour pour l'historique)
    // =========================================================

    /**
     * Traite l'action POST de validation ou de rejet d'une demande.
     */
    public function traiterDemandeAction($postData) {
        if (isset($postData['action'], $postData['demande_id'])) {
            
            $id = (int) $postData['demande_id'];
            $action = $postData['action'];
            $motif = $postData['commentaire_manager'] ?? null; 

            // 1. Déterminer le statut
            $nouveauStatut = ($action === 'valider') ? 'Validée Manager' : 'Rejetée Manager'; 
            
            // 2. Vérification des données entrantes (sécurité)
            if ($action === 'rejeter' && empty(trim($motif))) {
                $_SESSION['error_message'] = "Le motif de rejet est obligatoire.";
                return; // Arrête l'exécution
            }

            // 3. Appel du Modèle avec la signature CORRIGÉE pour l'Historique
            // Signature: updateStatut($id, $nouveauStatut, $managerId, $userIdAction, $motif)
            // L'ID du manager est à la fois l'ID pour la vérification des droits et pour l'enregistrement de l'action.
            if ($this->model->updateStatut($id, $nouveauStatut, $this->managerId, $this->userId, $motif)) {
                
                // Mettre à jour l'état de la session (Flash message)
                $_SESSION['message'] = "Demande (ID: {$id}) traitée et statut mis à jour à '{$nouveauStatut}'.";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la mise à jour, demande non trouvée, ou statut déjà finalisé.";
            }
        } else {
             $_SESSION['error_message'] = "Données d'action POST incomplètes.";
        }
    }
}