<?php
session_start();
// La variable $role est utilisée uniquement pour l'affichage conditionnel ou les permissions si nécessaire.
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';

// Assurez-vous que BASE_URL est défini
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}


require_once __DIR__ . '/../../includes/header.php'; 
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_admin.css">
<script src="<?= BASE_URL ?>assets/js/dashboard_admin.js" defer></script>

    
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-file-text me-2"></i>Gestion des Demandes</h1>
            <span class="text-muted small">Vue d'ensemble des demandes de frais</span>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary-custom dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical me-1"></i> Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="actionsDropdown">
                <li><a class="dropdown-item" href="#" onclick="exportData()">
                    <i class="bi bi-download me-2"></i> Exporter
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-2"></i> Actualiser
                </a></li>
            </ul>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stat-card success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" id="stat-validees">0</div> 
                        <div class="stat-label">Demandes validées</div>
                        <small class="text-success fw-bold mt-2 d-block">Approuvées Manager</small>
                    </div>
                    <div>
                        <img src="<?= BASE_URL ?>assets/img/approve_icon.png" alt="Validées" class="stat-icon">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" id="stat-attente">0</div>
                        <div class="stat-label">En attente</div>
                        <small class="text-warning fw-bold mt-2 d-block">À traiter</small>
                    </div>
                    <div>
                        <img src="<?= BASE_URL ?>assets/img/pending_icon.png" alt="Attente" class="stat-icon">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" id="stat-rejetees">0</div> 
                        <div class="stat-label">Rejetées</div>
                        <small class="text-danger fw-bold mt-2 d-block">Attention requise</small>
                    </div>
                    <div>
                        <img src="<?= BASE_URL ?>assets/img/decline.png" alt="Rejetées" class="stat-icon" style="transform: scale(1.3);">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="filter-section">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0" style="color: var(--text-primary);">
                Liste des demandes récentes <span class="badge bg-secondary" id="total-demandes">0</span>
            </h5>
            <div>
                <a href="<?= BASE_URL ?>views/admin/liste_demandes.php" class="btn btn-outline-primary">
                    <i class="bi bi-list-columns-reverse me-1"></i> Voir toutes les demandes
                </a>
            </div>
        </div>

        <div class="loading"> <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3 text-muted">Chargement des données...</p>
        </div>

        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="ps-4">Utilisateur</th>
                        <th>Objet Mission</th>
                        <th>Date Départ</th>
                        <th>Date Retour</th>
                        <th>Statut</th>
                        <th>Montant Total</th>
                        </tr>
                </thead>
                <tbody id="demandes-tbody"> 
                    <tr><td colspan="6" class="text-center text-muted py-5">Chargement des données...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    
</div> 
<?php 
require_once __DIR__ . '/../../includes/footer.php'; 
?>