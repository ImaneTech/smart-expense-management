<?php
namespace Controllers\Admin;

use Models\Admin\UserModel;
use PDO;

class UserController {
    private UserModel $model;
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->model = new UserModel($pdo);
    }

    // Centralise la gestion des requêtes API
    public function handleApiRequest(string $action, array $requestData, array $postData): void {
        try {
            switch ($action) {
                case 'get_stats':
                    $this->getStats();
                    break;
                case 'get_users':
                    $this->getUsers($requestData['role'] ?? null);
                    break;
                case 'create':
                    $this->createUser($postData);
                    break;
                case 'update':
                    $this->updateUser($postData);
                    break;
                case 'delete':
                    $this->deleteUser($postData['id'] ?? 0);
                    break;
                case 'export':
                    $this->exportUsers();
                    break;
                default:
                    $this->sendJson(['success' => false, 'message' => 'Action non reconnue']);
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
        $stats = $this->model->getStats();
        $this->sendJson($stats);
    }

    private function getUsers(?string $role): void {
        $users = $this->model->getUsers($role);
        $this->sendJson($users);
    }

    private function createUser(array $postData): void {
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'password', 'department'];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                throw new \Exception('Tous les champs obligatoires doivent être remplis.');
            }
        }

        $data = [
            'first_name' => $postData['first_name'],
            'last_name' => $postData['last_name'],
            'email' => $postData['email'],
            'phone' => $postData['phone'],
            'role' => $postData['role'] ?? 'employe',
            'department' => $postData['department'],
            'password' => $postData['password'],
            'manager_id' => $postData['manager_id'] ?? null
        ];

        $newId = $this->model->createUser($data);
        $this->sendJson([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'id' => $newId
        ]);
    }

  private function updateUser(array $postData): void {
      
        $requiredFields = ['id', 'first_name', 'last_name', 'email', 'phone', 'department', 'role'];
        
        foreach ($requiredFields as $field) {
            // L'ID doit être non nul, les autres ne doivent pas être vides.
            if (empty($postData[$field])) {
                throw new \Exception("Le champ '$field' est obligatoire.");
            }
        }

        $data = [
            'id' => $postData['id'],
            'first_name' => $postData['first_name'],
            'last_name' => $postData['last_name'],
            'email' => $postData['email'],
            'phone' => $postData['phone'],
            'role' => $postData['role'],
            'department' => $postData['department'],
            
            // Les champs 'manager_id' et 'password' sont optionnels dans le formulaire.
            // On les laisse null ou non définis s'ils ne sont pas envoyés.
            // Manager ID a été retiré du formulaire, donc il faut le traiter comme non critique ici
            'manager_id' => $postData['manager_id'] ?? null, 
            'password' => $postData['password'] ?? null 
        ];

        $success = $this->model->updateUser($data);
        $this->sendJson([
            'success' => $success,
            'message' => 'Utilisateur modifié avec succès'
        ]);
    }
    private function deleteUser(int $id): void {
        $success = $this->model->deleteUser($id);
        $this->sendJson([
            'success' => $success,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    private function exportUsers(): void {
        $users = $this->model->getAllUsersForExport();
        
        // Création du fichier CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=utilisateurs_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Rôle', 'Département', 'Date création']);
        
        foreach ($users as $user) {
            fputcsv($output, array_values($user)); // Utilisation de array_values pour correspondre aux en-têtes
        }
        
        fclose($output);
        exit;
    }
}