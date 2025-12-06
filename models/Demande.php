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

    public function creer(int $user_id, string $objet_mission, string $lieu_deplacement, string $date_depart, string $date_retour) {
        $sql = "INSERT INTO demande_frais (user_id, objet_mission, lieu_deplacement, date_depart, date_retour) VALUES (:u, :o, :l, :dd, :dr)";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([':u'=>$user_id, ':o'=>$objet_mission, ':l'=>$lieu_deplacement, ':dd'=>$date_depart, ':dr'=>$date_retour]);
        if ($ok) return (int)$this->pdo->lastInsertId();
        return false;
    }

    public function getAll(?string $statut = null): array {
        if ($statut) {
            $stmt = $this->pdo->prepare("
                SELECT d.*, u.first_name, u.last_name, CONCAT(u.first_name, ' ', u.last_name) AS user_nom,
                       m.first_name AS manager_first_name, m.last_name AS manager_last_name,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_nom
                FROM demande_frais d
                JOIN users u ON d.user_id = u.id
                LEFT JOIN users m ON d.manager_id_validation = m.id
                WHERE d.statut = ?
                ORDER BY d.created_at DESC
            ");
            $stmt->execute([$statut]);
        } else {
            $stmt = $this->pdo->query("
                SELECT d.*, u.first_name, u.last_name, CONCAT(u.first_name, ' ', u.last_name) AS user_nom,
                       m.first_name AS manager_first_name, m.last_name AS manager_last_name,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_nom
                FROM demande_frais d
                JOIN users u ON d.user_id = u.id
                LEFT JOIN users m ON d.manager_id_validation = m.id
                ORDER BY d.created_at DESC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM demande_frais WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function changerStatut(int $id, string $nouveau, int $user_id): bool {
        $stmt = $this->pdo->prepare("SELECT statut FROM demande_frais WHERE id = ?");
        $stmt->execute([$id]);
        $ancien = $stmt->fetchColumn();
        $upd = $this->pdo->prepare("UPDATE demande_frais SET statut = ?, date_traitement = NOW(), manager_id_validation = ? WHERE id = ?");
        $ok = $upd->execute([$nouveau, $user_id, $id]);
        $this->histoSrv->add($id, $ancien, $nouveau, $user_id);
        return (bool)$ok;
    }

    public function majMontantTotal(int $id): float {
        $total = $this->detailsSrv->sumMontants($id);
        return (float)$total;
    }

    public function supprimer(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM demande_frais WHERE id = ?");
        return (bool)$stmt->execute([$id]);
    }

    public function getDetails(int $id): array {
        return $this->detailsSrv->getByDemande($id);
    }
}