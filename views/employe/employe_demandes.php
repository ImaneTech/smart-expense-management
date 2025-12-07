<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-expense-management/views/auth/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$role = $_SESSION['role'] ?? 'employe';
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes - GoTrackr</title>
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

/* ==================== PAGE HEADER ==================== */
.page-header {
    margin-bottom: 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    color: var(--text-primary);
    font-weight: bold;
    font-size: 1.8rem;
    margin: 0;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin-top: 5px;
}

/* ==================== BUTTON ADD ==================== */
.btn-add {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

body.dark .btn-add {
    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
}

body.dark .btn-add:hover {
    box-shadow: 0 5px 15px rgba(74, 85, 104, 0.4);
}

/* ==================== FILTER BAR ==================== */
.filter-bar {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 25px;
    border: 1px solid var(--border-color);
}

body.dark .filter-bar {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.filter-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-muted);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.filter-bar .form-control,
.filter-bar .form-select {
    border-radius: 8px;
    border: 2px solid var(--border-color);
    padding: 10px 15px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background-color: var(--card-bg);
    color: var(--text-primary);
}

body.dark .filter-bar .form-control,
body.dark .filter-bar .form-select {
    background-color: var(--primary-color-light);
}

.filter-bar .form-control:focus,
.filter-bar .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(74, 95, 127, 0.15);
    background-color: var(--card-bg);
}

body.dark .filter-bar .form-control:focus,
body.dark .filter-bar .form-select:focus {
    background-color: var(--primary-color-light);
}

.btn-reset {
    background-color: var(--hover-bg);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background-color: var(--primary-color-light);
    border-color: var(--primary-color);
}

/* ==================== TABLE CONTAINER ==================== */
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

.table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.table-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-header i {
    font-size: 1.5rem;
    color: #667eea;
}

body.dark .table-header i {
    color: #8b9dc3;
}

.table-header h5 {
    color: var(--text-primary);
    font-weight: 600;
    margin: 0;
}

.results-count {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 400;
}

/* ==================== TABLE ==================== */
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

/* ==================== BADGES ==================== */
.badge-custom {
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.8rem;
}

/* ==================== JUSTIFICATIF ==================== */
.justificatif-image-preview {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 2px solid var(--border-color);
}

.justificatif-image-preview:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

body.dark .justificatif-image-preview {
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

body.dark .justificatif-image-preview:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.5);
}

.justificatif-btn {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
    border: 2px solid var(--border-color);
}

.justificatif-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

body.dark .justificatif-btn {
    background-color: var(--primary-color-light);
    color: var(--text-primary);
}

/* ==================== EMPTY STATE ==================== */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #cbd5e0;
    margin-bottom: 20px;
}

body.dark .empty-state i {
    color: #4a5568;
}

.empty-state h5 {
    color: var(--text-muted);
    margin-bottom: 10px;
    font-weight: 600;
}

.empty-state p {
    color: #a0aec0;
    font-size: 0.95rem;
}

body.dark .empty-state p {
    color: #718096;
}

/* ==================== LOADING ==================== */
.loading-container {
    text-align: center;
    padding: 40px;
}

.spinner-border {
    color: var(--primary-color);
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

/* ==================== IMAGE PREVIEW MODAL ==================== */
.image-preview-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.95);
    justify-content: center;
    align-items: center;
}

.image-preview-modal.show {
    display: flex;
}

.image-preview-modal img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 10px;
}

.image-preview-close {
    position: absolute;
    top: 20px;
    right: 40px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.image-preview-close:hover {
    color: #ffa726;
}

/* ==================== TEXT UTILITIES ==================== */
.text-muted {
    color: var(--text-muted) !important;
}

.text-primary {
    color: var(--primary-color) !important;
}

.text-success {
    color: var(--success-color) !important;
}

.text-warning {
    color: var(--warning-color) !important;
}

.text-danger {
    color: var(--danger-color) !important;
}

small.text-muted {
    font-size: 0.85rem;
}

/* ==================== BADGES BOOTSTRAP ==================== */
.badge {
    transition: var(--tran-03);
}

body.dark .badge.bg-warning {
    background-color: #f57c00 !important;
}

body.dark .badge.bg-success {
    background-color: #2e7d32 !important;
}

body.dark .badge.bg-danger {
    background-color: #c62828 !important;
}

body.dark .badge.bg-primary {
    background-color: var(--primary-color) !important;
}

body.dark .badge.bg-info {
    background-color: #0288d1 !important;
}

body.dark .badge.bg-secondary {
    background-color: #616161 !important;
}

/* ==================== BUTTONS ==================== */
.btn-secondary {
    background-color: var(--hover-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
}

body.dark .btn-secondary {
    background-color: var(--primary-color-light);
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: #3d4f68;
    border-color: #3d4f68;
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

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .page-title {
        font-size: 1.5rem;
    }
    
    .btn-add {
        width: 100%;
    }
    
    .filter-bar .row {
        gap: 0;
    }
    
    .btn-reset {
        width: 100%;
        margin-top: 10px;
    }
    
    .table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .modern-table {
        font-size: 0.85rem;
    }
    
    .modern-table thead th,
    .modern-table tbody td {
        padding: 10px;
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

.filter-bar,
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
    </style>
</head>
<body>
    <?php include('../../includes/sidebar.php'); ?>
    
    <div id="main-content">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="bi bi-receipt me-2"></i>Mes Demandes de Frais</h1>
                <p class="page-subtitle">Bienvenue, <?= htmlspecialchars($user_name) ?> - Gérez vos demandes</p>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addDemandeModal">
                <i class="bi bi-plus-circle me-2"></i>Ajouter une demande
            </button>
        </div>

        <!-- Barre de filtres -->
        <div class="filter-bar">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-search"></i> Rechercher</label>
                    <input type="text" class="form-control" id="filter-search" placeholder="Objet, lieu...">
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-flag"></i> Statut</label>
                    <select class="form-select" id="filter-statut">
                        <option value="">Tous</option>
                        <option value="En attente">En attente</option>
                        <option value="Validée Manager">Validée Manager</option>
                        <option value="Rejetée Manager">Rejetée Manager</option>
                        <option value="Approuvée Compta">Approuvée Compta</option>
                        <option value="Payée">Payée</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-calendar"></i> Date début</label>
                    <input type="date" class="form-control" id="filter-date-debut">
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-calendar"></i> Date fin</label>
                    <input type="date" class="form-control" id="filter-date-fin">
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-reset" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-header-left">
                    <i class="bi bi-list-ul"></i>
                    <h5>Toutes mes demandes</h5>
                    <span id="results-count" class="results-count"></span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Objet Mission</th>
                            <th>Lieu</th>
                            <th>Date Départ</th>
                            <th>Date Retour</th>
                            <th>Statut</th>
                            <th>Justificatif</th>
                            <th>Montant Total</th>
                            <th>Commentaire</th>
                            <th class="pe-4">Date Création</th>
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody">
                        <tr>
                            <td colspan="10">
                                <div class="loading-container">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Chargement de vos demandes...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Demande -->
    <div class="modal fade" id="addDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle Demande de Frais</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addDemandeForm">
                        <div class="mb-3">
                            <label for="objet_mission" class="form-label">Objet de la mission *</label>
                            <input type="text" class="form-control" id="objet_mission" name="objet_mission" required placeholder="Ex: Réunion client, Formation...">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lieu_deplacement" class="form-label">Lieu de déplacement *</label>
                                <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement" required placeholder="Ex: Paris, Lyon...">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_depart" class="form-label">Date de départ *</label>
                                <input type="date" class="form-control" id="date_depart" name="date_depart" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_retour" class="form-label">Date de retour *</label>
                                <input type="date" class="form-control" id="date_retour" name="date_retour" required>
                            </div>
                        </div>
                        <hr class="my-4">
                        <h6 class="mb-3" style="color: var(--text-primary);"><i class="bi bi-receipt-cutoff me-2"></i>Détails des frais</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type_frais" class="form-label">Type de frais *</label>
                                <select class="form-select" id="type_frais" name="type_frais" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Hébergement">Hébergement</option>
                                    <option value="Restauration">Restauration</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="montant" class="form-label">Montant (€) *</label>
                                <input type="number" step="0.01" class="form-control" id="montant" name="montant" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Détails supplémentaires..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="justificatif" class="form-label">Justificatif (PDF, Image)</label>
                            <input type="file" class="form-control" id="justificatif" name="justificatif" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Formats acceptés : PDF, JPG, PNG (Max 5MB)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Annuler</button>
                    <button type="button" class="btn btn-primary rounded-pill" onclick="submitDemande()"><i class="bi bi-check-circle me-1"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal prévisualisation image -->
    <div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview()">
        <span class="image-preview-close">&times;</span>
        <img id="previewImage" src="" alt="Prévisualisation">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
    <script>
        const API_URL = 'http://localhost/smart-expense-management/apiemp2.php';
        const UPLOADS_URL = 'http://localhost/smart-expense-management/uploads/';
        const USER_ID = <?= $user_id ?>;
        let allDemandes = [];

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard Employé - User ID:', USER_ID);
            loadAllDemandes();
            document.getElementById('filter-search').addEventListener('input', applyFilters);
            document.getElementById('filter-statut').addEventListener('change', applyFilters);
            document.getElementById('filter-date-debut').addEventListener('change', applyFilters);
            document.getElementById('filter-date-fin').addEventListener('change', applyFilters);
        });

        function loadAllDemandes() {
            fetch(`${API_URL}?action=get_all_user_demandes&user_id=${USER_ID}`)
                .then(response => response.json())
                .then(data => {
                    allDemandes = data;
                    displayDemandes(allDemandes);
                })
                .catch(error => {
                    console.error('❌ Erreur demandes:', error);
                    showAlert('Erreur lors du chargement des demandes', 'danger');
                });
        }

        function applyFilters() {
            const searchTerm = document.getElementById('filter-search').value.toLowerCase();
            const statutFilter = document.getElementById('filter-statut').value;
            const dateDebut = document.getElementById('filter-date-debut').value;
            const dateFin = document.getElementById('filter-date-fin').value;

            const filtered = allDemandes.filter(d => {
                const matchSearch = !searchTerm || 
                    (d.objet_mission && d.objet_mission.toLowerCase().includes(searchTerm)) ||
                    (d.lieu_deplacement && d.lieu_deplacement.toLowerCase().includes(searchTerm));
                const matchStatut = !statutFilter || d.statut === statutFilter;
                const matchDateDebut = !dateDebut || d.date_depart >= dateDebut;
                const matchDateFin = !dateFin || d.date_depart <= dateFin;
                return matchSearch && matchStatut && matchDateDebut && matchDateFin;
            });
            displayDemandes(filtered);
        }

        function resetFilters() {
            document.getElementById('filter-search').value = '';
            document.getElementById('filter-statut').value = '';
            document.getElementById('filter-date-debut').value = '';
            document.getElementById('filter-date-fin').value = '';
            displayDemandes(allDemandes);
        }

        function displayDemandes(demandes) {
            const tbody = document.getElementById('demandes-tbody');
            const resultsCount = document.getElementById('results-count');
            resultsCount.textContent = `(${demandes.length} résultat${demandes.length > 1 ? 's' : ''})`;
            
            if (!Array.isArray(demandes) || demandes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state"><i class="bi bi-inbox"></i><h5>Aucune demande trouvée</h5><p>Essayez de modifier vos critères de recherche</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = demandes.map(d => {
                const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('fr-FR') : '-';
                const formatDateTime = (dateStr) => {
                    if (!dateStr) return '-';
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('fr-FR') + '<br><small class="text-muted">' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}) + '</small>';
                };
                const getStatutBadge = (statut) => {
                    const badges = { 'En attente': 'bg-warning text-dark', 'Validée Manager': 'bg-success', 'Rejetée Manager': 'bg-danger', 'Approuvée Compta': 'bg-primary', 'Payée': 'bg-info' };
                    return `<span class="badge ${badges[statut] || 'bg-secondary'} badge-custom">${statut}</span>`;
                };
                const commentaire = d.commentaire_manager ? (d.commentaire_manager.length > 30 ? `<span title="${d.commentaire_manager}">${d.commentaire_manager.substring(0, 30)}...</span>` : d.commentaire_manager) : '<span class="text-muted">-</span>';
                const justificatifHTML = getJustificatifHTML(d.justificatif);
                return `<tr><td class="ps-4"><strong>${d.id}</strong></td><td>${d.objet_mission || '-'}</td><td>${d.lieu_deplacement || '-'}</td><td>${formatDate(d.date_depart)}</td><td>${formatDate(d.date_retour)}</td><td>${getStatutBadge(d.statut)}</td><td>${justificatifHTML}</td><td><strong class="text-primary">${parseFloat(d.montant_total || 0).toFixed(2)} €</strong></td><td><small>${commentaire}</small></td><td class="pe-4">${formatDateTime(d.created_at)}</td></tr>`;
            }).join('');
        }

        function getJustificatifHTML(justificatif) {
            if (!justificatif) return '<span class="text-muted"><small>Aucun</small></span>';
            const fileExt = justificatif.split('.').pop().toLowerCase();
            const filePath = UPLOADS_URL + justificatif;
            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                return `<img src="${filePath}" class="justificatif-image-preview" alt="Justificatif" onclick="showImagePreview('${filePath}')" title="Cliquer pour agrandir">`;
            } else if (fileExt === 'pdf') {
                return `<a href="${filePath}" target="_blank" class="btn btn-sm btn-outline-danger justificatif-btn" title="Ouvrir le PDF"><i class="bi bi-file-pdf"></i> PDF</a>`;
            } else {
                return `<a href="${filePath}" target="_blank" class="btn btn-sm btn-outline-secondary justificatif-btn" title="Télécharger"><i class="bi bi-download"></i> Fichier</a>`;
            }
        }

        function submitDemande() {
            const form = document.getElementById('addDemandeForm');
            if (!form.checkValidity()) { form.reportValidity(); return; }
            const formData = new FormData();
            formData.append('action', 'add_demande');
            formData.append('user_id', USER_ID);
            formData.append('objet_mission', document.getElementById('objet_mission').value);
            formData.append('lieu_deplacement', document.getElementById('lieu_deplacement').value);
            formData.append('date_depart', document.getElementById('date_depart').value);
            formData.append('date_retour', document.getElementById('date_retour').value);
            formData.append('type_frais', document.getElementById('type_frais').value);
            formData.append('montant', document.getElementById('montant').value);
            formData.append('description', document.getElementById('description').value);
            const justificatifFile = document.getElementById('justificatif').files[0];
            if (justificatifFile) formData.append('justificatif', justificatifFile);
            fetch(API_URL, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Demande enregistrée avec succès', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addDemandeModal')).hide();
                    form.reset();
                    loadAllDemandes();
                } else {
                    showAlert(data.message || 'Erreur lors de l\'enregistrement', 'danger');
                }
            })
            .catch(error => { console.error('❌ Erreur:', error); showAlert('Erreur lors de l\'enregistrement', 'danger'); });
        }

        function showImagePreview(imagePath) {
            document.getElementById('previewImage').src = imagePath;
            document.getElementById('imagePreviewModal').classList.add('show');
        }

        function closeImagePreview() {
            document.getElementById('imagePreviewModal').classList.remove('show');
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