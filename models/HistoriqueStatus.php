<?php
// classes/HistoriqueStatus.php
class HistoriqueStatus {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function add(int $demande_id, ?string $ancien, string $nouveau, int $user_id, ?string $commentaire = null): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO historique_statuts (demande_id, user_id, ancien_statut, nouveau_statut, commentaire)
            VALUES (?, ?, ?, ?, ?)
        ");
        return (bool)$stmt->execute([$demande_id, $user_id, $ancien, $nouveau, $commentaire]);
    }

    public function getByDemande(int $demande_id): array {
        $stmt = $this->pdo->prepare("SELECT * FROM historique_statuts WHERE demande_id = ? ORDER BY date_action DESC");
        $stmt->execute([$demande_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>