// DANS assets/js/gestion_categories.js

/////////////////////////////////////////////////////////////////////////////////////////////////////
const API_URL = 'http://localhost/smart-expense-management/api/admin.php'; 
let allCategories = [];

/////////////////////////////////////////////////////////////////////////////////////////////////////

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Gestion Catégories démarrée');
    loadCategories();
    document.getElementById('searchInput').addEventListener('input', function() {
        filterCategoriesBySearch(this.value);
    });
});

// --- Fonctions de chargement API ---

function loadCategories() {
    fetch(`${API_URL}?action=cat_get_categories`)
        .then(response => response.json())
        .then(data => {
            allCategories = data;
            displayCategories(data);
            
            // NOTE: stat-total est supprimé du HTML, mais on garde total-categories
            const totalCategoriesElement = document.getElementById('total-categories');
            if (totalCategoriesElement) {
                 totalCategoriesElement.textContent = data.length;
            }
        })
        .catch(error => {
            console.error('❌ Erreur categories:', error);
            showAlert('Erreur lors du chargement des catégories (API)', 'danger');
        });
}

// --- Fonctions d'affichage et d'action ---

function displayCategories(categories) {
    const tbody = document.getElementById('categories-tbody');
    const totalCategoriesElement = document.getElementById('total-categories');
    if (totalCategoriesElement) totalCategoriesElement.textContent = categories.length;
    
    // 🎯 Colspan ajusté à 3 (Nom, Description, Actions)
    const colspan = 3; 

    if (!Array.isArray(categories) || categories.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-5">Aucune catégorie trouvée</td></tr>`;
        return;
    }
    
    tbody.innerHTML = categories.map(c => `
        <tr>
            <td class="ps-4">${c.nom || 'N/A'}</td> <td>${c.description || '-'}</td>
            <td class="text-end pe-4">
                <button class="btn btn-action btn-edit" onclick='editCategorie(${c.id})' title="Modifier" data-bs-toggle="modal" data-bs-target="#modifierCategorieModal">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-action btn-delete" onclick='deleteCategorie(${c.id})' title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function filterCategoriesBySearch(searchTerm) {
    if (!searchTerm) {
        displayCategories(allCategories);
        return;
    }
    const filtered = allCategories.filter(c => {
        const nom = (c.nom || '').toLowerCase();
        const description = (c.description || '').toLowerCase();
        const id = String(c.id || '');
        const term = searchTerm.toLowerCase();
        return nom.includes(term) || description.includes(term) || id.includes(term);
    });
    displayCategories(filtered);
}

async function createCategorie() {
    const nom = document.getElementById('nom').value.trim();
    const description = document.getElementById('description').value.trim();
    if (!nom) {
        showAlert('Veuillez entrer un nom pour la catégorie', 'warning');
        return;
    }
    const formData = new FormData();
    formData.append('nom', nom);
    formData.append('description', description);
    try {
        const response = await fetch(`${API_URL}?action=cat_create`, { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) {
            const modalElement = document.getElementById('nouvelleCategorieModal');
            if (modalElement) {
                 bootstrap.Modal.getInstance(modalElement)?.hide();
            }
            document.getElementById('nouvelleCategorieForm').reset();
            loadCategories();
            showAlert('Catégorie créée avec succès', 'success');
        } else {
            showAlert(data.message || 'Erreur lors de la création', 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la création', 'danger');
    }
}

function editCategorie(id) {
    const categorie = allCategories.find(c => c.id == id);
    if (!categorie) return;
    
    document.getElementById('edit-id').value = categorie.id;
    document.getElementById('edit-nom').value = categorie.nom;
    document.getElementById('edit-description').value = categorie.description || '';
    
    new bootstrap.Modal(document.getElementById('modifierCategorieModal')).show();
}

async function updateCategorie() {
    const id = document.getElementById('edit-id').value;
    const nom = document.getElementById('edit-nom').value.trim();
    const description = document.getElementById('edit-description').value.trim();
    if (!nom) {
        showAlert('Veuillez entrer un nom pour la catégorie', 'warning');
        return;
    }
    const formData = new FormData();
    formData.append('id', id);
    formData.append('nom', nom);
    formData.append('description', description);
    try {
        const response = await fetch(`${API_URL}?action=cat_update`, { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) {
            const modalElement = document.getElementById('modifierCategorieModal');
            if (modalElement) {
                 bootstrap.Modal.getInstance(modalElement)?.hide();
            }
            loadCategories();
            showAlert('Catégorie modifiée avec succès', 'success');
        } else {
            showAlert(data.message || 'Erreur lors de la modification', 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la modification', 'danger');
    }
}

function deleteCategorie(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) return;
    const formData = new FormData();
    formData.append('id', id);
    fetch(`${API_URL}?action=cat_delete`, { method: 'POST', body: formData }) 
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCategories();
            showAlert('Catégorie supprimée', 'success');
        } else {
            showAlert(data.message || 'Erreur lors de la suppression', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la suppression', 'danger');
    });
}

// 🗑️ Les fonctions `refreshData` et `exportCategories` sont supprimées.

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}