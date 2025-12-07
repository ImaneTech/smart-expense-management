
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
    <title>Gestion des Utilisateurs - GoTrackr</title>
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
    
    /* Gradients for cards */
    --gradient-primary-start: #E3F2FD;
    --gradient-primary-end: #BBDEFB;
    --gradient-success-start: #E8F5E9;
    --gradient-success-end: #C8E6C9;
    --gradient-warning-start: #FFF8E1;
    --gradient-warning-end: #FFECB3;
    --gradient-danger-start: #FFEBEE;
    --gradient-danger-end: #FFCDD2;
    
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
    
    /* Gradients for cards - dark mode */
    --gradient-primary-start: #1e3a5f;
    --gradient-primary-end: #2c5282;
    --gradient-success-start: #1b3a1c;
    --gradient-success-end: #2e5d2f;
    --gradient-warning-start: #3a2f1a;
    --gradient-warning-end: #5d4a2e;
    --gradient-danger-start: #3a1a1b;
    --gradient-danger-end: #5d2e2f;
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

/* ==================== STATS CARDS ==================== */
.stat-card {
    border-radius: 20px;
    border: 1px solid var(--border-color);
    padding: 25px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    height: 100%;
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

.stat-card.primary {
    background: linear-gradient(135deg, var(--gradient-primary-start) 0%, var(--gradient-primary-end) 100%) !important;
}

.stat-card.success {
    background: linear-gradient(135deg, var(--gradient-success-start) 0%, var(--gradient-success-end) 100%) !important;
}

.stat-card.warning {
    background: linear-gradient(135deg, var(--gradient-warning-start) 0%, var(--gradient-warning-end) 100%) !important;
}

.stat-card.danger {
    background: linear-gradient(135deg, var(--gradient-danger-start) 0%, var(--gradient-danger-end) 100%) !important;
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

/* Icons in stats cards */
.stat-card .bi-people,
.stat-card .bi-person-check,
.stat-card .bi-person-badge,
.stat-card .bi-shield-check {
    opacity: 0.3;
}

/* ==================== SEARCH AND FILTER SECTION ==================== */
.search-filter-section {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border-color);
}

body.dark .search-filter-section {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.search-box-main {
    margin-bottom: 15px;
}

.search-box-main .input-group-text {
    background-color: transparent;
    border-right: none;
    border: 2px solid var(--border-color);
    border-radius: 8px 0 0 8px;
    color: var(--text-muted);
}

.search-box-main .form-control {
    border-left: none;
    border: 2px solid var(--border-color);
    border-radius: 0 8px 8px 0;
    padding: 10px 15px;
    background-color: var(--card-bg);
    color: var(--text-primary);
}

.search-box-main .form-control:focus {
    box-shadow: none;
    border-color: var(--primary-color);
    background-color: var(--card-bg);
}

.search-box-main .input-group-text:focus-within {
    border-color: var(--primary-color);
}

body.dark .search-box-main .form-control {
    background-color: var(--primary-color-light);
}

body.dark .search-box-main .form-control:focus {
    background-color: var(--primary-color-light);
}

/* Filter Buttons */
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

/* ==================== ROLE BADGES ==================== */
.role-badge {
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.8rem;
    display: inline-block;
    transition: var(--tran-03);
}

.role-admin {
    background-color: #FFEBEE;
    color: #e53935;
}

body.dark .role-admin {
    background-color: #3a1a1b;
    color: #ef5350;
}

.role-manager {
    background-color: #E3F2FD;
    color: #1976d2;
}

body.dark .role-manager {
    background-color: #1e3a5f;
    color: #64b5f6;
}

.role-employe {
    background-color: #E8F5E9;
    color: #43a047;
}

body.dark .role-employe {
    background-color: #1b3a1c;
    color: #66bb6a;
}

.role-badge:hover {
    transform: scale(1.05);
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

small.text-primary,
small.text-success,
small.text-warning,
small.text-danger {
    font-size: 0.85rem;
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

    .filter-btn {
        width: 100%;
        margin-right: 0;
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
.search-filter-section,
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
                <h1 class="page-title"><i class="bi bi-people me-2"></i>Gestion des Utilisateurs</h1>
                <span class="text-muted small">Administration des comptes utilisateurs</span>
            </div>
            <div class="dropdown">
                <button class="btn btn-primary-custom dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical me-1"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#nouvelUtilisateurModal"><i class="bi bi-person-plus me-2"></i> Nouvel utilisateur</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportUsers()"><i class="bi bi-download me-2"></i> Exporter</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="refreshData()"><i class="bi bi-arrow-clockwise me-2"></i> Actualiser</a></li>
                </ul>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card primary">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-total">0</div>
                            <div class="stat-label">Total</div>
                            <small class="text-primary fw-bold mt-2 d-block">Utilisateurs</small>
                        </div>
                        <div><i class="bi bi-people" style="font-size: 3rem; color: #1976d2; opacity: 0.3;"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-employes">0</div>
                            <div class="stat-label">Employés</div>
                            <small class="text-success fw-bold mt-2 d-block">Actifs</small>
                        </div>
                        <div><i class="bi bi-person-check" style="font-size: 3rem; color: #43a047; opacity: 0.3;"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-managers">0</div>
                            <div class="stat-label">Managers</div>
                            <small class="text-warning fw-bold mt-2 d-block">Superviseurs</small>
                        </div>
                        <div><i class="bi bi-person-badge" style="font-size: 3rem; color: #ffa726; opacity: 0.3;"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card danger">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-admins">0</div>
                            <div class="stat-label">Administrateurs</div>
                            <small class="text-danger fw-bold mt-2 d-block">Système</small>
                        </div>
                        <div><i class="bi bi-shield-check" style="font-size: 3rem; color: #e53935; opacity: 0.3;"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-filter-section">
            <div class="search-box-main">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Rechercher par nom, email ou ID...">
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center">
                <span class="me-3 fw-semibold text-muted small text-uppercase">Filtrer par:</span>
                <button class="btn filter-btn active" onclick="filterUsers('all', event)"><i class="bi bi-list me-1"></i> Tous</button>
                <button class="btn filter-btn" onclick="filterUsers('employe', event)"><i class="bi bi-person me-1"></i> Employés</button>
                <button class="btn filter-btn" onclick="filterUsers('manager', event)"><i class="bi bi-person-badge me-1"></i> Managers</button>
                <button class="btn filter-btn" onclick="filterUsers('admin', event)"><i class="bi bi-shield-check me-1"></i> Admins</button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0" style="color: var(--text-primary);">Liste des utilisateurs <span class="badge bg-secondary" id="total-users">0</span></h5>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Département</th><th>Rôle</th><th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <tr><td colspan="8" class="text-center text-muted py-5">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvel Utilisateur -->
    <div class="modal fade" id="nouvelUtilisateurModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nouvelUtilisateurForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="first_name" required placeholder="Ex: Jean">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="last_name" required placeholder="Ex: Dupont">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" required placeholder="exemple@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control" id="phone" required placeholder="0600000000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Département *</label>
                            <input type="text" class="form-control" id="department" required placeholder="Ex: IT, Finance, RH">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle *</label>
                            <select class="form-select" id="role" required>
                                <option value="">Sélectionner...</option>
                                <option value="employe">Employé</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirmer mot de passe *</label>
                                <input type="password" class="form-control" id="confirmPassword" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Annuler</button>
                    <button type="button" class="btn btn-primary-custom rounded-pill" onclick="createUser()"><i class="bi bi-check-circle me-1"></i> Créer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modifier Utilisateur -->
    <div class="modal fade" id="modifierUtilisateurModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Modifier Utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="modifierUtilisateurForm">
                        <input type="hidden" id="edit-id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="edit-first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="edit-last_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit-email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control" id="edit-phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Département *</label>
                            <input type="text" class="form-control" id="edit-department" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle *</label>
                            <select class="form-select" id="edit-role" required>
                                <option value="employe">Employé</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Annuler</button>
                    <button type="button" class="btn btn-warning rounded-pill" onclick="updateUser()"><i class="bi bi-save me-1"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
    <script>
        const API_URL = 'http://localhost/smart-expense-management/apiusers.php';
        let currentFilter = 'all';
        let allUsers = [];

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Gestion Utilisateurs démarrée');
            loadStats();
            loadUsers();

            document.getElementById('searchInput').addEventListener('input', function() {
                filterUsersBySearch(this.value);
            });
        });

        function loadStats() {
            fetch(`${API_URL}?action=get_stats`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('stat-total').textContent = data.total || 0;
                    document.getElementById('stat-employes').textContent = data.employes || 0;
                    document.getElementById('stat-managers').textContent = data.managers || 0;
                    document.getElementById('stat-admins').textContent = data.admins || 0;
                })
                .catch(error => {
                    console.error('❌ Erreur stats:', error);
                });
        }

        function loadUsers(role = null) {
            let url = `${API_URL}?action=get_users`;
            if (role && role !== 'all') {
                url += `&role=${role}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    allUsers = data;
                    displayUsers(data);
                })
                .catch(error => {
                    console.error('❌ Erreur users:', error);
                    showAlert('Erreur lors du chargement des utilisateurs', 'danger');
                });
        }

        function displayUsers(users) {
            const tbody = document.getElementById('users-tbody');
            document.getElementById('total-users').textContent = users.length;

            if (!Array.isArray(users) || users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Aucun utilisateur trouvé</td></tr>';
                return;
            }

            tbody.innerHTML = users.map(u => `
                <tr>
                    <td>${u.id || 'N/A'}</td>
                    <td>${u.first_name || 'N/A'}</td>
                    <td>${u.last_name || 'N/A'}</td>
                    <td>${u.email || 'N/A'}</td>
                    <td>${u.phone || 'N/A'}</td>
                    <td>${u.department || 'N/A'}</td>
                    <td>${getRoleBadge(u.role)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-action" onclick='editUser(${u.id})' title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick='deleteUser(${u.id})' title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function getRoleBadge(role) {
            const badges = {
                'admin': '<span class="role-badge role-admin">Admin</span>',
                'manager': '<span class="role-badge role-manager">Manager</span>',
                'employe': '<span class="role-badge role-employe">Employé</span>'
            };
            return badges[role] || `<span class="role-badge bg-secondary">${role}</span>`;
        }

        function filterUsers(role, event) {
            currentFilter = role;

            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'btn-dark');
                btn.classList.add('btn-outline-secondary');
            });
            
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('active', 'btn-dark');
                event.currentTarget.classList.remove('btn-outline-secondary');
            }

            loadUsers(role === 'all' ? null : role);
        }

        function filterUsersBySearch(searchTerm) {
            if (!searchTerm) {
                displayUsers(allUsers);
                return;
            }

            const filtered = allUsers.filter(u => {
                const firstName = (u.first_name || '').toLowerCase();
                const lastName = (u.last_name || '').toLowerCase();
                const email = (u.email || '').toLowerCase();
                const id = String(u.id || '');
                const term = searchTerm.toLowerCase();
                
                return firstName.includes(term) || lastName.includes(term) || email.includes(term) || id.includes(term);
            });

            displayUsers(filtered);
        }

        async function createUser() {
            const first_name = document.getElementById('first_name').value.trim();
            const last_name = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const department = document.getElementById('department').value.trim();
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!first_name || !last_name || !email || !phone || !department || !role || !password) {
                showAlert('Veuillez remplir tous les champs obligatoires', 'warning');
                return;
            }

            if (password !== confirmPassword) {
                showAlert('Les mots de passe ne correspondent pas', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('first_name', first_name);
            formData.append('last_name', last_name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('department', department);
            formData.append('role', role);
            formData.append('password', password);

            try {
                const response = await fetch(`${API_URL}?action=create`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('nouvelUtilisateurModal')).hide();
                    document.getElementById('nouvelUtilisateurForm').reset();
                    loadStats();
                    loadUsers();
                    showAlert('Utilisateur créé avec succès', 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de la création', 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la création', 'danger');
            }
        }

        function editUser(id) {
            const user = allUsers.find(u => u.id == id);
            if (!user) return;

            document.getElementById('edit-id').value = user.id;
            document.getElementById('edit-first_name').value = user.first_name;
            document.getElementById('edit-last_name').value = user.last_name;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-phone').value = user.phone;
            document.getElementById('edit-department').value = user.department;
            document.getElementById('edit-role').value = user.role;

            new bootstrap.Modal(document.getElementById('modifierUtilisateurModal')).show();
        }

        async function updateUser() {
            const id = document.getElementById('edit-id').value;
            const first_name = document.getElementById('edit-first_name').value.trim();
            const last_name = document.getElementById('edit-last_name').value.trim();
            const email = document.getElementById('edit-email').value.trim();
            const phone = document.getElementById('edit-phone').value.trim();
            const department = document.getElementById('edit-department').value.trim();
            const role = document.getElementById('edit-role').value;

            if (!first_name || !last_name || !email || !phone || !department || !role) {
                showAlert('Veuillez remplir tous les champs obligatoires', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('first_name', first_name);
            formData.append('last_name', last_name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('department', department);
            formData.append('role', role);

            try {
                const response = await fetch(`${API_URL}?action=update`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modifierUtilisateurModal')).hide();
                    loadStats();
                    loadUsers(currentFilter === 'all' ? null : currentFilter);
                    showAlert('Utilisateur modifié avec succès', 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de la modification', 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la modification', 'danger');
            }
        }

        function deleteUser(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return;

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
                    loadUsers();
                    showAlert('Utilisateur supprimé', 'success');
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
            loadStats();
            loadUsers(currentFilter === 'all' ? null : currentFilter);
            showAlert('Données actualisées', 'info');
        }

        function exportUsers() {
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