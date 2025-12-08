<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';

require_once BASE_PATH . 'includes/header.php';
require_once BASE_PATH . 'includes/flash.php';

// --- Gestion des erreurs de soumission (si redirection depuis submit_demande.php) ---
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$message_feedback = $_SESSION['feedback_message'] ?? ($_GET['feedback'] ?? '');
$type_feedback = $_SESSION['feedback_type'] ?? ($_GET['type'] ?? 'info');
unset($_SESSION['feedback_message'], $_SESSION['feedback_type']);

// Données de catégories (Ces données doivent venir du Modèle/Contrôleur)
$categories = [
    ['id' => 1, 'nom' => 'Transport (Hors Carburant)'],
    ['id' => 2, 'nom' => 'Hébergement'],
    ['id' => 3, 'nom' => 'Restauration'],
    ['id' => 4, 'nom' => 'Carburant'],
    ['id' => 5, 'nom' => 'Péage / Parking'],
];

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/create_demande.css">
<div class="container-fluid p-4 main-content-bg">


    <div class="d-flex justify-content-between align-items-center mb-5">

        <?php displayFlash(); ?>
        <h1 class="fw-bold m-0 page-title"><i class="fa fa-file-invoice-dollar me-2 text-primary-themed"></i> Nouvelle Demande de Frais</h1>
        <a href="../dashboard.php" class="btn btn-secondary-outline fw-bold rounded-pill px-4 py-2">
            <i class="fa fa-arrow-left me-1"></i> Retour au Tableau de Bord
        </a>
    </div>

    <?php if ($message_feedback): ?>
        <div class="alert alert-<?= htmlspecialchars($type_feedback) ?> alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <?= htmlspecialchars($message_feedback) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($form_errors)): ?>
        <div class="alert alert-danger rounded-3 shadow mb-4">
            <h5 class="alert-heading"><i class="fa fa-exclamation-triangle me-2"></i> Erreur(s) de Validation :</h5>
            <ul>
                <?php foreach ($form_errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="demandeFraisForm" action="<?= BASE_URL ?>controllers/submit_demande.php" method="POST" enctype="multipart/form-data">

        <div class="card shadow-lg border-0 mb-5 form-section-card">
            <div class="card-header primary-bg-card-header text-white">
                <h5 class="mb-0 fw-bold"><i class="fa fa-map-marker-alt me-2"></i> Détails du déplacement</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="objet_mission" class="form-label fw-bold">Objet de la mission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="objet_mission" name="objet_mission" required maxlength="255" value="<?= htmlspecialchars($_POST['objet_mission'] ?? '') ?>" placeholder="Ex: Rendez-vous client / Conférence annuelle">
                    </div>
                    <div class="col-md-6">
                        <label for="lieu_deplacement" class="form-label fw-bold">Lieu du déplacement</label>
                        <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement" maxlength="150" value="<?= htmlspecialchars($_POST['lieu_deplacement'] ?? '') ?>" placeholder="Ville, Pays (Ex: Lyon, France)">
                    </div>
                    <div class="col-md-3">
                        <label for="date_depart" class="form-label fw-bold">Date de départ <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_depart" name="date_depart" required value="<?= htmlspecialchars($_POST['date_depart'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="date_retour" class="form-label fw-bold">Date de retour <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_retour" name="date_retour" required value="<?= htmlspecialchars($_POST['date_retour'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg border-0 mb-4 form-section-card expense-details-card">
            <div class="card-header light-green-bg-card-header text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa fa-receipt me-2 text-secondary-themed"></i> Détails des dépenses (Lignes de frais)</h5>
                <div class="d-flex align-items-center">
                    <div class="total-frais-display me-3 p-2 rounded-3 fw-bold shadow-sm">
                        Total provisoire : <span id="totalMontantDisplay" class="text-primary-themed">0,00 €</span>
                    </div>
                    <button type="button" class="btn btn-secondary-themed fw-bold btn-sm rounded-pill px-3 py-1" id="addDetailBtn" title="Ajouter une nouvelle ligne de frais">
                        <i class="fa fa-plus-circle me-1"></i> Ajouter une dépense
                    </button>
                </div>
            </div>
            <div class="card-body p-4">

                <p class="text-muted small mb-3"><i class="fa fa-info-circle me-1"></i> Saisissez chaque dépense séparément. Le justificatif est obligatoire pour la validation.</p>

                <div class="table-responsive">
                    <table class="table modern-table align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 12%;">Date</th>
                                <th scope="col" style="width: 18%;">Catégorie</th>
                                <th scope="col" style="width: 15%;">Montant (€)</th>
                                <th scope="col" style="width: 30%;">Description</th>
                                <th scope="col" style="width: 20%;">Justificatif</th>
                                <th scope="col" style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody">
                        </tbody>
                    </table>
                </div>

                <div id="noDetailsMessage" class="alert alert-light-green text-center mt-3 rounded-3 shadow-sm">
                    <i class="fa fa-arrow-up me-2"></i> Cliquez sur **"Ajouter une dépense"** pour commencer à lister vos frais.
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 mb-5">
            <button type="submit" class="btn btn-primary-themed btn-lg fw-bold submit-btn">
                <i class="fa fa-paper-plane me-2"></i> Soumettre la Demande de Frais pour Validation
            </button>
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const detailsTableBody = document.getElementById('detailsTableBody');
        const addDetailBtn = document.getElementById('addDetailBtn');
        const noDetailsMessage = document.getElementById('noDetailsMessage');
        const totalMontantDisplay = document.getElementById('totalMontantDisplay');

        const categories = <?= json_encode($categories) ?>;
        let detailCount = 0;

        function createCategorySelect() {
            let options = categories.map(cat =>
                `<option value="${cat.id}">${cat.nom}</option>`
            ).join('');

            return `
                <select class="form-select form-select-sm expense-input" name="details[\${index}][categorie_id]" required>
                    <option value="" disabled selected>Choisir Catégorie...</option>
                    ${options}
                </select>
            `;
        }

        function updateOverallTotal() {
            let total = 0;
            const montantInputs = detailsTableBody.querySelectorAll('.montant-input');

            montantInputs.forEach(input => {
                const value = parseFloat(input.value.replace(',', '.')) || 0;
                total += value;
            });

            totalMontantDisplay.textContent = total.toFixed(2).replace('.', ',') + ' €';
        }

        function addDetailRow() {
            const index = detailCount++;
            const categorySelectHtml = createCategorySelect();

            const newRow = detailsTableBody.insertRow();
            newRow.id = `row-${index}`;
            newRow.classList.add('fade-in-row', 'expense-row');

            newRow.innerHTML = `
                <td>
                    <input type="date" class="form-control form-control-sm expense-input" name="details[${index}][date_depense]" required>
                </td>
                <td>
                    ${categorySelectHtml.replace(/details\[\$\{index\}\]/g, `details[${index}]`)} 
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-end expense-input montant-input" name="details[${index}][montant]" placeholder="0,00" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm expense-input" name="details[${index}][description]" placeholder="Détail de la dépense" maxlength="255">
                </td>
           <td class="justificatif-cell text-center">
    <div class="d-flex flex-column align-items-center justify-content-center"> 
        <label for="justificatif_file_${index}" class="btn btn-outline-secondary btn-sm file-upload-btn rounded-pill">
            <i class="fa fa-upload me-1"></i> Choisir fichier
        </label>
        <input type="file" id="justificatif_file_${index}" class="form-control form-control-sm d-none expense-input justificatif-input" name="justificatif_file_${index}" accept=".pdf, image/*">
        <span id="filename_display_${index}" class="file-name-display small text-muted mt-1"></span>
    </div>
</td>
<td class="action-cell">
    <div class="d-flex justify-content-center align-items-center h-100">
        <button type="button" class="btn btn-danger-themed remove-detail-btn rounded-circle" data-row-id="row-${index}" title="Supprimer la dépense">
            <i class="bi bi-trash3-fill"></i> 
        </button>
    </div>
</td>
                `;

            const montantInput = newRow.querySelector('.montant-input');
            montantInput.addEventListener('input', updateOverallTotal);

            const fileInput = newRow.querySelector('.justificatif-input');
            const fileUploadBtn = newRow.querySelector('.file-upload-btn');
            const fileNameDisplay = document.getElementById(`filename_display_${index}`);

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const filename = this.files[0].name;
                    fileNameDisplay.textContent = filename.length > 15 ? filename.substring(0, 12) + '...' : filename;

                    fileUploadBtn.classList.replace('btn-outline-secondary', 'btn-outline-primary-themed'); // Vert
                    fileUploadBtn.innerHTML = '<i class="fa fa-check-circle me-1"></i> Fichier joint';
                } else {
                    fileNameDisplay.textContent = '';
                    fileUploadBtn.classList.replace('btn-outline-primary-themed', 'btn-outline-secondary');
                    fileUploadBtn.innerHTML = '<i class="fa fa-upload me-1"></i> Choisir fichier';
                }
            });

            updateNoDetailsMessage();
            updateOverallTotal();
        }

        function updateNoDetailsMessage() {
            if (detailsTableBody.children.length === 0) {
                noDetailsMessage.style.display = 'block';
                noDetailsMessage.classList.add('fade-in');
            } else {
                noDetailsMessage.style.display = 'none';
            }
        }

        addDetailBtn.addEventListener('click', addDetailRow);

        detailsTableBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-detail-btn')) {
                const button = e.target.closest('.remove-detail-btn');
                const rowId = button.getAttribute('data-row-id');
                const row = document.getElementById(rowId);
                if (row) {
                    row.remove();
                    updateNoDetailsMessage();
                    updateOverallTotal();
                }
            }
        });

        updateNoDetailsMessage();
        addDetailRow();
    });
</script>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>