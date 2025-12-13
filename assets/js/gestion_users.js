// DANS assets/js/gestion_users.js

/////////////////////////////////////////////////////////////////////////////////////////////////////
// 🎯 CORRECTION: Utiliser l'API centrale admin.php
const API_URL = 'http://localhost/smart-expense-management/api/admin.php';
let currentFilter = 'all';
let allUsers = [];
//////////////////////////////////////////////////////////////////////////////////////////////////////////

document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Gestion Utilisateurs démarrée');
    loadStats();
    loadUsers();

    document.getElementById('searchInput').addEventListener('input', function () {
        filterUsersBySearch(this.value);
    });
});

// --- Fonctions de chargement API ---

function loadStats() {
    // 🎯 Action: user_get_stats
    fetch(`${API_URL}?action=user_get_stats`)
        .then(response => response.json())
        .then(data => {
            const total = document.getElementById('stat-total');
            if (total) total.textContent = data.total || 0;
            const employes = document.getElementById('stat-employes');
            if (employes) employes.textContent = data.employes || 0;
            const managers = document.getElementById('stat-managers');
            if (managers) managers.textContent = data.managers || 0;
            const admins = document.getElementById('stat-admins');
            if (admins) admins.textContent = data.admins || 0;
        })
        .catch(error => {
            console.error('❌ Erreur stats:', error);
            showAlert('Erreur lors du chargement des statistiques (API)', 'danger');
        });
}

function loadUsers(role = null) {
    // 🎯 Action: user_get_users
    let url = `${API_URL}?action=user_get_users`;
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
            showAlert('Erreur lors du chargement des utilisateurs (API)', 'danger');
        });
}

// --- Fonctions d'affichage et d'action ---

function displayUsers(users) {
    const tbody = document.getElementById('users-tbody');
    const totalUsersElement = document.getElementById('total-users');
    if (totalUsersElement) totalUsersElement.textContent = users.length;

    const colspan = 7;

    if (!Array.isArray(users) || users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted">Aucun utilisateur trouvé</td></tr>`;
        return;
    }

    tbody.innerHTML = users.map(u => `
        <tr>
            <td class="ps-4">${u.first_name || 'N/A'}</td> 
            <td>${u.last_name || 'N/A'}</td>
            <td>${u.email || 'N/A'}</td>
            <td>${u.phone || 'N/A'}</td>
            <td>${u.department || 'N/A'}</td>
            <td>${getRoleBadge(u.role)}</td>
            <td class="text-end pe-4">
                <button class="btn btn-action btn-delete" onclick='deleteUser(${u.id})' title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function getRoleBadge(role) {
    const badges = {
        'admin': '<span class="badge-theme badge-reject">Admin</span>', // Light Red
        'manager': '<span class="badge-theme badge-wait">Manager</span>', // Light Orange
        'employe': '<span class="badge-theme badge-valid">Employé</span>' // Light Green
    };
    return badges[role] || `<span class="badge-theme bg-light text-dark">${role}</span>`;
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

// 🗑️ L'ancienne fonction `createUser` est supprimée.

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

    const editPassword = document.getElementById('edit-password');
    const editConfirmPassword = document.getElementById('edit-confirmPassword');
    if (editPassword) editPassword.value = '';
    if (editConfirmPassword) editConfirmPassword.value = '';

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

    const passwordField = document.getElementById('edit-password');
    const confirmPasswordField = document.getElementById('edit-confirmPassword');

    const password = passwordField ? passwordField.value : '';
    const confirmPassword = confirmPasswordField ? confirmPasswordField.value : '';

    if (!first_name || !last_name || !email || !phone || !department || !role) {
        showAlert('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }

    if (password || confirmPassword) {
        if (password !== confirmPassword) {
            showAlert('Les nouveaux mots de passe ne correspondent pas', 'danger');
            return;
        }
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('first_name', first_name);
    formData.append('last_name', last_name);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('department', department);
    formData.append('role', role);

    if (password) {
        formData.append('password', password);
    }

    try {
        // 🎯 Action: user_update
        const response = await fetch(`${API_URL}?action=user_update`, {
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
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        background: '#fff5f5', 
        customClass: {
            popup: 'rounded-4 shadow-lg border border-danger', 
            title: 'fw-bold text-danger',
            confirmButton: 'btn btn-danger rounded-pill px-5 py-3 fs-5 me-3 fw-bold shadow-sm', 
            cancelButton: 'btn btn-secondary rounded-pill px-5 py-3 fs-5 fw-bold shadow-sm'
        },
        buttonsStyling: false,
        iconColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);

            fetch(`${API_URL}?action=user_delete`, { 
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh handled by reload to show flash message if set, 
                    // OR we can just reload the table and show a toast.
                    // For consistency with "flash", we usually want a reload if the API sets a session flash.
                    // But here loadStats() and loadUsers() are AJAX.
                    // Let's stick to the pattern: Show Success Alert matching Flash style.
                    
                    loadStats();
                    loadUsers();
                    
                    // Show success message using SweetAlert (mimicking Flash)
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: '✅ Utilisateur supprimé avec succès !',
                        showConfirmButton: false,
                        showCloseButton: true,
                        timer: null,
                        toast: true,
                        background: '#e8f5e9',
                        color: '#1b5e20',
                         customClass: {
                            popup: 'mt-5'
                        }
                    });
                    
                } else {
                    showAlert(data.message || 'Erreur lors de la suppression', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('Erreur lors de la suppression', 'danger');
            });
        }
    });
}

// 🗑️ L'ancienne fonction `refreshData` est supprimée.

function exportUsers() {
    // 🎯 Action: user_export
    window.location.href = `${API_URL}?action=user_export`;
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