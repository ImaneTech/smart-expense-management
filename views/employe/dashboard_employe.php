

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
    
    /* Gradients for stats cards */
    --gradient-warning-start: #FFF8E1;
    --gradient-warning-end: #FFECB3;
    --gradient-success-start: #E8F5E9;
    --gradient-success-end: #C8E6C9;
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
    
    /* Gradients for stats cards - dark mode */
    --gradient-warning-start: #3a2f1a;
    --gradient-warning-end: #5d4a2e;
    --gradient-success-start: #1b3a1c;
    --gradient-success-end: #2e5d2f;
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

/* ==================== PAGE HEADER ==================== */
.page-header {
    margin-bottom: 40px;
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

.stat-card.warning {
    background: linear-gradient(135deg, var(--gradient-warning-start) 0%, var(--gradient-warning-end) 100%) !important;
}

.stat-card.success {
    background: linear-gradient(135deg, var(--gradient-success-start) 0%, var(--gradient-success-end) 100%) !important;
}

.stat-card.danger {
    background: linear-gradient(135deg, var(--gradient-danger-start) 0%, var(--gradient-danger-end) 100%) !important;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    transition: var(--tran-03);
}

.stat-icon.warning {
    background-color: #ffa726;
}

body.dark .stat-icon.warning {
    background-color: #f57c00;
}

.stat-icon.success {
    background-color: #43a047;
}

body.dark .stat-icon.success {
    background-color: #2e7d32;
}

.stat-icon.danger {
    background-color: #e53935;
}

body.dark .stat-icon.danger {
    background-color: #c62828;
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

/* ==================== TABLE CONTAINER ==================== */
.table-container {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-top: 20px;
    border: 1px solid var(--border-color);
}

body.dark .table-container {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.table-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.table-header i {
    font-size: 1.5rem;
    color: #667eea;
    margin-right: 10px;
}

body.dark .table-header i {
    color: #8b9dc3;
}

.table-header h5 {
    color: var(--text-primary);
    font-weight: 600;
    margin: 0;
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

/* ==================== JUSTIFICATIF STYLES ==================== */
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

small.text-muted,
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

    .page-title {
        font-size: 1.5rem;
    }
    
    .table-header i {
        font-size: 1.2rem;
    }
    
    .table-header h5 {
        font-size: 1rem;
    }
    
    .modern-table {
        font-size: 0.85rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 24px;
    }
    
    .stat-number {
        font-size: 2rem;
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

body.dark .btn-close {
    filter: brightness(0) invert(1);
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
    </style>
</head>
<body>
    <?php include('../../includes/sidebar.php'); ?>
    
    <div id="main-content">
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-receipt me-2"></i>Mes Demandes de Frais</h1>
            <p class="page-subtitle">Bienvenue, <?= htmlspecialchars($user_name) ?> - Suivi de vos demandes</p>
        </div>

        <!-- Statistiques -->
        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-attente">0</div>
                            <div class="stat-label">En attente</div>
                            <small class="text-warning fw-bold mt-2 d-block">En cours de traitement</small>
                        </div>
                        <div class="stat-icon warning"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-validees">0</div>
                            <div class="stat-label">Validées</div>
                            <small class="text-success fw-bold mt-2 d-block">Approuvées</small>
                        </div>
                        <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card danger">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-rejetees">0</div>
                            <div class="stat-label">Rejetées</div>
                            <small class="text-danger fw-bold mt-2 d-block">À réviser</small>
                        </div>
                        <div class="stat-icon danger"><i class="bi bi-x-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <div class="table-header">
                <i class="bi bi-clock-history"></i>
                <h5>Mes 3 dernières demandes - Vue complète</h5>
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

    <!-- Modal prévisualisation image -->
    <div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview()">
        <span class="image-preview-close">&times;</span>
        <img id="previewImage" src="" alt="Prévisualisation">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
    <script>
        const API_URL = 'http://localhost/smart-expense-management/apiemp1v2.php';
        const UPLOADS_URL = 'http://localhost/smart-expense-management/uploads/';
        const USER_ID = <?= $user_id ?>;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard Employé - User ID:', USER_ID);
            loadUserStats();
            loadUserDemandes();
        });

        function loadUserStats() {
            fetch(`${API_URL}?action=get_user_stats&user_id=${USER_ID}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('stat-attente').textContent = data.en_attente || 0;
                    document.getElementById('stat-validees').textContent = data.validees || 0;
                    document.getElementById('stat-rejetees').textContent = data.rejetees || 0;
                })
                .catch(error => {
                    console.error('❌ Erreur stats:', error);
                    showAlert('Erreur lors du chargement des statistiques', 'danger');
                });
        }

        function loadUserDemandes() {
            fetch(`${API_URL}?action=get_user_recent_demandes&user_id=${USER_ID}&limit=3`)
                .then(response => response.json())
                .then(data => {
                    displayDemandes(data);
                })
                .catch(error => {
                    console.error('❌ Erreur demandes:', error);
                    showAlert('Erreur lors du chargement des demandes', 'danger');
                });
        }

        function displayDemandes(demandes) {
            const tbody = document.getElementById('demandes-tbody');
            if (!Array.isArray(demandes) || demandes.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>Aucune demande</h5>
                                <p>Vous n'avez pas encore soumis de demandes de frais</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = demandes.map(d => {
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
                        return date.toLocaleDateString('fr-FR') + '<br><small class="text-muted">' + 
                               date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}) + '</small>';
                    } catch (e) {
                        return dateStr;
                    }
                };

                const getStatutBadge = (statut) => {
                    const badges = {
                        'En attente': 'bg-warning text-dark',
                        'Validée Manager': 'bg-success',
                        'Rejetée Manager': 'bg-danger',
                        'Approuvée Compta': 'bg-primary',
                        'Payée': 'bg-info'
                    };
                    const badgeClass = badges[statut] || 'bg-secondary';
                    return `<span class="badge ${badgeClass} badge-custom">${statut}</span>`;
                };

                const commentaire = d.commentaire_manager 
                    ? (d.commentaire_manager.length > 30 
                        ? `<span title="${d.commentaire_manager}">${d.commentaire_manager.substring(0, 30)}...</span>` 
                        : d.commentaire_manager)
                    : '<span class="text-muted">-</span>';

                const justificatifHTML = getJustificatifHTML(d.justificatif);

                return `
                    <tr>
                        <td class="ps-4"><strong>${d.id}</strong></td>
                        <td>${d.objet_mission || '-'}</td>
                        <td>${d.lieu_deplacement || '-'}</td>
                        <td>${formatDate(d.date_depart)}</td>
                        <td>${formatDate(d.date_retour)}</td>
                        <td>${getStatutBadge(d.statut)}</td>
                        <td>${justificatifHTML}</td>
                        <td><strong class="text-primary">${parseFloat(d.montant_total || 0).toFixed(2)} €</strong></td>
                        <td><small>${commentaire}</small></td>
                        <td class="pe-4">${formatDateTime(d.created_at)}</td>
                    </tr>
                `;
            }).join('');
        }

        function getJustificatifHTML(justificatif) {
            if (!justificatif) {
                return '<span class="text-muted"><small>Aucun</small></span>';
            }
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

        function showImagePreview(imagePath) {
            const modal = document.getElementById('imagePreviewModal');
            const img = document.getElementById('previewImage');
            img.src = imagePath;
            modal.classList.add('show');
        }

        function closeImagePreview() {
            const modal = document.getElementById('imagePreviewModal');
            modal.classList.remove('show');
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