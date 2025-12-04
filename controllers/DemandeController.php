<?php
// controllers/DemandeController.php (VERSION FINALE CORRIGÉE ET HARMONISÉE)

require_once __DIR__ . '/../models/DemandeModel.php';
require_once __DIR__ . '/../controllers/TeamController.php';

class DemandeController {

    private DemandeModel $model;
    private \PDO $db;
    private int $managerId;
    private int $userId;

    public function __construct(\PDO $db) {
        $this->db = $db;

        // Démarrage de session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->checkAuth();

        $this->userId = (int)($_SESSION['user_id'] ?? 0);
        $this->managerId = $this->userId;

        $this->model = new DemandeModel($this->db);
    }

    private function checkAuth(): void {
        if (!isset($_SESSION['user_id'])) {
            $redirectUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'views/auth/login.php';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    // =========================================================
    // SECTION 1: DASHBOARD & LISTES
    // =========================================================

    public function getDashboardData(): array {
        $stats = $this->model->getDashboardStats($this->managerId);

        $teamController = new TeamController($this->db, $this->managerId);
        $allTeamMembers = $teamController->getAllTeamMembers();

        $allDemandes = $this->getDemandesList('En attente');
        $latestDemandes = array_slice($allDemandes, 0, 5);
        $teamMembers = array_slice($allTeamMembers, 0, 5);

        return [
            'stats' => $stats,
            'latest' => $latestDemandes,
            'team' => $teamMembers,
        ];
    }

    /**
     * Récupère l'historique des demandes traitées par le manager.
     */
    public function getHistorique(): array {
        $statuses = ['Validée Manager', 'Rejetée Manager'];
        return $this->model->getDemandesByStatuses($this->managerId, $statuses);
    }

    /**
     * Récupère la liste des demandes avec filtre de statut.
     */
    public function getDemandesList(?string $statut = 'toutes'): array {
        if (strtolower($statut) === 'toutes') {
            $statuses = ['En attente', 'Validée Manager', 'Rejetée Manager'];
        } else {
            $statuses = [$statut];
        }

        return $this->model->getDemandesByStatuses($this->managerId, $statuses);
    }

    // =========================================================
    // SECTION 2: DÉTAILS ET ACTIONS
    // =========================================================

    public function getDemandeDetails(int $demandeId): ?array {
        $demande_info = $this->model->getDemandeById($demandeId, $this->managerId);

        if (!$demande_info) return null;

        $demande_info['statut'] = $demande_info['statut'] ?? 'En attente';
        $demande_info['lignes_frais'] = $this->model->getDetailsFrais($demandeId);

        return $demande_info;
    }

    /**
     * Traite la demande en validant ou rejetant avec éventuellement un motif.
     */
    public function traiterDemandeAction(array $postData): void {
        if (!isset($postData['action'], $postData['demande_id'])) {
            $_SESSION['error_message'] = "Données incomplètes.";
            header('Location: demandes_liste.php');
            exit;
        }

        $id = (int)$postData['demande_id'];
        $action = $postData['action'];
        $motif = $postData['commentaire_manager'] ?? null;

        $demandeActuelle = $this->model->getDemandeById($id, $this->managerId);
        if (!$demandeActuelle) {
            $_SESSION['error_message'] = "Erreur: Demande introuvable ou accès refusé.";
            header('Location: demandes_liste.php');
            exit;
        }

        $nouveauStatut = ($action === 'valider') ? 'Validée Manager' : 'Rejetée Manager';

        if ($action === 'rejeter' && empty(trim($motif))) {
            $_SESSION['error_message'] = "Le motif de rejet est obligatoire.";
            header('Location: details_demande.php?id=' . $id);
            exit;
        }

        // Mise à jour via le modèle
        if ($this->model->updateStatut($id, $nouveauStatut, $this->managerId, $this->userId, $motif)) {
            $_SESSION['message'] = "Demande (ID: {$id}) traitée avec succès.";
            header('Location: details_demande.php?id=' . $id);
            exit;
        } else {
            $_SESSION['error_message'] = "Erreur technique lors de la mise à jour.";
            header('Location: details_demande.php?id=' . $id);
            exit;
        }
    }

    public function getManagerId(): int {
        return $this->managerId;
    }

    /**
     * Recherche avancée avec filtres multiples.
     * Recommandé: déplacer la logique SQL complète dans le modèle pour un contrôleur plus léger.
     */
    public function faireUneRecherche(array $filters = []): array {
        $sql = "
            SELECT
                d.*,
                u.first_name,
                u.last_name,
                u.email,
                COALESCE(SUM(df.montant), 0) AS total_calcule
            FROM demande_frais d
            JOIN users u ON d.user_id = u.id
            LEFT JOIN details_frais df ON d.id = df.demande_id
            WHERE (u.manager_id = :manager_id OR d.manager_id_validation = :manager_id)
        ";

        $params = [':manager_id' => $this->managerId];

        if (!empty($filters['employe'])) {
            $sql .= " AND d.user_id = :emp_id";
            $params[':emp_id'] = $filters['employe'];
        }

        if (!empty($filters['statut'])) {
            $sql .= " AND d.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }

        if (!empty($filters['date_debut'])) {
            $sql .= " AND d.date_depart >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $sql .= " AND d.date_depart <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }

        $sql .= " GROUP BY d.id ORDER BY d.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur Recherche : " . $e->getMessage());
            return [];
        }
    }
}
