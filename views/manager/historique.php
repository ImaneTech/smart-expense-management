<?php
// views/manager/historique.php

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php';
require_once BASE_PATH . 'controllers/UserController.php'; // <-- INCLUSION
require_once BASE_PATH . 'includes/header.php';

// Vérification connexion PDO
if (!isset($pdo)) {
    die("Erreur: La connexion à la base de données (\$pdo) est manquante.");
}

// Démarrage de session si nécessaire
if (session_status() === PHP_SESSION_NONE) session_start();

// Récupération historique
$controller = new DemandeController($pdo);
$userController = new UserController($pdo); // <-- INSTANCIATION
$historique = $controller->getHistorique();
$managerId = $controller->getManagerId(); // L'ID du manager est nécessaire

// --- GESTION DE LA DEVISE DYNAMIQUE ---
$managerCurrencyCode = $userController->getPreferredCurrency($managerId);
$currencySymbol = getCurrencySymbol($managerCurrencyCode);

/**
 * FONCTION : Convertit le code de devise (ex: EUR) en symbole (ex: €).
 */
function getCurrencySymbol(string $code): string {
    return match (strtoupper($code)) {
        'EUR' => '€',
        'USD' => '$',
        'MAD' => 'Dhs', // Dirham Marocain
        'GBP' => '£',
        default => '€', // Symbole par défaut si non trouvé
    };
}
// ------------------------------------


/**
 * Retourne la classe Bootstrap selon le statut de la demande.
 */
function getHistoriqueBadgeClass(string $statut): string {
    return match ($statut) {
        'Validée Manager' => 'bg-success-subtle text-success border border-success-subtle',
        'Rejetée Manager' => 'bg-danger-subtle text-danger border border-danger-subtle',
        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
    };
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<style>
.table th, .table td {
    padding: 1rem 0.75rem;
    border-top: 1px solid var(--table-border, #e3e6f0);
}
.table thead th {
    background-color: var(--secondary-color) !important;
    color: white;
    font-weight: 600;
    border-bottom: 2px solid var(--secondary-color);
}
.table-responsive {
    max-height: 80vh;
    overflow-y: auto;
}
.initial-circle {
    background-color: #f1f8e9;
    color: var(--primary-color);
    border: 1px solid #dcdcdc;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.btn-icon-detail {
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
    background-color: transparent;
    transition: all 0.2s;
    width: 36px;
    height: 36px;
    padding: 0;
    border-radius: 50%;
}
.btn-icon-detail:hover {
    background-color: var(--primary-color);
    color: white;
}
</style>

<div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: var(--secondary-color);">
            <i class='bx bx-history fs-3 me-2'></i> Historique de l'équipe
        </h2>
        <a href="dashboard_manager.php" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-arrow-back'></i> Retour
        </a>
    </div>

    <?php foreach (['message' => 'success', 'error_message' => 'danger'] as $sessionKey => $alertType): ?>
        <?php if (!empty($_SESSION[$sessionKey])): ?>
            <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION[$sessionKey]) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION[$sessionKey]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-4">Employé</th>
                            <th>Mission & Dates</th>
                            <th>Date Traitement</th>
                            <th>Montant Total</th>
                            <th>Statut Final</th>
                            <th class="text-end pe-4">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historique)): ?>
                            <tr>
                                <td colspan="6" class="p-5 text-center text-muted">
                                    <i class='bx bx-archive fs-1 opacity-50 mb-3'></i>
                                    <h6 class="fw-bold text-dark">Aucun historique disponible.</h6>
                                    <p class="small mb-0">Les demandes traitées apparaîtront ici.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historique as $h): 
                                $firstName = $h['first_name'] ?? '';
                                $lastName = $h['last_name'] ?? '';
                                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                $statut = $h['statut'] ?? 'Inconnu';
                                $badgeClass = getHistoriqueBadgeClass($statut);
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="initial-circle me-3 fw-bold"><?= htmlspecialchars($initials) ?></div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"><?= htmlspecialchars("$firstName $lastName") ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($h['email'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block fw-bold text-dark"><?= htmlspecialchars($h['objet_mission'] ?? 'N/A') ?></span>
                                        <small class="text-muted"><i class='bx bx-calendar me-1'></i> <?= !empty($h['date_depart']) ? date('d/m/Y', strtotime($h['date_depart'])) : 'N/A' ?></small>
                                    </td>
                                    <td><?= !empty($h['date_validation']) ? date('d M Y', strtotime($h['date_validation'])) : '<span class="text-muted">-</span>' ?></td>
                                    
                                    <!-- MONTANT AVEC DEVISE DYNAMIQUE -->
                                    <td class="fw-bold" style="color: var(--secondary-color);">
                                        <?= number_format($h['total_calcule'] ?? 0, 2, ',', ' ') ?> <?= $currencySymbol ?>
                                    </td>
                                    
                                    <td><span class="badge <?= $badgeClass ?> rounded-pill fw-normal px-3 py-2"><?= htmlspecialchars($statut) ?></span></td>
                                
                                    <td class="text-center">
                                        <a href="details_demande.php?id=<?= $h['id'] ?>" 
                                            class="btn-action-icon" style="color:white; background-color: var(--secondary-color);"> 
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

</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>