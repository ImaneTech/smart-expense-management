// ==========================================================
// Fichier : assets/js/demandes_employe.js (CORRIGÉ & FINAL)
// But : Gérer le chargement (limité ou complet) et le filtrage des demandes de frais, 
//       et charger les statistiques des cartes.
// ==========================================================

// Définition de l'URL API en utilisant la variable globale BASE_URL injectée par le PHP.
const API_ROOT = (window.BASE_URL || '/smart-expense-management/').replace(/\/+$/, '/');
const API_URL = API_ROOT + 'api/employe.php'; 

// La variable allDemandes stockera le jeu de données complet non filtré (utilisé seulement pour la page de liste complète)
let allDemandes = [];
const COLSPAN_COUNT_TABLE = 6; 
document.addEventListener('DOMContentLoaded', function() {
    // Vérification de l'ID utilisateur
    if (typeof USER_ID === 'undefined' || isNaN(USER_ID) || USER_ID === 0) {
        console.error("ERREUR CRITIQUE: USER_ID non valide ou non défini.");
        return; 
    }
    
    const tbody = document.getElementById('demandes-tbody');

    // NOUVEAU: Charger les statistiques pour les cartes (uniquement si DEFAULT_ROW_LIMIT est défini, i.e., dashboard)
    if (typeof DEFAULT_ROW_LIMIT !== 'undefined') {
        loadDemandeStats(); 
    }

    // 1. Logique de chargement SÉPARÉE pour le Dashboard vs Liste Complète
    
    // Si DEFAULT_ROW_LIMIT existe (défini dans le HTML du dashboard), on charge les récentes.
    if (typeof DEFAULT_ROW_LIMIT !== 'undefined' && tbody) {
        loadRecentDemandes(DEFAULT_ROW_LIMIT); // Charge seulement 6 lignes
        
    } else if (tbody) {
        // Sinon (Page "Mes Demandes de Frais"), on charge toutes les demandes pour le filtrage côté client.
        loadAllDemandes(); 
    }
    
    // 2. Initialisation des écouteurs de filtres (utilisation des listeners pour appliquerFilters)
    const filterSearch = document.getElementById('filter-search');
    const filterStatut = document.getElementById('filter-statut');
    const filterDateDebut = document.getElementById('filter-date-debut');
    const filterDateFin = document.getElementById('filter-date-fin');

    // ******************************************************************
    // ** MODIFICATION CLÉ : Suppression des écouteurs pour le filtrage instantané **
    // ******************************************************************
    
    if (filterSearch) {
        // filterSearch.addEventListener('input', applyFilters); <--- DÉSACTIVÉ
        filterSearch.addEventListener('input', checkActiveFilters);
    }
    if (filterStatut) {
        // filterStatut.addEventListener('change', applyFilters); <--- DÉSACTIVÉ
        filterStatut.addEventListener('change', checkActiveFilters);
    }
    if (filterDateDebut) {
        // filterDateDebut.addEventListener('change', applyFilters); <--- DÉSACTIVÉ
        filterDateDebut.addEventListener('change', checkActiveFilters);
    }
    if (filterDateFin) {
        // filterDateFin.addEventListener('change', applyFilters); <--- DÉSACTIVÉ
        filterDateFin.addEventListener('change', checkActiveFilters);
    }
    
    // Nous conservons checkActiveFilters car il n'impacte pas les résultats,
    // mais gère seulement l'affichage du lien "Réinitialiser".
    checkActiveFilters();
    // Le filtrage sera maintenant déclenché UNIQUEMENT par l'appel `onclick="applyFilters()"` sur le bouton HTML.
});

// ==========================================================
// --- NOUVELLE FONCTION : Charger les statistiques pour les cartes ---
// ==========================================================

/**
 * Récupère le compte des demandes par statut pour les cartes de statistiques.
 * Nécessite l'implémentation de l'action 'getDemandeStats' dans l'API PHP.
 */
function loadDemandeStats() {
    const fullApiCall = `${API_URL}?action=getDemandeStats&user_id=${USER_ID}`;
    
    fetch(fullApiCall)
        .then(response => response.json())
        .then(data => {
            // L'API doit retourner : { success: true, stats: { en_attente: N1, validee: N2, rejetees: N3 } }
            
            if (data.success && data.stats) {
                const attente = data.stats.en_attente || 0;
                const validee = data.stats.validee || 0;
                const rejetees = data.stats.rejetees || 0;
                
                document.getElementById('stat-attente').textContent = attente;
                document.getElementById('stat-validees').textContent = validee;
                document.getElementById('stat-rejetees').textContent = rejetees;
                
                // console.log(`✅ Statistiques chargées: Attente=${attente}, Validées=${validee}, Rejetées=${rejetees}`);

            } else {
                console.error("❌ API 'getDemandeStats' n'a pas renvoyé le format de données attendu ou success est false.", data);
                // Optionnel: Afficher un message d'erreur ou laisser 0
            }
        })
        .catch(error => {
            console.error('❌ Erreur lors du chargement des statistiques:', error);
            // Optionnel: Afficher un message d'erreur
        });
}


// ==========================================================
// --- Fonctions de Chargement pour le Tableau de Bord (LIMITÉ) ---
// ==========================================================

/**
 * Récupère seulement les N demandes les plus récentes pour le tableau de bord.
 * Appelle l'API avec action=getRecentDemandes
 * @param {number} limit La limite de lignes à récupérer (6).
 */
function loadRecentDemandes(limit) {
    const tbody = document.getElementById('demandes-tbody');
    
    // Affichage du spinner
    if (tbody) {
         tbody.innerHTML = `<tr><td colspan="${COLSPAN_COUNT_TABLE}" class="text-center p-4">
             <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>
             <p class="mt-2 text-muted">Chargement des ${limit} dernières demandes...</p>
         </td></tr>`;
    }
    
    // Appel à la nouvelle action API pour le dashboard
    const fullApiCall = `${API_URL}?action=getRecentDemandes&user_id=${USER_ID}&limit=${limit}`;
    
    fetch(fullApiCall)
        .then(response => response.json())
        .then(data => {
            // Pas de stockage dans allDemandes ici car on ne filtre pas sur cette page.
            const recentDemandes = data.demandes || [];
            // Si la liste complète utilise aussi loadAllDemandes, displayDemandes doit gérer le comptage
            const resultsCount = document.getElementById('results-count');
            if (resultsCount) {
                // Pour le dashboard, on affiche juste "Voir tout" ou rien. On peut laisser vide ici.
                resultsCount.textContent = ``; 
            }
            displayDemandes(recentDemandes); 
        })
        .catch(error => {
            console.error('❌ Erreur lors du chargement des demandes récentes:', error);
             if (tbody) {
                 tbody.innerHTML = `<tr><td colspan="${COLSPAN_COUNT_TABLE}" class="text-center text-danger p-4">
                     <i class="bi bi-x-circle me-2"></i> Erreur de chargement des demandes récentes.
                 </td></tr>`;
             }
        });
}


// ==========================================================
// --- Fonctions de Chargement pour la Page Complète ---
// ==========================================================

/**
 * Récupère TOUTES les demandes de frais pour l'utilisateur via l'action API 'getDemandes'.
 */
function loadAllDemandes() {
    const tbody = document.getElementById('demandes-tbody');
    
    // Affichage du spinner
    if (tbody) {
         tbody.innerHTML = `<tr><td colspan="${COLSPAN_COUNT_TABLE}" class="text-center p-4">
             <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>
             <p class="mt-2 text-muted">Chargement de toutes vos demandes...</p>
         </td></tr>`;
    }
    
    // L'URL d'appel est construite pour l'action getDemandes (liste complète)
    const fullApiCall = `${API_URL}?action=getDemandes&user_id=${USER_ID}`;
    
    fetch(fullApiCall)
        .then(response => {
            if (!response.ok) {
                if (response.status === 404) {
                    throw new Error(`Erreur critique: API non trouvée à l'adresse ${fullApiCall}`);
                }
                return response.json().catch(() => Promise.reject(new Error(`Erreur HTTP: ${response.status}`)))
                                    .then(error => Promise.reject(error));
            }
            return response.json();
        })
        .then(data => {
            // L'API est configurée pour retourner : {success: true, demandes: Array(N)}
            allDemandes = data.demandes || [];
            
            // Affiche les données initiales (non filtrées)
            displayDemandes(allDemandes); 
        })
        .catch(error => {
            console.error('❌ Erreur lors du chargement des demandes:', error);
            const errorMessage = (error.message && error.message.includes('API non trouvée')) 
                               ? 'Impossible de trouver le fichier API. Vérifiez BASE_URL et le chemin d\'accès.' 
                               : ((error.error || error.message) || 'Erreur de connexion ou du serveur.');
            showAlert(`Erreur de chargement: ${errorMessage}`, 'danger');
            
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="${COLSPAN_COUNT_TABLE}" class="text-center text-danger">
                    <i class="bi bi-x-circle me-2"></i> ${errorMessage}
                </td></tr>`;
            }
        });
}

/**
 * Applique les filtres au tableau allDemandes et appelle displayDemandes.
 */
function applyFilters() {
    const searchTerm = document.getElementById('filter-search')?.value.toLowerCase() || '';
    const statutFilter = document.getElementById('filter-statut')?.value || '';
    const dateDebut = document.getElementById('filter-date-debut')?.value || '';
    const dateFin = document.getElementById('filter-date-fin')?.value || '';
    
    const filtered = allDemandes.filter(d => {
        const matchSearch = !searchTerm || 
            (d.objet_mission && d.objet_mission.toLowerCase().includes(searchTerm)) ||
            (d.lieu_deplacement && d.lieu_deplacement.toLowerCase().includes(searchTerm));
        const matchStatut = !statutFilter || d.statut === statutFilter;
        
        // La comparaison de date
        const matchDateDebut = !dateDebut || d.date_depart >= dateDebut;
        const matchDateFin = !dateFin || d.date_depart <= dateFin;
        
        return matchSearch && matchStatut && matchDateDebut && matchDateFin;
    });
    displayDemandes(filtered);
}

/**
 * Réinitialise tous les champs de filtre et affiche toutes les demandes.
 */
function resetFilters() {
    const search = document.getElementById('filter-search');
    if (search) search.value = '';

    const statut = document.getElementById('filter-statut');
    if (statut) statut.value = '';

    const dateDebut = document.getElementById('filter-date-debut');
    if (dateDebut) dateDebut.value = '';

    const dateFin = document.getElementById('filter-date-fin');
    if (dateFin) dateFin.value = '';
    
    displayDemandes(allDemandes);
    checkActiveFilters();
}

/**
 * Gère la visibilité du lien "Réinitialiser les filtres".
 */
function checkActiveFilters() {
    // Vérifie si les éléments de filtrage existent (pour ne pas crasher sur la page Dashboard)
    const searchTerm = document.getElementById('filter-search')?.value.trim();
    const statutFilter = document.getElementById('filter-statut')?.value;
    const dateDebut = document.getElementById('filter-date-debut')?.value;
    const dateFin = document.getElementById('filter-date-fin')?.value;

    // Affiche le lien de réinitialisation uniquement si au moins un filtre est actif ET si nous sommes sur la page qui a des filtres
    const isActive = (searchTerm !== '' || statutFilter !== '' || dateDebut !== '' || dateFin !== '') && 
                     !!document.getElementById('filter-search');
                     
    const resetLinkContainer = document.getElementById('reset-link-container');

    if (resetLinkContainer) {
        resetLinkContainer.style.visibility = isActive ? 'visible' : 'hidden';
    }
}


// ==========================================================
// --- Fonctions d'Affichage et Utilities (Mise à jour pour les badges light et le bouton d'action) ---
// ==========================================================

/**
 * Retourne le tag de statut personnalisé (couleurs claires/light).
 * @param {string} statut 
 * @returns {string} HTML du tag.
 */
function getStatusTag(statut) {
    let className = 'status-tag '; // Nouvelle classe CSS de statut
    let displayStatut = statut;

    switch (statut) {
        case 'En attente':
            className += 'status-attente';
            break;
        case 'Validée Manager':
        case 'Approuvée Compta':
        case 'Payée': // Mettre Payée et Validée/Approuvée dans le même groupe de couleur 'Approuvée' (vert clair)
            className += 'status-validee';
            // Optionnel: On peut ajuster le texte affiché pour les statuts internes plus longs
            displayStatut = (statut === 'Validée Manager' || statut === 'Approuvée Compta') ? 'Approuvée' : statut;
            break;
        case 'Rejetée Manager':
        case 'Rejetée Compta':
            className += 'status-rejetee';
            displayStatut = 'Rejetée'; // Simplifier le texte affiché
            break;
        default:
            className += 'bg-secondary text-white'; // Fallback Bootstrap
            break;
    }

    return `<span class="${className}">${displayStatut}</span>`;
}


function displayDemandes(demandes) {
    const tbody = document.getElementById('demandes-tbody');
    const resultsCount = document.getElementById('results-count');
    
    // Le comptage des résultats n'a de sens que sur la page de liste complète
    if (resultsCount) {
        // Seulement si le filtre existe (page de liste complète)
        if (document.getElementById('filter-search')) {
             resultsCount.textContent = `(${demandes.length} résultat${demandes.length > 1 ? 's' : ''})`;
        } else {
             resultsCount.textContent = ``; // Vide sur le dashboard
        }
    }
    
    if (!tbody) {
        return;
    }

    if (!Array.isArray(demandes) || demandes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${COLSPAN_COUNT_TABLE}"><div class="empty-state p-4 text-center">
            <i class="bi bi-inbox fs-2 text-muted"></i>
            <h5 class="mt-2">Aucune demande trouvée</h5>
            <p>Essayez de réinitialiser ou de modifier vos filtres.</p>
        </div></td></tr>`;
        return;
    }

    tbody.innerHTML = demandes.map(d => {
        const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('fr-FR') : '-';
        
        const statusHtml = getStatusTag(d.statut); 
        const montant = parseFloat(d.montant_total || 0); 
        const montantFormatted = montant.toFixed(2).replace('.', ','); 
        const demandeId = d.id || d.demande_id;

        // *** Modification: Utilisation de la nouvelle classe .action-btn-details ***
        const actionButton = demandeId ? 
            `<button onclick="showDemandeDetails('${demandeId}')" class="action-btn-details" title="Voir les détails">
                <i class="bi bi-eye"></i>
            </button>` : 
            `<span class="text-muted">-</span>`;
        // **************************************************************************
        
        return `<tr>
                    <td class="ps-4">${d.objet_mission || '-'}</td>
                    <td>${formatDate(d.date_depart)}</td>
                    <td>${formatDate(d.date_retour)}</td>
                    <td>${statusHtml}</td>
                    <td><strong class="text-primary">${montantFormatted} ${CURRENCY_SYMBOL}</strong></td>
                    <td class="pe-4 text-center">${actionButton}</td>
                </tr>`;
    }).join('');
}

/**
 * Redirige vers la page de détails de la demande.
 * @param {number|string} demandeId L'ID unique de la demande de frais.
 */
function showDemandeDetails(demandeId) {
    // Vérifier si l'ID est valide
    if (!demandeId || isNaN(demandeId)) {
        console.error("ID de demande invalide pour la redirection.");
        showAlert("Impossible de charger les détails : ID de demande manquant ou invalide.", 'danger');
        return;
    }

    // Récupérer la BASE_URL globale (définie dans le fichier PHP)
    const baseUrl = window.BASE_URL || '/';

    // Construire l'URL de redirection vers la page dédiée.
    // L'ID est passé comme paramètre GET (e.g., /views/employe/details_demande.php?id=123)
    const redirectionUrl = `/smart-expense-management/views/employe/details_demande.php?id=${demandeId}`;

    // Effectuer la redirection
    window.location.href = redirectionUrl;
    
    // Optionnel: Afficher un message pendant la redirection
    showAlert(`Redirection vers les détails de la demande #${demandeId}...`, 'info');
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 4000);
}