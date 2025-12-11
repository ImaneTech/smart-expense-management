<?php
// =============================================
// ======= CONTROLLER CATEGORIE ================
// Fichier : controllers/CategorieController.php
// Consolidated version - Merges Admin API and Session-based functionality
// =============================================

require_once __DIR__ . '/../Models/CategorieModel.php'; 

// =============================================
// ======= DEFINITION DE LA CLASSE =============
// =============================================
class CategorieController {

    private $model;
    
    // =============================================
    // ======= CONSTRUCTEUR =======================
    // Initialise le modèle (auth check optionnel)
    // =============================================
    public function __construct($db, $checkAuth = true) {
        $this->model = new CategorieModel($db);
        if ($checkAuth) {
            $this->checkAuth();
        }
    }

    // =============================================
    // ======= VERIFICATION AUTH ==================
    // Vérifie si l'utilisateur est connecté et a le rôle 'admin'
    // =============================================
    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            $redirectUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'views/auth/login.php';
            header('Location: ' . $redirectUrl); 
            exit;
        }
    }

    // =============================================
    // ======= API REQUEST HANDLER ================
    // Centralise la gestion des requêtes API pour les catégories
    // =============================================
    public function handleApiRequest(string $action, array $requestData, array $postData): void {
        try {
            switch ($action) {
                case 'get_categories':
                    $this->getCategories();
                    break;
                case 'get_stats': // Utilisé pour le total des catégories
                    $this->getStats();
                    break;
                case 'create':
                    $this->createCategorieApi($postData);
                    break;
                case 'update':
                    $this->updateCategorieApi($postData);
                    break;
                case 'delete':
                    $this->deleteCategorieApi($postData['id'] ?? 0);
                    break;
                case 'export':
                    $this->exportCategories();
                    break;
                default:
                    $this->sendJson(['success' => false, 'message' => 'Action de catégorie non reconnue']);
            }
        } catch (Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =============================================
    // ======= JSON RESPONSE HELPER ===============
    // =============================================
    private function sendJson(array $data): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    // =============================================
    // ======= API METHODS ========================
    // =============================================

    private function getStats(): void {
        $count = $this->model->getTotalCount();
        $this->sendJson(['total' => $count]);
    }
    
    private function getCategories(): void {
        $categories = $this->model->getAll();
        $this->sendJson($categories);
    }

    private function createCategorieApi(array $postData): void {
        $nom = trim($postData['nom'] ?? '');
        $description = trim($postData['description'] ?? '');

        if (empty($nom)) {
            throw new Exception('Le nom de la catégorie est obligatoire');
        }

        $newId = $this->model->create($nom, $description ?: null);
        
        $this->sendJson(['success' => true, 'message' => 'Catégorie créée avec succès', 'id' => $newId]);
    }

    private function updateCategorieApi(array $postData): void {
        $id = (int)($postData['id'] ?? 0);
        $nom = trim($postData['nom'] ?? '');
        $description = trim($postData['description'] ?? '');

        if (!$id || empty($nom)) {
            throw new Exception('ID et nom de catégorie sont obligatoires pour la mise à jour');
        }

        $success = $this->model->update($id, $nom, $description ?: null);
        
        $this->sendJson(['success' => $success, 'message' => 'Catégorie modifiée avec succès']);
    }

    private function deleteCategorieApi(int $id): void {
        if (!$id) {
            throw new Exception('ID catégorie manquant pour la suppression');
        }
        $success = $this->model->delete($id);
        $this->sendJson(['success' => $success, 'message' => 'Catégorie supprimée avec succès']);
    }

    private function exportCategories(): void {
        $categories = $this->model->getAllForExport();
        
        // Création du fichier CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=categories_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['ID', 'Nom', 'Description']);
        
        foreach ($categories as $cat) {
            fputcsv($output, [$cat['id'], $cat['nom'], $cat['description']]);
        }
        
        fclose($output);
        exit;
    }

    // =============================================
    // ======= SESSION-BASED METHODS ==============
    // (For backward compatibility with existing views)
    // =============================================

    // =============================================
    // ======= LISTE DES CATEGORIES ===============
    // Récupère toutes les catégories pour l'affichage
    // =============================================
    public function index(): array {
        return $this->model->getAllCategories();
    }

    // =============================================
    // ======= AJOUT D'UNE CATEGORIE ==============
    // Traite le formulaire d'ajout via POST
    // =============================================
    public function addCategorieAction(array $postData) {
        if (empty(trim($postData['nom'] ?? ''))) {
            $_SESSION['error_message'] = "Le nom de la catégorie est obligatoire.";
            return;
        }

        $nom = htmlspecialchars(trim($postData['nom']));

        if ($this->model->addCategorie($nom)) {
            $_SESSION['message'] = "Catégorie '{$nom}' ajoutée avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur: Le nom '{$nom}' existe déjà ou la base de données a rencontré une erreur.";
        }

        // PRG (Post-Redirect-Get) pattern
        header('Location: ' . BASE_URL . 'views/admin/gerer_categories.php');
        exit;
    }

    // =============================================
    // ======= SUPPRESSION D'UNE CATEGORIE ========
    // Traite la suppression via POST
    // =============================================
    public function deleteCategorieAction(array $postData) {
        $id = (int)($postData['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error_message'] = "ID de catégorie invalide.";
            return;
        }

        if ($this->model->deleteCategorie($id)) {
            $_SESSION['message'] = "Catégorie (ID: {$id}) supprimée avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur: Impossible de supprimer la catégorie (elle est peut-être utilisée par des demandes de frais existantes).";
        }

        // PRG pattern
        header('Location: ' . BASE_URL . 'views/admin/gerer_categories.php');
        exit;
    }
}
