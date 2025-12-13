<?php
// views/admin/details_demande.php
// --- Initialisation PHP ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/DemandeController.php';
require_once BASE_PATH . 'Controllers/UserController.php';
require_once __DIR__ . '/../../includes/header.php';

$controller = new DemandeController($pdo);
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($demandeId <= 0) {
    header('Location: ' . BASE_URL . 'views/admin/liste_demandes.php');
    exit;
}

$demande = $controller->getDemandeDetails($demandeId);

if (!$demande) {
    $_SESSION['error_message'] = "Demande introuvable ou accès refusé.";
    header('Location: ' . BASE_URL . 'views/admin/liste_demandes.php');
    exit;
}

// Admin might not have a manager ID in the same way, but let's try to get currency preference.
// If getManagerId() relies on session role being manager, it might fail or return null.
// We'll assume admin wants to see the currency of the *user* who made the request, or a default.
// For now, let's try to get the current user's ID (admin) to check their currency preference if possible.
$adminId = $_SESSION['user_id'] ?? 0; 

// --- Devise ---
if (!class_exists('UserController')) {
    $currencyCode = 'USD';
} else {
    $userController = new UserController($pdo);
    // Use admin's preference or default
    $currencyCode = $userController->getPreferredCurrency($adminId);
}

function getCurrencySymbol(string $code): string {
    return match (strtoupper($code)) {
        'EUR' => '€',
        'USD' => '$',
        'MAD' => 'Dhs',
        'GBP' => '£',
        default => '$',
    };
}
$currencySymbol = getCurrencySymbol($currencyCode);

// --- Données ---
$current_statut = $demande['statut'] ?? 'En attente';
$lignesFrais = $demande['lignes_frais'] ?? [];
$totalFrais = array_sum(array_column($lignesFrais, 'montant'));

// Dates
$date_depart_ts = strtotime($demande['date_depart'] ?? '');
$date_retour_ts = strtotime($demande['date_retour'] ?? '');
$date_depart_fmt = ($date_depart_ts > 0) ? date('d M Y', $date_depart_ts) : 'N/A';
$date_retour_fmt = ($date_retour_ts > 0) ? date('d M Y', $date_retour_ts) : 'N/A';
$duree_jours = ($date_depart_ts > 0 && $date_retour_ts > 0) ? (floor(($date_retour_ts - $date_depart_ts) / 86400) + 1) : 0;

// Messages
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// --- Helpers ---
function getStatusClass($statut) {
    return match ($statut) {
        'En attente' => 'badge-wait',
        'Validée Manager' => 'badge-valid',
        'Rejetée Manager' => 'badge-reject',
        'Approuvée Compta' => 'badge-valid',
        'Payée' => 'badge-valid',
        default => 'bg-secondary text-white',
    };
}

function getJustificatifUrl(int $demandeId, ?string $fileName): string {
    if (empty($fileName)) return '#';
    return BASE_URL . 'Controllers/download_justificatif.php?file=' . urlencode($fileName) . '&demande_id=' . $demandeId;
}
?>

<!-- Inclusion des CSS du thème -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">

<style>
    /* Styles spécifiques à cette page pour ajustements mineurs */
    .page-header-custom {
        margin-bottom: 2rem;
    }
    .card-custom {
        background: var(--card-bg);
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .card-header-custom {
        background: var(--card-bg);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--table-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .card-title-custom {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-color);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: block;
    }
    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-color);
    }
    .avatar-circle {
        width: 80px;
        height: 80px;
        background: var(--secondary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .container, .container * {
            visibility: visible;
        }
        .container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            max-width: 100% !important;
            padding: 20px !important;
            margin: 0 !important;
        }

        /* Cacher la sidebar, navbar, footer, boutons */
        .sidebar, .navbar, header, footer, .btn, .no-print, .page-header-custom button, a {
            display: none !important;
        }
        
        /* Layout modifications - Stack columns vertically */
        .col-lg-8, .col-lg-4 {
            width: 100% !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 auto !important;
            display: block !important;
        }

        .card-custom {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }
    }
</style>

<div class="container py-5">
    
    <!-- Header & Breadcrumb -->
    <div class="page-header-custom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <a href="<?= BASE_URL ?>views/admin/liste_demandes.php" class="text-decoration-none text-muted small mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
            </a>
            <h2 class="fw-bold m-0">Détails de la demande (Admin)</h2>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-secondary text-white" style="background-color: var(--secondary-color);" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Imprimer
            </button>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($message): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4"><i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4"><i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Column: Mission & Expenses -->
        <div class="col-lg-8">
            
            <!-- Mission Info -->
            <div class="card-custom">
                <div class="card-header-custom" style="background-color: #f0fdf4;">
                    <h5 class="card-title-custom"><i class="fas fa-briefcase me-2 text-theme-primary"></i> Informations Mission</h5>
                    <!-- Status Badge Moved Here -->
                    <div class="d-flex gap-2">
                        <span class="badge-theme <?= getStatusClass($current_statut) ?>">
                            <i class="fas fa-user-tie me-1" style="font-size: 0.5rem;"></i> Manager: <?= htmlspecialchars($current_statut) ?>
                        </span>
                        <span class="badge-theme <?= getStatusClass($demande['statut_final'] ?? 'En attente') ?>">
                            <i class="fas fa-crown me-1" style="font-size: 0.5rem;"></i> Admin: <?= htmlspecialchars($demande['statut_final'] ?? 'En attente') ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <span class="info-label">Objet de la mission</span>
                            <div class="info-value fs-5 border rounded p-2 bg-white" style="border-color: #86efac !important;"><?= htmlspecialchars($demande['objet_mission']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label">Lieu</span>
                            <div class="info-value border rounded p-2 bg-white" style="border-color: #86efac !important;"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($demande['lieu_deplacement']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label">Départ</span>
                            <div class="info-value border rounded p-2 bg-white" style="border-color: #86efac !important;"><?= $date_depart_fmt ?></div>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label">Retour</span>
                            <div class="info-value border rounded p-2 bg-white" style="border-color: #86efac !important;"><?= $date_retour_fmt ?> <span class="text-muted small ms-1">(<?= $duree_jours ?> jours)</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses Table -->
            <div class="card-custom">
                <div class="card-header-custom">
                    <h5 class="card-title-custom"><i class="fas fa-receipt me-2 text-theme-primary"></i> Lignes de Frais</h5>
                    <span class="badge bg-light text-dark border"><?= count($lignesFrais) ?> lignes</span>
                </div>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Catégorie</th>
                                <th>Description</th>
                                <th class="text-center">Justificatif</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lignesFrais)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">Aucune ligne de frais.</td></tr>
                            <?php else: ?>
                                <?php foreach ($lignesFrais as $ligne): 
                                    $cat = htmlspecialchars($ligne['type_frais'] ?? 'Autre');
                                    $justificatifFile = $ligne['justificatif_path'] ?? null;
                                    $justificatifUrl = getJustificatifUrl($demandeId, $justificatifFile);
                                    
                                    $icon = match(true) {
                                        stripos($cat, 'transport') !== false => 'fa-car',
                                        stripos($cat, 'hotel') !== false => 'fa-bed',
                                        stripos($cat, 'repas') !== false => 'fa-utensils',
                                        default => 'fa-file-invoice-dollar'
                                    };
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($ligne['date_frais'] ?? 'now')) ?></td>
                                    <td><i class="fas <?= $icon ?> me-2 text-muted"></i> <?= $cat ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($ligne['description'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <?php if ($justificatifFile): ?>
                                            <button class="btn btn-sm btn-theme" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#justificatifModal"
                                                    data-file-url="<?= $justificatifUrl ?>"
                                                    data-file-name="<?= htmlspecialchars(basename($justificatifFile)) ?>">
                                                <i class="fas fa-eye"></i> Voir
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Absent</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold"><?= number_format($ligne['montant'] ?? 0, 2, ',', ' ') ?> <?= $currencySymbol ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white p-3 border-top">
                    <div class="d-flex justify-content-between align-items-center bg-light rounded p-3">
                        <span class="text-uppercase fw-bold text-muted small">Total Remboursement</span>
                        <span class="fs-4 fw-bold text-theme-primary"><?= number_format($totalFrais, 2, ',', ' ') ?> <?= $currencySymbol ?></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Sidebar -->
        <div class="col-lg-4">
            
            <!-- Employee Info -->
            <div class="card-custom">
                <div class="card-body p-4 text-center">
                    <div class="avatar-circle mx-auto mb-3">
                        <?= strtoupper(substr($demande['first_name'] ?? 'E',0,1).substr($demande['last_name'] ?? 'E',0,1)) ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($demande['first_name'] ?? '') . ' ' . htmlspecialchars($demande['last_name'] ?? '') ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($demande['email'] ?? '') ?></p>
                    
                    <div class="row border-top pt-3 mt-2">
                        <div class="col-6 border-end">
                            <span class="info-label">Département</span>
                            <div class="fw-bold"><?= htmlspecialchars($demande['department'] ?? 'Tech / IT') ?></div>
                        </div>
                        <div class="col-6">
                            <span class="info-label">Rôle</span>
                            <div class="fw-bold"><?= htmlspecialchars($demande['role'] ?? 'Employé') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejection Comment (Visible if exists) -->
            <?php if (!empty($demande['commentaire_manager'])): ?>
            <div class="card-custom" style="border: 1px solid #fca5a5;">
                <div class="card-header-custom" style="background-color: #fef2f2; border-bottom: 1px solid #fca5a5;">
                    <h5 class="card-title-custom" style="color: #dc2626;"><i class="fas fa-comment-dots me-2"></i> Motif du Rejet</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 fw-bold" style="color: #b91c1c;"><i class="fas fa-quote-left me-2 opacity-50"></i> <?= nl2br(htmlspecialchars($demande['commentaire_manager'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Validation Actions (Sticky) -->
            <!-- Admin can validate if status is 'En attente' or maybe 'Validée Manager' depending on workflow. -->
            <!-- For now, keeping it same as manager: 'En attente' -->
            <?php if ($current_statut === 'En attente' || $current_statut === 'Validée Manager'): ?>
            <div class="card-custom border-valid" style="border: 1px solid var(--primary-color);">
                <div class="card-header-custom bg-theme-light-green">
                    <h5 class="card-title-custom text-theme-primary"><i class="fas fa-check-double me-2"></i> Action Admin</h5>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-4">Actions d'administration sur la demande.</p>
                    
                    <form method="POST" action="<?= BASE_URL ?>Controllers/traitement_demande.php" class="mb-3"> 
                        <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                        <!-- Admin might validate to 'Validée Manager' (if acting as manager) or 'Approuvée Compta' (if acting as admin/compta) -->
                        <!-- Assuming admin validation means final approval or manager approval override -->
                        <input type="hidden" name="action" value="valider">
                        <button class="btn btn-success w-100 py-2 fw-bold" style="background-color: var(--primary-color); border: none;">
                            <i class="fas fa-check me-2"></i> Valider la demande
                        </button>
                    </form>

                    <button class="btn btn-outline-danger w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#rejetModal">
                        <i class="fas fa-times me-2"></i> Rejeter la demande
                    </button>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modals -->
<!-- Rejet Modal -->
<div class="modal fade" id="rejetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Rejeter la demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>Controllers/traitement_demande.php"> 
                <div class="modal-body">
                    <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                    <input type="hidden" name="action" value="rejeter">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Motif du rejet (Obligatoire)</label>
                        <textarea class="form-control bg-light" name="commentaire_manager" rows="4" required placeholder="Expliquez pourquoi cette demande est rejetée..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Justificatif Modal -->
<div class="modal fade" id="justificatifModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4" style="height: 85vh;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="justificatifModalLabel"><i class="fas fa-file-alt me-2"></i> Justificatif</h5>
                <div class="d-flex gap-2">
                    <a href="#" id="downloadLink" class="btn btn-primary btn-sm rounded-pill"><i class="fas fa-download me-1"></i> Télécharger</a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-light d-flex align-items-center justify-content-center" style="overflow: hidden;">
                <iframe id="justificatifViewer" style="width:100%; height:100%; border:none; display:none;" src=""></iframe>
                <img id="justificatifImage" style="max-width:100%; max-height:100%; object-fit:contain; display:none;" src="" alt="Justificatif">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const justificatifModal = document.getElementById('justificatifModal');
        const justificatifViewer = document.getElementById('justificatifViewer');
        const justificatifImage = document.getElementById('justificatifImage');
        const downloadLink = document.getElementById('downloadLink');
        const modalTitle = document.getElementById('justificatifModalLabel');

        justificatifModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; 
            const fileUrl = button.getAttribute('data-file-url');
            const fileName = button.getAttribute('data-file-name');
            
            // Determine file type based on extension
            const ext = fileName.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);

            downloadLink.href = fileUrl;
            modalTitle.innerHTML = `<i class="fas fa-file-alt me-2"></i> Justificatif: ${fileName}`;

            if (isImage) {
                justificatifViewer.style.display = 'none';
                justificatifViewer.src = '';
                
                justificatifImage.style.display = 'block';
                justificatifImage.src = fileUrl;
            } else {
                justificatifImage.style.display = 'none';
                justificatifImage.src = '';
                
                justificatifViewer.style.display = 'block';
                justificatifViewer.src = fileUrl;
            }
        });
        
        // Clear src on close
        justificatifModal.addEventListener('hidden.bs.modal', function () {
            justificatifViewer.src = '';
            justificatifImage.src = '';
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
