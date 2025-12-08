<?php
namespace Models\Admin;

use PDO;
use PDOException;

class UserModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // --- Stats ---
    public function getStats(): array {
        $stats = [];
        $roles = ['employe', 'manager', 'admin'];
        $total = 0;

        try {
            foreach ($roles as $role) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
                $stmt->execute([$role]);
                $count = $stmt->fetchColumn();
                $stats[$role . 's'] = $count;
                $total += $count;
            }
            $stats['total'] = $total;
            return $stats;
        } catch (PDOException $e) {
            // En production, il est préférable de ne pas exposer $e->getMessage()
            throw new \Exception("Database error retrieving stats: " . $e->getMessage());
        }
    }

    // --- Fetch Users ---
    public function getUsers(?string $role): array {
        try {
            if (!$role || $role === 'all') {
                $stmt = $this->pdo->query("SELECT id, first_name, last_name, email, phone, role, department, manager_id, created_at FROM users ORDER BY created_at DESC");
            } else {
                $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, email, phone, role, department, manager_id, created_at FROM users WHERE role = ? ORDER BY created_at DESC");
                $stmt->execute([$role]);
            }
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ajouter le nom complet pour le front-end
            foreach ($users as &$user) {
                $user['nom'] = $user['first_name'] . ' ' . $user['last_name'];
            }
            return $users;
        } catch (PDOException $e) {
            throw new \Exception("Database error retrieving users: " . $e->getMessage());
        }
    }

    // --- Create User ---
    public function createUser(array $data): int {
        // Validation de l'email
        $checkEmail = $this->pdo->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmail->execute([$data['email']]);
        if ($checkEmail->fetch()) {
            throw new \Exception("Cet email est déjà utilisé.");
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $managerId = !empty($data['manager_id']) ? (int)$data['manager_id'] : null;

        $stmt = $this->pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone, password, role, department, manager_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $hashedPassword,
            $data['role'],
            $data['department'],
            $managerId
        ]);

        return $this->pdo->lastInsertId();
    }

    // --- Update User ---
   // --- Update User ---
    public function updateUser(array $data): bool {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $checkEmail = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->execute([$data['email'], $data['id']]);
        if ($checkEmail->fetch()) {
            throw new \Exception("Cet email est déjà utilisé par un autre compte.");
        }

        // 🎯 L'update est fait uniquement sur les champs envoyés (password est géré conditionnellement)
        $fields = [
            'first_name', 'last_name', 'email', 'phone', 'role', 'department', 'manager_id'
        ];
        $setClauses = [];
        $executeParams = [];

        foreach ($fields as $field) {
            // On vérifie si la clé existe dans $data pour éviter de mettre à jour avec null si le champ n'est pas envoyé
            if (isset($data[$field])) { 
                $setClauses[] = "$field = ?";
                $executeParams[] = $data[$field];
            }
        }

        // Ajout du mot de passe s'il est fourni
        if (isset($data['password']) && !empty($data['password'])) {
             $setClauses[] = "password = ?";
             $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
             $executeParams[] = $hashedPassword;
        }

        if (empty($setClauses)) {
            return true; // Rien à mettre à jour
        }
        
        $executeParams[] = $data['id']; // L'ID pour la clause WHERE

        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute($executeParams);
    }

    // --- Delete User ---
    public function deleteUser(int $id): bool {
        if (!$id) {
             throw new \Exception("ID utilisateur manquant.");
        }
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Export Users ---
    public function getAllUsersForExport(): array {
         $stmt = $this->pdo->query("
            SELECT id, first_name, last_name, email, phone, role, department, created_at 
            FROM users 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}