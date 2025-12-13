<?php

class Notification {
    private PDO $pdo;

    /**
     * Constructeur
     * @param PDO $pdo L'objet de connexion PDO à la base de données.
     */
    public function __construct(PDO $pdo) {
      
        $this->pdo = $pdo; 
    }

    // -----------------------------------------------------------------
    // 1. CRÉATION (CREATE)
    // -----------------------------------------------------------------
    
    /**
     * Crée et insère une nouvelle notification dans la base de données.
     */
    public function creerNotification(int $user_cible_id, int $demande_id, string $message, string $lien_url): bool {
        $sql = "INSERT INTO notifications (user_id_cible, demande_id, message, lien_url, lue) 
                VALUES (:user_cible, :demande_id, :message, :lien_url, 0)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            ':user_cible' => $user_cible_id,
            ':demande_id' => $demande_id,
            ':message' => $message,
            ':lien_url' => $lien_url
        ]);
    }

    // -----------------------------------------------------------------
    // 2. LECTURE LIMITÉE (READ - Utilisé par l'API pour le Modal)
    // -----------------------------------------------------------------
    
    /**
     * Récupère la liste limitée des notifications pour un utilisateur.
     */
    public function listerNotifications(int $user_id, int $limit = 10): array {
        $sql = "SELECT id, message, lien_url, date_creation, lue 
                FROM notifications 
                WHERE user_id_cible = :user_id  /* CORRIGÉ: Utilise user_id_cible */
                ORDER BY date_creation DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // -----------------------------------------------------------------
    // 3. LECTURE COMPLÈTE (READ - Utilisé par la page d'Historique)
    // -----------------------------------------------------------------

    /**
     * Récupère TOUTES les notifications pour un utilisateur (pour la page d'historique).
     */
    public function getAllNotifications(int $user_id): array {
        $sql = "SELECT id, message, lien_url, date_creation, lue 
                FROM notifications 
                WHERE user_id_cible = :user_id 
                ORDER BY date_creation DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT); 
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // -----------------------------------------------------------------
    // 4. FONCTIONS DE STATUT (COMPTER & MARQUER)
    // -----------------------------------------------------------------

    /**
     * Compte le nombre de notifications non lues.
     */
    public function compterNonLues(int $user_id): int {
        $sql = "SELECT COUNT(id) FROM notifications WHERE user_id_cible = :user_id AND lue = 0"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Marque toutes les notifications non lues comme lues.
     */
    public function marquerCommeLues(int $user_id): bool {
        $sql = "UPDATE notifications SET lue = 1 WHERE user_id_cible = :user_id AND lue = 0"; 
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([':user_id' => $user_id]);
    }
    
    // -----------------------------------------------------------------
    // 5. MÉTHODE OPTIONNELLE (Laissée commentée)
    // -----------------------------------------------------------------
    
    /*
    public function getManagerIdByDemandeId(int $demande_id): ?int {
        $sql = "SELECT u.id 
                FROM users u 
                JOIN demande_frais df ON u.id = df.manager_id 
                WHERE df.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$demande_id]);
        $manager_id = $stmt->fetchColumn();
        
        return $manager_id !== false ? (int)$manager_id : null;
    }
    */

}
?>