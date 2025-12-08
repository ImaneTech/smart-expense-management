<?php
namespace Controllers\Admin;

use Models\Admin\CategorieModel;
use PDO;

class CategorieController {
    private CategorieModel $model;
    
    public function __construct(PDO $pdo) {
        $this->model = new CategorieModel($pdo);
    }

    // Centralise la gestion des requêtes API pour les catégories
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
                    $this->createCategorie($postData);
                    break;
                case 'update':
                    $this->updateCategorie($postData);
                    break;
                case 'delete':
                    $this->deleteCategorie($postData['id'] ?? 0);
                    break;
                case 'export':
                    $this->exportCategories();
                    break;
                default:
                    $this->sendJson(['success' => false, 'message' => 'Action de catégorie non reconnue']);
            }
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function sendJson(array $data): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    // --- Implémentation des actions ---

    private function getStats(): void {
        $count = $this->model->getTotalCount();
        $this->sendJson(['total' => $count]);
    }
    
    private function getCategories(): void {
        $categories = $this->model->getAll();
        $this->sendJson($categories);
    }

    private function createCategorie(array $postData): void {
        $nom = trim($postData['nom'] ?? '');
        $description = trim($postData['description'] ?? '');

        if (empty($nom)) {
            throw new \Exception('Le nom de la catégorie est obligatoire');
        }

        $newId = $this->model->create($nom, $description ?: null);
        
        $this->sendJson(['success' => true, 'message' => 'Catégorie créée avec succès', 'id' => $newId]);
    }

    private function updateCategorie(array $postData): void {
        $id = (int)($postData['id'] ?? 0);
        $nom = trim($postData['nom'] ?? '');
        $description = trim($postData['description'] ?? '');

        if (!$id || empty($nom)) {
            throw new \Exception('ID et nom de catégorie sont obligatoires pour la mise à jour');
        }

        $success = $this->model->update($id, $nom, $description ?: null);
        
        $this->sendJson(['success' => $success, 'message' => 'Catégorie modifiée avec succès']);
    }

    private function deleteCategorie(int $id): void {
        if (!$id) {
            throw new \Exception('ID catégorie manquant pour la suppression');
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
}