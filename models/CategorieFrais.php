<?php
// classes/CategorieFrais.php

class CategorieFrais {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function getAll(){
        $stmt = $this->pdo->query("SELECT * FROM categories_frais ORDER BY id ASC");
        return $stmt->fetchAll();
    }

        public function getById($id){
        $stmt = $this->pdo->prepare("SELECT * FROM categories_frais WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function create(string $nom, ?string $desc = null): bool {
        $stmt = $this->pdo->prepare("INSERT INTO categories_frais (nom, description) VALUES (?, ?)");
        return (bool)$stmt->execute([$nom, $desc]);
    }
}