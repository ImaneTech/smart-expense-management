<?php
// =============================================================
// ================= USER CONTROLLER ==========================
// Fichier : controllers/UserController.php
// Gère l'inscription, la connexion, la réinitialisation de mot de passe
// et les préférences utilisateur.
// =============================================================

require_once(__DIR__ . '/../Models/User.php');
require_once(__DIR__ . '/../Models/UserModel.php');
require_once __DIR__ . '/../includes/sendMail.php';
require_once __DIR__ . '/../includes/flash.php'; 

class UserController
{
    private $model;

    // =============================================================
    // =================== CONSTRUCTEUR ===========================
    // Initialise le modèle UserModel et démarre la session si nécessaire
    // =============================================================
    public function __construct($pdo)
    {
        $this->model = new UserModel($pdo);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // =============================================================
    // =================== INSCRIPTION ============================
    // =============================================================
    public function register(array $data): array
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

        // 3. Création du User
        $user = new User($first_name, $last_name, $email, $phone, $password, $role, $department);
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

    // =============================================================
    // =================== CONNEXION ==============================
    // =============================================================
    public function login(string $email, string $password): array
    {
        $user = $this->model->findUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Connexion réussie !'
            ];
        }

        return [
            'success' => false,
            'message' => 'Email ou mot de passe incorrect.'
        ];
    }

    // =============================================================
    // =================== RÉINITIALISATION =======================
    // =============================================================
/**
 * Envoie un email pour réinitialiser le mot de passe
 */
public function sendResetPassword(string $email): bool
{
    $user = $this->model->findUserByEmail($email);
    if (!$user) {
        setFlash('danger', 'Aucun compte trouvé avec cet email.');
        return false;
    }

    $token = bin2hex(random_bytes(32));
    $this->model->storeResetToken($email, $token);

    $resetLink = BASE_URL . "views/auth/resetpassword.php?token={$token}";

    $subject = "Réinitialisation de mot de passe - GoTrackr";

    // On passe le lien à sendMail pour qu’il le mette dans le bouton
    $body = $resetLink;

    if (sendMail($email, $subject, $body)) {
        setFlash('success', 'Un email de réinitialisation a été envoyé à votre adresse.');
        return true;
    } else {
        setFlash('danger', 'Erreur lors de l\'envoi de l\'email.');
        return false;
    }
}



    /**
     * Réinitialise le mot de passe à partir du token
     */
    public function resetPassword(string $token, string $new_password): bool
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

    // =============================================================
    // =================== PRÉFÉRENCES UTILISATEUR =================
    // =============================================================
    public function getPreferredCurrency(int $userId): string
    {
        return $this->model->getPreferredCurrency($userId);
    }
}
?>
