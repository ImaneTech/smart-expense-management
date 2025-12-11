<?php
// =============================================
// ======= MODEL CATEGORIE ====================
// Fichier : Models/CategorieModel.php
// Consolidated version - Complete CRUD operations
// =============================================

class CategorieModel {
    
    private $pdo;
    private $table = 'categories_frais';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // =============================================
    // ======= FETCH CATEGORIES ===================
    // =============================================
    
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
     * Récupère toutes les catégories avec description (pour API)
     * @return array
     */
    public function getAll(): array {
        try {
            // Utiliser l'alias "nom" explicitement pour garantir la casse attendue par le JS
            $stmt = $this->pdo->query("SELECT id, nom AS nom, description FROM categories_frais ORDER BY nom ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            throw new Exception("Database error retrieving categories: " . $e->getMessage());
        }
    }

    // =============================================
    // ======= GET STATS ==========================
    // =============================================
    
    /**
     * Get total count of categories
     * @return int
     */
    public function getTotalCount(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM categories_frais");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Database error retrieving category count: " . $e->getMessage());
        }
    }

    // =============================================
    // ======= CREATE CATEGORY ====================
    // =============================================
    
    /**
     * Ajoute une nouvelle catégorie (simple version).
     * @param string $nom Le nom de la nouvelle catégorie.
     * @return bool Succès de l'ajout.
     */
    public function addCategorie(string $nom): bool {
        // Vérifier si le nom existe déjà
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
     * Create category with description (API version)
     * @param string $nom
     * @param string|null $description
     * @return int Last insert ID
     */
    public function create(string $nom, ?string $description): int {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO categories_frais (nom, description) VALUES (?, ?)");
            $stmt->execute([$nom, $description]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de la catégorie: " . $e->getMessage());
        }
    }

    // =============================================
    // ======= UPDATE CATEGORY ====================
    // =============================================
    
    /**
     * Update category
     * @param int $id
     * @param string $nom
     * @param string|null $description
     * @return bool
     */
    public function update(int $id, string $nom, ?string $description): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE categories_frais SET nom = ?, description = ? WHERE id = ?");
            return $stmt->execute([$nom, $description, $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la modification de la catégorie: " . $e->getMessage());
        }
    }

    // =============================================
    // ======= DELETE CATEGORY ====================
    // =============================================
    
    /**
     * Supprime une catégorie par son ID.
     * NOTE: Il faut gérer les contraintes de clé étrangère (CASCADE DELETE ou RESTRICT).
     * @param int $id L'ID de la catégorie à supprimer.
     * @return bool Succès de la suppression.
     */
    public function deleteCategorie(int $id): bool {
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

    /**
     * Delete category (API version - same as deleteCategorie)
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM categories_frais WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la catégorie: " . $e->getMessage());
        }
    }

    // =============================================
    // ======= EXPORT CATEGORIES ==================
    // =============================================
    
    /**
     * Export categories (same as getAll)
     * @return array
     */
    public function getAllForExport(): array {
        return $this->getAll();
    }
}
