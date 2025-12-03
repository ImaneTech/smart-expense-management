<?php
class SettingsController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Mise à jour du Thème
    public function updateDisplaySettings($userId, $theme) {
        $validThemes = ['light', 'dark'];
        if (!in_array($theme, $validThemes)) return "Thème invalide.";

        $query = "UPDATE users SET theme = :theme WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':theme', $theme);
        $stmt->bindParam(':id', $userId);

        if ($stmt->execute()) {
            // Cookie pour 30 jours
            setcookie('theme', $theme, time() + (86400 * 30), "/"); 
            $_COOKIE['theme'] = $theme; 
            return true;
        }
        return "Erreur lors de l'enregistrement.";
    }

    // Mise à jour de la Devise
    public function updateInputPreferences($userId, $currency) {
        $validCurrencies = ['MAD', 'EUR', 'USD'];
        if (!in_array($currency, $validCurrencies)) return "Devise non supportée.";

        $query = "UPDATE users SET preferred_currency = :currency WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':currency', $currency);
        $stmt->bindParam(':id', $userId);

        if ($stmt->execute()) {
            $_SESSION['user_currency'] = $currency;
            return true;
        }
        return "Erreur lors de l'enregistrement.";
    }
    
    // Récupération des données
    public function getSettings($userId) {
        $stmt = $this->db->prepare("SELECT theme, preferred_currency FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>