<?php
// DANS gestion_categories.php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php'; 
require_once BASE_PATH . 'includes/header.php';


$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/gestion_categories.css">
<script src="<?= BASE_URL ?>assets/js/gestion_categories.js"></script>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title"><i class="bi bi-tags me-2"></i>Gestion des Catégories de Frais</h1>
        <span class="text-muted small">Classification des types de dépenses</span>
    </div>
    <div>
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#nouvelleCategorieModal">
            <i class="bi bi-plus-circle me-1"></i> Nouvelle catégorie
        </button>
        </div>
</div>
<div class="search-filter-section mb-4 p-3" style="background-color: var(--card-bg); border-radius: 8px;"> 
    <div class="search-box-main mb-3">
        <div class="input-group target-search-bg">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Rechercher une catégorie par nom, description ou ID...">
        </div>
    </div>

    <div class="table-container p-0 border-0 shadow-none">
        <div class="d-flex justify-content-between align-items-center mb-3 px-3">
            <h5 class="fw-bold m-0" style="color: var(--text-primary);">Liste des catégories <span class="badge bg-secondary" id="total-categories">0</span></h5>
        </div>
        
        <div class="table-scroll-wrapper" style="max-height: 400px; overflow-y: auto;">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Nom de la catégorie</th>
                            <th>Description</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categories-tbody">
                        <tr><td colspan="3" class="text-center text-muted py-5">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="nouvelleCategorieModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle Catégorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="nouvelleCategorieForm">
                    <div class="mb-3">
                        <label class="form-label">Nom de la catégorie *</label>
                        <input type="text" class="form-control" id="nom" required placeholder="Ex: Transport, Hébergement, Repas">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" placeholder="Description détaillée de la catégorie..."></textarea>
                        <small class="text-muted">Facultatif - Aide à clarifier l'usage de cette catégorie</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Annuler</button>
                <button type="button" class="btn btn-primary-custom rounded-pill" onclick="createCategorie()"><i class="bi bi-check-circle me-1"></i> Créer</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modifierCategorieModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Modifier Catégorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="modifierCategorieForm">
                    <input type="hidden" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label">Nom de la catégorie *</label>
                        <input type="text" class="form-control" id="edit-nom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Annuler</button>
                <button type="button" class="btn btn-success rounded-pill" onclick="updateCategorie()"><i class="bi bi-save me-1"></i> Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>