<?php
// views/manager/demandes_liste.php (Final version - Affiche la table même vide)

// --- Debugging (Optionnel) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// ---

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php'; 
require_once BASE_PATH . 'includes/header.php';

$controller = new DemandeController($pdo); 

// Récupérer le statut à filtrer depuis l'URL (par défaut 'En attente')
$statutFiltre = $_GET['statut'] ?? 'En attente'; 

// Charger les demandes en utilisant la nouvelle fonction plus flexible
$demandes = $controller->getDemandesList($statutFiltre);

// Liste des statuts pour générer les boutons de filtre
$statuts = [
    'toutes' => 'Toutes', 
    'En attente' => 'En attente', 
    'Validée Manager' => 'Validées', 
    'Rejetée Manager' => 'Rejetées'
];

/**
 * Fonction pour déterminer la classe de couleur SOFT pour les BADGES.
 */
function getStatutClass(string $statut): string {
    return match ($statut) {
        'En attente' => 'badge-wait', 
        'Validée Manager', 'Approuvée Compta' => 'badge-valid', 
        'Rejetée Manager' => 'badge-reject', 
        default => 'badge-secondary',
    };
}

/**
 * Fonction pour déterminer les classes de couleur pour les BOUTONS de filtre.
 */
function getFilterButtonClass(string $statut): string {
    return match ($statut) {
        'toutes' => 'btn-outline-secondary',
        'En attente' => 'btn-outline-warning text-dark-warning',
        'Validée Manager' => 'btn-outline-success text-success',
        'Rejetée Manager' => 'btn-outline-danger text-danger',
        default => 'btn-outline-secondary',
    };
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/demandes_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 

<div class="container-fluid p-4">
    <h2 class="fw-bold text-theme-secondary">Demandes de Frais</h2>
    
    <div class="d-flex mb-4 gap-2 mt-3"> 
<?php foreach ($statuts as $dbValue => $label): 
    $currentFilter = $dbValue;
    $isActive = strtolower($statutFiltre) === strtolower($currentFilter);

    $filterClass = match ($dbValue) {
        'toutes'           => 'filter-toutes',
        'En attente'       => 'filter-attente',
        'Validée Manager'  => 'filter-validees',
        'Rejetée Manager'  => 'filter-rejetees',
        default            => 'filter-toutes'
    };
?>
    <a href="?statut=<?= urlencode($currentFilter) ?>"
        class="filter-btn <?= $filterClass ?> <?= $isActive ? 'active' : '' ?>">
        <?= $label ?>
    </a>
<?php endforeach; ?>


    </div>

    <div class="card shadow-sm border-0 custom-table-card">
        <div class="card-body p-0">
            
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0 modern-table"> 
                    
                    <thead>
                        <tr class="text-uppercase small text-muted table-header-theme" style="background-color: rgba(118, 189, 70, 0.15);"> 
                            <th>Employé</th>
                            <th>Objet</th>
                            <th>Date Début</th>
                            <th>Statut</th> 
                            <th>Montant</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php if (!empty($demandes)): ?>
                            <?php foreach ($demandes as $demande): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($demande['first_name'] . ' ' . $demande['last_name']) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($demande['email']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($demande['objet_mission']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($demande['date_depart'])) ?></td>
                                    
                                    <td>
                                        <?php $statut = $demande['statut'] ?? 'Inconnu'; ?>
                                        <span class="badge badge-theme <?= getStatutClass($statut) ?> fw-bold py-1 px-2">
                                            <?= htmlspecialchars($statut) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-theme-primary fw-bold"> 
                                        <?= number_format($demande['total_calcule'] ?? 0, 2, ',', ' ') ?> €
                                    </td>
                                    
                                    <td class="text-center">
                                        <a href="details_demande.php?id=<?= $demande['id'] ?>" 
                                           class="btn-action-icon" style="color: var(--primary-color); background-color: transparent;"> 
                                            <i class="fas fa-chevron-right fa-2x"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php 
                            $titre = $statuts[$statutFiltre] ?? 'Toutes'; 
                            if ($titre === 'toutes') $titre = 'Toutes les Demandes';
                            ?>
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