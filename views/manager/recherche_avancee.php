<?php
// --- Debugging (Optionnel) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php';
require_once BASE_PATH . 'controllers/TeamController.php';
require_once BASE_PATH . 'controllers/UserController.php';
require_once BASE_PATH . 'includes/header.php';

// --- Sécuriser la session ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($pdo)) {
    die("Erreur: La connexion à la base de données (\$pdo) est manquante.");
}

// Initialisation des contrôleurs
$demandeController = new DemandeController($pdo);
$managerId = $demandeController->getManagerId();
$teamController = new TeamController($pdo, $managerId);
$userController = new UserController($pdo);
$employes = $teamController->getAllTeamMembers();
$resultats = [];

// --- Logique filtres ---
$current_emp = $_GET['employe'] ?? '';
$current_stat = $_GET['statut'] ?? '';
$current_d1 = $_GET['date_debut'] ?? '';
$current_d2 = $_GET['date_fin'] ?? '';

$hasActiveFilter = ($current_emp !== '' || $current_stat !== '' || $current_d1 !== '' || $current_d2 !== '');
$filters = $hasActiveFilter ? [
    'employe' => $current_emp,
    'statut' => $current_stat,
    'date_debut' => $current_d1,
    'date_fin' => $current_d2
] : [];

$resultats = $demandeController->faireUneRecherche($filters);

// Récupération de la devise préférée du Manager
$managerCurrencyCode = $userController->getPreferredCurrency($managerId);
$currencySymbol = getCurrencySymbol($managerCurrencyCode);

function getCurrencySymbol(string $code): string {
    return match (strtoupper($code)) {
        'EUR' => '€',
        'USD' => '$',
        'MAD' => 'Dhs',
        'GBP' => '£',
        default => '€',
    };
}

function getStatutStyle(string $statut): string {
    return match ($statut) {
        'En attente' => 'background-color:#fff3cd;color:#856404;border:1px solid #ffeeba; white-space: nowrap;',
        'Validée Manager', 'Approuvée Compta' => 'background-color:#d4edda;color:#155724;border:1px solid #c3e6cb; white-space: nowrap;',
        'Rejetée Manager' => 'background-color:#f8d7da;color:#721c24;border:1px solid #f5c6cb; white-space: nowrap;',
        'Payée' => 'background-color:#d1ecf1;color:#0c5460;border:1px solid #bee5eb; white-space: nowrap;',
        default => 'background-color:#e2e3e5;color:#383d41; white-space: nowrap;',
    };
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
.modern-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
}
.modern-table th,
.modern-table td {
    padding: 0.75rem;
    vertical-align: middle;
    text-align: left;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Largeurs colonnes */
.modern-table th:first-child,
.modern-table td:first-child { width: 180px; white-space: normal; }
.modern-table th:nth-child(2),
.modern-table td.mission-cell { width: 300px; word-break: break-word; white-space: normal; }
.modern-table th:nth-child(3),
.modern-table td:nth-child(3) { width: 120px; white-space: nowrap; }
.modern-table th:nth-child(4),
.modern-table td:nth-child(4) { width: 140px; text-align: right; font-weight: bold; color: var(--secondary-color); white-space: nowrap; }
.modern-table th:nth-child(5),
.modern-table td:nth-child(5) { width: 160px; white-space: nowrap; }
.modern-table th:last-child,
.modern-table td:last-child { width: 70px; text-align: center; }

.text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>

<div class="container-fluid p-4">
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color:var(--secondary-color);"><i class='bx bx-search-alt me-2'></i>Recherche Avancée</h2>
        <a href="dashboard_manager.php" class="btn btn-outline-secondary btn-sm"><i class='bx bx-arrow-back'></i> Retour</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
        <div class="card-body p-4">
            <form method="GET" action="">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Employé</label>
                        <select name="employe" class="form-select bg-light py-2">
                            <option value="">Tous les employés</option>
                            <?php foreach ($employes as $emp): ?>
                                <option value="<?= htmlspecialchars($emp['id']) ?>" <?= ($current_emp == $emp['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Statut</label>
                        <select name="statut" class="form-select bg-light py-2">
                            <option value="">Tous les statuts</option>
                            <?php
                            $statuses = ['En attente', 'Validée Manager', 'Rejetée Manager', 'Approuvée Compta', 'Payée'];
                            foreach ($statuses as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= ($current_stat == $st) ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Du</label>
                        <input type="date" name="date_debut" class="form-control bg-light py-2" value="<?= htmlspecialchars($current_d1) ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Au</label>
                        <input type="date" name="date_fin" class="form-control bg-light py-2" value="<?= htmlspecialchars($current_d2) ?>">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="background:var(--secondary-color);border:none;">
                            <i class='bx bx-filter-alt me-1'></i> Filtrer
                        </button>
                    </div>
                </div>

                <?php if ($hasActiveFilter): ?>
                    <div class="mt-3 text-end">
                        <a href="recherche_avancee.php" class="text-muted small text-decoration-none">
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
            <span class="badge bg-white text-secondary border shadow-sm px-3 py-2 rounded-pill"><?= count($resultats) ?> dossier(s)</span>
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
                        <tr>
                            <td colspan="6" class="p-5 text-center text-muted">
                                <i class='bx bx-search-alt fs-1 opacity-25'></i>
                                <h6 class="fw-bold mt-2">Aucun résultat</h6>
                                <p class="small">Essayez de modifier vos critères.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($resultats as $d): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= htmlspecialchars(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($d['email'] ?? 'N/A') ?></div>
                                </td>
                                <td class="mission-cell">
                                    <div class="fw-bold"><?= htmlspecialchars($d['objet_mission'] ?? 'N/A') ?></div>
                                    <div class="small text-muted"><i class='bx bx-map me-1'></i><?= htmlspecialchars($d['lieu_deplacement'] ?? 'N/A') ?></div>
                                </td>
                                <td><?= !empty($d['date_depart']) ? date('d/m/Y', strtotime($d['date_depart'])) : 'N/A' ?></td>
                                <td><?= number_format($d['total_calcule'] ?? 0, 2) ?> <?= $currencySymbol ?></td>
                                <td>
                                    <?php $statut = $d['statut'] ?? 'Inconnu'; ?>
                                    <span class="badge rounded-pill fw-bold py-2 px-3" style="<?= getStatutStyle($statut) ?>"><?= htmlspecialchars($statut) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="details_demande.php?id=<?= $d['id'] ?>" class="btn-action-icon" style="color:white; background-color: var(--primary-color);">
                                        <i class="fas fa-chevron-right fa-2x"></i>
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
