<?php
// classes/HistoriqueStatus.php
class HistoriqueStatus {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function add(int $demande_id, ?string $ancien, string $nouveau, string $utilisateur): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO historiquestatus (demande_id, ancien_statut, nouveau_statut, utilisateur)
            VALUES (?, ?, ?, ?)
        ");
        return (bool)$stmt->execute([$demande_id, $ancien, $nouveau, $utilisateur]);
    }

    public function getByDemande(int $demande_id): array {
        $stmt = $this->pdo->prepare("SELECT * FROM historiquestatus WHERE demande_id = ? ORDER BY date_changement DESC");
        $stmt->execute([$demande_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
