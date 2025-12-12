<?php
// 1. VÉRIFICATION ET DÉMARRAGE DE LA SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vérification d'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-expense-management/views/auth/login.php');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Assurez-vous que $pdo est défini dans config.php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/UserController.php';
require_once BASE_PATH . 'includes/header.php';

// Convertir en int pour la sécurité, et s'assurer que l'ID est toujours défini.
$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$role = $_SESSION['role'] ?? 'employe';

// Définition de la constante BASE_URL si elle n'existe pas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}

// --- LOGIQUE DE DEVISE DYNAMIQUE ---
if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol(string $code): string
    {
        return match (strtoupper($code)) {
            'EUR' => '€',
            'USD' => '$',
            'MAD' => 'Dhs',
            'GBP' => '£',
            default => '€',
        };
    }
}
$userController = new UserController($pdo);
$preferredCurrencyCode = $userController->getPreferredCurrency($user_id);
$currencySymbol = getCurrencySymbol($preferredCurrencyCode);
// --- FIN LOGIQUE DE DEVISE DYNAMIQUE ---
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/demandes_employe.css">

<script>
    // **********************************************
    // **********************************************
    // Variables globales déplacées dans header.php (USER_ID, CURRENCY_SYMBOL)
    // **********************************************
    
    // Vérifie si Bootstrap est chargé, nécessaire pour la modale
    if (typeof bootstrap === 'undefined') {
        console.warn("Bootstrap JS n'est pas chargé. Les modales ne fonctionneront pas.");
    }
</script>
<script src="<?= BASE_URL ?>assets/js/demandes_employe.js"></script>

<div class="container-fluid p-4">


    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold m-0" style="color: #32325d;"><i class="bi bi-receipt me-2"></i>Mes Demandes de Frais</h1>
            <span class="text-muted small">Bienvenue, <?= htmlspecialchars($user_name) ?> - Gérez vos demandes</span>
        </div>
        <div>
            <a href="<?= BASE_URL ?>views/employe/create_demande.php" class="btn btn-go-green">
                <i class="bi bi-plus-circle me-2"></i>Nouvelle Demande
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white filter-bar">
        <div class="card-body py-3 px-4">

            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">
                        <i class="bi bi-search me-1"></i> Rechercher
                    </label>
                    <input type="text" class="form-control bg-light py-2" id="filter-search" placeholder="Nom, Marchand, Référence...">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">
                        <i class="bi bi-flag me-1"></i> Statut
                    </label>
                    <select class="form-select bg-light py-2" id="filter-statut">
                        <option value="">Tous</option>
                        <option value="En attente">En attente</option>
                        <option value="Validée Manager">Validée Manager</option>
                        <option value="Rejetée Manager">Rejetée Manager</option>
                        <option value="Approuvée Compta">Approuvée Compta</option>
                        <option value="Payée">Payée</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">
                        <i class="bi bi-calendar me-1"></i> Date Début
                    </label>
                    <input type="date" class="form-control bg-light py-2" id="filter-date-debut">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">
                        <i class="bi bi-calendar me-1"></i> Date Fin
                    </label>
                    <input type="date" class="form-control bg-light py-2" id="filter-date-fin">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase invisible">Action</label>

                    <button type="button" class="btn btn-primary w-100 fw-bold **py-3** shadow-sm" style="background:var(--secondary-color);border:none;" onclick="applyFilters()">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>
                </div>

            </div>
            <div id="reset-link-container" class="mt-3 text-end" style="visibility: hidden;">
                <a href="#" onclick="event.preventDefault(); resetFilters();" class="text-muted small text-decoration-none">
                    <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser les filtres
                </a>
            </div>

        </div>
    </div>
<div class="table-container border-0 shadow-sm rounded-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-ul me-2"></i>Toutes mes demandes</h5>
        <span id="results-count" class="badge bg-white text-secondary border shadow-sm px-3 py-2 rounded-pill"></span>
    </div>

    <div class="table-wrapper table-body-scroll" style="max-height: 400px; overflow-y: auto;">
        <table class="modern-table w-100">
            <thead class="bg-white" style="position: sticky; top: 0; z-index: 10;">
                <tr>
                    <th class="ps-4" style="width: 25%;">Objet Mission</th>
                    <th style="width: 15%;">Date Départ</th>
                    <th style="width: 15%;">Date Retour</th>
                    <th style="width: 15%;">Statut</th>
                    <th style="width: 15%;">Montant Total (<?= htmlspecialchars($currencySymbol) ?>)</th>
                    <th class="pe-4 text-center" style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody id="demandes-tbody">
                <tr>
                    <td colspan="6">
                        <div class="loading-container p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <p class="mt-3 text-muted">Chargement de vos demandes...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>