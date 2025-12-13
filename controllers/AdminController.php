<?php
// Controllers/AdminController.php

require_once __DIR__ . '/../Models/AdminModel.php';
require_once __DIR__ . '/../Models/DemandeModel.php';

class AdminController {
    private $adminModel;
    private $demandeModel;
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
        $this->demandeModel = new DemandeModel($pdo);
    }

    public function handleApiRequest($action, $data) {
        try {
            switch ($action) {
                case 'get_stats':
                    $this->getStats();
                    break;
                case 'get_demandes':
                    $statut = $data['statut'] ?? 'all';
                    $this->getDemandes($statut);
                    break;
                case 'get_demande_by_id':
                    $id = (int)($data['id'] ?? 0);
                    $this->getDemandeById($id);
                    break;
                case 'delete':
                    $id = (int)($data['id'] ?? 0);
                    $this->deleteDemande($id);
                    break;
                case 'create':
                    $this->createDemande($data);
                    break;
                case 'update_demande':
                    $this->updateDemande($data);
                    break;
                case 'export':
                    $this->exportData();
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Action non reconnue']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function getStats() {
        $stats = $this->adminModel->getAllStats();
        echo json_encode($stats);
    }

    private function getDemandes($statut) {
        // Mapping des slugs frontend vers les valeurs en base de données et la colonne cible
        // Structure: 'slug' => ['value' => 'DB Value', 'column' => 'statut'|'statut_final']
        
        $filterConfig = [
            'en_attente' => ['value' => 'En attente', 'column' => 'statut_final'],
            'validee' => ['value' => 'Validée', 'column' => 'statut_final'],
            'rejetee' => ['value' => 'Rejetée', 'column' => 'statut_final'],
            'validee_manager' => ['value' => 'Validée Manager', 'column' => 'statut'],
            'rejetee_manager' => ['value' => 'Rejetée Manager', 'column' => 'statut'],
            // Legacy/Other
            'validee_admin' => ['value' => 'Validée', 'column' => 'statut_final'],
            'payee' => ['value' => 'Payée', 'column' => 'statut'], 
        ];

        $dbStatut = $statut;
        $column = 'statut';

        if (isset($filterConfig[$statut])) {
            $dbStatut = $filterConfig[$statut]['value'];
            $column = $filterConfig[$statut]['column'];
        }

        $demandes = $this->adminModel->getAllDemandes($dbStatut, $column);
        echo json_encode($demandes);
    }

    private function getDemandeById($id) {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invalide']);
            return;
        }
        $demande = $this->adminModel->getDemandeById($id);
        if ($demande) {
            echo json_encode($demande);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Demande introuvable']);
        }
    }

    private function deleteDemande($id) {
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }
        if ($this->adminModel->deleteDemande($id)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }

    private function createDemande($data) {
        // Validation basique
        if (empty($data['user_id']) || empty($data['objet_mission'])) {
            echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
            return;
        }

        $userId = (int)$data['user_id'];
        $missionData = [
            'objet_mission' => $data['objet_mission'],
            'lieu_deplacement' => $data['lieu_deplacement'] ?? '',
            'date_depart' => $data['date_depart'] ?? date('Y-m-d'),
            'date_retour' => $data['date_retour'] ?? date('Y-m-d')
        ];

        $newId = $this->demandeModel->createDemande($userId, $missionData);
        if ($newId) {
            echo json_encode(['success' => true, 'id' => $newId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
    }

    private function updateDemande($data) {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }

        
        $success = $this->updateDemandeInDb($id, $data);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
        }
    }
    
    private function updateDemandeInDb($id, $data) {
        // Méthode privée temporaire pour update
        $sql = "UPDATE demande_frais SET 
                objet_mission = :objet,
                lieu_deplacement = :lieu,
                date_depart = :depart,
                date_retour = :retour,
                statut = :statut,
                statut_final = :statut_final,
                montant_total = :montant
                WHERE id = :id";
     
        
        $statut = $data['statut'] ?? 'En attente';
        $statutFinal = $data['statut_final'] ?? 'En attente';
        
      
        if ($statut === 'Validée' || $statut === 'validee_admin') {
            $statutFinal = 'Validée';
            $statut = 'validee_admin';
        } elseif ($statut === 'Rejetée' || $statut === 'rejetee') {
            $statutFinal = 'Rejetée';
            $statut = 'rejetee_admin'; 
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':objet' => $data['objet_mission'],
            ':lieu' => $data['lieu_deplacement'],
            ':depart' => $data['date_depart'],
            ':retour' => $data['date_retour'],
            ':statut' => $statut,
            ':statut_final' => $statutFinal,
            ':montant' => $data['montant_total'] ?? 0,
            ':id' => $id
        ]);
    }

    private function exportData() {
        // Export CSV simple
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export_demandes.csv"');
        
        $demandes = $this->adminModel->getAllDemandes('all');
        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, ['ID', 'Utilisateur', 'Objet', 'Départ', 'Retour', 'Statut', 'Montant']);
        
        foreach ($demandes as $d) {
            fputcsv($output, [
                $d['id'],
                $d['utilisateur_nom'],
                $d['objet_mission'],
                $d['date_depart'],
                $d['date_retour'],
                $d['statut'],
                $d['montant_total']
            ]);
        }
        fclose($output);
        exit;
    }
}
