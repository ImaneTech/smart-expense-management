<?php
session_start();

// Vérifier si l'utilisateur est connecté
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
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            transition: background-color 0.3s ease;
        }

        body.dark {
            background-color: #0b1437;
        }

        #main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease, background-color 0.3s ease;
        }

        body.dark #main-content {
            background-color: #0b1437;
            color: #e0e0e0;
        }

        .sidebar.close ~ #main-content {
            margin-left: 88px;
        }

        /* Header */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 5px;
        }

        body.dark .page-header h2 {
            color: #e0e0e0;
        }

        .page-header p {
            color: #718096;
            margin: 0;
        }

        body.dark .page-header p {
            color: #a8b2c1;
        }

        /* Stats cards */
        .stat-card {
            border-radius: 15px;
            border: 2px solid;
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            background: white;
        }

        body.dark .stat-card {
            background: #1d2951;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        body.dark .stat-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }

        .stat-card.warning {
            border-color: #ffc107;
            background-color: #fffef8;
        }

        body.dark .stat-card.warning {
            background: #1d2951;
            border-color: #ffc107;
        }

        .stat-card.success {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        body.dark .stat-card.success {
            background: #1d2951;
            border-color: #28a745;
        }

        .stat-card.danger {
            border-color: #dc3545;
            background-color: #fff8f8;
        }

        body.dark .stat-card.danger {
            background: #1d2951;
            border-color: #dc3545;
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
        }

        .stat-icon.warning { background-color: #ffc107; }
        .stat-icon.success { background-color: #28a745; }
        .stat-icon.danger { background-color: #dc3545; }

        .stat-number {
            font-size: 42px;
            font-weight: bold;
            margin: 10px 0;
            color: #2d3748;
        }

        body.dark .stat-number {
            color: #e0e0e0;
        }

        .stat-label {
            color: #718096;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        body.dark .stat-label {
            color: #a8b2c1;
        }

        body.dark .text-muted {
            color: #a8b2c1 !important;
        }

        /* Table container */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-top: 20px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        body.dark .table-container {
            background: #1d2951;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .table-container h5 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        body.dark .table-container h5 {
            color: #e0e0e0;
        }

        .table-container h5 i {
            margin-right: 10px;
            color: #667eea;
        }

        body.dark .table-container h5 i {
            color: #4facfe;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background-color: #4a5f7f;
            color: white;
        }

        .table thead th {
            border: none;
            padding: 15px 10px;
            font-weight: 500;
            font-size: 13px;
            white-space: nowrap;
        }

        .table tbody {
            background: white;
        }

        body.dark .table tbody {
            background: #1d2951;
        }

        body.dark .table {
            color: #e0e0e0;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            font-size: 13px;
            border-color: #e2e8f0;
        }

        body.dark .table tbody td {
            border-color: #3d5a80;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        body.dark .table tbody tr:hover {
            background-color: #2a3f5f;
        }

        .badge-custom {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 11px;
        }

        /* Justificatif buttons */
        .justificatif-btn {
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 11px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .justificatif-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .justificatif-btn i {
            font-size: 14px;
        }

        .justificatif-image-preview {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .justificatif-image-preview:hover {
            transform: scale(1.1);
        }

        .loading-container {
            text-align: center;
            padding: 40px;
        }

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
            color: #3d5a80;
        }

        .empty-state h5 {
            color: #718096;
            margin-bottom: 10px;
        }

        body.dark .empty-state h5 {
            color: #a8b2c1;
        }

        .empty-state p {
            color: #a0aec0;
        }

        body.dark .empty-state p {
            color: #718096;
        }

        /* Modal pour prévisualisation d'image */
        .image-preview-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
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
        }

        .image-preview-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
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
    <?php include('../../includes/sidebar.php'); ?>

    <!-- CONTENU PRINCIPAL -->
    <div id="main-content">
        <!-- Header -->
        <div class="page-header">
            <h2><i class="bi bi-receipt"></i> Mes Demandes de Frais</h2>
            <p>Bienvenue, <?= htmlspecialchars($user_name) ?></p>
        </div>

        <!-- Statistiques -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-attente">0</div>
                            <div class="stat-label">En attente</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card success">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-validees">0</div>
                            <div class="stat-label">Validées</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card danger">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-rejetees">0</div>
                            <div class="stat-label">Rejetées</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau avec Justificatifs -->
        <div class="table-container">
            <h5>
                <i class="bi bi-clock-history"></i> 
                Mes 3 dernières demandes - Vue complète
            </h5>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Objet Mission</th>
                            <th>Lieu</th>
                            <th>Date Départ</th>
                            <th>Date Retour</th>
                            <th>Statut</th>
                            <th>Justificatif</th>
                            <th>Montant Total</th>
                            <th>Commentaire</th>
                            <th>Date Création</th>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>

    <!-- Script Mode Sombre -->
    <script>
        if (localStorage.getItem("darkMode") === "enabled") {
            document.body.classList.add("dark");
        }
    </script>

    <!-- Script Principal -->
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
                        return date.toLocaleDateString('fr-FR') + '<br>' + 
                               date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
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
                        ? d.commentaire_manager.substring(0, 30) + '...' 
                        : d.commentaire_manager)
                    : '-';

                // Gestion du justificatif
                const justificatifHTML = getJustificatifHTML(d.justificatif);

                return `
                    <tr>
                        <td><strong>${d.id}</strong></td>
                        <td><small>${d.objet_mission || '-'}</small></td>
                        <td><small>${d.lieu_deplacement || '-'}</small></td>
                        <td><small>${formatDate(d.date_depart)}</small></td>
                        <td><small>${formatDate(d.date_retour)}</small></td>
                        <td>${getStatutBadge(d.statut)}</td>
                        <td>${justificatifHTML}</td>
                        <td><strong class="text-primary">${parseFloat(d.montant_total || 0).toFixed(2)} €</strong></td>
                        <td><small title="${d.commentaire_manager || ''}">${commentaire}</small></td>
                        <td><small>${formatDateTime(d.created_at)}</small></td>
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
                // Image - Prévisualisation miniature
                return `
                    <img src="${filePath}" 
                         class="justificatif-image-preview" 
                         alt="Justificatif"
                         onclick="showImagePreview('${filePath}')"
                         title="Cliquer pour agrandir">
                `;
            } else if (fileExt === 'pdf') {
                // PDF - Bouton de téléchargement
                return `
                    <a href="${filePath}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-danger justificatif-btn"
                       title="Ouvrir le PDF">
                        <i class="bi bi-file-pdf"></i> PDF
                    </a>
                `;
            } else {
                // Autre fichier
                return `
                    <a href="${filePath}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-secondary justificatif-btn"
                       title="Télécharger le fichier">
                        <i class="bi bi-download"></i> Fichier
                    </a>
                `;
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
            alertDiv.style.minWidth = '300px';
            alertDiv.innerHTML = `
                <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-x-circle'}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
</body>
</html>