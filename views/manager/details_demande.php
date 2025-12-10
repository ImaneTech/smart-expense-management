<?php
// views/manager/details_demande.php (DESIGN PREMIUM)

// --- Initialisation PHP ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/DemandeController.php';
require_once BASE_PATH . 'Controllers/UserController.php'; // <-- INCLUSION
require_once BASE_PATH . 'includes/header.php';

$controller = new DemandeController($pdo);
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($demandeId <= 0) {
    header('Location: demandes_liste.php');
    exit;
}

$demande = $controller->getDemandeDetails($demandeId);

if (!$demande) {
    $_SESSION['error_message'] = "Demande introuvable ou accès refusé.";
    header('Location: demandes_liste.php');
    exit;
}

// Récupération de l'ID du manager
$managerId = $controller->getManagerId(); 

// --- GESTION DE LA DEVISE DYNAMIQUE ---
$userController = new UserController($pdo);
$managerCurrencyCode = $userController->getPreferredCurrency($managerId);
$currencySymbol = getCurrencySymbol($managerCurrencyCode);

/**
 * FONCTION : Convertit le code de devise (ex: EUR) en symbole (ex: €).
 * Dupliquée ici pour l'autonomie du fichier si nécessaire, mais idéalement dans un Helper.
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

// --- Données et Calculs ---
$current_statut = $demande['statut'] ?? 'En attente';
$lignesFrais = $demande['lignes_frais'] ?? [];
$totalFrais = array_sum(array_column($lignesFrais, 'montant'));

// Dates
$date_depart_ts = strtotime($demande['date_depart'] ?? '');
$date_retour_ts = strtotime($demande['date_retour'] ?? '');
$date_depart_fmt = ($date_depart_ts > 0) ? date('d M Y', $date_depart_ts) : 'N/A';
$date_retour_fmt = ($date_retour_ts > 0) ? date('d M Y', $date_retour_ts) : 'N/A';

// Durée (Jours)
$duree_jours = ($date_depart_ts > 0 && $date_retour_ts > 0) 
    ? (floor(($date_retour_ts - $date_depart_ts) / 86400) + 1)
    : 0;

// Messages flash
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// --- Helper Statut (CSS Classes mapping) ---
function getStatusClass($statut) {
    return match ($statut) {
        'En attente' => 'status-warning',
        'Validée Manager' => 'status-success',
        'Rejetée Manager' => 'status-danger',
        default => 'status-secondary',
    };
}
?>

<style>
    :root {
        --bg-color: #f4f7fc;
        --card-bg: #ffffff;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --primary-soft: rgba(118, 189, 70, 0.1);
        --primary-color: #76BD46;
        --secondary-color: #2566A1;
        --danger-color: #ef4444;
        --radius-lg: 16px;
        --radius-sm: 8px;
        --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', sans-serif;
        color: var(--text-dark);
    }

    /* --- Composants Cards --- */
    .premium-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid rgba(226, 232, 240, 0.8);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: transform 0.2s ease;
    }

    .section-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* --- Sidebar Profile --- */
    .user-avatar-lg {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, var(--secondary-color), #4f46e5);
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-bottom: 1rem;
        box-shadow: 0 4px 10px rgba(37, 102, 161, 0.3);
    }

    /* --- Total Box --- */
    .total-display {
        background: linear-gradient(135deg, var(--primary-color) 0%, #5da530 100%);
        color: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    /* Mettre à jour l'icône de la devise pour le total */
    .total-display::after {
        content: '<?= $currencySymbol ?>';
        position: absolute;
        right: -10px;
        bottom: -20px;
        font-size: 8rem;
        font-weight: 900;
        opacity: 0.1;
        transform: rotate(-15deg);
    }

    /* --- Timeline Mission --- */
    .mission-timeline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin: 2rem 0;
        padding: 0 10px;
    }
    .timeline-track {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 4px;
        background: #e2e8f0;
        z-index: 1;
        transform: translateY(-50%);
        border-radius: 4px;
    }
    .timeline-progress {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%; /* Si mission passée à 100% */
        height: 4px;
        background: var(--secondary-color);
        z-index: 2;
        transform: translateY(-50%);
        border-radius: 4px;
    }
    .timeline-point {
        width: 14px;
        height: 14px;
        background: white;
        border: 3px solid var(--secondary-color);
        border-radius: 50%;
        position: relative;
        z-index: 3;
    }
    .timeline-date {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        color: var(--secondary-color);
    }
    .timeline-label {
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* --- Table Design --- */
    .table-custom thead th {
        background: #f8fafc;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        border-top: none;
        padding: 1rem;
    }
    .table-custom tbody td {
        vertical-align: middle;
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.95rem;
    }
    .category-icon {
        width: 32px;
        height: 32px;
        background: #f1f5f9;
        color: var(--text-muted);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }
    /* Catégories spécifiques */
    tr[data-cat="Transport"] .category-icon { background: #e0f2fe; color: #0284c7; }
    tr[data-cat="Hébergement"] .category-icon { background: #fef3c7; color: #d97706; }
    tr[data-cat="Repas"] .category-icon { background: #dcfce7; color: #16a34a; }

    /* --- Status Badges --- */
    .status-badge {
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status-warning { background: #fffbeb; color: #d97706; border: 1px solid #fcd34d; }
    .status-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .status-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

    /* --- Action Buttons --- */
    .btn-action-lg {
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
        border: none;
    }
    .btn-accept { background: var(--primary-color); color: white; box-shadow: 0 4px 12px rgba(118, 189, 70, 0.25); }
    .btn-accept:hover { background: #65a33b; transform: translateY(-2px); }
    
    .btn-reject-outline { background: white; border: 2px solid #fee2e2; color: #ef4444; }
    .btn-reject-outline:hover { background: #fef2f2; border-color: #ef4444; }

</style>

<div class="container py-5">
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div class="d-flex align-items-center gap-3">
            <a href="demandes_liste.php" class="btn btn-white border rounded-circle shadow-sm p-0" style="width: 45px; height: 45px; display:grid; place-items:center;">
                <i class="fas fa-arrow-left text-muted"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-dark">Détails Demande #<?= $demandeId ?></h2>
                <span class="text-muted small">Soumis le <?= date('d/m/Y', strtotime($demande['date_soumission'] ?? 'now')) ?></span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="status-badge <?= getStatusClass($current_statut) ?>">
                <i class="fas fa-circle" style="font-size: 0.6em;"></i>
                <?= htmlspecialchars($current_statut) ?>
            </span>
            <button class="btn btn-light text-muted border px-3 rounded-pill" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Imprimer
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success shadow-sm border-0 rounded-3 mb-4"><i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4"><i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        
        <div class="col-lg-8">
            
            <div class="premium-card">
                <div class="section-title"><i class="fas fa-briefcase"></i> Mission</div>
                
                <h4 class="fw-bold mb-3"><?= htmlspecialchars($demande['objet_mission']) ?></h4>
                <p class="text-muted mb-4"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?= htmlspecialchars($demande['lieu_deplacement']) ?></p>

                <div class="bg-light p-4 rounded-3 border-start border-4 border-secondary">
                    <div class="mission-timeline">
                        <div class="timeline-track"></div>
                        <div class="timeline-progress"></div> <div class="timeline-point">
                            <span class="timeline-date"><?= $date_depart_fmt ?></span>
                            <span class="timeline-label">Départ</span>
                        </div>
                        
                        <div class="text-center position-relative" style="z-index:4; background: #f8f9fa; padding: 0 10px; color: var(--secondary-color); font-weight:bold;">
                            <?= $duree_jours ?> Jours
                        </div>

                        <div class="timeline-point">
                            <span class="timeline-date"><?= $date_retour_fmt ?></span>
                            <span class="timeline-label">Retour</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="premium-card p-0 overflow-hidden">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <div class="section-title mb-0"><i class="fas fa-receipt"></i> Lignes de Frais</div>
                    <span class="badge bg-light text-dark border"><?= count($lignesFrais) ?> justicatifs</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0 table-hover">
                        <thead>
                            <tr>
                                <th class="ps-4">Catégorie</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Montant</th>
                            </tr>
                        </thead>
                <tbody>
    <?php if (empty($lignesFrais)): ?>
        <tr>
            <td colspan="4" class="text-center py-5 text-muted">
                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i><br>
                Aucune ligne de frais saisie.
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($lignesFrais as $ligne): 
            // 1. Gestion sécurisée de la catégorie
            $cat = htmlspecialchars($ligne['type_frais'] ?? 'Autre');
            
            // 2. Choix de l'icône
            $icon = match(true) {
                stripos($cat, 'transport') !== false => 'fa-car',
                stripos($cat, 'taxi') !== false => 'fa-taxi',
                stripos($cat, 'hotel') !== false => 'fa-bed',
                stripos($cat, 'repas') !== false => 'fa-utensils',
                default => 'fa-file-invoice-dollar'
            };

            // 3. CORRECTION DES ERREURS DE DATE ICI
            // On vérifie si 'date_frais' existe, sinon on essaie 'date', sinon NULL
            $rawDate = $ligne['date_frais'] ?? $ligne['date'] ?? null;
            // Si une date est trouvée, on la formate, sinon on affiche "N/A"
            $dateAffichee = $rawDate ? date('d/m', strtotime($rawDate)) : 'N/A';
        ?>
        <tr data-cat="<?= $cat ?>">
            <td class="ps-4 fw-bold text-dark">
                <div class="d-flex align-items-center">
                    <span class="category-icon"><i class="fas <?= $icon ?>"></i></span>
                    <?= $cat ?>
                </div>
            </td>
            <td class="text-muted"><?= htmlspecialchars($ligne['description'] ?? '-') ?></td>
            
            <td><?= $dateAffichee ?></td>
            
            <td class="text-end fw-bold pe-4">
                <?= number_format($ligne['montant'] ?? 0, 2, ',', ' ') ?> <?= $currencySymbol ?> <!-- DEVISE DYNAMIQUE -->
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
                        <?php if (!empty($lignesFrais)): ?>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold text-uppercase fs-6 pt-3">Total Général</td>
                                <td class="text-end fw-bolder fs-5 text-primary pe-4 pt-3">
                                    <?= number_format($totalFrais, 2, ',', ' ') ?> <?= $currencySymbol ?> <!-- DEVISE DYNAMIQUE -->
                                </td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="total-display shadow-lg mb-4">
                <p class="mb-1 opacity-75 text-uppercase small fw-bold">Remboursement total</p>
                <h1 class="fw-bold mb-0 display-4">
                    <?= number_format($totalFrais, 2, ',', ' ') ?>
                </h1>
                <small class="opacity-75">Devise: <?= htmlspecialchars($managerCurrencyCode) ?></small> <!-- CODE DEVISE DYNAMIQUE -->
            </div>

            <div class="premium-card text-center">
                <div class="section-title justify-content-center"><i class="fas fa-user-circle"></i> Demandeur</div>
                
                <div class="d-flex flex-column align-items-center">
                    <div class="user-avatar-lg">
                        <?= strtoupper(substr($demande['first_name'],0,1).substr($demande['last_name'],0,1)) ?>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($demande['first_name'] . ' ' . $demande['last_name']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($demande['email']) ?></p>
                    
                    <div class="row w-100 border-top pt-3 mt-2">
                        <div class="col-6 border-end">
                            <small class="d-block text-muted text-uppercase" style="font-size:0.7rem">Département</small>
                            <span class="fw-bold text-dark">Tech / IT</span>
                        </div>
                        <div class="col-6">
                            <small class="d-block text-muted text-uppercase" style="font-size:0.7rem">Rôle</small>
                            <span class="fw-bold text-dark">Staff</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($current_statut === 'En attente'): ?>
            <div class="premium-card" style="position: sticky; top: 20px; border-color: var(--primary-color);">
                <div class="section-title text-primary"><i class="fas fa-check-double"></i> Validation Requise</div>
                <p class="small text-muted mb-4">En tant que manager, vous devez vérifier les justificatifs avant de valider.</p>

                <form method="POST" action="traitement_demande.php" class="mb-3">
                    <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                    <input type="hidden" name="action" value="valider">
                    <button class="btn-action-lg btn-accept">
                        <i class="fas fa-check"></i> Accepter la demande
                    </button>
                </form>

                <button class="btn-action-lg btn-reject-outline" data-bs-toggle="modal" data-bs-target="#rejetModal">
                    <i class="fas fa-times"></i> Rejeter
                </button>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>