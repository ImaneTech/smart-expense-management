<?php
require_once BASE_PATH . 'models/UserModel.php'; 

class ProfileController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        if (class_exists('UserModel')) {
            $this->userModel = new UserModel($this->db);
        } else {
            die("Erreur critique : UserModel introuvable.");
        }
        $this->checkAuth();
    }

    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }

    public function getUser($id) {
        $query = "SELECT id, last_name AS nom, first_name AS prenom, email, role, phone, department, password 
                  FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateInfo($id, $nom, $prenom, $phone, $department) {
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

        return $stmt->execute(); // Retourne true ou false
    }

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