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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid p-4">

    <!-- Flash Messages -->
    <?php require_once BASE_PATH . 'includes/flash.php'; ?>
    <?php displayFlash(); ?>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold m-0" style="color: #32325d;"><i class="bi bi-people me-2"></i>Gestion des Utilisateurs</h1>
            <span class="text-muted small">Administration des comptes utilisateurs</span>
        </div>
        <div>
            <button class="btn btn-primary-custom" onclick="exportUsers()">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exporter en Excel
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card primary">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <div class="stat-number mb-1" id="stat-total">0</div>
                        <div class="stat-label small text-muted">Total Utilisateurs</div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary rounded-circle p-3">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card success">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <div class="stat-number mb-1" id="stat-employes">0</div>
                        <div class="stat-label small text-muted">Employés Actifs</div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success rounded-circle p-3">
                        <i class="bi bi-person-badge-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card warning">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <div class="stat-number mb-1" id="stat-managers">0</div>
                        <div class="stat-label small text-muted">Managers</div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning rounded-circle p-3">
                        <i class="bi bi-briefcase-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card danger">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div>
                        <div class="stat-number mb-1" id="stat-admins">0</div>
                        <div class="stat-label small text-muted">Administrateurs</div>
                    </div>
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger rounded-circle p-3">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="search-filter-section d-flex align-items-center mb-4 p-4" style="background-color: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        
        <div class="d-flex align-items-center me-4">
            <span class="me-3 fw-semibold text-muted small text-uppercase">Filtrer par:</span>
            <button class="btn filter-btn active btn-sm" onclick="filterUsers('all', event)"><i class="bi bi-list me-1"></i> Tous</button>
            <button class="btn filter-btn btn-sm" onclick="filterUsers('employe', event)"><i class="bi bi-person me-1"></i> Employés</button>
            <button class="btn filter-btn btn-sm" onclick="filterUsers('manager', event)"><i class="bi bi-person-badge me-1"></i> Managers</button>
            <button class="btn filter-btn btn-sm" onclick="filterUsers('admin', event)"><i class="bi bi-shield-check me-1"></i> Admins</button>
        </div>

        <div class="search-box-main flex-grow-1">
            <div class="input-group w-100 target-search-bg">
                <span class="input-group-text border-0 bg-transparent"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-transparent" id="searchInput" placeholder="Rechercher par nom, email ou ID...">
            </div>
        </div>
    </div>

    <div class="table-container p-0 border-0 shadow-none" style="background-color: var(--card-bg); border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <div class="table-scroll-wrapper px-4 py-4">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="modern-table">
                    <thead style="position: sticky; top: 0; background: white; z-index: 5;">
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

<!-- Modal Modifier (conservé au cas où, mais bouton caché) -->
<div class="modal fade" id="modifierUtilisateurModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Modifier Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4"> <form id="modifierUtilisateurForm">
                    <input type="hidden" id="edit-id"> 
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-last_name" required>
                        </div>
                    </div>

                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit-email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="edit-phone" required>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Département <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-department" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit-role" required>
                                <option value="employe">Employé</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary-custom rounded-pill px-4" onclick="updateUser()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>

<script src="<?= BASE_URL ?>assets/js/gestion_users.js"></script>