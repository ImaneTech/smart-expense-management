<?php
$dbname = "gestion_frais_db";
require_once "config.php";

class Demande {

    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    // Récupérer toutes les demandes
    public function getAllDemandes() {

        $sql = "SELECT d.id, d.date_demande, d.montant_total, d.status,
                       v.nom AS visiteur, c.libelle AS categorie
                FROM demandes_frais d
                JOIN visiteur v ON d.id_visiteur = v.id
                JOIN categorie_frais c ON d.id_categorie = c.id
                ORDER BY d.date_demande DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une demande par ID
    public function getDemandeById($id) {

        $sql = "SELECT * FROM demandes_frais WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mise à jour du statut
    public function updateStatus($id, $status) {

        $sql = "UPDATE demandes_frais SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$status, $id]);
    }
}
