<?php

class DemandeFrais {
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getAllStats(){
        $stmt = $this->pdo->query("SELECT statut, COUNT(*) as total FROM demande_frais GROUP BY statut");
        return $stmt->fetchAll();
    }

    public function getById($id){
        $stmt = $this->pdo->prepare("
            SELECT d.*, u.first_name, u.last_name 
            FROM demande_frais d 
            JOIN users u ON d.user_id = u.id 
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getDetails($demande_id){
        $stmt = $this->pdo->prepare("
            SELECT df.*, c.nom as categorie 
            FROM details_frais df 
            JOIN categories_frais c ON df.categorie_id = c.id 
            WHERE df.demande_id = ?
        ");
        $stmt->execute([$demande_id]);
        return $stmt->fetchAll();
    }
}
