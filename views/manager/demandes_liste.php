<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php';
require_once BASE_PATH . 'controllers/UserController.php';
require_once BASE_PATH . 'includes/header.php';

$controller = new DemandeController($pdo);
$userController = new UserController($pdo);
$managerId = $controller->getManagerId(); 

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

$statutFiltre = $_GET['statut'] ?? 'En attente'; 
$demandes = $controller->getDemandesList($statutFiltre);

$statuts = [
    'toutes' => 'Toutes', 
    'En attente' => 'En attente', 
    'Validée Manager' => 'Validées', 
    'Rejetée Manager' => 'Rejetées',
];

function getBadgeStyle(string $statut): string {
    $base = "border-radius: 50px; padding: 8px 16px; font-weight: 700; font-size: 0.85rem; display: inline-block; border-width: 1px; border-style: solid; text-decoration: none; white-space: nowrap;";
    $colors = match ($statut) {
        'En attente' => "background-color: #FFF8E1; color: #F57F17; border-color: #FFE0B2;",
        'Validée Manager', 'Approuvée Compta' => "background-color: #E8F5E9; color: #2E7D32; border-color: #C8E6C9;",
        'Rejetée Manager' => "background-color: #FFEBEE; color: #C62828; border-color: #FFCDD2;",
        default => "background-color: #F5F5F5; color: #616161; border-color: #E0E0E0;",
    };
    return $base . ' ' . $colors;
}

function getFilterBtnStyle(string $statutKey, bool $isActive): string {
    $base = "border-radius: 50px; padding: 8px 20px; font-weight: 600; font-size: 0.9rem; text-decoration: none; display: inline-block; border-style: solid; margin-right: 8px; transition: all 0.2s;";
    $borderWidth = $isActive ? "border-width: 2px;" : "border-width: 1px;";
    $colors = match ($statutKey) {
        'toutes' => "background-color: #F5F5F5; color: #616161; border-color: #BDBDBD;",
        'En attente' => "background-color: #FFF8E1; color: #F57F17; border-color: #FFE0B2;",
        'Validée Manager' => "background-color: #E8F5E9; color: #2E7D32; border-color: #C8E6C9;",
        'Rejetée Manager' => "background-color: #FFEBEE; color: #C62828; border-color: #FFCDD2;",
        'Approuvée Compta' => "background-color: #E8F5E9; color: #2E7D32; border-color: #C8E6C9;",
        default => "background-color: #fff; color: #333; border-color: #ccc;"
    };

    if ($isActive) {
        $colors = match ($statutKey) {
            'toutes' => "background-color: #e0e0e0; color: #424242; border-color: #616161;",
            'En attente' => "background-color: #FFF8E1; color: #F57F17; border-color: #F57F17;",
            'Validée Manager', 'Approuvée Compta' => "background-color: #E8F5E9; color: #2E7D32; border-color: #2E7D32;",
            'Rejetée Manager' => "background-color: #FFEBEE; color: #C62828; border-color: #C62828;",
            default => $colors,
        };
    }

    return $base . $borderWidth . $colors;
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/demandes_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
/* -------------------- */
/* TABLEAU FIXE ET CELLULES */
/* -------------------- */
.modern-table {
    width: 100%;
    table-layout: fixed; /* IMPORTANT pour largeur fixe */
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

.modern-table th:first-child,
.modern-table td.ps-4 {
    width: 180px; /* Employé */
}

.modern-table th:nth-child(2),
.modern-table td:nth-child(2) {
    width: 220px; /* Objet Mission réduit */
    word-break: break-word;
    white-space: normal;
}

.modern-table th:nth-child(3),
.modern-table td:nth-child(3) {
    width: 100px; /* Date Début */
    white-space: nowrap;
}

.modern-table th:nth-child(4),
.modern-table td:nth-child(4) {
    width: 140px; /* Statut */
    white-space: nowrap; /* Empêche retour à la ligne */
}

.modern-table th:nth-child(5),
.modern-table td:nth-child(5) {
    width: 120px; /* Montant */
    text-align: right;
    font-weight: bold;
}

.modern-table th:nth-child(6),
.modern-table td:nth-child(6) {
    width: 80px; /* Détails */
    text-align: center;
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-theme-secondary">Demandes de Frais</h2>
        <a href="<?= BASE_URL ?>views/manager/export_demandes.php?statut=<?= urlencode($statutFiltre) ?>" class="btn btn-success fw-bold py-2 px-3" style="background-color: #2E7D32; border-color: #2E7D32; transition: all 0.2s;">
            <i class="fas fa-file-excel me-2"></i> Exporter en Excel
        </a>
    </div>

    <div class="d-flex mb-4 gap-2 flex-wrap">
    <?php foreach ($statuts as $dbValue => $label):
        $isActive = strtolower($statutFiltre) === strtolower($dbValue);
    ?>
        <a href="?statut=<?= urlencode($dbValue) ?>" style="<?= getFilterBtnStyle($dbValue, $isActive) ?>">
            <?= $label ?>
            <?php if ($isActive): ?><i class="fas fa-check ms-1" style="font-size: 0.8em;"></i><?php endif; ?>
        </a>
    <?php endforeach; ?>
    </div>

    <div class="card shadow-sm border-0 custom-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0 modern-table">
                    <thead>
                        <tr class="text-uppercase small text-muted table-header-theme" style="background-color: rgba(118, 189, 70, 0.15);">
                            <th class="ps-4">Employé</th>
                            <th>Objet</th>
                            <th>Date Début</th>
                            <th>Statut</th>
                            <th>Montant</th>
                            <th class="text-center">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($demandes)): ?>
                            <?php foreach ($demandes as $demande): ?>
                                <tr>
                                    <td class="ps-4">
                                        <strong><?= htmlspecialchars($demande['first_name'] . ' ' . $demande['last_name']) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($demande['email']) ?></div>
                                    </td>
                                    <td><div class="text-truncate" title="<?= htmlspecialchars($demande['objet_mission']) ?>"><?= htmlspecialchars($demande['objet_mission']) ?></div></td>
                                    <td><?= date('d/m/Y', strtotime($demande['date_depart'])) ?></td>
                                    <td><span style="<?= getBadgeStyle($demande['statut'] ?? 'Inconnu') ?>"><?= htmlspecialchars($demande['statut'] ?? 'Inconnu') ?></span></td>
                                    <td class="text-theme-primary fw-bold"><?= number_format($demande['total_calcule'] ?? 0, 2, ',', ' ') ?> <?= $currencySymbol ?></td>
                                    <td class="text-center">
                                        <a href="details_demande.php?id=<?= $demande['id'] ?>" class="btn-action-icon" style="color:white; background-color: var(--primary-color) ;">
                                            <i class="fas fa-chevron-right fa-2x"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php $titre = $statuts[$statutFiltre] ?? 'Toutes'; if ($titre === 'toutes') $titre = 'Toutes les Demandes'; ?>
                            <tr>
                                <td colspan="6" class="text-center" style="height: 150px; vertical-align: middle;">
                                    <p class="p-4 text-muted">
                                        <i class="fas fa-search fa-2x mb-3"></i><br>
                                        Aucune demande <?= htmlspecialchars(strtolower($titre)) ?> pour le moment.
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>
