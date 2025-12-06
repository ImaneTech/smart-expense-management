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
    <title>Gestion des Demandes - GoTrackr</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        #main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.close ~ #main-content {
            margin-left: 88px;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .page-header h2 {
            color: #2d3748;
            font-weight: 700;
            margin: 0;
        }

        .section-title {
            color: #2d3748;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        /* Stats cards */
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 48px rgba(0,0,0,0.15);
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        }

        .stat-card.success {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            margin-bottom: 15px;
        }

        .stat-icon.warning { 
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        }
        .stat-icon.success { 
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }
        .stat-icon.danger { 
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        }

        .stat-number {
            font-size: 48px;
            font-weight: 700;
            margin: 10px 0 5px 0;
            color: #1F2937;
        }

        .stat-label {
            color: #6B7280;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-sublabel {
            color: #9CA3AF;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Table container */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .table-container h5 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 18px;
        }

        .filter-btn {
            margin: 5px;
            border-radius: 25px;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 500;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }

        .filter-btn:not(.active) {
            background: white;
            color: #667eea;
            border-color: #667eea;
        }

        .filter-btn:not(.active):hover {
            background: #667eea;
            color: white;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 12px;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table thead th:first-child {
            border-radius: 10px 0 0 10px;
        }

        .table thead th:last-child {
            border-radius: 0 10px 10px 0;
        }

        .table tbody tr {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            font-size: 13px;
            border: none;
        }

        .table tbody td:first-child {
            border-radius: 10px 0 0 10px;
        }

        .table tbody td:last-child {
            border-radius: 0 10px 10px 0;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 11px;
        }

        .btn-group-sm .btn {
            border-radius: 8px;
            padding: 6px 12px;
            margin: 0 2px;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            border-radius: 20px 20px 0 0;
            padding: 20px 30px;
        }

        .modal-body {
            padding: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 15px;
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
            border-bottom: 2px solid #f3f4f6;
        }

        @media (max-width: 768px) {
            #main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .sidebar.close ~ #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <?php include('../../includes/sidebar.php'); ?>

    <div id="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-speedometer2 me-2"></i>Tableau de Bord</h2>
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
        </div>

        <!-- Section Title -->
        <div class="section-title">
            <i class="bi bi-bar-chart-line me-2"></i>Statistiques générales
        </div>

        <!-- Statistiques -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="stat-card warning">
                    <div class="stat-icon warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-number" id="stat-attente">0</div>
                    <div class="stat-label">En Attente</div>
                    <div class="stat-sublabel">0,00</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card success">
                    <div class="stat-icon success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-number" id="stat-validees">0</div>
                    <div class="stat-label">Validés</div>
                    <div class="stat-sublabel">Approuvé</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card danger">
                    <div class="stat-icon danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="stat-number" id="stat-rejetees">0</div>
                    <div class="stat-label">Rejetés</div>
                    <div class="stat-sublabel">Attention</div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn filter-btn active" onclick="filterDemandes('all', event)">
                <i class="bi bi-list"></i> Toutes
            </button>
            <button class="btn filter-btn" onclick="filterDemandes('En attente', event)">
                <i class="bi bi-clock"></i> En attente
            </button>
            <button class="btn filter-btn" onclick="filterDemandes('Validée Manager', event)">
                <i class="bi bi-check"></i> Validées
            </button>
            <button class="btn filter-btn" onclick="filterDemandes('Rejetée Manager', event)">
                <i class="bi bi-x"></i> Rejetées
            </button>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <h5 class="mb-3">
                <i class="bi bi-clock-history me-2"></i>
                Toutes les demandes <span class="badge bg-secondary" id="total-demandes">0</span>
            </h5>

            <div class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Utilisateur</th>
                            <th>Objet Mission</th>
                            <th>Lieu</th>
                            <th>Date Départ</th>
                            <th>Date Retour</th>
                            <th>Statut</th>
                            <th>Manager</th>
                            <th>Valid.</th>
                            <th>Traitement</th>
                            <th>Commentaire</th>
                            <th>Montant</th>
                            <th>Création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody">
                        <tr><td colspan="15" class="text-center text-muted">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle Demande -->
    <div class="modal fade" id="nouvelleDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nouvelle Demande de Frais</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nouvelleDemandeForm">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person"></i> Informations Utilisateur</h6>
                            </div>
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
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-briefcase"></i> Détails de la Mission</h6>
                            </div>
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
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Statut et Validation</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Statut *</label>
                                        <select class="form-select" id="statut" required>
                                            <option value="En attente" selected>En attente</option>
                                            <option value="Validée Manager">Validée Manager</option>
                                            <option value="Rejetée Manager">Rejetée Manager</option>
                                        </select>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="createDemande()">
                        <i class="bi bi-check-circle"></i> Créer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modifier -->
    <div class="modal fade" id="modifierDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Modifier Demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="modifierDemandeForm">
                        <input type="hidden" id="edit_demande_id">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person"></i> Informations Utilisateur</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">User ID *</label>
                                        <input type="number" class="form-control" id="edit_user_id" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Manager ID</label>
                                        <input type="number" class="form-control" id="edit_manager_id">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-briefcase"></i> Détails de la Mission</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Objet de la mission *</label>
                                    <textarea class="form-control" id="edit_objet_mission" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Lieu de déplacement *</label>
                                    <input type="text" class="form-control" id="edit_lieu_deplacement" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date de départ *</label>
                                        <input type="date" class="form-control" id="edit_date_depart" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date de retour *</label>
                                        <input type="date" class="form-control" id="edit_date_retour" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Statut et Validation</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Statut *</label>
                                        <select class="form-select" id="edit_statut" required>
                                            <option value="En attente">En attente</option>
                                            <option value="Validée Manager">Validée Manager</option>
                                            <option value="Rejetée Manager">Rejetée Manager</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Manager ID Validation</label>
                                        <input type="number" class="form-control" id="edit_manager_id_validation">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date de traitement</label>
                                        <input type="datetime-local" class="form-control" id="edit_date_traitement">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Montant Total (€)</label>
                                        <input type="number" step="0.01" class="form-control" id="edit_montant_total">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Commentaire Manager</label>
                                    <textarea class="form-control" id="edit_commentaire_manager" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-warning" onclick="updateDemande()">
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
        const API_URL = 'http://localhost/smart-expense-management/api2.php';
        let currentFilter = 'all';

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Application démarrée');
            loadStats();
            loadDemandes();
        });

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
                url += `&statut=${encodeURIComponent(statut)}`;
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
                tbody.innerHTML = '<tr><td colspan="15" class="text-center text-muted">Aucune demande trouvée</td></tr>';
                return;
            }

            tbody.innerHTML = demandes.map(d => {
                // Formatage des dates
                const formatDate = (dateStr) => {
                    if (!dateStr) return '-';
                    try {
                        return new Date(dateStr).toLocaleDateString('fr-FR');
                    } catch (e) {
                        return dateStr;
                    }
                };

                const formatDateTime = (dateStr) => {
                    if (!dateStr) return '-';
                    try {
                        const date = new Date(dateStr);
                        return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
                    } catch (e) {
                        return dateStr;
                    }
                };

                return `
                    <tr>
                        <td><strong>${d.id}</strong></td>
                        <td>${d.user_id || '-'}</td>
                        <td>${d.utilisateur_nom || '-'}</td>
                        <td><small>${d.objet_mission || '-'}</small></td>
                        <td><small>${d.lieu_deplacement || '-'}</small></td>
                        <td><small>${formatDate(d.date_depart)}</small></td>
                        <td><small>${formatDate(d.date_retour)}</small></td>
                        <td>${getStatutBadge(d.statut)}</td>
                        <td>${d.manager_id || '-'}</td>
                        <td>${d.manager_id_validation || '-'}</td>
                        <td><small>${formatDateTime(d.date_traitement)}</small></td>
                        <td><small>${d.commentaire_manager ? d.commentaire_manager.substring(0, 30) + '...' : '-'}</small></td>
                        <td><strong>${parseFloat(d.montant_total || 0).toFixed(2)} €</strong></td>
                        <td><small>${formatDateTime(d.created_at)}</small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                ${getActionButtons(d.id, d.statut)}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function getStatutBadge(statut) {
            const badges = {
                'En attente': '<span class="badge bg-warning text-dark">En attente</span>',
                'Validée Manager': '<span class="badge bg-success">Validée Manager</span>',
                'Rejetée Manager': '<span class="badge bg-danger">Rejetée Manager</span>'
            };
            return badges[statut] || `<span class="badge bg-secondary">${statut}</span>`;
        }

        function getActionButtons(id, statut) {
    let buttons = '';
    
    // Bouton Modifier
    buttons += `<button class="btn btn-warning btn-sm" onclick="editDemande(${id})" title="Modifier">
        <i class="bi bi-pencil"></i>
    </button>`;
    
    // Bouton Supprimer
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
            formData.append('user_id', <?= $_SESSION['user_id'] ?? 1 ?>);

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
            // Récupération de tous les champs du formulaire
            const user_id = document.getElementById('user_id').value.trim();
            const objet_mission = document.getElementById('objet_mission').value.trim();
            const lieu_deplacement = document.getElementById('lieu_deplacement').value.trim();
            const date_depart = document.getElementById('date_depart').value;
            const date_retour = document.getElementById('date_retour').value;
            const statut = document.getElementById('statut').value;
            const manager_id = document.getElementById('manager_id').value.trim();
            const manager_id_validation = document.getElementById('manager_id_validation').value.trim();
            const date_traitement = document.getElementById('date_traitement').value;
            const commentaire_manager = document.getElementById('commentaire_manager').value.trim();
            const montant_total = document.getElementById('montant_total').value;

            // Validation des champs obligatoires
            if (!user_id || !objet_mission || !lieu_deplacement || !date_depart || !date_retour) {
                showAlert('Veuillez remplir tous les champs obligatoires (*)', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('user_id', user_id);
            formData.append('objet_mission', objet_mission);
            formData.append('lieu_deplacement', lieu_deplacement);
            formData.append('date_depart', date_depart);
            formData.append('date_retour', date_retour);
            formData.append('statut', statut);
            
            // Champs optionnels
            if (manager_id) formData.append('manager_id', manager_id);
            if (manager_id_validation) formData.append('manager_id_validation', manager_id_validation);
            if (date_traitement) formData.append('date_traitement', date_traitement);
            if (commentaire_manager) formData.append('commentaire_manager', commentaire_manager);
            if (montant_total) formData.append('montant_total', montant_total);

            try {
                const response = await fetch(`${API_URL}?action=create`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('nouvelleDemandeModal')).hide();
                    document.getElementById('nouvelleDemandeForm').reset();
                    loadStats();
                    loadDemandes();
                    showAlert('Demande créée avec succès !', 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de la création', 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la création', 'danger');
            }
        }

        async function updateDemande() {
          const id = document.getElementById('edit_demande_id').value;
          console.log('ID envoyé:', id); // Pour déboguer

          if (!id) {
            showAlert('ID de la demande manquant', 'danger');
            return;
          }
          const user_id = document.getElementById('edit_user_id').value.trim();
          const objet_mission = document.getElementById('edit_objet_mission').value.trim();
          const lieu_deplacement = document.getElementById('edit_lieu_deplacement').value.trim();
          const date_depart = document.getElementById('edit_date_depart').value;
          const date_retour = document.getElementById('edit_date_retour').value;
          const statut = document.getElementById('edit_statut').value;
          const manager_id = document.getElementById('edit_manager_id').value.trim();
          const manager_id_validation = document.getElementById('edit_manager_id_validation').value.trim();
          const date_traitement = document.getElementById('edit_date_traitement').value;
          const commentaire_manager = document.getElementById('edit_commentaire_manager').value.trim();
          const montant_total = document.getElementById('edit_montant_total').value;

          if (!user_id || !objet_mission || !lieu_deplacement || !date_depart || !date_retour) {
             showAlert('Veuillez remplir tous les champs obligatoires (*)', 'warning');
              return;
           }

           const formData = new FormData();
           formData.append('id', id);
           formData.append('user_id', user_id);
           formData.append('objet_mission', objet_mission);
           formData.append('lieu_deplacement', lieu_deplacement);
           formData.append('date_depart', date_depart);
           formData.append('date_retour', date_retour);
           formData.append('statut', statut);
    
           if (manager_id) formData.append('manager_id', manager_id);
           if (manager_id_validation) formData.append('manager_id_validation', manager_id_validation);
           if (date_traitement) formData.append('date_traitement', date_traitement);
           if (commentaire_manager) formData.append('commentaire_manager', commentaire_manager);
           if (montant_total) formData.append('montant_total', montant_total);

           try {
             const response = await fetch(`${API_URL}?action=update_demande`, {
              method: 'POST',
              body: formData
            });
            const data = await response.json();

            if (data.success) {
              bootstrap.Modal.getInstance(document.getElementById('modifierDemandeModal')).hide();
              loadStats();
              loadDemandes();
              showAlert('Demande modifiée avec succès !', 'success');
           } else {
            showAlert(data.message || 'Erreur lors de la modification', 'danger');
          }
         } catch (error) {
           console.error('Erreur:', error);
           showAlert('Erreur lors de la modification', 'danger');
         }
         }

        function viewDetails(id) {
            window.location.href = `<?= BASE_URL ?>views/admin/demande-details.php?id=${id}`;
        }

        function editDemande(id) {
    console.log('🔍 Chargement de la demande ID:', id); // Debug
    
    fetch(`${API_URL}?action=get_demande_by_id&id=${id}`)
        .then(response => response.json())
        .then(data => {
            console.log('📦 Données reçues:', data); // Debug
            
            if (!data || !data.id) {
                showAlert('Demande introuvable', 'danger');
                return;
            }
            
            // Remplir le formulaire avec les données existantes
            document.getElementById('edit_demande_id').value = data.id;
            document.getElementById('edit_user_id').value = data.user_id || '';
            document.getElementById('edit_objet_mission').value = data.objet_mission || '';
            document.getElementById('edit_lieu_deplacement').value = data.lieu_deplacement || '';
            document.getElementById('edit_date_depart').value = data.date_depart || '';
            document.getElementById('edit_date_retour').value = data.date_retour || '';
            document.getElementById('edit_statut').value = data.statut || 'En attente';
            document.getElementById('edit_manager_id').value = data.manager_id || '';
            document.getElementById('edit_manager_id_validation').value = data.manager_id_validation || '';
            
            // Date de traitement (convertir format)
            if (data.date_traitement) {
                const dt = new Date(data.date_traitement);
                document.getElementById('edit_date_traitement').value = dt.toISOString().slice(0, 16);
            } else {
                document.getElementById('edit_date_traitement').value = '';
            }
            
            document.getElementById('edit_commentaire_manager').value = data.commentaire_manager || '';
            document.getElementById('edit_montant_total').value = data.montant_total || 0;
            
            console.log('✅ Formulaire rempli, ID:', document.getElementById('edit_demande_id').value); // Debug
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('modifierDemandeModal'));
            modal.show();
        })
        .catch(error => {
            console.error('❌ Erreur:', error);
            showAlert('Erreur lors du chargement des données', 'danger');
        });
}

        function deleteDemande(id) {
            if (!confirm('Supprimer cette demande ? Toutes les lignes de frais associées seront également supprimées.')) return;

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