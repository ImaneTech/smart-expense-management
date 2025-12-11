<?php

declare(strict_types=1);
// =============================================================
// ================= DEMANDE CONTROLLER =======================
// Fichier : controllers/DemandeController.php
// Version VÉRIFIÉE et COMPLÈTE avec Notifications
// =============================================================

// --- Détection de la classe de notification disponible ---
// Utilisé pour le helper notifyUsers
require_once BASE_PATH . 'Models/NotificationModel.php';
$notificationClass = 'Notification';

// --- Inclusions sûres (teste plusieurs variantes de chemin) ---
$requiredFiles = [
    __DIR__ . '/../Models/DemandeModel.php',
    __DIR__ . '/../models/DemandeModel.php'
];
$included = false;
foreach ($requiredFiles as $f) {
    if (file_exists($f)) {
        require_once $f;
        $included = true;
        break;
    }
}
if (!$included) {
    throw new \RuntimeException("Fichier DemandeModel introuvable (checked: " . implode(', ', $requiredFiles) . ").");
}

// TeamController (si utilisé)
$teamControllerCandidates = [
    __DIR__ . '/../Controllers/TeamController.php',
    __DIR__ . '/../controllers/TeamController.php'
];
foreach ($teamControllerCandidates as $f) {
    if (file_exists($f)) {
        require_once $f;
        break;
    }
}

// DemandeFraisModel (employé) — tentative sur plusieurs chemins
$demandeFraisCandidates = [
    __DIR__ . '/../Models/Employe/DemandeFraisModel.php',
    __DIR__ . '/../models/Employe/DemandeFraisModel.php'
];
foreach ($demandeFraisCandidates as $f) {
    if (file_exists($f)) {
        require_once $f;
        break;
    }
}

// FileHandler (optionnel)
$fileHandlerCandidates = [
    __DIR__ . '/../includes/file_handler.php',
    __DIR__ . '/../Includes/file_handler.php'
];
foreach ($fileHandlerCandidates as $f) {
    if (file_exists($f)) {
        require_once $f;
        break;
    }
}

// NotificationModel / Notification
$notificationModelCandidates = [
    __DIR__ . '/../models/NotificationModel.php',
    __DIR__ . '/../Models/NotificationModel.php',
    __DIR__ . '/../models/Notification.php', // Ajout de la variante simple
    __DIR__ . '/../Models/Notification.php'
];
foreach ($notificationModelCandidates as $f) {
    if (file_exists($f)) {
        require_once $f;
        break;
    }
}


// =============================================================
// =================== DEFINITION DE LA CLASSE =================
// =============================================================
class DemandeController
{
    private DemandeModel $model;
    private \PDO $pdo;
    private int $managerId;
    private int $userId;

    /** @var \Models\Employe\DemandeFraisModel|null */
    private $employeModel;

    // =================== CONSTRUCTEUR ===========================
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->checkAuth();

        $this->userId = (int)($_SESSION['user_id'] ?? 0);
        $this->managerId = $this->userId;

        $this->model = new DemandeModel($this->pdo);

        if (class_exists('\Models\Employe\DemandeFraisModel')) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : '';
            $this->employeModel = new \Models\Employe\DemandeFraisModel($this->pdo, $basePath);
        } else {
            $this->employeModel = null;
        }
    }

    // =================== VERIFICATION AUTH ======================
    private function checkAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $redirectUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'views/auth/login.php';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    // =================== HELPER NOTIFICATION =====================

    /**
     * Helper pour la création de notification
     * @param int|int[] $recipientIds IDs des utilisateurs à notifier
     * @param int $demandeId ID de la demande concernée
     * @param string $message Le contenu de la notification
     * @param string $link Le lien vers la demande
     */
    private function notifyUsers(int|array $recipientIds, int $demandeId, string $message, string $link): void
    {
        try {
            $notificationModel = new Notification($this->pdo); 
            $recipients = is_array($recipientIds) ? $recipientIds : [$recipientIds];
            
            foreach ($recipients as $recipientId) {
                if ($recipientId > 0) {
                    $notificationModel->creerNotification($recipientId, $demandeId, $message, $link);
                }
            }
        } catch (\Exception $e) {
             error_log("Impossible de notifier : " . $e->getMessage());
        }
    }

    // =============================================================
    // =================== DASHBOARD & LISTES ======================
    // =============================================================

    /**
     * Récupère les données pour le dashboard manager
     */
    public function getDashboardData(): array {
        $stats = $this->model->getDashboardStats($this->managerId);

        if (!class_exists('TeamController')) {
             return [ 'stats' => $stats, 'latest' => [], 'team' => [] ];
        }

        $teamController = new TeamController($this->pdo, $this->managerId);
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
     * Historique des demandes traitées par le manager
     */
    public function getHistorique(): array {
        $statuses = ['Validée Manager', 'Rejetée Manager'];
        return $this->model->getDemandesByStatuses($this->managerId, $statuses);
    }

    /**
     * Liste des demandes filtrées par statut
     */
    public function getDemandesList(?string $statut = 'toutes'): array {
        $statuses = (strtolower($statut) === 'toutes') 
                         ? ['En attente', 'Validée Manager', 'Rejetée Manager'] 
                         : [$statut];

        return $this->model->getDemandesByStatuses($this->managerId, $statuses);
    }

    // =============================================================
    // =================== SECTION 1 : MANAGER/GENERIC =============
    // =============================================================

    /**
     * Récupère les détails complets d'une demande (logique Manager / générique).
     */
    public function getDemandeDetails(int $demandeId): ?array
    {
        // 💡 CORRECTION : Gestion du rôle Admin
        $role = $_SESSION['role'] ?? 'employe';

        if ($role === 'admin') {
            // L'admin peut tout voir, on utilise la méthode sans restriction manager
            $demande_info = $this->model->getDemandeByIdAdmin($demandeId);
        } else {
            // Le manager ne voit que ce qui le concerne
            $demande_info = $this->model->getDemandeById($demandeId, $this->managerId);
        }

        if (!$demande_info) {
            return null;
        }

        $demande_info['statut'] = $demande_info['statut'] ?? 'En attente';
        $demande_info['lignes_frais'] = $this->model->getDetailsFrais($demandeId);

        return $demande_info;
    }

    /**
     * Traite la demande: validation ou rejet avec motif (Logique Manager)
     */
    public function traiterDemandeAction(array $postData): void
    {
        // 💡 CORRECTION : Délégation à la méthode Admin si le rôle est admin
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $this->traiterDemandeAdmin($postData);
            return;
        }

        if (!isset($postData['action'], $postData['demande_id'])) {
            $_SESSION['error_message'] = "Données incomplètes.";
            header('Location: ' . BASE_URL . 'views/manager/demandes_liste.php');
            exit;
        }

        $id = (int)$postData['demande_id'];
        $action = $postData['action'];
        $motif = $postData['commentaire_manager'] ?? null;

        // 1. Récupérer la demande pour l'ID de l'employé
        $demandeActuelle = $this->model->getDemandeById($id, $this->managerId);
        if (!$demandeActuelle) {
            $_SESSION['error_message'] = "Erreur: Demande introuvable ou accès refusé.";
            header('Location: ' . BASE_URL . 'views/manager/demandes_liste.php');
            exit;
        }

        $employe_id = $demandeActuelle['user_id']; 
        $nouveauStatut = ($action === 'valider') ? 'Validée Manager' : 'Rejetée Manager';

        if ($action === 'rejeter' && empty(trim((string)$motif))) {
            $_SESSION['error_message'] = "Le motif de rejet est obligatoire.";
            header('Location: ' . BASE_URL . 'views/manager/details_demande.php?id=' . $id);
            exit;
        }

        // 2. Tentative de mise à jour du statut dans la BDD
        if ($this->model->updateStatut($id, $nouveauStatut, $this->managerId, $this->userId, $motif)) {
            
            // =================================================================
            // === LOGIQUE DE NOTIFICATION : Manager -> Employé ===
            // =================================================================
            $lien_notif = "views/manager/details_demande.php?id={$id}";
            
            if ($action === 'valider') {
                $message_notif = "Votre demande n°{$id} a été **VALIDÉE** par votre manager. (Statut: {$nouveauStatut})";
            } else { // rejeter
                $message_notif = "Votre demande n°{$id} a été **REJETÉE**. Consultez les détails pour le motif.";
            }
            
            // Envoi de la notification
            $this->notifyUsers($employe_id, $id, $message_notif, $lien_notif);
            // =================================================================
            
            $_SESSION['message'] = "Demande (ID: {$id}) traitée avec succès. L'employé a été notifié.";
            header('Location: ' . BASE_URL . 'views/manager/details_demande.php?id=' . $id);
            exit;
        } else {
            $_SESSION['error_message'] = "Erreur technique lors de la mise à jour du statut.";
            header('Location: ' . BASE_URL . 'views/manager/details_demande.php?id=' . $id);
            exit;
        }
    }

    /**
     * Traite la demande pour l'Admin (Validation Finale)
     */
    private function traiterDemandeAdmin(array $postData): void
    {
        if (!isset($postData['action'], $postData['demande_id'])) {
            $_SESSION['error_message'] = "Données incomplètes.";
            header('Location: ' . BASE_URL . 'views/admin/liste_demandes.php');
            exit;
        }

        $id = (int)$postData['demande_id'];
        $action = $postData['action'];
        $motif = $postData['commentaire_manager'] ?? null;

        // 1. Récupérer la demande (Admin bypass manager check)
        $demandeActuelle = $this->model->getDemandeByIdAdmin($id);

        if (!$demandeActuelle) {
            $_SESSION['error_message'] = "Erreur: Demande introuvable.";
            header('Location: ' . BASE_URL . 'views/admin/liste_demandes.php');
            exit;
        }

        $employe_id = $demandeActuelle['user_id']; 
        $nouveauStatutFinal = ($action === 'valider') ? 'Validée' : 'Rejetée';
        
        if ($action === 'rejeter' && empty(trim((string)$motif))) {
            $_SESSION['error_message'] = "Le motif de rejet est obligatoire.";
            header('Location: ' . BASE_URL . 'views/admin/details_demande.php?id=' . $id);
            exit;
        }

        // 2. Mise à jour du statut final
        if ($this->model->updateStatutFinal($id, $nouveauStatutFinal, $this->userId, $motif)) {
             $lien_notif = "views/employe/details_demande.php?id={$id}";
             $message_notif = ($action === 'valider') 
                ? "Votre demande n°{$id} a été **VALIDÉE** par l'administrateur."
                : "Votre demande n°{$id} a été **REJETÉE** par l'administrateur.";
             
             $this->notifyUsers($employe_id, $id, $message_notif, $lien_notif);
             
             $_SESSION['message'] = "Demande (ID: {$id}) traitée avec succès (Admin).";
             header('Location: ' . BASE_URL . 'views/admin/details_demande.php?id=' . $id);
             exit;
        } else {
            $_SESSION['error_message'] = "Erreur technique lors de la mise à jour du statut final.";
            header('Location: ' . BASE_URL . 'views/admin/details_demande.php?id=' . $id);
            exit;
        }
    }
    
    // =============================================================
    // =================== SECTION 2: DÉTAILS & ACTIONS (Employé) ==
    // =============================================================

    /**
     * Crée une nouvelle demande de frais. (Employé soumet)
     */
    public function creerDemandeAction(array $demandeData, array $details, array $uploadedFiles = []): void
    {
        // Utilisation des méthodes de DemandeModel que vous avez fournies.
        $this->pdo->beginTransaction();
        try {
            // 1. Création de l'en-tête de la demande
            $nouvel_id_demande = $this->model->createDemande($this->userId, $demandeData);
            
            if (!$nouvel_id_demande) {
                $this->pdo->rollBack();
                throw new \Exception("Échec de la création de la demande principale.");
            }
            
            // 2. Ajout des détails
            foreach ($details as $detail) {
                 // Assurez-vous que les données 'detail' contiennent les bonnes clés (ex: categorie_id, justificatif_path)
                 if (!$this->model->addDetailFrais($nouvel_id_demande, $detail)) {
                     $this->pdo->rollBack();
                     throw new \Exception("Échec de l'ajout d'un détail de frais.");
                 }
            }
            
            // 3. Calculate and update total amount
            $stmtTotal = $this->pdo->prepare("SELECT COALESCE(SUM(montant), 0) AS total FROM details_frais WHERE demande_id = ?");
            $stmtTotal->execute([$nouvel_id_demande]);
            $montantTotal = (float)$stmtTotal->fetchColumn();
            
            $stmtUpdate = $this->pdo->prepare("UPDATE demande_frais SET montant_total = ? WHERE id = ?");
            $stmtUpdate->execute([$montantTotal, $nouvel_id_demande]);
            
            $this->pdo->commit();
            
            // =================================================================
            // === LOGIQUE DE NOTIFICATION : Employé -> Manager ===
            // =================================================================
            $manager_id = $this->model->getManagerIdForUser($this->userId); 
            
            if ($manager_id > 0) {
                $employe_nom = $_SESSION['user_full_name'] ?? ('Employé ID: ' . $this->userId); 
                $message_notif = "Nouvelle demande de frais ({$nouvel_id_demande}) à valider soumise par **{$employe_nom}**.";
                $lien_notif = "views/manager/details_demande.php?id={$nouvel_id_demande}"; 
                
                $this->notifyUsers($manager_id, $nouvel_id_demande, $message_notif, $lien_notif);
            }
            // =================================================================
            
            $_SESSION['message'] = "Demande (ID: {$nouvel_id_demande}) soumise avec succès à votre manager.";
            // 🛑 CORRECTION: Utilisation de BASE_URL pour une redirection absolue
            header('Location: ' . BASE_URL . 'views/employe/employe_demandes.php');
            exit;
            
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("❌ EXCEPTION: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error_message'] = "Erreur lors de la soumission de la demande: " . $e->getMessage();
            // 🛑 CORRECTION: Utilisation de BASE_URL pour une redirection absolue
            header('Location: ' . BASE_URL . 'views/employe/employe_demandes.php');
            exit;
        }
    }

    /**
     * Récupère les détails complets d'une demande de frais par son ID (employé).
     */
    public function getDemandeDetailsById(int $demandeId, int $userId): ?array
    {
        // 🛑 CORRECTION: Le modèle DemandeFraisModel n'étant pas toujours disponible,
        // on exécute la requête directement.
        $stmt = $this->pdo->prepare("
            SELECT df.*, CONCAT(u.first_name, ' ', u.last_name) AS employe_nom, u.email AS employe_email 
            FROM demande_frais df 
            JOIN users u ON df.user_id = u.id 
            WHERE df.id = ? AND df.user_id = ?
        ");
        $stmt->execute([$demandeId, $userId]);
        $demande = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$demande) return null;
        
        return [
            'demande_frais' => $demande, 
            'details_frais' => $this->model->getDetailsFrais($demandeId)
        ];
    }

    /**
     * Met à jour une demande de frais existante et ses détails. (Utilisée par l'Employé pour modifier une demande 'En attente')
     */
    public function updateDemande(
        int $demandeId,
        int $userId,
        array $demandeData,
        array $details,
        array $detailsToDelete = [],
        $fileHandler = null
    ): bool {
        $filesToDeletePhysical = [];

        try {
            $this->pdo->beginTransaction();

            // 1. Vérification de l'autorisation et du statut "En attente"
            $stmt = $this->pdo->prepare("SELECT statut FROM demande_frais WHERE id = :id AND user_id = :user_id FOR UPDATE");
            $stmt->execute(['id' => $demandeId, 'user_id' => $userId]);
            $currentDemande = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$currentDemande || ($currentDemande['statut'] ?? '') !== 'En attente') {
                $this->pdo->rollBack();
                throw new \Exception("Modification non autorisée ou demande non éditable.");
            }

            // 2. Suppression des détails demandés
            if (!empty($detailsToDelete)) {
                // Récupérer les chemins des fichiers à supprimer pour nettoyage physique
                if ($fileHandler) {
                    $placeholders = implode(',', array_fill(0, count($detailsToDelete), '?'));
                    $stmtGetFiles = $this->pdo->prepare("SELECT justificatif_path FROM details_frais WHERE id IN ($placeholders) AND demande_id = ?");
                    $stmtGetFiles->execute(array_merge($detailsToDelete, [$demandeId]));
                    $filesToDeletePhysical = array_merge($filesToDeletePhysical, $stmtGetFiles->fetchAll(\PDO::FETCH_COLUMN));
                }

                $placeholders = implode(',', array_fill(0, count($detailsToDelete), '?'));
                $stmtDelete = $this->pdo->prepare("DELETE FROM details_frais WHERE id IN ($placeholders) AND demande_id = ?");
                $stmtDelete->execute(array_merge($detailsToDelete, [$demandeId]));
            }

            // 3. Traitement des détails (Ajout / Modification)
            $stmtInsertDetail = $this->pdo->prepare("
                INSERT INTO details_frais (demande_id, date_depense, categorie_id, montant, description, justificatif_path)
                VALUES (:demande_id, :date_depense, :categorie_id, :montant, :description, :justificatif_path)
            ");

            $stmtUpdateDetail = $this->pdo->prepare("
                UPDATE details_frais 
                SET date_depense = :date_depense, 
                categorie_id = :categorie_id, 
                montant = :montant, 
                description = :description, 
                justificatif_path = :justificatif_path 
                WHERE id = :id AND demande_id = :demande_id
            ");

            foreach ($details as $detail) {
                // Préparation des données
                $params = [
                    ':date_depense' => $detail['date_depense'],
                    ':categorie_id' => $detail['id_categorie_frais'],
                    ':montant' => $detail['montant'],
                    ':description' => $detail['description'],
                    ':justificatif_path' => $detail['justificatif_path']
                ];

                if (!empty($detail['id_detail_frais'])) {
                    // --- UPDATE ---
                    // Si un nouveau fichier a été uploadé, on peut vouloir supprimer l'ancien
                    if ($fileHandler && !empty($detail['is_new_file'])) {
                        $stmtOldFile = $this->pdo->prepare("SELECT justificatif_path FROM details_frais WHERE id = ?");
                        $stmtOldFile->execute([$detail['id_detail_frais']]);
                        $oldPath = $stmtOldFile->fetchColumn();
                        if ($oldPath && $oldPath !== $detail['justificatif_path']) {
                            $filesToDeletePhysical[] = $oldPath;
                        }
                    }

                    $params[':id'] = $detail['id_detail_frais'];
                    $params[':demande_id'] = $demandeId;
                    $stmtUpdateDetail->execute($params);

                } else {
                    // --- INSERT ---
                    $params[':demande_id'] = $demandeId;
                    $stmtInsertDetail->execute($params);
                }
            }


            // 4. Recalculer le Montant Total FINAL
            $stmtTotal = $this->pdo->prepare("SELECT COALESCE(SUM(montant), 0) AS total FROM details_frais WHERE demande_id = ?");
            $stmtTotal->execute([$demandeId]);
            $finalMontantTotal = (float)$stmtTotal->fetchColumn();

            // 5. Mise à jour de la demande principale (Statut 'En attente')
            $stmtUpdateDemande = $this->pdo->prepare("
                 UPDATE demande_frais SET 
                      objet_mission = :objet, 
                      lieu_deplacement = :lieu, 
                      date_depart = :date_dep, 
                      date_retour = :date_ret, 
                      montant_total = :total, 
                      updated_at = NOW(),
                      statut = 'En attente' 
                 WHERE id = :id
            ");
            $stmtUpdateDemande->execute([
                 'objet' => $demandeData['objet_mission'] ?? null,
                 'lieu' => $demandeData['lieu_deplacement'] ?? null,
                 'date_dep' => $demandeData['date_depart'] ?? null,
                 'date_ret' => $demandeData['date_retour'] ?? null,
                 'total' => $finalMontantTotal,
                 'id' => $demandeId
            ]);

            // Commit
            $this->pdo->commit();

            // 6. Suppression physique des fichiers APRES commit
            if ($fileHandler && !empty($filesToDeletePhysical)) {
                foreach ($filesToDeletePhysical as $filePath) {
                    if (!empty($filePath)) {
                        $fileHandler->deleteFile($filePath);
                    }
                }
            }

            // =================================================================
            // === LOGIQUE DE NOTIFICATION : Employé -> Manager ===
            // =================================================================
            $manager_id = $this->model->getManagerIdForUser($userId);
            if ($manager_id > 0) {
                 $employe_nom = $_SESSION['user_full_name'] ?? ('Employé ID: ' . $userId);
                 $message_notif = "La demande n°{$demandeId} soumise par **{$employe_nom}** a été **MODIFIÉE** et est en attente de validation.";
                 $lien_notif = "views/manager/details_demande.php?id={$demandeId}";
                 
                 $this->notifyUsers($manager_id, $demandeId, $message_notif, $lien_notif);
            }
            // =================================================================

            return true;
        } catch (\Throwable $e) {
             if ($this->pdo->inTransaction()) {
                 $this->pdo->rollBack();
             }
             throw new \Exception("Erreur BDD lors de la mise à jour de la demande : " . $e->getMessage(), 0, $e);
        }
    }

    // =============================================================
    // =================== UTILITAIRES ============================
    // =============================================================

    public function getManagerId(): int {
        return $this->managerId;
    }

    /**
     * Recherche avancée avec filtres multiples
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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur Recherche : " . $e->getMessage());
            return [];
        }
    }
}