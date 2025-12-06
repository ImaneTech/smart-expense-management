<?php
// classes/CategorieFrais.php
class CategorieFrais {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM categories_frais ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nom, ?string $desc = null): bool {
        $stmt = $this->pdo->prepare("INSERT INTO categories_frais (nom, description) VALUES (?, ?)");
        return (bool)$stmt->execute([$nom, $desc]);
    }
}