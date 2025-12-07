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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css">
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

/* Stats Cards - Style moderne */
.stat-card {
    border-radius: 20px;
    border: 1px solid var(--border-color);
    padding: 25px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    height: 100%;
    background-color: var(--card-bg);
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

.stat-card.success {
    background-color: #E8F5E9 !important;
}

body.dark .stat-card.success {
    background-color: #1b3a1c !important;
    border-color: #2e5d2f;
}

.stat-card.warning {
    background-color: #FFF8E1 !important;
}

body.dark .stat-card.warning {
    background-color: #3a2f1a !important;
    border-color: #5d4a2e;
}

.stat-card.danger {
    background-color: #FFEBEE !important;
}

body.dark .stat-card.danger {
    background-color: #3a1a1b !important;
    border-color: #5d2e2f;
}

.stat-icon {
    width: 60px;
    height: 60px;
    opacity: 0.9;
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

/* Filtres modernes */
.filter-section {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border-color);
}

body.dark .filter-section {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.filter-btn {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 500;
    font-size: 0.9rem;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    margin-right: 10px;
    margin-bottom: 10px;
}

.filter-btn.active {
    background-color: var(--primary-color) !important;
    color: white !important;
    border-color: var(--primary-color);
}

.filter-btn:not(.active) {
    background-color: var(--hover-bg);
    color: var(--text-muted);
    border-color: var(--border-color);
}

.filter-btn:not(.active):hover {
    background-color: var(--primary-color-light);
    border-color: var(--primary-color);
}

/* Table moderne */
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

.table-primary-text {
    color: var(--text-primary);
    font-weight: 500;
}

/* Badges modernes */
.badge {
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.8rem;
}

/* Boutons d'action */
.btn-action-group {
    display: flex;
    gap: 5px;
}

.btn-action {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.3s ease;
    font-size: 0.9rem;
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
}

.btn-action.btn-delete {
    background-color: #FFEBEE;
    color: #e53935;
}

body.dark .btn-action.btn-delete {
    background-color: #3a1a1b;
}

/* Header section */
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

/* Modal personnalisé */
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

.card {
    border-radius: 12px;
    border: 1px solid var(--border-color);
    background-color: var(--card-bg);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    font-weight: 600;
    background-color: var(--hover-bg) !important;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

.card-body {
    background-color: var(--card-bg);
    color: var(--text-primary);
}

.form-label {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.form-control, .form-select {
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

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(74, 95, 127, 0.15);
    background-color: var(--card-bg);
}

body.dark .form-control:focus,
body.dark .form-select:focus {
    background-color: var(--primary-color-light);
}

/* Loading */
.loading {
    display: none;
    text-align: center;
    padding: 40px;
}

/* Alert personnalisée */
.alert-custom {
    border-radius: 12px;
    border: none;
    padding: 15px 20px;
    font-weight: 500;
}

/* Dropdown */
.dropdown-menu {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

body.dark .dropdown-menu {
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

.dropdown-item {
    color: var(--text-primary);
    transition: var(--tran-02);
}

.dropdown-item:hover {
    background-color: var(--hover-bg);
    color: var(--primary-color);
}

.dropdown-divider {
    border-top: 1px solid var(--border-color);
}

.btn-close-white {
    filter: brightness(0) invert(1);
}

body.dark .btn-close {
    filter: brightness(0) invert(1);
}

.text-muted {
    color: var(--text-muted) !important;
}

small.text-muted,
small.text-success,
small.text-warning,
small.text-danger {
    font-size: 0.85rem;
}

/* Responsive */
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

    .filter-btn {
        width: 100%;
        margin-right: 0;
    }

    .page-title {
        font-size: 1.5rem;
    }
}

/* Animations */
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

.stat-card, .filter-section, .table-container {
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

/* Scrollbar personnalisé */
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
    </style>
</head>
<body>

    <?php include('../../includes/sidebar.php'); ?>

    <div id="main-content">
        <!-- Header -->
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
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#nouvelleDemandeModal">
                        <i class="bi bi-plus-circle me-2"></i> Nouvelle demande
                    </a></li>
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

        <!-- Statistiques -->
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

        <!-- Filtres -->
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

        <!-- Tableau -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0" style="color: var(--text-primary);">
                    Liste des demandes <span class="badge bg-secondary" id="total-demandes">0</span>
                </h5>
            </div>

            <div class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-3 text-muted">Chargement des données...</p>
            </div>

            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                           <tr>
                               <th class="ps-4">ID</th>
                               <th>User ID</th>
                               <th>Utilisateur</th>
                               <th>Objet Mission</th>
                               <th>Lieu</th>
                               <th>Date Départ</th>
                               <th>Date Retour</th>
                               <th>Statut</th>
                               <th>Manager</th>
                               <th>Montant Id Validation </th>
                               <th>Date De Traitement</th>
                               <th>Commentaire Manager</th>
                               <th>Montant Total</th>
                               <th>Date De Creation</th>
                               <th class="text-end pe-4">Actions</th>
                          </tr>
                    </thead>
                    <tbody id="demandes-tbody">
                        <tr><td colspan="12" class="text-center text-muted py-5">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle Demande -->
    <div class="modal fade" id="nouvelleDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Nouvelle Demande de Frais</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nouvelleDemandeForm">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person me-2"></i> Informations Utilisateur</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">User ID *</label>
                                        <input type="number" class="form-control" id="user_id" required>
                                        <small class="text-muted">ID de l'utilisateur qui fait la demande</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Manager ID</label>
                                        <input type="number" class="form-control" id="manager_id">
                                        <small class="text-muted">ID du manager responsable (optionnel)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i> Détails de la Mission</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Objet de la mission *</label>
                                    <textarea class="form-control" id="objet_mission" rows="3" required placeholder="Décrivez l'objectif de la mission..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Lieu de déplacement *</label>
                                    <input type="text" class="form-control" id="lieu_deplacement" required placeholder="Ex: Paris, Lyon, Marseille...">
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
                                <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i> Statut et Validation</h6>
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
                                        <small class="text-muted">ID du manager qui valide</small>
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
                                    <textarea class="form-control" id="commentaire_manager" rows="3" placeholder="Commentaires ou remarques du manager..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info alert-custom">
                            <i class="bi bi-info-circle me-2"></i> <strong>Note :</strong> Les champs marqués d'un astérisque (*) sont obligatoires.
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

    <!-- Modal Modifier Demande -->
    <div class="modal fade" id="modifierDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Modifier Demande de Frais</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="modifierDemandeForm">
                        <input type="hidden" id="edit_demande_id">
                        
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person me-2"></i> Informations Utilisateur</h6>
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
                                <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i> Détails de la Mission</h6>
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
                                <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i> Statut et Validation</h6>
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
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-warning rounded-pill" onclick="updateDemande()">
                        <i class="bi bi-save me-1"></i> Enregistrer
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
                'en_attente': '<span class="badge bg-warning text-dark">En attente</span>',
                'validee_manager': '<span class="badge bg-success">Validée Manager</span>',
                'rejetee': '<span class="badge bg-danger">Rejetée Manager</span>'
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

    // Retirer la classe active de tous les boutons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Ajouter la classe active au bouton cliqué
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }

    // CORRECTION: Mapper le statut correctement
    let statutAPI = null;
    if (statut !== 'all') {
        const mapping = {
            'en_attente': 'en_attente',
            'validee_manager': 'validee_manager',
            'rejetee': 'rejetee',
            'validee_admin': 'validee_admin'
        };
        statutAPI = mapping[statut] || statut;
    }

    // Charger les demandes avec le filtre
    loadDemandes(statutAPI);
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