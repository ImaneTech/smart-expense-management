<?php
// =============================================================
// ================= SETTINGS CONTROLLER ======================
// Fichier : controllers/SettingsController.php
// Gère les préférences utilisateur : thème et devise
// =============================================================

class SettingsController {

    private $pdo;

    // =============================================================
    // =================== CONSTRUCTEUR ===========================
    // Initialise la connexion à la base de données
    // =============================================================
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // =============================================================
    // =================== MISE À JOUR DU THÈME ===================
    // =============================================================
    /**
     * Met à jour le thème d'affichage d'un utilisateur.
     * @param int $userId
     * @param string $theme (light ou dark)
     * @return bool|string True si succès, message d'erreur sinon
     */
    public function updateDisplaySettings($userId, $theme) {
        $validThemes = ['light', 'dark'];
        if (!in_array($theme, $validThemes)) return "Thème invalide.";

        $query = "UPDATE users SET theme = :theme WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':theme', $theme);
        $stmt->bindParam(':id', $userId);

        if ($stmt->execute()) {
            // Mise à jour du cookie pour 30 jours
            setcookie('theme', $theme, time() + (86400 * 30), "/"); 
            $_COOKIE['theme'] = $theme; 
            return true;
        }
        return "Erreur lors de l'enregistrement.";
    }

    // =============================================================
    // =================== MISE À JOUR DE LA DEVISE =================
    // =============================================================
    /**
     * Met à jour la devise préférée de l'utilisateur.
     * @param int $userId
     * @param string $currency (MAD, EUR, USD)
     * @return bool|string True si succès, message d'erreur sinon
     */
    public function updateInputPreferences($userId, $currency) {
        $validCurrencies = ['MAD', 'EUR', 'USD'];
        if (!in_array($currency, $validCurrencies)) return "Devise non supportée.";

        $query = "UPDATE users SET preferred_currency = :currency WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':currency', $currency);
        $stmt->bindParam(':id', $userId);

        if ($stmt->execute()) {
            $_SESSION['user_currency'] = $currency;
            return true;
        }
        return "Erreur lors de l'enregistrement.";
    }

    // =============================================================
    // =================== RÉCUPÉRATION DES PARAMÈTRES ============
    // =============================================================
    /**
     * Récupère les préférences de l'utilisateur.
     * @param int $userId
     * @return array Associatif avec 'theme' et 'preferred_currency'
     */
    public function getSettings($userId) {
        $stmt = $this->pdo->prepare("SELECT theme, preferred_currency FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
