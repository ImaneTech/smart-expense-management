<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php'; 



require_once BASE_PATH . 'includes/header.php';

// --- Gestion des erreurs de soumission (si redirection depuis submit_demande.php) ---
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$message_feedback = $_SESSION['feedback_message'] ?? ($_GET['feedback'] ?? '');
$type_feedback = $_SESSION['feedback_type'] ?? ($_GET['type'] ?? 'info');
unset($_SESSION['feedback_message'], $_SESSION['feedback_type']);

// Données de catégories (Ces données doivent venir du Modèle/Contrôleur)
// **NOTE : REMPLACER CES DONNÉES STATIQUES PAR UN APPEL À $employeController->getCategories()**
$categories = [
    ['id' => 1, 'nom' => 'Transport (Hors Carburant)'],
    ['id' => 2, 'nom' => 'Hébergement'],
    ['id' => 3, 'nom' => 'Restauration'],
    ['id' => 4, 'nom' => 'Carburant'],
    ['id' => 5, 'nom' => 'Péage / Parking'],
];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/create_demande.css">

<div class="container-fluid p-4 main-content-bg">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold m-0 page-title">Nouvelle Demande de Frais</h1>
        <a href="dashboard.php" class="btn btn-primary-outline fw-bold rounded-pill px-4 py-2">
            <i class="fa fa-arrow-left me-1"></i> Retour au Tableau de Bord
        </a>
    </div>

    <?php if ($message_feedback): ?>
        <div class="alert alert-<?= htmlspecialchars($type_feedback) ?> alert-dismissible fade show rounded-3" role="alert">
            <?= htmlspecialchars($message_feedback) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($form_errors)): ?>
        <div class="alert alert-danger rounded-3 shadow mb-4">
            <h5 class="alert-heading">Erreur(s) de Validation :</h5>
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
                <h5 class="mb-0 fw-bold">Détails du déplacement</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="objet_mission" class="form-label fw-bold">Objet de la mission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="objet_mission" name="objet_mission" required maxlength="255" value="<?= htmlspecialchars($_POST['objet_mission'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="lieu_deplacement" class="form-label fw-bold">Lieu du déplacement</label>
                        <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement" maxlength="150" value="<?= htmlspecialchars($_POST['lieu_deplacement'] ?? '') ?>">
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

        <div class="card shadow-lg border-0 mb-4 form-section-card">
            <div class="card-header secondary-bg-card-header text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Détails des dépenses (Lignes de frais)</h5>
                <button type="button" class="btn btn-warning-themed fw-bold btn-sm" id="addDetailBtn">
                    <i class="fa fa-plus-circle me-1"></i> Ajouter une dépense
                </button>
            </div>
            <div class="card-body p-4">

                <p class="text-muted small">Veuillez saisir chaque dépense séparément et joindre le justificatif associé.</p>

                <div class="table-responsive">
                    <table class="table modern-table table-borderless align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 12%;">Date</th>
                                <th scope="col" style="width: 18%;">Catégorie</th>
                                <th scope="col" style="width: 15%;">Montant (€)</th>
                                <th scope="col" style="width: 30%;">Description</th>
                                <th scope="col" style="width: 20%;">Justificatif</th>
                                <th scope="col" style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody">
                            </tbody>
                    </table>
                </div>

                <div id="noDetailsMessage" class="alert alert-info text-center mt-3 rounded-3 shadow-sm">
                    Cliquez sur "Ajouter une dépense" pour commencer.
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 mb-5">
            <button type="submit" class="btn btn-primary-themed btn-lg fw-bold submit-btn">
                <i class="fa fa-check-circle me-2"></i> Soumettre la Demande de Frais pour Validation
            </button>
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const detailsTableBody = document.getElementById('detailsTableBody');
        const addDetailBtn = document.getElementById('addDetailBtn');
        const noDetailsMessage = document.getElementById('noDetailsMessage');

        // Les catégories sont injectées par PHP (corrigé)
        const categories = <?= json_encode($categories) ?>;
        let detailCount = 0;

        // Créer la structure HTML du SELECT des catégories (corrigé pour être entièrement JS)
        function createCategorySelect() {
            let options = categories.map(cat =>
                `<option value="${cat.id}">${cat.nom}</option>`
            ).join('');

            return `
                <select class="form-select form-select-sm" name="details[\${index}][categorie_id]" required>
                    <option value="">Sélectionner...</option>
                    ${options}
                </select>
            `;
        }

        // Ajouter une nouvelle ligne de dépense
        function addDetailRow() {
            const index = detailCount++;

            const categorySelectHtml = createCategorySelect(index); // Appeler la fonction de création

            const newRow = detailsTableBody.insertRow();
            newRow.id = `row-${index}`;
            newRow.classList.add('fade-in-row');

            newRow.innerHTML = `
            <td>
                <input type="date" class="form-control form-control-sm" name="details[${index}][date_depense]" required>
            </td>
            <td>
                ${categorySelectHtml.replace(/details\[\$\{index\}\]/g, `details[${index}]`)} 
                </td>
            <td>
                <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="details[${index}][montant]" placeholder="0.00" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="details[${index}][description]" placeholder="Détail de la dépense" maxlength="255">
            </td>
            <td>
                <input type="file" class="form-control form-control-sm" name="justificatif_file_${index}" accept=".pdf, image/*">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger-themed btn-sm remove-detail-btn rounded-circle" data-row-id="row-${index}" title="Supprimer">
                    <i class="fa fa-times"></i>
                </button>
            </td>
            `;

            updateNoDetailsMessage();
        }

        // Mise à jour de l'affichage du message "Aucune dépense"
        function updateNoDetailsMessage() {
            if (detailsTableBody.children.length === 0) {
                noDetailsMessage.style.display = 'block';
            } else {
                noDetailsMessage.style.display = 'none';
            }
        }

        // Gestionnaire d'événement pour ajouter une ligne
        addDetailBtn.addEventListener('click', addDetailRow);

        // Gestionnaire d'événement pour supprimer une ligne
        detailsTableBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-detail-btn')) {
                const button = e.target.closest('.remove-detail-btn');
                const rowId = button.getAttribute('data-row-id');
                const row = document.getElementById(rowId);
                if (row) {
                    row.remove();
                    updateNoDetailsMessage();
                }
            }
        });

        // Initialisation
        updateNoDetailsMessage();
        addDetailRow(); // Ajoute une première ligne par défaut
    });
</script>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>