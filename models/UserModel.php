<?php
// =============================================================
// ================= USER MODEL ===============================
// Fichier : Models/UserModel.php
// Consolidated version - Merges Admin CRUD and Auth functionality
// =============================================================

require_once 'User.php';

class UserModel
{
    private $pdo;

    public function __construct($pdo)
    {
        if (!$pdo) {
            throw new Exception("PDO non initialisé.");
        }
        $this->pdo = $pdo;
    }

    // =============================================================
    // =================== ADMIN CRUD METHODS =====================
    // =============================================================

    /**
     * Get user statistics by role
     * @return array
     */
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
            throw new Exception("Database error retrieving stats: " . $e->getMessage());
        }
    }

    /**
     * Get users filtered by role
     * @param string|null $role
     * @return array
     */
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
            throw new Exception("Database error retrieving users: " . $e->getMessage());
        }
    }

    /**
     * Create user (Admin version with all fields)
     * @param array $data
     * @return int
     */
    public function createUserAdmin(array $data): int {
        // Validation de l'email
        $checkEmail = $this->pdo->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmail->execute([$data['email']]);
        if ($checkEmail->fetch()) {
            throw new Exception("Cet email est déjà utilisé.");
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

    /**
     * Update user (Admin version)
     * @param array $data
     * @return bool
     */
    public function updateUserAdmin(array $data): bool {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $checkEmail = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->execute([$data['email'], $data['id']]);
        if ($checkEmail->fetch()) {
            throw new Exception("Cet email est déjà utilisé par un autre compte.");
        }

        $fields = [
            'first_name', 'last_name', 'email', 'phone', 'role', 'department', 'manager_id'
        ];
        $setClauses = [];
        $executeParams = [];

        foreach ($fields as $field) {
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

    /**
     * Delete user (Admin version)
     * @param int $id
     * @return bool
     */
    public function deleteUserAdmin(int $id): bool {
        if (!$id) {
             throw new Exception("ID utilisateur manquant.");
        }
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all users for export
     * @return array
     */
    public function getAllUsersForExport(): array {
         $stmt = $this->pdo->query("
            SELECT id, first_name, last_name, email, phone, role, department, created_at 
            FROM users 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =============================================================
    // =================== AUTHENTICATION METHODS =================
    // =============================================================

    /**
     * Vérifie si un email existe déjà
     * @param string $email
     * @return bool
     */
    public function emailExists($email)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Récupère un utilisateur par ID
     * @param int $userId
     * @return array|false
     */
    public function findById(int $userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur (Auth version - uses User object)
     * @param User $user
     * @return bool
     */
    public function createUser(User $user)
    {
        $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone, password, role, department)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
            $user->getPhone(),
            $hashedPassword,
            $user->getRole(),
            $user->getDepartment()
        ]);
    }

    /**
     * Récupère un utilisateur par email
     * @param string $email
     * @return array|false
     */
    public function findUserByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Stocke le token de réinitialisation et sa date d'expiration
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function storeResetToken($email, $token)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
            WHERE email = ?
        ");
        return $stmt->execute([$token, $email]);
    }

    /**
     * Vérifie si le token est valide (existe et non expiré)
     * @param string $token
     * @return array|false
     */
    public function verifyToken($token)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE reset_token = ? AND reset_expires > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le mot de passe et supprime le token
     * @param int $user_id
     * @param string $password
     * @return bool
     */
    public function updatePassword($user_id, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE id = ?
        ");
        return $stmt->execute([$hashedPassword, $user_id]);
    }

    /**
     * Récupère le code de devise préféré de l'utilisateur.
     * @param int $userId L'ID de l'utilisateur.
     * @return string Le code de devise (ex: 'EUR', 'MAD').
     */
    public function getPreferredCurrency(int $userId): string {
        try {
            $sql = "SELECT preferred_currency FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Retourne la devise si trouvée, sinon 'EUR' par défaut.
            return $result['preferred_currency'] ?? 'EUR';
        } catch (PDOException $e) {
            // Loggez l'erreur de BDD
            error_log("DB Error in UserModel::getPreferredCurrency: " . $e->getMessage());
            return 'EUR';
        }
    }
}
