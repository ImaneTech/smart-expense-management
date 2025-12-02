<?php
// views/manager/details_demande.php (Amélioration Design - Titres Hors Cartes)

// --- Initialisation PHP (Inchangée) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php';
require_once BASE_PATH . 'includes/header.php';

// Initialisation du contrôleur
$controller = new DemandeController($pdo);

$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($demandeId <= 0) {
    header('Location: demandes_liste.php');
    exit;
}

// Récupération des détails de la demande
$demande = $controller->getDemandeDetails($demandeId);

if (!$demande) {
    $_SESSION['error_message'] = "Demande introuvable ou vous n'êtes pas autorisé à la consulter.";
    header('Location: demandes_liste.php');
    exit;
}

// ----------------------------------------------------------------------------------
// *** Données et Calculs (Inchangé) ***

// 1. Définition du statut actuel
$current_statut = $demande['statut'] ?? 'En attente';

// 2. CORRECTION DU CALCUL DES FRAIS
$totalFrais = array_sum(array_column($demande['lignes_frais'] ?? [], 'montant'));

// 3. Définition des classes de badge
$statutClass = match ($current_statut) {
    'En attente' => 'badge-wait',
    'Validée Manager' => 'badge-valid',
    'Rejetée Manager' => 'badge-reject',
    default => 'badge-secondary',
};

// 4. Protection contre les dates NULL/0000-00-00
$date_depart_ts = strtotime($demande['date_depart'] ?? '');
$date_retour_ts = strtotime($demande['date_retour'] ?? '');
$date_depart_fmt = ($date_depart_ts > 0) ? date('d/m/Y', $date_depart_ts) : 'Non spécifiée';
$date_retour_fmt = ($date_retour_ts > 0) ? date('d/m/Y', $date_retour_ts) : 'Non spécifiée';

// Messages de session
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
// ----------------------------------------------------------------------------------
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/details_demande.css"> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="container-fluid p-4 details-page-manager">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h2 class="fw-bolder text-theme-primary">Détails de la Demande <span class="text-theme-secondary">#<?= $demande['id'] ?></span></h2>
        <a href="demandes_liste.php" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fas fa-arrow-left me-2"></i> Retour aux demandes
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5">
            <h4 class="fw-bold mb-3 text-theme-secondary section-title-custom"><i class="fas fa-info-circle me-2"></i> Informations Générales</h4>
            
            <div class="card detail-card shadow-sm mb-4 <?= $statutClass ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-3">
                         <span class="badge badge-lg fw-bold <?= $statutClass ?>-pill">
                            <?= htmlspecialchars($current_statut) ?>
                        </span>
                    </div>
                    
                    <ul class="list-group list-group-flush detail-list">
                        <li class="list-group-item"><strong>Demandeur :</strong> <span><?= htmlspecialchars($demande['first_name'] . ' ' . $demande['last_name']) ?></span></li>
                        <li class="list-group-item"><strong>Mission :</strong> <span><?= htmlspecialchars($demande['objet_mission']) ?></span></li>
                        <li class="list-group-item"><strong>Lieu :</strong> <span><?= htmlspecialchars($demande['lieu_deplacement']) ?></span></li>
                        <li class="list-group-item"><strong>Période :</strong> <span>Du <?= $date_depart_fmt ?> au <?= $date_retour_fmt ?></span></li>
                    </ul>

                    <div class="mt-4 p-3 text-center total-frais-box bg-light rounded shadow-sm">
                        <small class="text-muted fw-bold">Total des Frais Déclarés</small>
                        <p class="fs-1 text-theme-secondary fw-bolder mb-0">
                            <?= number_format($totalFrais, 2, ',', ' ') ?> €
                        </p>
                    </div>

                    <?php if ($current_statut === 'Rejetée Manager' && $demande['commentaire_manager']): ?>
                        <div class="alert alert-danger mt-3 rejection-reason">
                            <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Motif du Rejet</h6>
                            <p class="mb-0 small"><?= nl2br(htmlspecialchars($demande['commentaire_manager'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <h4 class="fw-bold mb-3 text-theme-secondary section-title-custom"><i class="fas fa-receipt me-2"></i> Lignes de Dépenses (<span id="frais-count"><?= count($demande['lignes_frais'] ?? []) ?></span>)</h4>
            
            <div class="card detail-card shadow-sm mb-4">
                <div class="card-body p-0">
                    <?php if (!empty($demande['lignes_frais'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 detail-table">
                                <thead class="table-light">
                                    <tr class="text-uppercase small text-muted">
                                        <th>Date</th>
                                        <th>Catégorie</th>
                                        <th class="text-end">Montant</th>
                                        <th class="text-center">Justificatif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($demande['lignes_frais'] as $ligne): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($ligne['date_depense'])) ?></td>
                                            <td><?= htmlspecialchars($ligne['nom_categorie']) ?></td>
                                            <td class="text-end text-theme-primary fw-bold"><?= number_format($ligne['montant'], 2, ',', ' ') ?> €</td>
                                            <td class="text-center">
                                                <?php if (!empty($ligne['justificatif_path'])): ?>
                                                    <a href="<?= BASE_URL . htmlspecialchars($ligne['justificatif_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 small">
                                                        <i class="fas fa-file-alt"></i> Voir
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-muted text-center no-frais">
                            <i class="fas fa-hand-holding-usd fa-3x mb-3 text-light-gray"></i>
                            <p class="mb-0 fw-bold">Aucune ligne de frais détaillée soumise.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($current_statut === 'En attente'): ?>
        <h4 class="fw-bold mt-5 mb-3 text-theme-secondary section-title-custom"><i class="fas fa-cogs me-2"></i> Actions</h4>
        <div class="card shadow-lg border-0 action-card">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <form method="POST" action="traitement_demande.php">
                            <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                            <input type="hidden" name="action" value="valider">
                            <button type="submit" class="btn btn-success w-100 btn-action fw-bold" onclick="return confirm('Confirmez-vous la validation de cette demande ?');">
                                <i class="fas fa-thumbs-up me-2"></i> VALIDER la Demande
                            </button>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <button type="button" class="btn btn-danger w-100 btn-action fw-bold" data-bs-toggle="modal" data-bs-target="#rejetModal">
                            <i class="fas fa-thumbs-down me-2"></i> REJETER la Demande
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($current_statut === 'Validée Manager'): ?>
        <div class="alert alert-success mt-4 validation-info text-center">
            <i class="fas fa-check-circle me-2"></i> Cette demande a été **Validée** par vous le **<?= date('d/m/Y', strtotime($demande['date_traitement'])) ?>**.
        </div>
    <?php elseif ($current_statut === 'Rejetée Manager'): ?>
        <div class="alert alert-danger mt-4 validation-info text-center">
            <i class="fas fa-times-circle me-2"></i> Cette demande a été **Rejetée** par vous le **<?= date('d/m/Y', strtotime($demande['date_traitement'])) ?>**.
        </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="rejetModal" tabindex="-1" aria-labelledby="rejetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="traitement_demande.php">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="rejetModalLabel"><i class="fas fa-ban me-2"></i> Motif du Rejet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger fw-bold">Veuillez expliquer clairement la raison du rejet de cette demande :</p>
                    <div class="mb-3">
                        <label for="motif_rejet" class="form-label fw-bold">Commentaire :</label>
                        <textarea class="form-control" id="motif_rejet" name="commentaire_manager" rows="4" required placeholder="Explication détaillée..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                    <input type="hidden" name="action" value="rejeter">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-times me-2"></i> Confirmer le Rejet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>