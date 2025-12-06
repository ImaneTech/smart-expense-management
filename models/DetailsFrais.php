<?php
// classes/DetailsFrais.php
class DetailsFrais {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function addDetail(int $demande_id, int $categorie_id, string $date_depense, float $montant, ?string $description = null, ?string $justificatif_path = null): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO details_frais (demande_id, categorie_id, date_depense, montant, description, justificatif_path)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return (bool)$stmt->execute([$demande_id, $categorie_id, $date_depense, $montant, $description, $justificatif_path]);
    }

    public function getByDemande(int $demande_id): array {
        $stmt = $this->pdo->prepare("
            SELECT df.*, c.nom AS categorie_nom
            FROM details_frais df
            JOIN categories_frais c ON df.categorie_id = c.id
            WHERE df.demande_id = ?
            ORDER BY df.id ASC
        ");
        $stmt->execute([$demande_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sumMontants(int $demande_id): float {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(montant),0) FROM details_frais WHERE demande_id = ?");
        $stmt->execute([$demande_id]);
        return (float)$stmt->fetchColumn();
    }
}