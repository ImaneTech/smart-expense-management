<?php

class Categorie {
    private $pdo;
    public function __construct($pdo){
        $this->pdo = $pdo;
    }
    public function getAll(){
        $stmt = $this->pdo->query("SELECT * FROM categories_frais ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
