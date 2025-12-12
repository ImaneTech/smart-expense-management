<?php
session_start();
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}

require_once __DIR__ . '/../../includes/header.php'; 
?>

<!-- Link to Unified Admin Theme -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/gestion_users.css">
<script>
    window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>assets/js/dashboard_admin.js" defer></script> 

<div class="container-fluid p-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold m-0" style="color: #32325d;">Toutes les Demandes</h1>
            <span class="text-muted small">Gestion complète et historique des frais</span>
        </div>
        <div>
            <button class="btn btn-primary-custom" onclick="exportData()">
                <i class="bi bi-file-earmark-excel me-2"></i> Exporter
            </button>
        </div>
    </div>

    <!-- Search & Filters Section -->
    <div class="search-filter-section d-flex align-items-center mb-4 p-4" style="background-color: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        
        <div class="d-flex align-items-center me-4">
            <span class="me-3 fw-semibold text-muted small text-uppercase">Filtrer par:</span>
            <button class="filter-btn active btn-sm" onclick="filterDemandes('all', event)">
                <i class="bi bi-collection me-1"></i> Tout
            </button>
            <button class="filter-btn btn-sm" onclick="filterDemandes('en_attente', event)">
                <i class="bi bi-hourglass-split me-1"></i> En attente
            </button>
            <button class="filter-btn btn-sm" onclick="filterDemandes('validee_manager', event)">
                <i class="bi bi-check-circle me-1"></i> Validées
            </button>
            <button class="filter-btn btn-sm" onclick="filterDemandes('rejetee', event)">
                <i class="bi bi-x-circle me-1"></i> Rejetées
            </button>
        </div>

        <div class="search-box-main flex-grow-1">
            <div class="input-group w-100 target-search-bg">
                <span class="input-group-text border-0 bg-transparent"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-transparent" id="searchInput" placeholder="Rechercher par utilisateur, objet...">
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container p-0 border-0 shadow-none" style="background-color: var(--card-bg); border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <div class="loading p-5 text-center" style="display:none;"> 
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <div class="table-scroll-wrapper px-4 py-4">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Utilisateur</th> 
                            <th>Objet Mission</th>
                            <th>Départ</th>
                            <th>Retour</th>
                            <th>Statut</th>
                            <th>Montant</th>
                            <th class="text-end pe-4">Actions</th> 
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody"> 
                        <tr><td colspan="7" class="text-center text-muted py-5">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 

<!-- Modal Edit (Updated Design) -->
<div class="modal fade" id="modifierDemandeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i> Modifier la Demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="modifierDemandeForm">
                    <input type="hidden" id="edit_demande_id">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Détails de la Mission</h6>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Objet de la mission</label>
                            <input type="text" class="form-control" id="edit_objet_mission" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lieu</label>
                            <input type="text" class="form-control" id="edit_lieu_deplacement" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Départ</label>
                            <input type="date" class="form-control" id="edit_date_depart" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Retour</label>
                            <input type="date" class="form-control" id="edit_date_retour" required>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Statut & Validation</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Statut Manager</label>
                            <select class="form-select" id="edit_statut" required></select>
                        </div>
                        
                        <div class="col-md-6">
                             <label class="form-label fw-semibold">Statut Final (Admin)</label>
                             <select class="form-select" id="edit_statut_final">
                                 <option value="En attente">En attente</option>
                                 <option value="Validée">Validée</option>
                                 <option value="Rejetée">Rejetée</option>
                             </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Montant Total (<?= $currencySymbol ?>)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_montant_total">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Commentaire</label>
                            <textarea class="form-control" id="edit_commentaire_manager" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary-custom rounded-pill px-4" onclick="updateDemande()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../../includes/footer.php'; 
?>