<?php
// classes/Demande.php
require_once __DIR__ . '/DetailsFrais.php';
require_once __DIR__ . '/HistoriqueStatus.php';

class Demande {
    private $pdo;
    private $detailsSrv;
    private $histoSrv;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->detailsSrv = new DetailsFrais($pdo);
        $this->histoSrv = new HistoriqueStatus($pdo);
    }

    public function creer(int $visiteur_id, string $objectif, string $date_mission) {
        $sql = "INSERT INTO demandedefrais (visiteur_id, objectif, date_mission) VALUES (:v, :o, :d)";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([':v'=>$visiteur_id, ':o'=>$objectif, ':d'=>$date_mission]);
        if ($ok) return (int)$this->pdo->lastInsertId();
        return false;
    }

    public function getAll(?string $statut = null): array {
        if ($statut) {
            $stmt = $this->pdo->prepare("
                SELECT d.*, v.nom AS visiteur_nom, m.nom AS manager_nom
                FROM demandedefrais d
                JOIN visiteur v ON d.visiteur_id = v.id
                LEFT JOIN manager m ON d.manager_id = m.id
                WHERE d.statut_actuel = ?
                ORDER BY d.date_creation DESC
            ");
            $stmt->execute([$statut]);
        } else {
            $stmt = $this->pdo->query("
                SELECT d.*, v.nom AS visiteur_nom, m.nom AS manager_nom
                FROM demandedefrais d
                JOIN visiteur v ON d.visiteur_id = v.id
                LEFT JOIN manager m ON d.manager_id = m.id
                ORDER BY d.date_creation DESC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM demandedefrais WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function changerStatut(int $id, string $nouveau, string $utilisateur): bool {
        $stmt = $this->pdo->prepare("SELECT statut_actuel FROM demandedefrais WHERE id = ?");
        $stmt->execute([$id]);
        $ancien = $stmt->fetchColumn();
        $upd = $this->pdo->prepare("UPDATE demandedefrais SET statut_actuel = ? WHERE id = ?");
        $ok = $upd->execute([$nouveau, $id]);
        $this->histoSrv->add($id, $ancien, $nouveau, $utilisateur);
        return (bool)$ok;
    }

    public function majMontantTotal(int $id): float {
        $total = $this->detailsSrv->sumMontants($id);
        $stmt = $this->pdo->prepare("UPDATE demandedefrais SET montant_total = ? WHERE id = ?");
        $stmt->execute([$total, $id]);
        return (float)$total;
    }

    public function supprimer(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM demandedefrais WHERE id = ?");
        return (bool)$stmt->execute([$id]);
    }

    public function getDetails(int $id): array {
        return $this->detailsSrv->getByDemande($id);
    }
}
