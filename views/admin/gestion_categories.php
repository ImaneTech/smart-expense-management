<?php
session_start();
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - GoTrackr</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">
    <style>
        /* ==================== VARIABLES CSS ==================== */
:root {
    /* Mode Clair */
    --body-color: #e4e9f7;
    --sidebar-color: #fff;
    --primary-color: #4a5f7f;
    --primary-color-light: #f6f5ff;
    --toggle-color: #ddd;
    --text-color: #707070;
    --card-bg: #ffffff;
    --success-color: #43a047;
    --warning-color: #ffa726;
    --danger-color: #e53935;
    --text-primary: #32325d;
    --text-muted: #6c757d;
    --border-color: #e9ecef;
    --hover-bg: #f8f9fa;
    --gradient-start: #E3F2FD;
    --gradient-end: #BBDEFB;
    --category-gradient-start: #667eea;
    --category-gradient-end: #764ba2;
    
    /* Transitions */
    --tran-02: all 0.2s ease;
    --tran-03: all 0.3s ease;
    --tran-04: all 0.4s ease;
    --tran-05: all 0.5s ease;
}

/* Mode Sombre */
body.dark {
    --body-color: #18191a;
    --sidebar-color: #242526;
    --primary-color: #6c8cb4;
    --primary-color-light: #3a3b3c;
    --toggle-color: #fff;
    --text-color: #ccc;
    --card-bg: #242526;
    --text-primary: #e4e6eb;
    --text-muted: #b0b3b8;
    --border-color: #3a3b3c;
    --hover-bg: #3a3b3c;
    --gradient-start: #1e3a5f;
    --gradient-end: #2c5282;
    --category-gradient-start: #4a5568;
    --category-gradient-end: #2d3748;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--body-color);
    transition: var(--tran-03);
}

#main-content {
    margin-left: 250px;
    padding: 30px;
    min-height: 100vh;
    transition: margin-left 0.3s ease;
    background-color: var(--body-color);
}

.sidebar.close ~ #main-content {
    margin-left: 88px;
}

/* ==================== STATS CARD ==================== */
.stat-card {
    border-radius: 20px;
    border: 1px solid var(--border-color);
    padding: 25px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    height: 100%;
    background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
}

body.dark .stat-card {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

body.dark .stat-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}

.stat-icon {
    width: 60px;
    height: 60px;
    opacity: 0.3;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 10px 0 5px 0;
    color: var(--text-primary);
}

.stat-label {
    color: var(--text-muted);
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ==================== SEARCH SECTION ==================== */
.search-section {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border-color);
}

body.dark .search-section {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.search-section .input-group-text {
    background-color: transparent;
    border-right: none;
    border: 2px solid var(--border-color);
    border-radius: 8px 0 0 8px;
    color: var(--text-muted);
}

.search-section .form-control {
    border-left: none;
    border-radius: 0 8px 8px 0;
    padding: 10px 15px;
    border: 2px solid var(--border-color);
    background-color: var(--card-bg);
    color: var(--text-primary);
}

.search-section .form-control:focus {
    box-shadow: none;
    border-color: var(--primary-color);
    background-color: var(--card-bg);
}

.search-section .input-group-text:focus-within {
    border-color: var(--primary-color);
}

body.dark .search-section .form-control {
    background-color: var(--primary-color-light);
}

body.dark .search-section .form-control:focus {
    background-color: var(--primary-color-light);
}

/* ==================== TABLE ==================== */
.table-container {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border-color);
}

body.dark .table-container {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead {
    background-color: var(--hover-bg);
}

.modern-table thead th {
    border: none;
    padding: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.modern-table tbody td {
    padding: 15px;
    font-size: 0.9rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-color);
}

.modern-table tbody tr {
    transition: background-color 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: var(--hover-bg);
}

/* ==================== CATEGORY BADGE ==================== */
.category-badge {
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 500;
    display: inline-block;
    background: linear-gradient(135deg, var(--category-gradient-start) 0%, var(--category-gradient-end) 100%);
    color: white;
    font-size: 0.9rem;
    transition: var(--tran-03);
}

.category-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

body.dark .category-badge {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

body.dark .category-badge:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

/* ==================== ACTION BUTTONS ==================== */
.btn-action {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    margin: 0 2px;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-action.btn-edit {
    background-color: #FFF8E1;
    color: #f57c00;
}

body.dark .btn-action.btn-edit {
    background-color: #3a2f1a;
    color: #ffa726;
}

.btn-action.btn-delete {
    background-color: #FFEBEE;
    color: #e53935;
}

body.dark .btn-action.btn-delete {
    background-color: #3a1a1b;
    color: #ef5350;
}

/* ==================== HEADER ==================== */
.page-header {
    margin-bottom: 40px;
}

.page-title {
    color: var(--text-primary);
    font-weight: bold;
    font-size: 1.8rem;
    margin: 0;
}

.btn-primary-custom {
    background-color: var(--primary-color);
    border: none;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 500;
    transition: all 0.3s ease;
    color: white;
}

.btn-primary-custom:hover {
    background-color: #3d4f68;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 95, 127, 0.3);
}

/* ==================== MODAL ==================== */
.modal-content {
    border-radius: 20px;
    border: 1px solid var(--border-color);
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    background-color: var(--card-bg);
}

body.dark .modal-content {
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
}

.modal-header {
    border-radius: 20px 20px 0 0;
    padding: 20px 30px;
    border-bottom: 1px solid var(--border-color);
}

.modal-body {
    padding: 30px;
    background-color: var(--card-bg);
}

.modal-footer {
    border-top: 1px solid var(--border-color);
    background-color: var(--card-bg);
}

.form-label {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 2px solid var(--border-color);
    padding: 10px 15px;
    transition: all 0.3s ease;
    background-color: var(--card-bg);
    color: var(--text-primary);
}

body.dark .form-control,
body.dark .form-select {
    background-color: var(--primary-color-light);
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(74, 95, 127, 0.15);
    background-color: var(--card-bg);
}

body.dark .form-control:focus,
body.dark .form-select:focus {
    background-color: var(--primary-color-light);
}

textarea.form-control {
    resize: vertical;
}

.btn-close-white {
    filter: brightness(0) invert(1);
}

body.dark .btn-close {
    filter: brightness(0) invert(1);
}

/* ==================== DROPDOWN ==================== */
.dropdown-menu {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 12px;
}

body.dark .dropdown-menu {
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

.dropdown-item {
    color: var(--text-primary);
    transition: var(--tran-02);
    padding: 10px 20px;
}

.dropdown-item:hover {
    background-color: var(--hover-bg);
    color: var(--primary-color);
}

.dropdown-divider {
    border-top: 1px solid var(--border-color);
}

/* ==================== BADGES ==================== */
.badge {
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.8rem;
}

/* ==================== TEXT UTILITIES ==================== */
.text-muted {
    color: var(--text-muted) !important;
}

.text-primary {
    color: var(--text-primary) !important;
}

small.text-muted,
small.text-primary,
small.text-success {
    font-size: 0.85rem;
}

/* ==================== ICONS ==================== */
.bi-tags {
    color: var(--primary-color);
    opacity: 0.3;
}

body.dark .bi-tags {
    color: var(--primary-color);
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
    #main-content {
        margin-left: 0;
        padding: 15px;
    }
    
    .sidebar.close ~ #main-content {
        margin-left: 0;
    }

    .stat-card {
        margin-bottom: 15px;
    }

    .page-title {
        font-size: 1.5rem;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
    }
}

/* ==================== ANIMATIONS ==================== */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card,
.search-section,
.table-container {
    animation: fadeIn 0.5s ease;
}

/* Notification de changement de mode */
@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.mode-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.mode-notification .mode-icon {
    font-size: 1.5rem;
}

.mode-notification .mode-message {
    font-size: 0.95rem;
}

/* ==================== SCROLLBAR ==================== */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: var(--body-color);
}

::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #3d4f68;
}

body.dark ::-webkit-scrollbar-thumb {
    background: var(--primary-color-light);
}

body.dark ::-webkit-scrollbar-thumb:hover {
    background: var(--toggle-color);
}

/* ==================== ALERT PERSONNALISÉE ==================== */
.alert {
    border-radius: 12px;
    border: none;
    font-weight: 500;
}

.alert-dismissible .btn-close {
    padding: 0.75rem 1rem;
}

/* ==================== LOADING STATE ==================== */
.table-container .text-center.text-muted {
    color: var(--text-muted);
    padding: 40px;
    font-size: 1rem;
}
    </style>
</head>
<body>
    <?php include('../../includes/sidebar.php'); ?>
    
    <div id="main-content">
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title"><i class="bi bi-tags me-2"></i>Gestion des Catégories de Frais</h1>
                <span class="text-muted small">Classification des types de dépenses</span>
            </div>
            <div class="dropdown">
                <button class="btn btn-primary-custom dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical me-1"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#nouvelleCategorieModal"><i class="bi bi-plus-circle me-2"></i> Nouvelle catégorie</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportCategories()"><i class="bi bi-download me-2"></i> Exporter</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="refreshData()"><i class="bi bi-arrow-clockwise me-2"></i> Actualiser</a></li>
                </ul>
            </div>
        </div>

        <!-- Statistique -->
        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-total">0</div>
                            <div class="stat-label">Catégories</div>
                            <small class="text-primary fw-bold mt-2 d-block">Types de frais</small>
                        </div>
                        <div><i class="bi bi-tags" style="font-size: 3rem; color: #1976d2; opacity: 0.3;"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="search-section">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Rechercher une catégorie par nom, description ou ID...">
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0" style="color: var(--text-primary);">Liste des catégories <span class="badge bg-secondary" id="total-categories">0</span></h5>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th><th>Nom de la catégorie</th><th>Description</th><th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categories-tbody">
                        <tr><td colspan="4" class="text-center text-muted py-5">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle Catégorie -->
    <div class="modal fade" id="nouvelleCategorieModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
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

    <!-- Modal Modifier Catégorie -->
    <div class="modal fade" id="modifierCategorieModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
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
                    <button type="button" class="btn btn-warning rounded-pill" onclick="updateCategorie()"><i class="bi bi-save me-1"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
    <script>
        const API_URL = 'http://localhost/smart-expense-management/apigestiondescategories.php';
        let allCategories = [];

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Gestion Catégories démarrée');
            loadCategories();
            document.getElementById('searchInput').addEventListener('input', function() {
                filterCategoriesBySearch(this.value);
            });
        });

        function loadCategories() {
            fetch(`${API_URL}?action=get_categories`)
                .then(response => response.json())
                .then(data => {
                    allCategories = data;
                    displayCategories(data);
                    document.getElementById('stat-total').textContent = data.length;
                })
                .catch(error => {
                    console.error('❌ Erreur categories:', error);
                    showAlert('Erreur lors du chargement des catégories', 'danger');
                });
        }

        function displayCategories(categories) {
            const tbody = document.getElementById('categories-tbody');
            document.getElementById('total-categories').textContent = categories.length;
            if (!Array.isArray(categories) || categories.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-5">Aucune catégorie trouvée</td></tr>';
                return;
            }
            tbody.innerHTML = categories.map(c => `
                <tr>
                    <td class="ps-4"><strong>${c.id || 'N/A'}</strong></td>
                    <td><span class="category-badge">${c.nom || 'N/A'}</span></td>
                    <td>${c.description || '-'}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-action btn-edit" onclick='editCategorie(${c.id})' title="Modifier"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-action btn-delete" onclick='deleteCategorie(${c.id})' title="Supprimer"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function filterCategoriesBySearch(searchTerm) {
            if (!searchTerm) {
                displayCategories(allCategories);
                return;
            }
            const filtered = allCategories.filter(c => {
                const nom = (c.nom || '').toLowerCase();
                const description = (c.description || '').toLowerCase();
                const id = String(c.id || '');
                const term = searchTerm.toLowerCase();
                return nom.includes(term) || description.includes(term) || id.includes(term);
            });
            displayCategories(filtered);
        }

        async function createCategorie() {
            const nom = document.getElementById('nom').value.trim();
            const description = document.getElementById('description').value.trim();
            if (!nom) {
                showAlert('Veuillez entrer un nom pour la catégorie', 'warning');
                return;
            }
            const formData = new FormData();
            formData.append('nom', nom);
            formData.append('description', description);
            try {
                const response = await fetch(`${API_URL}?action=create`, { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('nouvelleCategorieModal')).hide();
                    document.getElementById('nouvelleCategorieForm').reset();
                    loadCategories();
                    showAlert('Catégorie créée avec succès', 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de la création', 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la création', 'danger');
            }
        }

        function editCategorie(id) {
            const categorie = allCategories.find(c => c.id == id);
            if (!categorie) return;
            document.getElementById('edit-id').value = categorie.id;
            document.getElementById('edit-nom').value = categorie.nom;
            document.getElementById('edit-description').value = categorie.description || '';
            new bootstrap.Modal(document.getElementById('modifierCategorieModal')).show();
        }

        async function updateCategorie() {
            const id = document.getElementById('edit-id').value;
            const nom = document.getElementById('edit-nom').value.trim();
            const description = document.getElementById('edit-description').value.trim();
            if (!nom) {
                showAlert('Veuillez entrer un nom pour la catégorie', 'warning');
                return;
            }
            const formData = new FormData();
            formData.append('id', id);
            formData.append('nom', nom);
            formData.append('description', description);
            try {
                const response = await fetch(`${API_URL}?action=update`, { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modifierCategorieModal')).hide();
                    loadCategories();
                    showAlert('Catégorie modifiée avec succès', 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de la modification', 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la modification', 'danger');
            }
        }

        function deleteCategorie(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) return;
            const formData = new FormData();
            formData.append('id', id);
            fetch(`${API_URL}?action=delete`, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCategories();
                    showAlert('Catégorie supprimée', 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de la suppression', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la suppression', 'danger');
            });
        }

        function refreshData() {
            loadCategories();
            showAlert('Données actualisées', 'info');
        }

        function exportCategories() {
            window.location.href = `${API_URL}?action=export`;
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
</body>
</html>