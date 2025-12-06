
const API_URL = 'http://localhost/smart-expense-management/api.php';
let currentFilter = 'all';
let selectedFile = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Application démarrée');
    loadStats();
    loadDemandes();
    setupFileUpload();
});

// Configuration de l'upload drag & drop
function setupFileUpload() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('justificatif');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');

    uploadZone.addEventListener('click', () => fileInput.click());

    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('drag-over');
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('drag-over');
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });
}

function handleFile(file) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];

    if (!allowedTypes.includes(file.type)) {
        showAlert('Type de fichier non autorisé. Utilisez PDF, JPG ou PNG.', 'danger');
        return;
    }

    if (file.size > maxSize) {
        showAlert('Fichier trop volumineux (max 5MB)', 'danger');
        return;
    }

    selectedFile = file;
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('filePreview').style.display = 'block';
}

function removeFile() {
    selectedFile = null;
    document.getElementById('justificatif').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

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
        url += `&statut=${statut}`;
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
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Aucune demande trouvée</td></tr>';
        return;
    }

    tbody.innerHTML = demandes.map(d => {
        let dateFormatted = 'Date invalide';
        try {
            if (d.date) {
                const dateObj = new Date(d.date.replace(' ', 'T'));
                dateFormatted = dateObj.toLocaleDateString('fr-FR');
            }
        } catch (e) {
            console.error('Erreur format date:', e);
        }

        // Gestion du justificatif
        let justificatifBtn = '<span class="text-muted">-</span>';
        if (d.justificatif) {
            justificatifBtn = `<button class="btn btn-sm btn-info" onclick="viewJustificatif('${d.justificatif}')" title="Voir le justificatif">
                <i class="bi bi-eye"></i>
            </button>`;
        }

        return `
            <tr>
                <td>${d.id || 'N/A'}</td>
                <td>${d.utilisateur || 'N/A'}</td>
                <td>${d.objectif || 'N/A'}</td>
                <td>${dateFormatted}</td>
                <td>${parseFloat(d.montant_total || 0).toFixed(2)} €</td>
                <td>${justificatifBtn}</td>
                <td>${getStatutBadge(d.statut)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        ${getActionButtons(d.id, d.statut)}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function viewJustificatif(filename) {
    window.open(`<?= BASE_URL ?>uploads/${filename}`, '_blank');
}

function getStatutBadge(statut) {
    const badges = {
        'en_attente': '<span class="badge bg-warning text-dark">En attente</span>',
        'validee_manager': '<span class="badge bg-success">Validée Manager</span>',
        'validee_admin': '<span class="badge bg-primary">Validée Admin</span>',
        'rejetee': '<span class="badge bg-danger">Rejetée</span>'
    };
    return badges[statut] || `<span class="badge bg-secondary">${statut}</span>`;
}

function getActionButtons(id, statut) {
    let buttons = '';
    if (statut === 'en_attente') {
        buttons += `<button class="btn btn-success btn-sm" onclick="updateStatus(${id}, 'validee_manager')" title="Valider">
            <i class="bi bi-check"></i>
        </button>`;
        buttons += `<button class="btn btn-danger btn-sm" onclick="updateStatus(${id}, 'rejetee')" title="Rejeter">
            <i class="bi bi-x"></i>
        </button>`;
    }
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
    const utilisateur = document.getElementById('utilisateur').value.trim();
    const objectif = document.getElementById('objectif').value.trim();
    const montant = document.getElementById('montant').value;

    if (!utilisateur || !objectif) {
        showAlert('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('utilisateur', utilisateur);
    formData.append('objectif', objectif);
    formData.append('montant', montant);

    // Ajouter le justificatif s'il existe
    if (selectedFile) {
        formData.append('justificatif', selectedFile);
    }

    try {
        const response = await fetch(`${API_URL}?action=create`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('nouvelleDemandeModal')).hide();
            document.getElementById('nouvelleDemandeForm').reset();
            removeFile();
            loadStats();
            loadDemandes();
            showAlert('Demande créée avec succès', 'success');
        } else {
            showAlert('Erreur lors de la création', 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la création', 'danger');
    }
}

function deleteDemande(id) {
    if (!confirm('Supprimer cette demande ?')) return;

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
