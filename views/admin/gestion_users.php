<?php
// DANS gestion_users.php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'includes/header.php';

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/gestion_users.css">

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title"><i class="bi bi-people me-2"></i>Gestion des Utilisateurs</h1>
        <span class="text-muted small">Administration des comptes utilisateurs</span>
    </div>
    <div>
        <button class="btn btn-success" onclick="exportUsers()">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exporter en Excel
        </button>
        </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card primary">
            <div class="d-flex align-items-start justify-content-between" style="padding: 1rem;">
                <div>
                    <div class="stat-number" id="stat-total">0</div>
                    <div class="stat-label small">Total Utilisateurs</div>
                </div>
                <div><i class="bi bi-people" style="font-size: 2.5rem; color: #1976d2; opacity: 0.3;"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card success">
            <div class="d-flex align-items-start justify-content-between" style="padding: 1rem;">
                <div>
                    <div class="stat-number" id="stat-employes">0</div>
                    <div class="stat-label small">Employés Actifs</div>
                </div>
                <div><i class="bi bi-person-check" style="font-size: 2.5rem; color: #43a047; opacity: 0.3;"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card warning">
            <div class="d-flex align-items-start justify-content-between" style="padding: 1rem;">
                <div>
                    <div class="stat-number" id="stat-managers">0</div>
                    <div class="stat-label small">Managers</div>
                </div>
                <div><i class="bi bi-person-badge" style="font-size: 2.5rem; color: #ffa726; opacity: 0.3;"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card danger">
            <div class="d-flex align-items-start justify-content-between" style="padding: 1rem;">
                <div>
                    <div class="stat-number" id="stat-admins">0</div>
                    <div class="stat-label small">Administrateurs</div>
                </div>
                <div><i class="bi bi-shield-check" style="font-size: 2.5rem; color: #e53935; opacity: 0.3;"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="search-filter-section d-flex align-items-center mb-4 p-2" style="background-color: var(--card-bg); border-radius: 8px;">
    
    <div class="search-box-main d-flex flex-grow-1 me-3">
        <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Rechercher par nom, email ou ID...">
        </div>
    </div>
    
    <div class="d-flex align-items-center">
        <span class="me-3 fw-semibold text-muted small text-uppercase">Filtrer par:</span>
        <button class="btn filter-btn active btn-sm" onclick="filterUsers('all', event)"><i class="bi bi-list me-1"></i> Tous</button>
        <button class="btn filter-btn btn-sm" onclick="filterUsers('employe', event)"><i class="bi bi-person me-1"></i> Employés</button>
        <button class="btn filter-btn btn-sm" onclick="filterUsers('manager', event)"><i class="bi bi-person-badge me-1"></i> Managers</button>
        <button class="btn filter-btn btn-sm" onclick="filterUsers('admin', event)"><i class="bi bi-shield-check me-1"></i> Admins</button>
    </div>
</div>
<div class="table-container">
   
    
    <div class="table-scroll-wrapper">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="ps-4">Prénom</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Département</th>
                        <th>Rôle</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">Chargement des données...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="modifierUtilisateurModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Modifier Utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4"> <form id="modifierUtilisateurForm">
                    <input type="hidden" id="edit-id"> 
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="edit-first_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="edit-last_name" required>
                        </div>
                    </div>

                    <div class="row">
                         <div class="col-md-12 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit-email" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control" id="edit-phone" required>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Département *</label>
                            <input type="text" class="form-control" id="edit-department" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Rôle *</label>
                            <select class="form-select" id="edit-role" required>
                                <option value="employe">Employé</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Annuler</button>
                <button type="button" class="btn btn-success rounded-pill" onclick="updateUser()"><i class="bi bi-save me-1"></i> Enregistrer</button>
            </div>
        </div>
    </div>
</div>
<?php require_once BASE_PATH . 'includes/footer.php'; ?>

<script src="<?= BASE_URL ?>assets/js/gestion_users.js"></script>