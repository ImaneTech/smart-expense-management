<?php
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

    /* -----------------------------------------------------------
       Vérifie si un email existe déjà
       ----------------------------------------------------------- */
    public function emailExists($email)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    /* -----------------------------------------------------------
       Crée un nouvel utilisateur
       ----------------------------------------------------------- */
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


    /* ----------------------------------------------------------------
    Récupère un utilisateur par email
    ---------------------------------------------------------------- */
    public function findUserByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Stocke le token de réinitialisation et sa date d'expiration
    public function storeResetToken($email, $token)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
            WHERE email = ?
        ");
        return $stmt->execute([$token, $email]);
    }


    /* ------------------------------------------------------------------
    Vérifie si le token est valide (existe et non expiré)
  ------------------------------------------------------------------- */
    public function verifyToken($token)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE reset_token = ? AND reset_expires > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ----------------------------------------------------------------
     Met à jour le mot de passe et supprime le token
    ---------------------------------------------------------------- */
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
}
