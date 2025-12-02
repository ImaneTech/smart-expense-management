<?php
session_start();
// Définir le rôle (à adapter selon votre système d'authentification)
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';

// Définir BASE_URL si ce n'est pas déjà fait
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Demandes - GoTrackr</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Boxicons pour la sidebar -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">

    <style>
        /* Adaptation du contenu principal pour la sidebar */
        #main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.close ~ #main-content {
            margin-left: 88px;
        }

        /* Stats cards */
        .stat-card {
            border-radius: 10px;
            border: 2px solid;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-card.success {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .stat-card.warning {
            border-color: #ffc107;
            background-color: #fffef8;
        }

        .stat-card.danger {
            border-color: #dc3545;
            background-color: #fff8f8;
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
        }

        .stat-icon.success { background-color: #28a745; }
        .stat-icon.warning { background-color: #ffc107; }
        .stat-icon.danger { background-color: #dc3545; }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }

        .filter-btn {
            margin: 5px;
            border-radius: 5px;
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

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        /* Upload zone */
        .upload-zone {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-zone:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }

        .upload-zone.drag-over {
            border-color: #28a745;
            background-color: #e8f5e9;
        }

        .file-preview {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            display: none;
        }

        /* Responsive */
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

    <!-- SIDEBAR - Inclusion du sidebarA.php -->
    <?php include('../../includes/sidebarA.php'); ?>

    <!-- CONTENU PRINCIPAL -->
    <div id="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-text"></i> Gestion des Demandes</h2>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i> Actions
                </button>
                <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#nouvelleDemandeModal">
                        <i class="bi bi-plus-circle"></i> Nouvelle demande
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData()">
                        <i class="bi bi-download"></i> Exporter
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i> Actualiser
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card success">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon success">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-validees">0</div>
                            <div class="text-muted">Demandes validées (Manager)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon warning">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-attente">0</div>
                            <div class="text-muted">Demandes en attente</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card danger">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon danger">
                            <i class="bi bi-x-lg"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-rejetees">0</div>
                            <div class="text-muted">Demandes rejetées</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn btn-dark filter-btn active" onclick="filterDemandes('all', event)">
                <i class="bi bi-list"></i> Toutes
            </button>
            <button class="btn btn-outline-secondary filter-btn" onclick="filterDemandes('en_attente', event)">
                <i class="bi bi-clock"></i> En attente
            </button>
            <button class="btn btn-outline-success filter-btn" onclick="filterDemandes('validee_manager', event)">
                <i class="bi bi-check"></i> Validées Manager
            </button>
            <button class="btn btn-outline-primary filter-btn" onclick="filterDemandes('validee_admin', event)">
                <i class="bi bi-check-all"></i> Validées Admin
            </button>
            <button class="btn btn-outline-danger filter-btn" onclick="filterDemandes('rejetee', event)">
                <i class="bi bi-x"></i> Rejetées
            </button>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <h5 class="mb-3">
                Toutes les demandes <span class="badge bg-secondary" id="total-demandes">0</span>
            </h5>

            <div class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Objectif</th>
                            <th>Date</th>
                            <th>Montant Total</th>
                            <th>Justificatif</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody">
                        <tr><td colspan="8" class="text-center text-muted">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle Demande avec Upload -->
    <div class="modal fade" id="nouvelleDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nouvelleDemandeForm">
                        <div class="mb-3">
                            <label class="form-label">Utilisateur</label>
                            <input type="text" class="form-control" id="utilisateur" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Objectif</label>
                            <textarea class="form-control" id="objectif" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Montant Total (€)</label>
                            <input type="number" step="0.01" class="form-control" id="montant" required>
                        </div>
                        
                        <!-- Zone d'upload du justificatif -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-paperclip"></i> Justificatif (optionnel)
                            </label>
                            <div class="upload-zone" id="uploadZone">
                                <i class="bi bi-cloud-upload" style="font-size: 48px; color: #6c757d;"></i>
                                <p class="mb-0 mt-2">Glissez un fichier ici ou cliquez pour sélectionner</p>
                                <small class="text-muted">Formats acceptés: PDF, JPG, PNG (max 5MB)</small>
                                <input type="file" id="justificatif" accept=".pdf,.jpg,.jpeg,.png" style="display:none">
                            </div>
                            <div class="file-preview" id="filePreview">
                                <i class="bi bi-file-earmark-check text-success"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeFile()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="createDemande()">
                        <i class="bi bi-check-circle"></i> Créer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
    <script>
        const API_URL = 'http://localhost/smart-expense-management/api.php';
        let currentFilter = 'all';
        let selectedFile = null;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Application démarrée');
            loadStats();
            loadDemandes();
            setupFileUpload();
        });

        // Configuration de l'upload drag & drop
        function setupFileUpload() {
            const uploadZone = document.getElementById('uploadZone');
            const fileInput = document.getElementById('justificatif');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');

            uploadZone.addEventListener('click', () => fileInput.click());

            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('drag-over');
            });

            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('drag-over');
            });

            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFile(files[0]);
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFile(e.target.files[0]);
                }
            });
        }

        function handleFile(file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];

            if (!allowedTypes.includes(file.type)) {
                showAlert('Type de fichier non autorisé. Utilisez PDF, JPG ou PNG.', 'danger');
                return;
            }

            if (file.size > maxSize) {
                showAlert('Fichier trop volumineux (max 5MB)', 'danger');
                return;
            }

            selectedFile = file;
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('filePreview').style.display = 'block';
        }

        function removeFile() {
            selectedFile = null;
            document.getElementById('justificatif').value = '';
            document.getElementById('filePreview').style.display = 'none';
        }

        function loadStats() {
            fetch(`${API_URL}?action=get_stats`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('stat-validees').textContent = data.validees_manager || 0;
                    document.getElementById('stat-attente').textContent = data.en_attente || 0;
                    document.getElementById('stat-rejetees').textContent = data.rejetees || 0;
                })
                .catch(error => {
                    console.error('❌ Erreur stats:', error);
                    showAlert('Erreur lors du chargement des statistiques', 'danger');
                });
        }

        function loadDemandes(statut = null) {
            document.querySelector('.loading').style.display = 'block';
            
            let url = `${API_URL}?action=get_demandes`;
            if (statut && statut !== 'all') {
                url += `&statut=${statut}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    displayDemandes(data);
                    document.querySelector('.loading').style.display = 'none';
                })
                .catch(error => {
                    console.error('❌ Erreur demandes:', error);
                    document.querySelector('.loading').style.display = 'none';
                    showAlert('Erreur lors du chargement des demandes', 'danger');
                });
        }

        function displayDemandes(demandes) {
            const tbody = document.getElementById('demandes-tbody');
            document.getElementById('total-demandes').textContent = demandes.length;

            if (!Array.isArray(demandes) || demandes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Aucune demande trouvée</td></tr>';
                return;
            }

            tbody.innerHTML = demandes.map(d => {
                let dateFormatted = 'Date invalide';
                try {
                    if (d.date) {
                        const dateObj = new Date(d.date.replace(' ', 'T'));
                        dateFormatted = dateObj.toLocaleDateString('fr-FR');
                    }
                } catch (e) {
                    console.error('Erreur format date:', e);
                }

                // Gestion du justificatif
                let justificatifBtn = '<span class="text-muted">-</span>';
                if (d.justificatif) {
                    justificatifBtn = `<button class="btn btn-sm btn-info" onclick="viewJustificatif('${d.justificatif}')" title="Voir le justificatif">
                        <i class="bi bi-eye"></i>
                    </button>`;
                }

                return `
                    <tr>
                        <td>${d.id || 'N/A'}</td>
                        <td>${d.utilisateur || 'N/A'}</td>
                        <td>${d.objectif || 'N/A'}</td>
                        <td>${dateFormatted}</td>
                        <td>${parseFloat(d.montant_total || 0).toFixed(2)} €</td>
                        <td>${justificatifBtn}</td>
                        <td>${getStatutBadge(d.statut)}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                ${getActionButtons(d.id, d.statut)}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function viewJustificatif(filename) {
            window.open(`<?= BASE_URL ?>uploads/${filename}`, '_blank');
        }

        function getStatutBadge(statut) {
            const badges = {
                'en_attente': '<span class="badge bg-warning text-dark">En attente</span>',
                'validee_manager': '<span class="badge bg-success">Validée Manager</span>',
                'validee_admin': '<span class="badge bg-primary">Validée Admin</span>',
                'rejetee': '<span class="badge bg-danger">Rejetée</span>'
            };
            return badges[statut] || `<span class="badge bg-secondary">${statut}</span>`;
        }

        function getActionButtons(id, statut) {
            let buttons = '';
            if (statut === 'en_attente') {
                buttons += `<button class="btn btn-success btn-sm" onclick="updateStatus(${id}, 'validee_manager')" title="Valider">
                    <i class="bi bi-check"></i>
                </button>`;
                buttons += `<button class="btn btn-danger btn-sm" onclick="updateStatus(${id}, 'rejetee')" title="Rejeter">
                    <i class="bi bi-x"></i>
                </button>`;
            }
            buttons += `<button class="btn btn-danger btn-sm" onclick="deleteDemande(${id})" title="Supprimer">
                <i class="bi bi-trash"></i>
            </button>`;
            return buttons;
        }

        function filterDemandes(statut, event) {
            currentFilter = statut;

            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'btn-dark');
                btn.classList.add('btn-outline-secondary');
            });
            
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('active', 'btn-dark');
                event.currentTarget.classList.remove('btn-outline-secondary');
            }

            loadDemandes(statut === 'all' ? null : statut);
        }

        function updateStatus(id, statut) {
            if (!confirm('Êtes-vous sûr de vouloir modifier le statut ?')) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('statut', statut);

            fetch(`${API_URL}?action=update_status`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadStats();
                    loadDemandes(currentFilter === 'all' ? null : currentFilter);
                    showAlert('Statut mis à jour', 'success');
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

        async function createDemande() {
            const utilisateur = document.getElementById('utilisateur').value.trim();
            const objectif = document.getElementById('objectif').value.trim();
            const montant = document.getElementById('montant').value;

            if (!utilisateur || !objectif) {
                showAlert('Veuillez remplir tous les champs obligatoires', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('utilisateur', utilisateur);
            formData.append('objectif', objectif);
            formData.append('montant', montant);

            // Ajouter le justificatif s'il existe
            if (selectedFile) {
                formData.append('justificatif', selectedFile);
            }

            try {
                const response = await fetch(`${API_URL}?action=create`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('nouvelleDemandeModal')).hide();
                    document.getElementById('nouvelleDemandeForm').reset();
                    removeFile();
                    loadStats();
                    loadDemandes();
                    showAlert('Demande créée avec succès', 'success');
                } else {
                    showAlert('Erreur lors de la création', 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la création', 'danger');
            }
        }

        function deleteDemande(id) {
            if (!confirm('Supprimer cette demande ?')) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch(`${API_URL}?action=delete`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadStats();
                    loadDemandes();
                    showAlert('Demande supprimée', 'success');
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

        function refreshData() {
            loadStats();
            loadDemandes(currentFilter === 'all' ? null : currentFilter);
            showAlert('Données actualisées', 'info');
        }

        function exportData() {
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