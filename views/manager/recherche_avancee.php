<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/ManagerController.php';
require_once BASE_PATH . 'includes/header.php';

$controller = new ManagerController($pdo);

// 1. Data Retrieval via Controller
$employes = $controller->getListeEmployes();
$resultats = $controller->faireUneRecherche($_GET);

// 2. Persist Form Values
$current_emp  = $_GET['employe'] ?? '';
$current_stat = $_GET['statut'] ?? '';
$current_d1   = $_GET['date_debut'] ?? '';
$current_d2   = $_GET['date_fin'] ?? '';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table.css">

<div class="container-fluid p-4">

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
                            <?php foreach ($employes as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= ($current_emp == $emp['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Statut</label>
                        <select name="statut" class="form-select border-0 bg-light py-2">
                            <option value="">Tous les statuts</option>
                            <?php 
                            $statuses = ['En attente', 'Validé Manager', 'Rejeté', 'Approuvé', 'Payé'];
                            foreach ($statuses as $st): ?>
                                <option value="<?= $st ?>" <?= ($current_stat == $st) ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Du</label>
                        <input type="date" name="date_debut" class="form-control border-0 bg-light py-2" value="<?= htmlspecialchars($current_d1) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Au</label>
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
                                    <div class="fw-bold"><?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($d['email']) ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($d['objet_mission']) ?>
                                    <div class="small text-muted"><i class='bx bx-map me-1'></i><?= htmlspecialchars($d['lieu_deplacement']) ?></div>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($d['date_depart'])) ?>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?= number_format($d['total_calcule'], 2) ?> €
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if (strpos($d['statut'], 'Validé') !== false || $d['statut'] == 'Approuvé') {
                                        $badgeClass = 'bg-success';
                                    } elseif ($d['statut'] == 'Rejeté') {
                                        $badgeClass = 'bg-danger';
                                    } elseif ($d['statut'] == 'En attente') {
                                        $badgeClass = 'bg-warning text-dark';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3">
                                        <?= $d['statut'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="details_demande.php?id=<?= $d['id'] ?>" class="btn-action-icon" title="Voir les détails">
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