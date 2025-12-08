/////////////////////////////////////////////////////////////////////////////////////////////////////////
// CORRECTION CLÉ : Utiliser l'URL du point d'entrée API unique
const API_URL = 'http://localhost/smart-expense-management/api/employe.php'; 
/////////////////////////////////////////////////////////////////////////////////////////////////////////
const UPLOADS_URL = 'http://localhost/smart-expense-management/uploads/';
const USER_ID = parseInt("<?= $user_id ?>");
let allDemandes = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Liste des Demandes Employé - User ID:', USER_ID);
    loadAllDemandes();
    
    // Initialisation des écouteurs de filtres (Vérification implicite de l'existence des éléments)
    document.getElementById('filter-search').addEventListener('input', applyFilters);
    document.getElementById('filter-statut').addEventListener('change', applyFilters);
    document.getElementById('filter-date-debut').addEventListener('change', applyFilters);
    document.getElementById('filter-date-fin').addEventListener('change', applyFilters);
});

function loadAllDemandes() {
    // Utilisation de la nouvelle API_URL et de l'action correcte
    fetch(`${API_URL}?action=get_all_user_demandes&user_id=${USER_ID}`)
        .then(response => {
             // Tenter de lire le corps même en cas d'erreur HTTP pour obtenir le message JSON
            if (!response.ok) {
                return response.json().then(error => Promise.reject(error));
            }
            return response.json();
        })
        .then(data => {
            allDemandes = data;
            displayDemandes(allDemandes);
        })
        .catch(error => {
            console.error('❌ Erreur demandes:', error);
            // Afficher le message d'erreur si disponible
            const errorMessage = error.message || 'Erreur lors du chargement des demandes';
            showAlert(errorMessage, 'danger');
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
    
    // Mise à jour du compteur
    if (resultsCount) {
        resultsCount.textContent = `(${demandes.length} résultat${demandes.length > 1 ? 's' : ''})`;
    }
    
    if (!tbody) {
        console.error("Le corps du tableau (demandes-tbody) est introuvable.");
        return;
    }

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
        // Utilisation de la fusion nulle (??) n'est pas nécessaire ici car le ternaire gère le null/falsey
        const commentaire = d.commentaire_manager ? (d.commentaire_manager.length > 30 ? `<span title="${d.commentaire_manager}">${d.commentaire_manager.substring(0, 30)}...</span>` : d.commentaire_manager) : '<span class="text-muted">-</span>';
        const justificatifHTML = getJustificatifHTML(d.justificatif);
        
        return `<tr><td class="ps-4"><strong>${d.id}</strong></td><td>${d.objet_mission || '-'}</td><td>${d.lieu_deplacement || '-'}</td><td>${formatDate(d.date_depart)}</td><td>${formatDate(d.date_retour)}</td><td>${getStatutBadge(d.statut)}</td><td>${justificatifHTML}</td><td><strong class="text-primary">${parseFloat(d.montant_total || 0).toFixed(2)} €</strong></td><td><small>${commentaire}</small></td><td class="pe-4">${formatDateTime(d.created_at)}</td></tr>`;
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
    if (img) img.src = imagePath;
    if (modal) modal.classList.add('show');
}

function closeImagePreview() {
    const modal = document.getElementById('imagePreviewModal');
    if (modal) modal.classList.remove('show');
}

// Fonction d'alerte (sans les styles CSS/HTML associés)
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}