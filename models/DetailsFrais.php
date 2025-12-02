<?php
// classes/DetailsFrais.php
class DetailsFrais {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function addDetail(int $demande_id, int $categorie_id, float $montant, ?string $description = null, ?string $justificatif = null): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO detailsfrais (demande_id, categorie_id, montant, description, justificatif)
            VALUES (?, ?, ?, ?, ?)
        ");
        return (bool)$stmt->execute([$demande_id, $categorie_id, $montant, $description, $justificatif]);
    }

    public function getByDemande(int $demande_id): array {
        $stmt = $this->pdo->prepare("
            SELECT df.*, c.nom AS categorie_nom
            FROM detailsfrais df
            JOIN categoriefrais c ON df.categorie_id = c.id
            WHERE df.demande_id = ?
            ORDER BY df.id ASC
        ");
        $stmt->execute([$demande_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sumMontants(int $demande_id): float {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(montant),0) FROM detailsfrais WHERE demande_id = ?");
        $stmt->execute([$demande_id]);
        return (float)$stmt->fetchColumn();
    }
}

