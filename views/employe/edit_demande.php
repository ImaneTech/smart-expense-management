<?php
// Fichier : views/employe/edit_demande.php

// 1. Initialisation de la session et contrôle d'accès
if (session_status() === PHP_SESSION_NONE) session_start();

$allowed_roles = ['employe'];
$user_role = $_SESSION['role'] ?? 'guest';

$base_url = defined('BASE_URL') ? BASE_URL : '/';

if (!isset($_SESSION['user_id']) || !in_array($user_role, $allowed_roles)) {
    header('Location: ' . $base_url . 'views/auth/login.php');
    exit();
}

// 2. Configuration PHP et inclusions
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/DemandeController.php';
require_once BASE_PATH . 'includes/flash.php'; 

// 3. Récupération et préparation des données pour le formulaire
$user_id = (int)($_SESSION['user_id'] ?? 0);
$demande_id = (int)($_GET['id'] ?? 0);

$demande = null;
$details = [];
$errorMessage = '';
$isEditable = false;

// Fetch categories from database
$categoriesStmt = $pdo->query("SELECT id, nom FROM categories_frais ORDER BY nom");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

try {
    if ($demande_id === 0) {
        throw new Exception("ID de demande non fourni pour l'édition.");
    }
    
    if (!isset($pdo)) {
        throw new Exception("Erreur de connexion à la base de données (PDO non initialisé).");
    }

    $demandeController = new DemandeController($pdo);
    $data = $demandeController->getDemandeDetailsById($demande_id, $user_id);

    if (!$data) {
        throw new Exception("Demande introuvable ou non autorisée pour votre compte.");
    }
    
    $demande = $data['demande_frais'];
    $details = is_array($data['details_frais']) ? $data['details_frais'] : [];
    
    // Contrôle d'éligibilité à la modification (Seulement si en attente)
    $isEditable = ($demande['statut'] === 'En attente');

    if (!$isEditable) {
        throw new Exception("La demande #$demande_id est déjà « {$demande['statut']} » et ne peut plus être modifiée. Redirection vers la vue détails.");
    }

} catch (Exception $e) {
    // Si la demande n'est pas éditable ou si une erreur survient, redirige vers les détails ou la liste.
    if (strpos($e->getMessage(), 'ne peut plus être modifiée') !== false) {
        setFlash('info', $e->getMessage());
        header('Location: ' . $base_url . 'views/employe/details_demande.php?id=' . $demande_id);
        exit();
    }
    $errorMessage = $e->getMessage();
}

$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

// Inclusion de l'en-tête
require_once BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/create_demande.css">

<style>
    /* Styles pour la cohérence avec la vue de création/détails */
    .outer-container {
        max-width: 1300px;
        background-color: #f8f9fa;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .card {
        border-radius: 15px;
    }
    .card-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
    .table {
        border-radius: 10px;
    }
    .btn {
        border-radius: 10px;
    }
    .submit-btn {
        border-radius: 15px;
    }
    .remove-detail-btn {
        background-color: #dc3545; /* Rouge pour la suppression */
        color: #fff;
    }
    .remove-detail-btn:hover {
        background-color: #c82333;
        color: #fff;
    }
    .total-frais-display {
        background: #e6f7e6;
        border: 1px solid #c8e6c8;
    }
</style>

<div class="mx-auto outer-container">
    <div class="mx-auto" style="max-width:1300px;">

        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
            <?php displayFlash(); ?>
            <h1 class="fw-bold m-0 page-title">
                <i class="fa fa-pencil-square me-2 text-secondary-themed"></i> Modifier la Demande <span class="text-primary-themed">#<?= htmlspecialchars($demande_id) ?></span>
            </h1>
            <a href="<?= BASE_URL ?>views/employe/details_demande.php?id=<?= htmlspecialchars($demande_id) ?>" class="btn btn-secondary-outline fw-bold rounded-pill px-4 py-2 mt-3 mt-md-0">
                <i class="fa fa-arrow-left me-1"></i> Annuler / Retour aux Détails
            </a>
        </div>

        <?php if ($errorMessage && !$isEditable): ?>
            <div class="alert alert-danger rounded-3 shadow mb-4" role="alert">
                <h5 class="alert-heading"><i class="fa fa-exclamation-triangle me-2"></i> Erreur :</h5>
                <p class="mb-0"><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php elseif (!empty($form_errors)): ?>
            <div class="alert alert-danger rounded-3 shadow mb-4">
                <h5 class="alert-heading"><i class="fa fa-exclamation-triangle me-2"></i> Erreur(s) de Validation :</h5>
                <ul>
                    <?php foreach ($form_errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($demande && $isEditable): ?>
            <form id="demandeFraisForm" action="<?= BASE_URL ?>controllers/update_demande.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="demande_id" value="<?= htmlspecialchars($demande_id) ?>">

                <div class="d-flex flex-column gap-4">

                    <div>
                        <div class="card shadow-lg border-0">
                            <div class="card-header primary-bg-card-header text-white">
                                <h5 class="mb-0 fw-bold"><i class="fa fa-map-marker-alt me-2"></i> Détails du déplacement</h5>
                            </div>
                            <div class="card-body p-4">

                                <div class="row g-4">

                                    <div class="col-md-6">
                                        <label for="objet_mission" class="form-label fw-bold">Objet de la mission <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="objet_mission" name="objet_mission"
                                            required maxlength="255"
                                            placeholder="Ex: Rendez-vous client / Conférence annuelle"
                                            value="<?= htmlspecialchars($_POST['objet_mission'] ?? $demande['objet_mission'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="lieu_deplacement" class="form-label fw-bold">Lieu du déplacement</label>
                                        <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement"
                                            maxlength="150"
                                            placeholder="Ville, Pays (Ex: Lyon, France)"
                                            value="<?= htmlspecialchars($_POST['lieu_deplacement'] ?? $demande['lieu_deplacement'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="date_depart" class="form-label fw-bold">Date de départ <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date_depart" name="date_depart"
                                            required value="<?= htmlspecialchars($_POST['date_depart'] ?? $demande['date_depart'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="date_retour" class="form-label fw-bold">Date de retour <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date_retour" name="date_retour"
                                            required value="<?= htmlspecialchars($_POST['date_retour'] ?? $demande['date_retour'] ?? '') ?>">
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card shadow-lg border-0">
                            <div class="card-header light-green-bg-card-header text-dark d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-0 fw-bold"><i class="fa fa-receipt me-2 text-secondary-themed"></i> Détails des dépenses</h5>
                                <div class="d-flex align-items-center ms-auto">
                                    <div class="total-frais-display me-3 p-2 rounded-3 fw-bold shadow-sm">
                                        Total provisoire :
                                        <span id="totalMontantDisplay" class="text-primary-themed">0,00 <?= $currencySymbol ?></span> </div>
                                    <button type="button" class="btn btn-secondary-themed fw-bold btn-sm rounded-pill px-3 py-1" id="addDetailBtn">
                                        <i class="fa fa-plus-circle me-1"></i> Ajouter une dépense
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table class="table modern-table align-middle w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:12%;">Date</th>
                                                <th style="width:18%;">Catégorie</th>
                                                <th style="width:15%;">Montant (<?= $currencySymbol ?>)</th>
                                                <th style="width:30%;">Description</th>
                                                <th style="width:20%;">Justificatif</th>
                                                <th style="width:5%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailsTableBody">
                                            </tbody>
                                    </table>
                                </div>

                                <div id="noDetailsMessage" class="alert alert-light-green text-center mt-3 rounded-3 shadow-sm" style="display:none;">
                                    <i class="fa fa-arrow-up me-2"></i> Cliquez sur <b>"Ajouter une dépense"</b> pour commencer.
                                </div>
                            </div>

                        </div>
                    </div>

                </div> <div class="d-grid gap-2 mt-4 mb-5">
                    <button type="submit" class="btn btn-secondary-themed btn-lg fw-bold py-3 submit-btn" style="font-size:1.25rem;">
                        <i class="bi bi-pencil-square me-2"></i> Enregistrer les Modifications
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const detailsTableBody = document.getElementById('detailsTableBody');
        const addDetailBtn = document.getElementById('addDetailBtn');
        const noDetailsMessage = document.getElementById('noDetailsMessage');
        const totalMontantDisplay = document.getElementById('totalMontantDisplay');
        const form = document.getElementById('demandeFraisForm');
        const categories = <?= json_encode($categories) ?>;
        
        // Données existantes (PHP passe les détails dans une variable JS)
        const existingDetails = <?= json_encode($details) ?>;

        let detailCount = 0; // Compteur pour les NOUVELLES lignes (pour les index)

        function createCategorySelect(index, selectedId = null) {
            let options = categories.map(c => 
                `<option value="${c.id}" ${selectedId == c.id ? 'selected' : ''}>${c.nom}</option>`
            ).join('');

            return `
            <select class="form-select form-select-sm expense-input" name="details[${index}][categorie_id]" required>
                <option value="" disabled ${selectedId === null ? 'selected' : ''}>Choisir...</option>
                ${options}
            </select>`;
        }

        function updateOverallTotal() {
            let total = 0;
            // Utiliser le nom de l'input pour être sûr de ne compter que les montants
            detailsTableBody.querySelectorAll('input[name*="[montant]"]').forEach(input => {
                total += parseFloat(input.value.replace(',', '.')) || 0;
            });
            // Assurez-vous d'utiliser le bon symbole monétaire défini en PHP
            totalMontantDisplay.textContent = total.toFixed(2).replace('.', ',') + ' ' + CURRENCY_SYMBOL; 
        }

        function updateNoDetailsMessage() {
            noDetailsMessage.style.display = detailsTableBody.children.length === 0 ? 'block' : 'none';
        }
        
        // Fonction pour ajouter une ligne (pour les détails EXISTANTS ou NOUVEAUX)
        function addDetailRow(detail = {}) {
            const detailId = detail.id || detail.id_detail_frais;
            const index = detailId ? `existing-${detailId}` : `new-${detailCount++}`;
            const newRow = detailsTableBody.insertRow();
            newRow.id = `row-${index}`;
            
            // Si c'est un détail existant, on ajoute un champ caché pour l'ID
            const existingIdField = detailId 
                ? `<input type="hidden" name="details[${index}][id_detail_frais]" value="${detailId}">` 
                : '';
                
            const today = new Date().toISOString().split('T')[0];
            const dateValue = detail.date_depense ? detail.date_depense.substring(0, 10) : today;
            const montantValue = detail.montant ? parseFloat(detail.montant).toFixed(2) : '';
            const descriptionValue = detail.description ?? '';
            const justificatifPath = detail.justificatif_path ?? '';
            
            const fileInput = justificatifPath
                ? `<span class="d-block mb-1 text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Fichier actuel</span>
                   <input type="hidden" name="details[${index}][justificatif_path_old]" value="${justificatifPath}">
                   <input type="file" class="form-control form-control-sm" name="details[${index}][justificatif_new]" accept="image/*,.pdf">
                   <small class="text-muted">Laisser vide pour garder l'ancien.</small>`
                : `<input type="file" class="form-control form-control-sm" name="details[${index}][justificatif]" accept="image/*,.pdf">`;

            newRow.innerHTML = `
                <td>
                    ${existingIdField}
                    <input type="date" class="form-control form-control-sm" name="details[${index}][date_depense]" required value="${dateValue}">
                </td>
                <td>${createCategorySelect(index, detail.categorie_id)}</td>
                <td><input type="number" step="0.01" class="form-control form-control-sm text-end montant-input" name="details[${index}][montant]" required value="${montantValue}"></td>
                <td><input type="text" class="form-control form-control-sm" name="details[${index}][description]" maxlength="255" value="${descriptionValue}"></td>
                <td>${fileInput}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger remove-detail-btn rounded-circle" data-row-id="row-${index}" data-detail-id="${detailId ?? 0}">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </td>
            `;
            
            newRow.querySelector('.montant-input').addEventListener('input', updateOverallTotal);
            updateNoDetailsMessage();
            updateOverallTotal();
        }

        // 4. Initialisation : Charger les détails existants
        existingDetails.forEach(detail => addDetailRow(detail));
        
        // 5. Gestion des événements
        addDetailBtn.addEventListener('click', () => addDetailRow({})); // Ajout d'une nouvelle ligne

        detailsTableBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-detail-btn')) {
                const button = e.target.closest('.remove-detail-btn');
                const rowId = button.dataset.rowId;
                const detailId = button.dataset.detailId;
                
                if (detailId && detailId !== '0') {
                    // Pour une ligne existante, on la supprime visuellement et on ajoute un champ caché pour la suppression en BDD
                    if (confirm(`Voulez-vous vraiment supprimer cette ligne de dépense (ID: ${detailId}) ?`)) {
                        document.getElementById(rowId)?.remove();
                        
                        // Ajout d'un champ caché pour signaler au contrôleur la suppression
                        const deleteInput = document.createElement('input');
                        deleteInput.type = 'hidden';
                        deleteInput.name = 'details_to_delete[]';
                        deleteInput.value = detailId;
                        form.appendChild(deleteInput);
                        
                        updateNoDetailsMessage();
                        updateOverallTotal();
                    }
                } else {
                    // Pour une nouvelle ligne (non encore en BDD), on la supprime simplement
                    document.getElementById(rowId)?.remove();
                    updateNoDetailsMessage();
                    updateOverallTotal();
                }
            }
        });
        
        // Validation du formulaire (similaire à create_demande)
        form.addEventListener('submit', function(e) {
             // 1. Vérifier si au moins une dépense est présente
            if (detailsTableBody.children.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins une dépense avant de soumettre la demande.');
                return false;
            }
            
            // 2. Vérifier les catégories non sélectionnées
            for (const sel of detailsTableBody.querySelectorAll('.expense-input')) {
                if (!sel.value) {
                    e.preventDefault();
                    alert('Veuillez sélectionner une catégorie pour chaque dépense.');
                    sel.focus();
                    return false;
                }
            }
            // 3. Vérification des dates (date_depart <= date_retour) - Simplifiée, la validation complexe se fait dans le contrôleur
            const dateDepart = document.getElementById('date_depart').value;
            const dateRetour = document.getElementById('date_retour').value;
            
            if (dateDepart && dateRetour && new Date(dateDepart) > new Date(dateRetour)) {
                e.preventDefault();
                alert('La date de départ ne peut pas être après la date de retour.');
                return false;
            }
            
            // 4. Vérifier les montants vides ou zéro
            for (const input of detailsTableBody.querySelectorAll('.montant-input')) {
                 if (parseFloat(input.value) <= 0 || !input.value) {
                    e.preventDefault();
                    alert('Veuillez entrer un montant valide supérieur à zéro pour toutes les dépenses.');
                    input.focus();
                    return false;
                }
            }
        });
    });
</script>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>