<?php
// =============================================================
// ================= PROFILE CONTROLLER =======================
// Fichier : controllers/ProfileController.php
// Gère les informations et la sécurité du profil utilisateur
// =============================================================

require_once BASE_PATH . 'models/UserModel.php';

class ProfileController {

    private $db;
    private $userModel;

    // =============================================================
    // =================== CONSTRUCTEUR ===========================
    // Initialise le modèle et vérifie l'authentification
    // =============================================================
    public function __construct($db) {
        $this->db = $db;

        if (class_exists('UserModel')) {
            $this->userModel = new UserModel($this->db);
        } else {
            die("Erreur critique : UserModel introuvable.");
        }

        $this->checkAuth();
    }

    // =============================================================
    // =================== AUTHENTIFICATION =======================
    // Vérifie si l'utilisateur est connecté
    // =============================================================
    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }

    // =============================================================
    // =================== GESTION DU PROFIL ======================
    // =============================================================

    /**
     * Récupère les informations d'un utilisateur
     * @param int $id
     * @return array|false
     */
    public function getUser($id) {
        $query = "SELECT id, last_name AS nom, first_name AS prenom, email, role, phone, department, password 
                  FROM users 
                  WHERE id = :id 
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour les informations de l'utilisateur
     * @param int $id
     * @param string $nom
     * @param string $prenom
     * @param string $phone
     * @param string $department
     * @return bool
     */
    public function updateInfo($id, $nom, $prenom, $phone, $department) {
        // Nettoyage des données
        $nom = strip_tags(trim($nom));
        $prenom = strip_tags(trim($prenom));
        $phone = strip_tags(trim($phone));
        $department = strip_tags(trim($department));

        $query = "UPDATE users 
                  SET last_name = :nom, first_name = :prenom, phone = :phone, department = :dept 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':dept', $department);
        $stmt->bindParam(':id', $id);

        return $stmt->execute(); // true si succès, false sinon
    }

    /**
     * Met à jour le mot de passe de l'utilisateur
     * Vérifie le mot de passe actuel avant mise à jour
     * @param int $id
     * @param string $currentPwd
     * @param string $newPwd
     * @return bool|string true si succès, message d'erreur sinon
     */
    public function updatePassword($id, $currentPwd, $newPwd) {
        $user = $this->getUser($id);
        if (!$user) return "Utilisateur introuvable.";
        if (!password_verify($currentPwd, $user['password'])) return "Le mot de passe actuel est incorrect.";

        if ($this->userModel->updatePassword($id, $newPwd)) {
            return true;
        }
        return "Erreur technique lors de la mise à jour.";
    }
}
?>
