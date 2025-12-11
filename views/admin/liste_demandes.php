<?php
session_start();
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';

// Assurez-vous que BASE_URL est défini
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}


require_once __DIR__ . '/../../includes/header.php'; 
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_admin2.css">
<script src="<?= BASE_URL ?>assets/js/dashboard_admin.js" defer></script> 

    
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-list-columns-reverse me-2"></i>Liste Complète des Demandes</h1>
            <span class="text-muted small">Toutes les demandes de frais en détail, avec actions de gestion.</span>
        </div>
        <div>
            <button class="btn" onclick="exportData()" title="Exporter les données" style="background-color: #87D37C; color: white; border-color: #7BC571;">
                <i class="bi bi-download me-1"></i> Exporter
            </button>
        </div>
    </div>

    <div class="filter-section mt-4">
        <div class="d-flex flex-wrap align-items-center">
            <span class="me-3 fw-semibold text-muted small text-uppercase">Filtrer par:</span>
            <button class="btn filter-btn active" onclick="filterDemandes('all', event)">
                <i class="bi bi-list me-1"></i> Toutes
            </button>
            <button class="btn filter-btn" onclick="filterDemandes('en_attente', event)">
                <i class="bi bi-clock me-1"></i> En attente
            </button>
            <button class="btn filter-btn" onclick="filterDemandes('validee_manager', event)">
                <i class="bi bi-check-circle me-1"></i> Validées Manager
            </button>
            <button class="btn filter-btn" onclick="filterDemandes('rejetee', event)">
                <i class="bi bi-x-circle me-1"></i> Rejetées Manager
            </button>
        </div>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-2">
             <div>
            </div>
        </div>

        <div class="loading"> 
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3 text-muted">Chargement des données...</p>
        </div>

        <div class="table-scroll-wrapper">
            
            <div class="table-header-fixed table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Utilisateur</th> 
                            <th>Objet Mission</th>
                            <th>Date Départ</th>
                            <th>Date Retour</th>
                            <th>Statut</th>
                            <th>Montant Total</th>
                            <th class="text-end pe-4">Détails</th> 
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="table-body-scroll table-responsive">
                <table class="modern-table">
                    <tbody id="demandes-tbody"> 
                        <tr><td colspan="7" class="text-center text-muted py-5">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    
</div> 

<div class="modal fade" id="nouvelleDemandeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Nouvelle Demande de Frais</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="nouvelleDemandeForm">
                    <div class="card mb-3">
                        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-person me-2"></i> Informations Utilisateur</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">User ID *</label>
                                    <input type="number" class="form-control" id="user_id" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Manager ID</label>
                                    <input type="number" class="form-control" id="manager_id">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-briefcase me-2"></i> Détails de la Mission</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Objet de la mission *</label>
                                <textarea class="form-control" id="objet_mission" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lieu de déplacement *</label>
                                <input type="text" class="form-control" id="lieu_deplacement" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date de départ *</label>
                                    <input type="date" class="form-control" id="date_depart" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date de retour *</label>
                                    <input type="date" class="form-control" id="date_retour" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-check-circle me-2"></i> Statut et Validation</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Statut *</label>
                                    <select class="form-select" id="statut" required></select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Manager ID Validation</label>
                                    <input type="number" class="form-control" id="manager_id_validation">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date de traitement</label>
                                    <input type="datetime-local" class="form-control" id="date_traitement">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant Total (€)</label>
                                    <input type="number" step="0.01" class="form-control" id="montant_total" value="0.00">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Commentaire Manager</label>
                                <textarea class="form-control" id="commentaire_manager" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Annuler
                </button>
                <button type="button" class="btn btn-primary-custom rounded-pill" onclick="createDemande()">
                    <i class="bi bi-check-circle me-1"></i> Créer la demande
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modifierDemandeModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header py-2" style="background-color: var(--primary-color); color: white; border-bottom: 2px solid var(--primary-color);">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-pencil-square me-2"></i> Modifier Demande de Frais</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <form id="modifierDemandeForm">
                    <input type="hidden" id="edit_demande_id">
                    
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-header border-0 pb-0 pt-2 bg-transparent">
                            <h6 class="mb-0 text-uppercase small fw-bold" style="color: var(--secondary-color); font-size: 0.75rem !important;">
                                <i class="bi bi-briefcase me-2"></i> Détails de la Mission
                            </h6>
                        </div>
                        <div class="card-body pt-1 pb-2">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Objet de la mission *</label>
                                <textarea class="form-control compact-input" id="edit_objet_mission" rows="2" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-semibold">Lieu de déplacement *</label>
                                    <input type="text" class="form-control compact-input" id="edit_lieu_deplacement" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-semibold">Date de départ *</label>
                                    <input type="date" class="form-control compact-input" id="edit_date_depart" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-semibold">Date de retour *</label>
                                    <input type="date" class="form-control compact-input" id="edit_date_retour" required>
                                </div>
                                </div>
                        </div>
                    </div>

                    <div class="card mb-0 shadow-sm border-0">
                        <div class="card-header border-0 pb-0 pt-2 bg-transparent">
                            <h6 class="mb-0 text-uppercase small fw-bold" style="color: var(--secondary-color); font-size: 0.75rem !important;">
                                <i class="bi bi-check-circle me-2"></i> Statut & Traitement Comptable
                            </h6>
                        </div>
                        <div class="card-body pt-1 pb-2">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-semibold">Statut *</label>
                                    <select class="form-select compact-input" id="edit_statut" required></select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-semibold">Validation ID</label>
                                    <input type="number" class="form-control compact-input" id="edit_manager_id_validation">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-semibold">Montant Total (€)</label>
                                    <input type="number" step="0.01" class="form-control compact-input" id="edit_montant_total">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-semibold">Date de traitement</label>
                                    <input type="datetime-local" class="form-control compact-input" id="edit_date_traitement">
                                </div>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label small fw-semibold">Commentaire Manager / Admin</label>
                                <textarea class="form-control compact-input" id="edit_commentaire_manager" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2 d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill me-2" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Annuler
                </button>
                <button type="button" class="btn btn-warning btn-sm rounded-pill" onclick="updateDemande()">
                    <i class="bi bi-save me-1"></i> Enregistrer les modifications
                </button>
            </div>
        </div>
    </div>
</div>
<?php 
require_once __DIR__ . '/../../includes/footer.php'; 
?>