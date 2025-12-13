// Définition de l'URL de l'API
const API_URL = 'http://localhost/smart-expense-management/api/admin.php';
let currentFilter = 'all';
let allDemandes = []; // Store all fetched demands for local searching


const isDashboardView = !(
    window.location.pathname.toLowerCase().includes('liste_demandes.php') ||
    window.location.pathname.toLowerCase().includes('full_list.php')
);
const MAX_DASHBOARD_ROWS = 5;

const STATUT_BADGE_MAP = {
    'en_attente': { text: 'En attente', class: 'bg-warning text-dark' },
    'validee_manager': { text: 'Validée Manager', class: 'bg-success' },
    'rejetee': { text: 'Rejetée Manager', class: 'bg-danger' },
    'validee_admin': { text: 'Approuvée Compta', class: 'bg-primary' },
    'payee': { text: 'Payée', class: 'bg-info' }
};

document.addEventListener('DOMContentLoaded', function () {
    console.log(`Application démarrée. Mode: ${isDashboardView ? 'Dashboard Limité' : 'Liste Complète'}`);

    // Charger les stats uniquement si les éléments du Dashboard existent
    if (document.getElementById('stat-validees')) {
        loadStats();
    }

    loadDemandes();
    setupModalStatutMapping();

    // Add Search Listener
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            filterDemandesBySearch(e.target.value);
        });
    }
});

function filterDemandesBySearch(searchTerm) {
    if (!searchTerm) {
        displayDemandes(allDemandes);
        return;
    }

    const lowerTerm = searchTerm.toLowerCase();
    const filtered = allDemandes.filter(d => {
        const user = (d.utilisateur_nom || d.utilisateur || '').toLowerCase();
        const objet = (d.objet_mission || '').toLowerCase();
        const statut = (d.statut || '').toLowerCase();
        const montant = (d.montant_total || '').toString();

        return user.includes(lowerTerm) ||
            objet.includes(lowerTerm) ||
            statut.includes(lowerTerm) ||
            montant.includes(lowerTerm);
    });

    displayDemandes(filtered);
}

/**
 * Initialise les selects de statut dans les modals pour utiliser les clés standardisées.
 */
function setupModalStatutMapping() {
    const newStatutSelect = document.getElementById('statut');
    if (newStatutSelect) {
        newStatutSelect.innerHTML = Object.entries(STATUT_BADGE_MAP).map(([key, info]) => {
            const selected = (key === 'en_attente') ? 'selected' : '';
            return `<option value="${key}" ${selected}>${info.text}</option>`;
        }).join('');
    }

    const editStatutSelect = document.getElementById('edit_statut');
    if (editStatutSelect) {
        editStatutSelect.innerHTML = Object.entries(STATUT_BADGE_MAP).map(([key, info]) => {
            return `<option value="${key}">${info.text}</option>`;
        }).join('');
    }
}


// #region Data Loading
function loadStats() {
    if (!document.getElementById('stat-validees')) return;

    fetch(`${API_URL}?action=get_stats`)
        .then(response => response.json())
        .then(data => {
            const statValidees = document.getElementById('stat-validees');
            const statAttente = document.getElementById('stat-attente');
            const statRejetees = document.getElementById('stat-rejetees');

            if (statValidees) statValidees.textContent = data.validees_manager || 0;
            if (statAttente) statAttente.textContent = data.en_attente || 0;
            if (statRejetees) statRejetees.textContent = data.rejetees || 0;
        })
        .catch(error => {
            console.error('Erreur stats:', error);
            showAlert('Erreur lors du chargement des statistiques', 'danger');
        });
}
function loadDemandes(statut = null) {
    const loadingElement = document.querySelector('.loading');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }

    let url = `${API_URL}?action=get_demandes`;
    if (statut && statut !== 'all') {
        url += `&statut=${encodeURIComponent(statut)}`;
    }

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP Error! Status: ${response.status}`);
            }
            return response.text(); 
        })
        .then(text => {
            try {
                const data = JSON.parse(text); // Essayer de parser le texte en JSON
                allDemandes = data; // Save to global variable
                displayDemandes(data);
            } catch (e) {
                // Si le parsing JSON échoue, cela signifie que le serveur a renvoyé du texte HTML ou une erreur PHP.
                console.error('API RESPONSE TEXT:', text);
                throw new Error('La réponse de l\'API n\'est pas un JSON valide. Voir la console pour le texte brut de l\'erreur.');
            }

            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
        })
        .catch(error => {
            console.error(' Erreur demandes:', error);
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            // Affiche un message plus précis si l'erreur est liée au réseau ou au statut HTTP
            const errorMessage = error.message.includes('HTTP Error') ? `Erreur API: ${error.message}` : 'Erreur critique de l\'API. Voir console pour les détails.';
            showAlert(errorMessage, 'danger');
        });
}
// #endregion

// #region UI Functions
function displayDemandes(demandes) {
    const tbody = document.getElementById('demandes-tbody');
    const totalDemandes = document.getElementById('total-demandes');

    if (totalDemandes) totalDemandes.textContent = demandes.length;
    if (!tbody) return;

    let dataToDisplay = demandes;

    // LOGIQUE CLÉ : Limiter l'affichage à 5 lignes SEULEMENT si c'est la vue Dashboard
    if (isDashboardView) {
        dataToDisplay = demandes.slice(0, MAX_DASHBOARD_ROWS);
    }

    // CORRECTION Colspan : 6 cols pour Dashboard, 7 cols pour Liste Complète
    const colSpan = isDashboardView ? 6 : 7;

    if (!Array.isArray(dataToDisplay) || dataToDisplay.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center text-muted py-5">Aucune demande trouvée</td></tr>`;
        return;
    }

    tbody.innerHTML = dataToDisplay.map(d => {
        const formatDate = (dateStr) => {
            if (!dateStr) return '-';
            try {
                return dateStr.length === 10 ? new Date(dateStr).toLocaleDateString('fr-FR') : formatDateTime(dateStr);
            } catch (e) {
                return dateStr;
            }
        };

        const formatDateTime = (dateStr) => {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                if (isNaN(date)) return '-';
                return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return dateStr;
            }
        };

        const statusBadgeStyle = getBadgeStyle(d.statut);
        const montantTotal = parseFloat(d.montant_total || 0).toFixed(2);

        // Déterminer le contenu des colonnes selon la vue
        let userColumnClass = '';
        let actionColumnHtml = '';

        if (!isDashboardView) {
            // LISTE COMPLÈTE (7 colonnes, Actions incluses)
            userColumnClass = 'ps-4'; // L'utilisateur est la première colonne avec padding

            // Construction de l'URL de détails
            // On utilise un lien relatif simple car liste_demandes.php et details_demande.php sont dans le même dossier
            const detailsUrl = `details_demande.php?id=${d.id}`;

            actionColumnHtml = `
                <td class="text-center">
                    <a href="${detailsUrl}" class="btn-action-icon d-inline-flex align-items-center justify-content-center" style="color:white; background-color: var(--primary-color);" title="Voir détails">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            `;
        } else {
            // DASHBOARD (6 colonnes, Actions omises)
            userColumnClass = 'ps-4';
            actionColumnHtml = '';
        }

        // Structure de la ligne (7 <td> au total)
        return `
            <tr>
                <td class="${userColumnClass}">
                    <strong>${d.utilisateur_nom || d.utilisateur || '-'}</strong>
                </td>
                <td><div title="${d.objet_mission || '-'}">${d.objet_mission || '-'}</div></td>
                <td>${formatDate(d.date_depart)}</td>
                <td>${formatDate(d.date_retour)}</td>
                <td><span style="${statusBadgeStyle}">${d.statut || 'Inconnu'}</span></td>
                <td class="text-theme-primary fw-bold">${montantTotal} ${CURRENCY_SYMBOL}</td>
                ${actionColumnHtml}
            </tr>
        `;
    }).join('');
}

function getBadgeStyle(statut) {
    const base = "border-radius: 50px; padding: 8px 16px; font-weight: 700; font-size: 0.85rem; display: inline-block; border-width: 1px; border-style: solid; text-decoration: none; white-space: nowrap;";
    let colors = "background-color: #F5F5F5; color: #616161; border-color: #E0E0E0;";

    switch (statut) {
        case 'En attente':
        case 'en_attente':
            colors = "background-color: #FFF8E1; color: #F57F17; border-color: #FFE0B2;";
            break;
        case 'Validée Manager':
        case 'validee_manager':
        case 'Approuvée Compta':
        case 'validee_admin':
        case 'Validée': // Admin status
        case 'Validée Admin':
        case 'validee':
        case 'Payée':
        case 'payee':
            colors = "background-color: #E8F5E9; color: #2E7D32; border-color: #C8E6C9;";
            break;
        case 'Rejetée Manager':
        case 'rejetee':
        case 'Rejetée': // Admin status
        case 'Rejetée Admin':
            colors = "background-color: #FFEBEE; color: #C62828; border-color: #FFCDD2;";
            break;
    }
    return base + ' ' + colors;
}

function getStatutBadge(statutKey) {
 
    const style = getBadgeStyle(statutKey);
    return `<span style="${style}">${statutKey}</span>`;
}

function getActionButtons(id, statut) {
    if (isDashboardView) return '';

    return `<a href="details_demande.php?id=${id}" class="btn btn-info btn-sm text-white" title="Voir détails"><i class="bi bi-eye"></i></a>`;
}

function filterDemandes(statut, event) {
    currentFilter = statut;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
    loadDemandes(statut);
}

function collectFormData(formId, excludedFields = []) {
    const form = document.getElementById(formId);
    if (!form) return null;

    const formData = new FormData();
    const inputs = form.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        if (excludedFields.includes(input.id)) return;
        let value = input.value;
        if (input.type === 'number' && value !== '') {
            value = parseFloat(value);
        }
        if (value !== null && value !== undefined && value.toString().trim() !== '') {
            const name = input.id.replace('edit_', '');
            formData.append(name, value);
        }
    });
    return formData;
}

async function createDemande() {
    const formData = collectFormData('nouvelleDemandeForm');
    if (!formData) return;

    if (!formData.get('user_id') || !formData.get('objet_mission') || !formData.get('lieu_deplacement') || !formData.get('date_depart') || !formData.get('date_retour')) {
        showAlert('Veuillez remplir tous les champs obligatoires (*)', 'warning');
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=create`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            const modalElement = document.getElementById('nouvelleDemandeModal');
            if (modalElement) {
                bootstrap.Modal.getInstance(modalElement)?.hide();
            }
            document.getElementById('nouvelleDemandeForm').reset();
            loadStats();
            loadDemandes(currentFilter === 'all' ? null : currentFilter);
            showAlert('Demande créée avec succès !', 'success');
        } else {
            showAlert(data.message || 'Erreur lors de la création', 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la création', 'danger');
    }
}
// DANS dashboard_admin.js

async function updateDemande() {
    const id = document.getElementById('edit_demande_id').value;
    if (!id) {
        showAlert('ID de la demande manquant', 'danger');
        return;
    }

    const formData = collectFormData('modifierDemandeForm');
    if (!formData) return;

    formData.append('id', id);

    
    if (!formData.get('objet_mission') || !formData.get('lieu_deplacement') || !formData.get('date_depart') || !formData.get('date_retour')) {
        showAlert('Veuillez remplir tous les champs obligatoires (*)', 'warning');
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=update_demande`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            const modalElement = document.getElementById('modifierDemandeModal');
            if (modalElement) {
                bootstrap.Modal.getInstance(modalElement)?.hide();
            }
            loadStats();
            loadDemandes(currentFilter === 'all' ? null : currentFilter);
            showAlert('Demande modifiée avec succès !', 'success');
        } else {
            showAlert(data.message || 'Erreur lors de la modification', 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la modification', 'danger');
    }
}

function editDemande(id) {


    fetch(`${API_URL}?action=get_demande_by_id&id=${id}`)
        .then(response => response.json())
        .then(data => {


            if (!data || !data.id) {
                showAlert('Demande introuvable', 'danger');
                return;
            }

            const modalElement = document.getElementById('modifierDemandeModal');
            if (modalElement) {
                document.getElementById('edit_demande_id').value = data.id;
                const editUserIdField = document.getElementById('edit_user_id');
                if (editUserIdField) {
                    editUserIdField.value = data.user_id || '';
                }

                document.getElementById('edit_objet_mission').value = data.objet_mission || '';
                document.getElementById('edit_lieu_deplacement').value = data.lieu_deplacement || '';
                document.getElementById('edit_date_depart').value = data.date_depart ? data.date_depart.slice(0, 10) : '';
                document.getElementById('edit_date_retour').value = data.date_retour ? data.date_retour.slice(0, 10) : '';

                document.getElementById('edit_statut').value = data.statut || 'en_attente';

                document.getElementById('edit_manager_id_validation').value = data.manager_id_validation || '';
                document.getElementById('edit_montant_total').value = data.montant_total || 0.00;
                document.getElementById('edit_commentaire_manager').value = data.commentaire_manager || '';

                if (data.date_traitement) {
                    document.getElementById('edit_date_traitement').value = data.date_traitement.slice(0, 16).replace(' ', 'T');
                } else {
                    document.getElementById('edit_date_traitement').value = '';
                }

                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                showAlert('Erreur : Modal de modification manquant dans le HTML.', 'danger');
            }
        })
        .catch(error => {
            console.error('❌ Erreur:', error);
            showAlert('Erreur lors du chargement des données. (Vérifiez les données API ou le Modal)', 'danger');
        });
}

function deleteDemande(id) {
    if (!confirm('Supprimer cette demande ? Toutes les lignes de frais associées seront également supprimées.')) return;

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
                loadDemandes(currentFilter === 'all' ? null : currentFilter);
                showAlert('Demande supprimée', 'success');
            } else {
                showAlert(data.message || 'Erreur lors de la suppression', 'danger');
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
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 alert-custom`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}