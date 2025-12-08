<?php

namespace Controllers\Employe;

use Exception;
use Models\Employe\DemandeFraisModel;

class EmployeController {
    // Utilisation de \PDO pour référencer la classe PDO globale
    private \PDO $pdo;
    private int $employeId;
    private DemandeFraisModel $demandeFraisModel;

    // Le constructeur doit initialiser le Model
    // Utilisation de \PDO pour référencer la classe PDO globale
    public function __construct(\PDO $pdo, int $employeId, string $basePath) {
        $this->pdo = $pdo;
        $this->employeId = $employeId;
        // Initialiser le Model avec PDO et le chemin de base
      $this->demandeFraisModel = new DemandeFraisModel($pdo, $basePath);
    }
    
    // --- NOUVELLE MÉTHODE : Validation des données (extrait de votre code initial) ---
    private function validateDemandeData(array $data): void {
        $required_fields = ['objet_mission', 'lieu_deplacement', 'date_depart', 'date_retour', 'categorie_id', 'montant'];
        foreach ($required_fields as $field) {
            // Note : on utilise categorie_id au lieu de type_frais
            if (empty($data[$field])) {
                // L'exception est déjà dans l'espace de noms global si elle n'est pas préfixée
                throw new Exception("Le champ {$field} est requis.");
            }
        }
        // Ajouter d'autres validations ici (dates, montants numériques, etc.)
    }

    /**
     * Routeur principal pour les requêtes API des employés.
     */
    public function handleApiRequest(string $action, array $requestData, array $requestFiles = []): void {
        try {
            switch ($action) {
                
                case 'get_all_user_demandes':
                    $demandes = $this->demandeFraisModel->getAllUserDemandes($this->employeId);
                    echo json_encode($demandes);
                    break;
                    
                case 'get_user_recent_demandes':
                    $limit = (int)($requestData['limit'] ?? 3);
                    $demandes = $this->demandeFraisModel->getUserRecentDemandes($this->employeId, $limit);
                    echo json_encode($demandes);
                    break;
                    
                case 'get_user_stats':
                    $stats = $this->demandeFraisModel->getUserStats($this->employeId);
                    echo json_encode($stats);
                    break;

                case 'add_demande':
                    // 1. Validation des données
                    $this->validateDemandeData($requestData); 
                    
                    // 2. Déléguer au Modèle
                    $demandeId = $this->demandeFraisModel->addDemande($this->employeId, $requestData, $requestFiles);
                    
                    // 3. Réponse JSON de succès
                    echo json_encode([
                        'success' => true,
                        'message' => 'Demande créée avec succès',
                        'demande_id' => $demandeId
                    ]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Action non reconnue',
                        'action' => $action
                    ]);
                    break;
            }
            
        } catch (Exception $e) { // Exception est globale par défaut
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            
        } catch (\PDOException $e) { // Utilisation de l'antislash
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur de base de données.', 'error' => $e->getMessage()]);
        }
    }
}