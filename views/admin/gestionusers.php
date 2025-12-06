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
            transition: margin-left 0.3s ease, background-color 0.3s ease, color 0.3s ease;
            background-color: #f5f7fa;
        }

        body.dark #main-content {
            background-color: #0b1437;
            color: #e0e0e0;
        }

        .sidebar.close ~ #main-content {
            margin-left: 88px;
        }

        /* Stats cards */
        .stat-card {
            border-radius: 10px;
            border: 2px solid;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
            background: white;
        }

        body.dark .stat-card {
            background: #1d2951;
            border-color: #3d5a80 !important;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        body.dark .stat-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }

        .stat-card.primary {
            border-color: #007bff;
            background-color: #f8f9ff;
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

        body.dark .stat-card .text-muted {
            color: #a8b2c1 !important;
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

        .stat-icon.primary { background-color: #007bff; }
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
            transition: background-color 0.3s ease, box-shadow 0.3s ease, color 0.3s ease;
        }

        body.dark .table-container {
            background: #1d2951;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            color: #e0e0e0;
        }

        body.dark .table-container h5 {
            color: #e0e0e0;
        }

        .table thead {
            background-color: #4a5f7f;
            color: white;
        }

        body.dark .table {
            color: #e0e0e0;
        }

        body.dark .table tbody tr {
            border-color: #3d5a80;
        }

        body.dark .table tbody tr:hover {
            background-color: #2a3f5f;
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
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        body.dark .search-box-main {
            background: #1d2951;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .search-box-main input {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.dark .search-box-main input {
            background-color: #0b1437;
            color: #e0e0e0;
            border-color: #3d5a80;
        }

        body.dark .search-box-main .input-group-text {
            background-color: #0b1437 !important;
            border-color: #3d5a80;
            color: #e0e0e0;
        }

        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
        }

        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .role-admin { background-color: #dc3545; color: white; }
        .role-manager { background-color: #007bff; color: white; }
        .role-employe { background-color: #28a745; color: white; }

        /* Mode sombre pour les modals */
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
            color: #e0e0e0;
            border-color: #3d5a80;
        }

        body.dark .form-control:focus,
        body.dark .form-select:focus {
            background-color: #0b1437;
            color: #e0e0e0;
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        body.dark .form-label {
            color: #e0e0e0;
        }

        body.dark h2,
        body.dark h5 {
            color: #e0e0e0;
        }

        body.dark .btn-close {
            filter: invert(1);
        }

        body.dark .dropdown-menu {
            background-color: #1d2951;
            border-color: #3d5a80;
        }

        body.dark .dropdown-item {
            color: #e0e0e0;
        }

        body.dark .dropdown-item:hover {
            background-color: #2a3f5f;
            color: #fff;
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
            <h2><i class="bi bi-people"></i> Gestion des Utilisateurs</h2>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i> Actions
                </button>
                <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#nouvelUtilisateurModal">
                        <i class="bi bi-person-plus"></i> Nouvel utilisateur
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportUsers()">
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
            <div class="col-md-3 mb-3">
                <div class="stat-card primary">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-total">0</div>
                            <div class="text-muted">Total Utilisateurs</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card success">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon success">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-employes">0</div>
                            <div class="text-muted">Employés</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon warning">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-managers">0</div>
                            <div class="text-muted">Managers</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card danger">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon danger">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-admins">0</div>
                            <div class="text-muted">Administrateurs</div>
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
                <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Rechercher par nom, email ou ID...">
            </div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn btn-dark filter-btn active" onclick="filterUsers('all', event)">
                <i class="bi bi-list"></i> Tous
            </button>
            <button class="btn btn-outline-success filter-btn" onclick="filterUsers('employe', event)">
                <i class="bi bi-person"></i> Employés
            </button>
            <button class="btn btn-outline-warning filter-btn" onclick="filterUsers('manager', event)">
                <i class="bi bi-person-badge"></i> Managers
            </button>
            <button class="btn btn-outline-danger filter-btn" onclick="filterUsers('admin', event)">
                <i class="bi bi-shield-check"></i> Admins
            </button>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <h5 class="mb-3">
                Tous les utilisateurs <span class="badge bg-secondary" id="total-users">0</span>
            </h5>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Prénom</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Département</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <tr><td colspan="8" class="text-center text-muted">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvel Utilisateur -->
    <div class="modal fade" id="nouvelUtilisateurModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="createUser()">
                        <i class="bi bi-check-circle"></i> Créer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modifier Utilisateur -->
    <div class="modal fade" id="modifierUtilisateurModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="updateUser()">
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