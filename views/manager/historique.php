<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/ManagerController.php';
require_once BASE_PATH . 'includes/header.php';

// USE CONTROLLER INSTEAD OF MODEL
// $controller = new ManagerController($pdo);
$historique = $controller->getHistorique();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: var(--secondary-color);">Historique de l'équipe</h2>
        <a href="dashboard_manager.php" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-arrow-back'></i> Retour
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive"> 
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employé</th>
                            <th>Mission</th>
                            <th>Date Validation</th>
                            <th>Montant</th>
                            <th>Statut Final</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historique)): ?>
                            <tr class="empty-state-row">
                                <td colspan="6" class="p-5 text-center">
                                    <div class="empty-state-card">
                                        <div class="mb-3">
                                            <i class='bx bx-archive fs-1 text-secondary opacity-25'></i>
                                        </div>
                                        <h6 class="text-dark fw-bold">Aucun historique disponible.</h6>
                                        <p class="text-muted small">Les demandes traitées apparaîtront ici.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historique as $h): ?>
                                <tr> 
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 fw-bold bg-light rounded-circle p-2 text-secondary border d-flex justify-content-center align-items-center" style="width:40px;height:40px;">
                                                <?= strtoupper(substr($h['first_name'], 0, 1) . substr($h['last_name'], 0, 1)) ?>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"><?= htmlspecialchars($h['first_name'] . ' ' . $h['last_name']) ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($h['email'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block fw-bold text-dark"><?= htmlspecialchars($h['objet_mission']) ?></span>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($h['date_depart'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if($h['date_validation']): ?>
                                            <?= date('d M Y', strtotime($h['date_validation'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        <?= number_format($h['total_calcule'], 2) ?> €
                                    </td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            if (strpos($h['statut'], 'Validé') !== false || $h['statut'] == 'Approuvé') {
                                                $badgeClass = 'bg-success';
                                            } elseif ($h['statut'] == 'Rejeté') {
                                                $badgeClass = 'bg-danger';
                                            } elseif ($h['statut'] == 'Payé') {
                                                $badgeClass = 'bg-info text-dark';
                                            }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> rounded-pill fw-normal px-3 py-2">
                                            <?= $h['statut'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="details_demande.php?id=<?= $h['id'] ?>" class="btn btn-sm btn-outline-primary" title="Revoir le dossier">
                                            <i class='bx bx-show fs-5'></i>
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