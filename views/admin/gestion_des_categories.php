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
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        #main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            background-color: #f5f7fa;
        }

        .sidebar.close ~ #main-content {
            margin-left: 88px;
        }

        /* Stats card */
        .stat-card {
            border-radius: 10px;
            border: 2px solid #007bff;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            background-color: #f8f9ff;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            background-color: #007bff;
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .table thead {
            background-color: #4a5f7f;
            color: white;
        }

        .table thead th {
            border: none;
            padding: 15px;
        }

        .search-box-main {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .search-box-main input {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
        }

        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
        }

        .category-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        @media (max-width: 768px) {
            #main-content {
                margin-left: 0;
            }
            
            .sidebar.close ~ #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?php include('../../includes/sidebarA.php'); ?>

    <!-- CONTENU PRINCIPAL -->
    <div id="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-tags"></i> Gestion des Catégories de Frais</h2>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i> Actions
                </button>
                <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#nouvelleCategorieModal">
                        <i class="bi bi-plus-circle"></i> Nouvelle catégorie
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportCategories()">
                        <i class="bi bi-download"></i> Exporter
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i> Actualiser
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Statistique -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="bi bi-tags"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-total">0</div>
                            <div class="text-muted">Catégories de frais</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de recherche -->
        <div class="search-box-main">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Rechercher une catégorie...">
            </div>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <h5 class="mb-3">
                Toutes les catégories <span class="badge bg-secondary" id="total-categories">0</span>
            </h5>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categories-tbody">
                        <tr><td colspan="4" class="text-center text-muted">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle Catégorie -->
    <div class="modal fade" id="nouvelleCategorieModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nouvelleCategorieForm">
                        <div class="mb-3">
                            <label class="form-label">Nom de la catégorie *</label>
                            <input type="text" class="form-control" id="nom" required placeholder="Ex: Transport">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3" placeholder="Description optionnelle..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="createCategorie()">
                        <i class="bi bi-check-circle"></i> Créer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modifier Catégorie -->
    <div class="modal fade" id="modifierCategorieModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier Catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="updateCategorie()">
                        <i class="bi bi-save"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
    <script>
        const API_URL = 'http://localhost/smart-expense-management/apigestiondescategories.php';
        let allCategories = [];

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Gestion Catégories démarrée');
            loadCategories();

            // Recherche en temps réel
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
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Aucune catégorie trouvée</td></tr>';
                return;
            }

            tbody.innerHTML = categories.map(c => `
                <tr>
                    <td>${c.id || 'N/A'}</td>
                    <td><span class="category-badge">${c.nom || 'N/A'}</span></td>
                    <td>${c.description || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-action" onclick='editCategorie(${c.id})' title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick='deleteCategorie(${c.id})' title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
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
                const response = await fetch(`${API_URL}?action=create`, {
                    method: 'POST',
                    body: formData
                });
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
                const response = await fetch(`${API_URL}?action=update`, {
                    method: 'POST',
                    body: formData
                });
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

            fetch(`${API_URL}?action=delete`, {
                method: 'POST',
                body: formData
            })
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
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
</body>
</html>