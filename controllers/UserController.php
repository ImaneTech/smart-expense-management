<?php
require_once(__DIR__ . '/../models/User.php');
require_once(__DIR__ . '/../models/UserModel.php');
require_once __DIR__ . '/../includes/sendMail.php';
require_once __DIR__ . '/../includes/flash.php'; 

class UserController
{
    private $model;

    public function __construct($pdo)
    {
        $this->model = new UserModel($pdo);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /* ============================================================
       REGISTER
       ============================================================ */
 public function register($data)
{
    $first_name   = trim($data['first_name']);
    $last_name    = trim($data['last_name']);
    $email        = trim($data['email']);
    $phone        = trim($data['phone']);
    $password     = $data['password'];
    $confirm_pass = $data['confirm_password'];
    $role         = $data['role'];
    $department   = $data['department'];

    // 1. Vérification Mots de passe
    if ($password !== $confirm_pass) {
        // On retourne un tableau au lieu de faire setFlash + return false
        return [
            'type' => 'danger',
            'message' => 'Les mots de passe ne correspondent pas.'
        ];
    }

    // 2. Vérification Email existant
    if ($this->model->emailExists($email)) {
        return [
            'type' => 'warning',
            'message' => 'Email déjà utilisé.'
        ];
    }

    $user = new User($first_name, $last_name, $email, $phone, $password, $role, $department);

    // 3. Création du User
    if ($this->model->createUser($user)) {
        return [
            'type' => 'success',
            'message' => 'Compte créé avec succès !'
        ];
    }

    // 4. Erreur générale
    return [
        'type' => 'danger', 
        'message' => 'Erreur lors de la création du compte.'
    ];
}

    /* ============================================================
       LOGIN
       ============================================================ */
   public function login($email, $password)
{
    // 1. Récupération de l'utilisateur
    $user = $this->model->findUserByEmail($email);

    // 2. Vérification mot de passe
    if ($user && password_verify($password, $user['password'])) {
        // Succès : On retourne un tableau avec success = true
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Connexion réussie !'
        ];
    }

    // 3. Échec : On retourne un tableau avec success = false
    return [
        'success' => false,
        'message' => 'Email ou mot de passe incorrect.'
    ];
}

    /* ============================================================
       SEND RESET PASSWORD
       ============================================================ */
    public function sendResetPassword($email)
    {
        $user = $this->model->findUserByEmail($email);
        if (!$user) {
            setFlash('danger', 'Aucun compte trouvé avec cet email.');
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $this->model->storeResetToken($email, $token);

        $resetLink = "http://localhost/smart-expense-management/views/resetpassword.php?token=$token";

        $subject = "Réinitialisation de mot de passe - GoTrackr";
        $message = "Bonjour " . $user['first_name'] . ",<br>";
        $message .= "Cliquez sur ce lien pour réinitialiser votre mot de passe :<br>";
        $message .= "<a href='$resetLink'>$resetLink</a><br>";
        $message .= "Ce lien expire dans 1 heure.<br>";

        if (sendMail($email, $subject, $message)) {
            setFlash('success', 'Un email de réinitialisation a été envoyé à votre adresse.');
            return true;
        } else {
            setFlash('danger', 'Erreur lors de l\'envoi de l\'email.');
            return false;
        }
    }

    /* ============================================================
       RESET PASSWORD
       ============================================================ */
    public function resetPassword($token, $new_password)
    {
        $user = $this->model->verifyToken($token);
        if (!$user) {
            setFlash('danger', 'Lien invalide ou expiré.');
            return false;
        }

        $this->model->updatePassword($user["id"], $new_password);
        setFlash('success', 'Mot de passe réinitialisé avec succès.');
        return true;
    }
}
