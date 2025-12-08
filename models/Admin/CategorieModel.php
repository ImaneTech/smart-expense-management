<?php
namespace Models\Admin;

use PDO;
use PDOException;

class CategorieModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

// --- Fetch Categories ---
// --- Fetch Categories ---
public function getAll(): array {
    try {
        // 🎯 CORRECTION: Utiliser l'alias "nom" explicitement pour garantir la casse attendue par le JS.
        $stmt = $this->pdo->query("SELECT id, nom AS nom, description FROM categories_frais ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    } catch (PDOException $e) {
        // Si cela échoue, cette erreur sera affichée par le Contrôleur
        throw new \Exception("Database error retrieving categories: " . $e->getMessage());
    }
}
    // --- Get Stats (Simple Count) ---
    public function getTotalCount(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM categories_frais");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new \Exception("Database error retrieving category count: " . $e->getMessage());
        }
    }

    // --- Create Category ---
    public function create(string $nom, ?string $description): int {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO categories_frais (nom, description) VALUES (?, ?)");
            $stmt->execute([$nom, $description]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la création de la catégorie: " . $e->getMessage());
        }
    }

    // --- Update Category ---
    public function update(int $id, string $nom, ?string $description): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE categories_frais SET nom = ?, description = ? WHERE id = ?");
            return $stmt->execute([$nom, $description, $id]);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la modification de la catégorie: " . $e->getMessage());
        }
    }

    // --- Delete Category ---
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM categories_frais WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la suppression de la catégorie: " . $e->getMessage());
        }
    }

    // --- Export Categories (same as getAll) ---
    public function getAllForExport(): array {
        return $this->getAll();
    }
}