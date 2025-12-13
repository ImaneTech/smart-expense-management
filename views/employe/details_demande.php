<?php
// Fichier : views/employe/details_demande.php

// 1. Initialisation de la session et contrôle d'accès
if (session_status() === PHP_SESSION_NONE) session_start();

// Définir les rôles autorisés pour cette page
$allowed_roles = ['employe'];
$user_role = $_SESSION['role'] ?? 'guest';

// Utiliser BASE_URL qui doit être définie dans config.php
$base_url = defined('BASE_URL') ? BASE_URL : '/';

if (!isset($_SESSION['user_id']) || !in_array($user_role, $allowed_roles)) {
    header('Location: ' . $base_url . 'views/auth/login.php');
    exit();
}

// 2. Configuration PHP et inclusions
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/DemandeController.php';
require_once BASE_PATH . 'includes/flash.php'; 

// Définir la devise
$currencySymbol = defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : 'Dhs';

// 3. Récupération des données
$user_id = (int)($_SESSION['user_id'] ?? 0);
$demande_id = (int)($_GET['id'] ?? 0);

$demande = null;
$errorMessage = '';
$details = [];

try {
    if ($demande_id === 0) {
        throw new Exception("ID de demande non fourni.");
    }
    
    if (!isset($pdo)) {
        throw new Exception("Erreur de connexion à la base de données (PDO non initialisé).");
    }

    $demandeController = new DemandeController($pdo);
    $data = $demandeController->getDemandeDetailsById($demande_id, $user_id);

    if (!$data) {
        throw new Exception("Demande introuvable ou non autorisée pour votre compte.");
    }
    
    $demande = $data['demande_frais'];
    $details = is_array($data['details_frais']) ? $data['details_frais'] : [];

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}

// 4. Fonctions d'aide (Helpers)
function getStatusClass(string $statut): string {
    return str_replace([' ', 'é', 'è', 'à', 'û'], ['-', 'e', 'e', 'a', 'u'], strtolower($statut));
}

function getStatusIcon(string $statut): string {
    $icons = [
        'En attente' => 'bi-clock-history',
        'Validée Manager' => 'bi-check-circle-fill',
        'Approuvée Compta' => 'bi-check-circle-fill',
        'Rejetée Manager' => 'bi-x-circle-fill',
        'Rejetée Compta' => 'bi-x-circle-fill',
        'Payée' => 'bi-currency-dollar'
    ];
    return $icons[$statut] ?? 'bi-info-circle-fill';
}

function formatAmount($amount, $symbol): string {
    return number_format((float)($amount ?? 0), 2, ',', ' ') . ' ' . htmlspecialchars($symbol);
}

// Inclusion de l'en-tête
require_once BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/create_demande.css"> 

<style>
/* ---------------------------------------------------- */
/* CORRECTIONS GENERALES POUR ESPACEMENT & ALIGNEMENT */
/* ---------------------------------------------------- */
.outer-container {
    max-width: 1300px;
    background-color: #f8f9fa;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.card { 
    border-radius: 15px; 
    margin-bottom: 2rem; 
}
.card-header { 
    padding: 1rem 1.5rem;
}
.card-body-mission {
    padding: 1.5rem; 
}

/* ---------------------------------------------------- */
/* FIX DU LAYOUT EN COLONNES (utilisant le Grid de Bootstrap) */
/* ---------------------------------------------------- */
.detail-label {
    font-weight: 600;
    color: #6c757d;
    display: block;
    margin-bottom: 0.2rem; 
    font-size: 0.95rem;
}
.detail-value {
    font-size: 1.05rem;
    color: #343a40;
}
.detail-item {
    padding: 0;
}

/* Bordure vert clair pour tous les conteneurs d'informations de mission */
.mission-data-container .detail-value {
    border: 1px solid #76BD46; 
    padding: 0.5rem;
    border-radius: 8px;
    background-color: #f9fff6;
    display: block;
}

/* Les lignes pour la grille de mission */
.mission-row {
    margin-bottom: 1rem;
}
.mission-row:last-child {
    margin-bottom: 0;
}

/* ---------------------------------------------------- */
/* STATUT & MONTANT TOTAL (pour s'adapter aux colonnes) */
/* ---------------------------------------------------- */
.status-badge-container {
    padding: 0.4rem 1rem;
    font-size: 0.95rem;
    font-weight: 700;
    border-radius: 50px; 
}
.status-en-attente { background-color: #ffc107; color: #000; }
.status-validee-manager, .status-approuvee-compta { background-color: var(--primary-color); color: #fff; }
.status-rejetee-manager, .status-rejetee-compta { background-color: #dc3545; color: #fff; } 
.status-payee { background-color: var(--secondary-color); color: #fff; }

.total-frais-display-header {
    background: #e6f7e6;
    border: 1px solid #c8e6c8;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    /* Styles pour l'alignement tel que dans l'image */
    display: flex;
    flex-direction: column;
    align-items: center; 
    justify-content: center;
    text-align: center;
    height: 100%; /* S'assurer qu'il prend toute la hauteur disponible dans sa colonne */
}

/* ---------------------------------------------------- */
/* TABLEAU ET MARGES (Les fix précédents sont conservés) */
/* ---------------------------------------------------- */
:root {
    --table-content-padding: 1.5rem; 
}
.card-body-expenses {
    padding: 0; 
}
.modern-table thead th {
    padding: 1rem var(--table-content-padding);
}
.modern-table tbody td {
    padding: 0.8rem var(--table-content-padding);
}
.alert-footer-table {
    border-top: none !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    border-top-left-radius: 0 !important;
    border-top-right-radius: 0 !important;
    padding: 1rem var(--table-content-padding) !important; 
}
.btn-voir-justificatif {
    background-color: #76BD46; 
    color: #fff;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background-color 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}


/* ---------------------------------------------------- */
/* FIX LIGHTBOX / MODAL */
/* ---------------------------------------------------- */
.image-preview-modal {
    display: none; 
    position: fixed !important; 
    z-index: 99999 !important; 
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.8) !important; 
    backdrop-filter: blur(5px); 
    align-items: center;
    justify-content: center;
    overflow: auto;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.image-preview-modal.show {
    display: flex !important; 
    opacity: 1;
}

.image-preview-close {
    position: absolute;
    top: 30px; 
    right: 30px;
    color: #fff !important; 
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    padding: 5px 12px;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
    transition: background 0.2s, color 0.2s;
}

@media print {
    /* Cache les éléments de navigation et l'interface utilisateur générale */
    body * {
        visibility: hidden; 
    }
    
    /* Affiche uniquement le contenu pertinent */
    .outer-container, .outer-container * {
        visibility: visible;
    }

    /* Positionnement absolu pour le contenu imprimable */
    .outer-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        background-color: white !important;
    }
    
    /* Reset styles inline des containers internes */
    .outer-container > div {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Masquer Sidebar (App), Header, Footer, Boutons */
    .sidebar, header, footer, .navbar, .btn, .no-print, .alert-footer-table, .page-header-custom .btn {
        display: none !important;
    }

    /* Main content takes full width and stacks */
    .col-lg-8, .col-lg-4 {
        width: 100% !important; 
        flex: 0 0 100% !important;
        max-width: 100% !important;
        display: block !important; /* Force stacking */
    }

    /* Ajustements layout pour impression */
    .col-lg-4, .col-lg-8 {
        width: 100% !important; 
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        break-inside: avoid; 
    }

    /* Assurer que les liens/textes sont lisibles */
    a {
        text-decoration: none !important;
        color: black !important;
    }
}
</style>


<div class="mx-auto outer-container">
<div class="mx-auto" style="max-width:1300px;">

    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
        <?php displayFlash(); ?>
        <h1 class="fw-bold m-0 page-title">
            <i class="fa fa-file-invoice-dollar me-2 text-primary-themed"></i> Détails de la Demande <span class="text-secondary-themed">#<?= htmlspecialchars($demande_id) ?></span>
        </h1>
        <div class="d-flex gap-2">
             <button class="btn btn-secondary-outline fw-bold rounded-pill px-4 py-2 mt-3 mt-md-0 d-flex align-items-center" onclick="window.print()">
                <i class="fa fa-print me-2"></i> Imprimer
            </button>
            <a href="<?= BASE_URL ?>views/employe/employe_demandes.php" class="btn btn-secondary-outline fw-bold rounded-pill px-4 py-2 mt-3 mt-md-0">
                <i class="fa fa-arrow-left me-1"></i> Retour aux Demandes
            </a>
        </div>
    </div>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger rounded-3 shadow mb-4" role="alert">
            <h5 class="alert-heading"><i class="fa fa-exclamation-triangle me-2"></i> Erreur :</h5>
            <p class="mb-0"><?= htmlspecialchars($errorMessage) ?></p>
        </div>
    <?php elseif ($demande): ?>
        
        <div class="card shadow-lg border-0 mb-4">
            <div class="card-header primary-bg-card-header text-white">
                <h5 class="mb-0 fw-bold"><i class="fa fa-map-marker-alt me-2"></i> Informations de la Mission et Statut</h5>
            </div>
            <div class="card-body card-body-mission p-4">
                
                <div class="row g-3 align-items-start">
                    
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="mission-row">
                            <div class="detail-item mission-data-container">
                                <span class="detail-label"><i class="bi bi-bookmark-fill me-1"></i> Objet de la Mission</span>
                                <div class="detail-value fw-bold text-primary-themed"><?= htmlspecialchars($demande['objet_mission']) ?></div>
                            </div>
                        </div>
                        <div class="mission-row mt-3">
                            <div class="detail-item mission-data-container">
                                <span class="detail-label"><i class="bi bi-geo-alt-fill me-1"></i> Lieu du Déplacement</span>
                                <div class="detail-value"><?= htmlspecialchars($demande['lieu_deplacement'] ?? 'Non spécifié') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="mission-row">
                            <div class="detail-item mission-data-container">
                                <span class="detail-label"><i class="bi bi-calendar-range-fill me-1"></i> Période</span>
                                <div class="detail-value">
                                    Du <?= date('d/m/Y', strtotime($demande['date_depart'])) ?> au <?= date('d/m/Y', strtotime($demande['date_retour'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="mission-row mt-3">
                            <div class="detail-item mission-data-container">
                                <span class="detail-label"><i class="bi bi-clock-fill me-1"></i> Date de Soumission</span>
                                <div class="detail-value">
                                    <?= date('d/m/Y à H:i', strtotime($demande['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="mission-row">
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-flag-fill me-1"></i> Statut Actuel</span>
                                <?php 
                                $statutClass = getStatusClass($demande['statut']); 
                                $statutIcon = getStatusIcon($demande['statut']);
                                ?>
                                <div class="mt-2">
                                    <span class="status-badge-container status-<?= $statutClass ?>">
                                        <i class="bi <?= $statutIcon ?>"></i>
                                        <?= htmlspecialchars($demande['statut']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="total-frais-display-header mt-3 w-100">
                             <span class="fw-bold me-2" style="font-size: 1rem;"><i class="bi bi-cash me-1"></i> Montant Total Déclaré:</span>
                             <span class="amount"><?= formatAmount($demande['montant_total'] ?? 0, $currencySymbol) ?></span>
                        </div>
                    </div>

                </div>
                
                <!-- Rejection Comment (Visible if exists) -->
                <?php if (!empty($demande['commentaire_manager'])): ?>
                    <div class="manager-comment mt-4 p-3 rounded border border-danger bg-light-danger" style="background-color: #fff5f5;">
                        <p class="manager-comment-label text-danger fw-bold mb-2"><i class="fa fa-comment-dots me-1"></i> Motif du Rejet / Commentaire Manager</p>
                        <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($demande['commentaire_manager'])) ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <div class="card shadow-lg border-0">
            <div class="card-header light-green-bg-card-header text-dark">
                <h5 class="mb-0 fw-bold"><i class="fa fa-receipt me-2 text-secondary-themed"></i> Tableau des Dépenses (<?= count($details) ?> ligne<?= count($details) > 1 ? 's' : '' ?>)</h5>
            </div>
            
            <div class="card-body card-body-expenses">
                
                <?php if (!empty($details)): ?>
                <div class="table-responsive">
                    <table class="table modern-table align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th style="width:10%;">Date</th>
                                <th style="width:18%;">Catégorie</th>
                                <th style="width:35%;">Description</th>
                                <th style="width:15%;" class="text-end">Montant (<?= htmlspecialchars($currencySymbol) ?>)</th>
                                <th style="width:22%;" class="text-center">Justificatif</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $detail): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($detail['date_depense'])) ?></td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-secondary category-badge-pill">
                                            <?= htmlspecialchars($detail['categorie_nom'] ?? 'Inconnu') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($detail['description'] ?? 'N/A') ?></td>
                                    <td class="text-end fw-bold text-primary-themed">
                                        <?= formatAmount($detail['montant'] ?? 0, $currencySymbol) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $justificatif_path = $detail['justificatif_path'] ?? '';
                                            if (!empty($justificatif_path)):
                                                $filePath = BASE_URL . ltrim($justificatif_path, '/'); 
                                                $fileName = basename($justificatif_path);
                                                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        ?>
                                            <?php if (in_array($fileExt, ['jpg','jpeg','png','gif','webp'])): ?>
                                                <a href="javascript:void(0)" onclick="showImagePreview('<?= htmlspecialchars($filePath) ?>')" class="btn-voir-justificatif">
                                                    <i class="bi bi-file-earmark-image me-1"></i> Voir Justificatif
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($filePath) ?>" target="_blank" class="file-preview-link">
                                                    <i class="bi bi-file-earmark-<?= ($fileExt === 'pdf' ? 'pdf' : 'text') ?>-fill me-1"></i> Télécharger
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic" style="font-size: 0.8rem;">Non fourni</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info text-center rounded-0 mb-0 alert-footer-table">
                        <i class="bi bi-info-circle me-2"></i> Aucune dépense détaillée n'a été trouvée pour cette demande.
                    </div>
                <?php endif; ?>

            </div>
            
            <div class="alert-light-info alert-footer-table" style="display: flex; align-items: center; border: 1px solid #c8e6c8; background: #e6f7e6; color: #1e7e34; border-top: none; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                 <?php 
                    $status = $demande['statut'] ?? '';
                    $isEditable = ($status === 'En attente');
                ?>
                
                <?php if ($isEditable): ?>
                    <div style="flex: 1;">
                        <i class="bi bi-info-circle-fill me-2" style="font-size: 1.2rem;"></i> Cette demande est encore modifiable.
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-danger fw-bold py-2 px-4 rounded-pill" onclick="deleteDemande(<?= htmlspecialchars($demande_id) ?>)">
                            <i class="bi bi-trash-fill me-1"></i> Supprimer
                        </button>
                        <a href="<?= BASE_URL ?>views/employe/edit_demande.php?id=<?= htmlspecialchars($demande_id) ?>" class="btn btn-primary-themed fw-bold py-2 px-4 rounded-pill">
                            <i class="bi bi-pencil-square me-1"></i> Modifier
                        </a>
                    </div>
                <?php else: ?>
                    <div style="flex: 1;">
                        <i class="bi bi-lock-fill me-2" style="font-size: 1.2rem;"></i>
                        La demande est **<?= htmlspecialchars($status) ?>**. Elle ne peut plus être modifiée ou supprimée.
                    </div>
                <?php endif; ?>
            </div>

        </div>

    <?php endif; ?>
</div>
</div>

<div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview(event)">
    <span class="image-preview-close" id="closeLightbox">
        <i class="bi bi-x-lg"></i>
    </span>
    <img id="previewImage" src="" alt="Prévisualisation du justificatif">
</div>

<script>
function showImagePreview(path) {
    const modal = document.getElementById('imagePreviewModal');
    const img = document.getElementById('previewImage');
    
    if (img) img.src = path;
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden'; 
}

function closeImagePreview(event) {
    const modal = document.getElementById('imagePreviewModal');
    
    if (event.target === modal || event.target.closest('.image-preview-close')) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto'; 
    }
}

function deleteDemande(id) {
    if (confirm('⚠️ Êtes-vous sûr de vouloir supprimer cette demande ?\n\nCette action est irréversible.')) {
        fetch('<?= BASE_URL ?>api/employe.php?action=deleteDemande&demande_id=' + id, { 
            method: 'POST',
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Erreur réseau ou du serveur (Statut: ' + res.status + ')');
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                sessionStorage.setItem('feedback_message', '✅ Demande supprimée avec succès !');
                sessionStorage.setItem('feedback_type', 'success');
                window.location.href = '<?= BASE_URL ?>views/employe/employe_demandes.php';
            } else {
                alert('❌ Erreur lors de la suppression : ' + (data.error || 'Erreur inconnue'));
            }
        })
        .catch(err => {
            alert('❌ Erreur de traitement de la requête: ' + err.message);
            console.error(err);
        });
    }
}

// Fermer le modal avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('imagePreviewModal').classList.contains('show')) {
        closeImagePreview({target: document.getElementById('imagePreviewModal')}); 
    }
});
</script>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>