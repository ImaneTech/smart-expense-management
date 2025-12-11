<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';

require_once BASE_PATH . 'Controllers/UserController.php';
require_once BASE_PATH . 'includes/header.php';
require_once BASE_PATH . 'includes/flash.php';

$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$message_feedback = $_SESSION['feedback_message'] ?? ($_GET['feedback'] ?? '');
$type_feedback = $_SESSION['feedback_type'] ?? ($_GET['type'] ?? 'info');
unset($_SESSION['feedback_message'], $_SESSION['feedback_type']);

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

<style>
    .main-content-bg {
        background-color: #f8f9fa;
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
</style>

<div class="mx-auto outer-container" style="max-width:1300px;background-color:#f8f9fa;border-radius:20px;padding:40px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
    <div class="mx-auto" style="max-width:1300px;">

        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
            <?php displayFlash(); ?>
            <h1 class="fw-bold m-0 page-title">
                <i class="fa fa-file-invoice-dollar me-2 text-primary-themed"></i> Demande de remboursement de frais
            </h1>
            <a href="../dashboard.php" class="btn btn-secondary-outline fw-bold rounded-pill px-4 py-2 mt-3 mt-md-0">
                <i class="fa fa-arrow-left me-1"></i> Retour au Tableau de Bord
            </a>
        </div>

        <?php if ($message_feedback): ?>
            <div class="alert alert-<?= htmlspecialchars($type_feedback) ?> alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <?= htmlspecialchars($message_feedback) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

        <form id="demandeFraisForm" action="<?= BASE_URL ?>Controllers/submit_demande.php" method="POST" enctype="multipart/form-data">

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
                                        value="<?= htmlspecialchars($_POST['objet_mission'] ?? '') ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="lieu_deplacement" class="form-label fw-bold">Lieu du déplacement</label>
                                    <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement"
                                        maxlength="150"
                                        placeholder="Ville, Pays (Ex: Lyon, France)"
                                        value="<?= htmlspecialchars($_POST['lieu_deplacement'] ?? '') ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="date_depart" class="form-label fw-bold">Date de départ <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_depart" name="date_depart"
                                        required value="<?= htmlspecialchars($_POST['date_depart'] ?? '') ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="date_retour" class="form-label fw-bold">Date de retour <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_retour" name="date_retour"
                                        required value="<?= htmlspecialchars($_POST['date_retour'] ?? '') ?>">
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
                                    <span id="totalMontantDisplay" class="text-primary-themed">0,00 €</span>
                                </div>
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
                                            <th style="width:15%;">Montant (€)</th>
                                            <th style="width:30%;">Description</th>
                                            <th style="width:20%;">Justificatif</th>
                                            <th style="width:5%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailsTableBody"></tbody>
                                </table>
                            </div>

                            <div id="noDetailsMessage" class="alert alert-light-green text-center mt-3 rounded-3 shadow-sm">
                                <i class="fa fa-arrow-up me-2"></i> Cliquez sur <b>"Ajouter une dépense"</b> pour commencer.
                            </div>
                        </div>

                    </div>
                </div>

            </div> <div class="d-grid gap-2 mt-4 mb-5">
                <button type="submit" class="btn btn-primary-themed btn-lg fw-bold py-3 submit-btn" style="font-size:1.25rem;">
                    <i class="fa fa-paper-plane me-2"></i> Soumettre la Demande de Frais
                </button>
            </div>
        </form>
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

        let detailCount = 0;

        function createCategorySelect(index) {
            return `
            <select class="form-select form-select-sm expense-input" name="details[${index}][categorie_id]" required>
                <option value="" disabled selected>Choisir...</option>
                ${categories.map(c => `<option value="${c.id}">${c.nom}</option>`).join('')}
            </select>`;
        }

        function updateOverallTotal() {
            let total = 0;
            detailsTableBody.querySelectorAll('.montant-input').forEach(input => {
                total += parseFloat(input.value.replace(',', '.')) || 0;
            });
            totalMontantDisplay.textContent = total.toFixed(2).replace('.', ',') + ' €';
        }

        function updateNoDetailsMessage() {
            noDetailsMessage.style.display = detailsTableBody.children.length === 0 ? 'block' : 'none';
        }

        function addDetailRow() {
            const index = detailCount++;
            const today = new Date().toISOString().split('T')[0];
            const newRow = detailsTableBody.insertRow();
            newRow.id = `row-${index}`;
            newRow.innerHTML = `
            <td><input type="date" class="form-control form-control-sm" name="details[${index}][date_depense]" required value="${today}"></td>
            <td>${createCategorySelect(index)}</td>
            <td><input type="number" step="0.01" class="form-control form-control-sm text-end montant-input" name="details[${index}][montant]" required></td>
            <td><input type="text" class="form-control form-control-sm" name="details[${index}][description]" maxlength="255"></td>
            <td><input type="file" class="form-control form-control-sm" name="details[${index}][justificatif]"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger-themed remove-detail-btn rounded-circle" data-row-id="row-${index}">
                    <i class="bi bi-trash3-fill"></i>
                </button>
            </td>
            `;
            newRow.querySelector('.montant-input').addEventListener('input', updateOverallTotal);

            updateNoDetailsMessage();
            updateOverallTotal();
        }

        addDetailBtn.addEventListener('click', addDetailRow);

        detailsTableBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-detail-btn')) {
                document.getElementById(e.target.closest('.remove-detail-btn').dataset.rowId)?.remove();
                updateNoDetailsMessage();
                updateOverallTotal();
            }
        });

        form.addEventListener('submit', function(e) {
            for (const sel of document.querySelectorAll('.expense-input')) {
                if (!sel.value) {
                    e.preventDefault();
                    alert('Veuillez sélectionner une catégorie pour chaque dépense.');
                    sel.focus();
                    return false;
                }
            }
        });

        addDetailRow();
    });
</script>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>
