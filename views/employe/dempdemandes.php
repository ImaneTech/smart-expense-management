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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        body.dark .filter-bar {
            background: #1d2951;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .filter-bar .form-control,
        .filter-bar .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 14px;
        }

        body.dark .filter-bar .form-control,
        body.dark .filter-bar .form-select {
            background-color: #0b1437;
            border-color: #3d5a80;
            color: #e0e0e0;
        }

        body.dark .filter-bar .form-control::placeholder {
            color: #718096;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 5px;
        }

        body.dark .filter-label {
            color: #a8b2c1;
        }

        .btn-reset {
            background-color: #e2e8f0;
            color: #4a5568;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background-color: #cbd5e0;
            color: #2d3748;
        }

        body.dark .btn-reset {
            background-color: #3d5a80;
            color: #e0e0e0;
        }

        body.dark .btn-reset:hover {
            background-color: #4a6fa5;
        }

        /* Table container */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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

        /* Modal */
        .modal-content {
            border-radius: 15px;
        }

        body.dark .modal-content {
            background-color: #1d2951;
            color: #e0e0e0;
        }

        body.dark .modal-header {
            border-bottom-color: #3d5a80;
        }

        body.dark .modal-footer {
            border-top-color: #3d5a80;
        }

        body.dark .form-control,
        body.dark .form-select {
            background-color: #0b1437;
            border-color: #3d5a80;
            color: #e0e0e0;
        }

        body.dark .form-control:focus,
        body.dark .form-select:focus {
            background-color: #0b1437;
            border-color: #4facfe;
            color: #e0e0e0;
        }

        body.dark .btn-close {
            filter: invert(1);
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

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-add {
                margin-top: 10px;
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
            <div>
                <h2><i class="bi bi-receipt"></i> Mes Demandes de Frais</h2>
                <p>Bienvenue, <?= htmlspecialchars($user_name) ?></p>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addDemandeModal">
                <i class="bi bi-plus-circle"></i> Ajouter une demande
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
                        <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau de toutes les demandes -->
        <div class="table-container">
            <h5>
                <i class="bi bi-list-ul"></i> 
                Toutes mes demandes
                <span id="results-count" class="ms-2 text-muted" style="font-size: 14px; font-weight: 400;"></span>
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

    <!-- Modal Ajouter Demande -->
    <div class="modal fade" id="addDemandeModal" tabindex="-1" aria-labelledby="addDemandeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDemandeModalLabel">
                        <i class="bi bi-plus-circle"></i> Nouvelle Demande de Frais
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDemandeForm">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="objet_mission" class="form-label">Objet de la mission *</label>
                                <input type="text" class="form-control" id="objet_mission" name="objet_mission" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lieu_deplacement" class="form-label">Lieu de déplacement *</label>
                                <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement" required>
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
                        <h6 class="mb-3"><i class="bi bi-receipt-cutoff"></i> Détails des frais</h6>

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
                                <input type="number" step="0.01" class="form-control" id="montant" name="montant" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="justificatif" class="form-label">Justificatif (PDF, Image)</label>
                                <input type="file" class="form-control" id="justificatif" name="justificatif" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Formats acceptés : PDF, JPG, PNG (Max 5MB)</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="submitDemande()">
                        <i class="bi bi-check-circle"></i> Enregistrer
                    </button>
                </div>
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
        const API_URL = 'http://localhost/smart-expense-management/apiemp2.php';
        const UPLOADS_URL = 'http://localhost/smart-expense-management/uploads/';
        const USER_ID = <?= $user_id ?>;

        let allDemandes = []; // Stocker toutes les demandes

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard Employé - User ID:', USER_ID);
            loadAllDemandes();
            
            // Écouter les changements de filtres
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
                // Filtre de recherche
                const matchSearch = !searchTerm || 
                    (d.objet_mission && d.objet_mission.toLowerCase().includes(searchTerm)) ||
                    (d.lieu_deplacement && d.lieu_deplacement.toLowerCase().includes(searchTerm));

                // Filtre de statut
                const matchStatut = !statutFilter || d.statut === statutFilter;

                // Filtre de date de départ
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
            
            // Mise à jour du compteur
            resultsCount.textContent = `(${demandes.length} résultat${demandes.length > 1 ? 's' : ''})`;
            
            if (!Array.isArray(demandes) || demandes.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Essayez de modifier vos critères de recherche</p>
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
                return `
                    <img src="${filePath}" 
                         class="justificatif-image-preview" 
                         alt="Justificatif"
                         onclick="showImagePreview('${filePath}')"
                         title="Cliquer pour agrandir">
                `;
            } else if (fileExt === 'pdf') {
                return `
                    <a href="${filePath}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-danger justificatif-btn"
                       title="Ouvrir le PDF">
                        <i class="bi bi-file-pdf"></i> PDF
                    </a>
                `;
            } else {
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

        function submitDemande() {
            const form = document.getElementById('addDemandeForm');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

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
            if (justificatifFile) {
                formData.append('justificatif', justificatifFile);
            }

            fetch(API_URL, {
                method: 'POST',
                body: formData
            })
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
            .catch(error => {
                console.error('❌ Erreur:', error);
                showAlert('Erreur lors de l\'enregistrement', 'danger');
            });
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