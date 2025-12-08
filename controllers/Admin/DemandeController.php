<?php
// --- Déclaration du Namespace ---
namespace Controllers\Admin;

use Models\Admin\DemandeModel; 
use PDO;

class DemandeController
{
    private DemandeModel $demandeModel;
    private PDO $db;
    
    private array $statutMapping = [
        'en_attente' => 'En attente',
        'validee_manager' => 'Validée Manager',
        'validee_admin' => 'Approuvée Compta',
        'rejetee' => 'Rejetée Manager',
        'payee' => 'Payée'
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->demandeModel = new DemandeModel($db); 
    }

    // ----------------------------------------------------------------------
    // --- ROUTEUR INTERNE DU CONTRÔLEUR ---
    // ----------------------------------------------------------------------

    public function handleApiRequest(string $action, array $requestData): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            switch ($action) {
                case 'get_stats':
                    $this->getStatsAction();
                    break;
                case 'get_demandes':
                    $this->getDemandesAction($requestData);
                    break;
                case 'get_demande_by_id':
                    $this->getDemandeByIdAction($requestData);
                    break;
                case 'create':
                    $this->createDemandeAction($requestData);
                    break;
                case 'update_demande':
                    $this->updateDemandeAction($requestData);
                    break;
                case 'delete':
                    $this->deleteDemandeAction($requestData);
                    break;
                case 'export':
                    $this->exportDemandesAction();
                    break;
                default:
                    echo json_encode(['error' => 'Action inconnue: ' . $action]);
                    break;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            error_log("Erreur Controller: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // ----------------------------------------------------------------------
    // --- ACTIONS GÉRÉES PAR LE CONTRÔLEUR ---
    // ----------------------------------------------------------------------
    
    private function getStatsAction(): void
    {
        $stats = $this->demandeModel->getStats();
        echo json_encode($stats);
    }

    private function getDemandesAction(array $requestData): void
    {
        $statutFront = $requestData['statut'] ?? null;
        $statutDB = $this->statutMapping[$statutFront] ?? null;

        if ($statutFront === 'all' || !array_key_exists($statutFront, $this->statutMapping)) {
             $statutDB = null;
        }

        $demandes = $this->demandeModel->getAll($statutDB);
        
        $output = array_map(function($d) {
             $statutFront = array_search($d['statut'], $this->statutMapping) ?: 'en_attente';

             return [
                 'id' => (int)$d['id'],
                 'user_id' => (int)$d['user_id'],
                 'utilisateur_nom' => $d['utilisateur_nom'],
                 'objet_mission' => $d['objet_mission'],
                 'lieu_deplacement' => $d['lieu_deplacement'],
                 'date_depart' => $d['date_depart'],
                 'date_retour' => $d['date_retour'],
                 'statut' => $statutFront, 
                 'manager_id' => $d['manager_id'] ?? null,
                 'manager_id_validation' => $d['manager_id_validation'] ?? null,
                 'date_traitement' => $d['date_traitement'] ?? null,
                 'commentaire_manager' => $d['commentaire_manager'] ?? null,
                 'montant_total' => (float)$d['montant_total'],
                 'created_at' => $d['created_at'],
             ];
        }, $demandes);

        echo json_encode($output);
    }

    private function getDemandeByIdAction(array $requestData): void
    {
        $id = (int)($requestData['id'] ?? 0);
        if ($id === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }
        $demande = $this->demandeModel->getById($id);

        if ($demande) {
            $statutFront = array_search($demande['statut'], $this->statutMapping) ?: 'en_attente';
            $demande['statut'] = $statutFront;
            echo json_encode($demande);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Demande introuvable']);
        }
    }

    private function createDemandeAction(array $requestData): void
    {
        $requiredFields = ['user_id', 'objet_mission', 'lieu_deplacement', 'date_depart', 'date_retour', 'statut'];
        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données obligatoires manquantes: ' . $field]);
                return;
            }
        }

        $requestData['statut'] = $this->statutMapping[$requestData['statut']] ?? 'En attente';

        $newId = $this->demandeModel->create($requestData);

        if ($newId) {
            $montantTotal = (float)($requestData['montant_total'] ?? 0);
            if ($montantTotal > 0) {
                $this->handleInitialDetailCreation($newId, $montantTotal, $requestData['date_depart']);
            }
            
            echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Demande créée']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Échec de la création']);
        }
    }

    private function updateDemandeAction(array $requestData): void
    {
        $id = (int)($requestData['id'] ?? 0);
        if ($id === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $requiredFields = ['user_id', 'objet_mission', 'lieu_deplacement', 'date_depart', 'date_retour', 'statut'];
        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données obligatoires manquantes: ' . $field]);
                return;
            }
        }

        $requestData['statut'] = $this->statutMapping[$requestData['statut']] ?? 'En attente';

        unset($requestData['montant_total']); 
        
        $ok = $this->demandeModel->update($id, $requestData);

        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Demande mise à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour']);
        }
    }

    private function deleteDemandeAction(array $requestData): void
    {
        $id = (int)($requestData['id'] ?? 0);
        if ($id === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $ok = $this->demandeModel->delete($id);

        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Demande supprimée']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Échec de la suppression']);
        }
    }

    private function exportDemandesAction(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=demandes.csv');
        $out = fopen('php://output', 'w');
        
        fputcsv($out, ['ID','Utilisateur','Objet mission','Lieu','Date départ','Date retour','Montant total','Statut','Date création']);
        
        $rows = $this->demandeModel->getAll(null); 

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['utilisateur_nom'],
                $r['objet_mission'],
                $r['lieu_deplacement'],
                $r['date_depart'],
                $r['date_retour'],
                $r['montant_total'],
                $r['statut'],
                $r['created_at']
            ]);
        }
        fclose($out);
        exit;
    }

    private function handleInitialDetailCreation(int $demandeId, float $montant, string $dateDepense): void
    {
        // Cette logique est conservée de l'originale, mais devrait idéalement appeler un DetailsFraisModel
        $catStmt = $this->db->query("SELECT id FROM categories_frais LIMIT 1");
        $cat = $catStmt->fetch(PDO::FETCH_ASSOC);

        if ($cat) {
            $catId = (int)$cat['id'];
        } else {
            $this->db->prepare("INSERT INTO categories_frais (nom, description) VALUES (?, ?)")->execute(['Autre', 'Catégorie automatique']);
            $catId = (int)$this->db->lastInsertId();
        }

        $stmt = $this->db->prepare("
            INSERT INTO details_frais (demande_id, categorie_id, date_depense, montant, description, justificatif_path)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $demandeId, 
            $catId, 
            $dateDepense, 
            $montant, 
            'Montant initial lors de la création manuelle (Admin)', 
            null
        ]);
    }
}