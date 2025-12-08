<?php
// models/CategorieModel.php

class CategorieModel {
    
    private $pdo;
    private $table = 'categories_frais';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les catégories de frais.
     * @return array
     */
    public function getAllCategories(): array {
        $sql = "SELECT id, nom FROM {$this->table} ORDER BY nom ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute une nouvelle catégorie.
     * @param string $nom Le nom de la nouvelle catégorie.
     * @return bool Succès de l'ajout.
     */
    public function addCategorie(string $nom): bool {
        // Optionnel : Vérifier si le nom existe déjà
        $checkSql = "SELECT COUNT(*) FROM {$this->table} WHERE nom = ?";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([$nom]);
        if ($checkStmt->fetchColumn() > 0) {
            // Catégorie existe déjà
            return false; 
        }

        $sql = "INSERT INTO {$this->table} (nom) VALUES (?)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$nom]);
        } catch (PDOException $e) {
            // Gérer les erreurs (log)
            return false;
        }
    }

    /**
     * Supprime une catégorie par son ID.
     * NOTE: Il faut gérer les contraintes de clé étrangère (CASCADE DELETE ou RESTRICT).
     * @param int $id L'ID de la catégorie à supprimer.
     * @return bool Succès de la suppression.
     */
    public function deleteCategorie(int $id): bool {
        // Envisagez une transaction si vous devez d'abord effacer les détails de frais
        // associés ou si la clé est en RESTRICT.
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // Si la contrainte RESTRICT bloque la suppression :
            error_log("Erreur de suppression de catégorie: " . $e->getMessage());
            return false;
        }
    }
}