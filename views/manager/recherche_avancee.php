<?php
// views/manager/recherche_avancee.php

require_once __DIR__ . '/../../config.php';
// Nous avons besoin de DemandeController pour la recherche
require_once BASE_PATH . 'controllers/DemandeController.php'; 
// Et de TeamController pour la liste des employés, si la logique y est
require_once BASE_PATH . 'controllers/TeamController.php'; 
require_once BASE_PATH . 'includes/header.php';

// Assurez-vous que $pdo est disponible (généralement défini dans config.php)
if (!isset($pdo)) {
    die("Erreur: La connexion à la base de données (\$pdo) est manquante.");
}

// 1. Initialisation des contrôleurs
$demandeController = new DemandeController($pdo);
// On suppose que TeamController existe et gère la liste des employés
$teamController = new TeamController($pdo, $demandeController->getManagerId()); 

// 2. Récupération des données via les contrôleurs
$employes = $teamController->getAllTeamMembers(); // Assurez-vous que cette méthode existe
$resultats = []; // Initialiser les résultats à vide

// Exécuter la recherche seulement si des paramètres sont soumis (méthode GET)
if (!empty($_GET) && (isset($_GET['employe']) || isset($_GET['statut']) || isset($_GET['date_debut']) || isset($_GET['date_fin']))) {
    // CORRECTION: Appeler la fonction de recherche sur le contrôleur de demande
    // NOTE: Vous devez ajouter la méthode 'faireUneRecherche' à DemandeController
    $resultats = $demandeController->faireUneRecherche($_GET); 
}

// 3. Persist Form Values
$current_emp  = $_GET['employe'] ?? '';
$current_stat = $_GET['statut'] ?? '';
$current_d1   = $_GET['date_debut'] ?? '';
$current_d2   = $_GET['date_fin'] ?? '';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">

<div class="container-fluid p-4">

    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color: var(--secondary-color);">
            <i class='bx bx-search-alt me-2'></i>Recherche Avancée
        </h2>
        <a href="dashboard_manager.php" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-arrow-back'></i> Retour
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
        <div class="card-body p-4">
            <form method="GET" action="">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Employé</label>
                        <select name="employe" class="form-select border-0 bg-light py-2">
                            <option value="">Tous les employés</option>
                            <?php 
                            // SECURITE: Vérifier si $employes est un tableau avant de boucler
                            if (is_array($employes)):
                            foreach ($employes as $emp): ?>
                                <option value="<?= htmlspecialchars($emp['id']) ?>" <?= ($current_emp == $emp['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                </option>
                            <?php endforeach; 
                            endif; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Statut</label>
                        <select name="statut" class="form-select border-0 bg-light py-2">
                            <option value="">Tous les statuts</option>
                            <?php 
                            // CORRECTION: Correction du statut 'Validé Manager'
                            $statuses = ['En attente', 'Validée Manager', 'Rejetée Manager', 'Approuvée Compta', 'Payée'];
                            foreach ($statuses as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= ($current_stat == $st) ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Du (Date départ)</label>
                        <input type="date" name="date_debut" class="form-control border-0 bg-light py-2" value="<?= htmlspecialchars($current_d1) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Au (Date départ)</label>
                        <input type="date" name="date_fin" class="form-control border-0 bg-light py-2" value="<?= htmlspecialchars($current_d2) ?>">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="background: var(--secondary-color); border:none;">
                            <i class='bx bx-filter-alt me-1'></i> Filtrer
                        </button>
                    </div>
                </div>

                <?php if (!empty($_GET)): ?>
                    <div class="mt-3 text-end">
                        <a href="recherche_avancee.php" class="text-muted small text-decoration-none hover-underline">
                            <i class='bx bx-refresh'></i> Réinitialiser les filtres
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h5 class="fw-bold text-dark mb-0">Résultats</h5>
            <span class="badge bg-white text-secondary border shadow-sm px-3 py-2 rounded-pill">
                <?= count($resultats) ?> dossier(s) trouvé(s)
            </span>
        </div>

        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="ps-4">Employé</th>
                        <th>Objet Mission</th>
                        <th>Date Début</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resultats)): ?>
                        <tr class="empty-state-row">
                            <td colspan="6" class="p-5 text-center">
                                <div class="empty-state-card">
                                    <div class="mb-3">
                                        <i class='bx bx-search-alt fs-1 text-secondary opacity-25'></i>
                                    </div>
                                    <h6 class="text-dark fw-bold">Aucun résultat</h6>
                                    <p class="text-muted small">Essayez de modifier vos critères de recherche.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($resultats as $d): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name'] ?? 'N/A') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($d['email'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($d['objet_mission'] ?? 'N/A') ?>
                                    <div class="small text-muted"><i class='bx bx-map me-1'></i><?= htmlspecialchars($d['lieu_deplacement'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <?= isset($d['date_depart']) ? date('d/m/Y', strtotime($d['date_depart'])) : 'N/A' ?>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?= number_format($d['total_calcule'] ?? 0, 2) ?> €
                                </td>
                                <td>
                                    <?php
                                    $statut = $d['statut'] ?? 'Inconnu';
                                    $badgeClass = 'bg-secondary';
                                    if (strpos($statut, 'Validée') !== false || strpos($statut, 'Approuvée') !== false) {
                                        $badgeClass = 'bg-success';
                                    } elseif (strpos($statut, 'Rejetée') !== false) {
                                        $badgeClass = 'bg-danger';
                                    } elseif ($statut == 'En attente') {
                                        $badgeClass = 'bg-warning text-dark';
                                    } elseif ($statut == 'Payée') {
                                        $badgeClass = 'bg-info text-dark';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3">
                                        <?= htmlspecialchars($statut) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="details_demande.php?id=<?= (int)($d['id'] ?? 0) ?>" class="btn-action-icon" title="Voir les détails">
                                        <i class='bx bx-chevron-right fs-4'></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>