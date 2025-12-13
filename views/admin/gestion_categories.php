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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>assets/js/gestion_categories.js"></script>

<div class="container-fluid p-4">

    <!-- Flash Messages -->
    <?php require_once BASE_PATH . 'includes/flash.php'; ?>
    <?php displayFlash(); ?>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold m-0" style="color: #32325d;"><i class="bi bi-tags me-2"></i>Gestion des Catégories de Frais</h1>
            <span class="text-muted small">Classification des types de dépenses</span>
        </div>
        <div>
            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#nouvelleCategorieModal">
                <i class="bi bi-plus-circle me-1"></i> Nouvelle catégorie
            </button>
        </div>
    </div>

    <!-- Search Section -->
    <div class="search-section mb-4 p-4" style="background-color: var(--card-bg); border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);"> 
        <div class="search-box-main">
            <div class="input-group target-search-bg">
                <span class="input-group-text border-0 bg-transparent"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-transparent" id="searchInput" placeholder="Rechercher une catégorie par nom, description ou ID...">
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-container p-0 border-0 shadow-none" style="background-color: var(--card-bg); border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <div class="d-flex justify-content-between align-items-center mb-3 px-4 pt-4">
            <h5 class="fw-bold m-0" style="color: var(--text-primary);">Liste des catégories</h5>
        </div>
        
        <div class="table-scroll-wrapper px-4 pb-4" style="max-height: 500px; overflow-y: auto;">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 30%;">Nom de la catégorie</th>
                            <th style="width: 50%;">Description</th>
                            <th class="text-end pe-4" style="width: 20%;">Actions</th>
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
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle Catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <form id="nouvelleCategorieForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Nom de la catégorie <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="nom" required placeholder="Ex: Transport, Hébergement...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" rows="3" placeholder="Description détaillée..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary-custom rounded-pill px-4" onclick="createCategorie()">Créer la catégorie</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modifierCategorieModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2" style="color: var(--primary-color);"></i>Modifier Catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <form id="modifierCategorieForm">
                    <input type="hidden" id="edit-id">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Nom de la catégorie <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="edit-nom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="edit-description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary-custom rounded-pill px-4" onclick="updateCategorie()">Enregistrer les modifications</button>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>