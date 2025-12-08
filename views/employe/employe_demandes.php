<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-expense-management/views/auth/login.php');
    exit();

}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'includes/header.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$role = $_SESSION['role'] ?? 'employe';
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}
?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/demandes_employe.css">
<script src="<?= BASE_URL ?>assets/js/demandes_employe.js"></script>


        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="bi bi-receipt me-2"></i>Mes Demandes de Frais</h1>
                <p class="page-subtitle">Bienvenue, <?= htmlspecialchars($user_name) ?> - Gérez vos demandes</p>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addDemandeModal">
                <i class="bi bi-plus-circle me-2"></i>Ajouter une demande
            </button>
        </div>

        <!-- Barre de filtres -->
        <div class="filter-bar">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-search"></i> Rechercher</label>
                    <input type="text" class="form-control" id="filter-search" placeholder="Objet, lieu...">
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-flag"></i> Statut</label>
                    <select class="form-select" id="filter-statut">
                        <option value="">Tous</option>
                        <option value="En attente">En attente</option>
                        <option value="Validée Manager">Validée Manager</option>
                        <option value="Rejetée Manager">Rejetée Manager</option>
                        <option value="Approuvée Compta">Approuvée Compta</option>
                        <option value="Payée">Payée</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-calendar"></i> Date début</label>
                    <input type="date" class="form-control" id="filter-date-debut">
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="filter-label"><i class="bi bi-calendar"></i> Date fin</label>
                    <input type="date" class="form-control" id="filter-date-fin">
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-reset" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-header-left">
                    <i class="bi bi-list-ul"></i>
                    <h5>Toutes mes demandes</h5>
                    <span id="results-count" class="results-count"></span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Objet Mission</th>
                            <th>Lieu</th>
                            <th>Date Départ</th>
                            <th>Date Retour</th>
                            <th>Statut</th>
                            <th>Justificatif</th>
                            <th>Montant Total</th>
                            <th>Commentaire</th>
                            <th class="pe-4">Date Création</th>
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody">
                        <tr>
                            <td colspan="10">
                                <div class="loading-container">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Chargement de vos demandes...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Demande -->
    <div class="modal fade" id="addDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle Demande de Frais</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addDemandeForm">
                        <div class="mb-3">
                            <label for="objet_mission" class="form-label">Objet de la mission *</label>
                            <input type="text" class="form-control" id="objet_mission" name="objet_mission" required placeholder="Ex: Réunion client, Formation...">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lieu_deplacement" class="form-label">Lieu de déplacement *</label>
                                <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement" required placeholder="Ex: Paris, Lyon...">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_depart" class="form-label">Date de départ *</label>
                                <input type="date" class="form-control" id="date_depart" name="date_depart" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_retour" class="form-label">Date de retour *</label>
                                <input type="date" class="form-control" id="date_retour" name="date_retour" required>
                            </div>
                        </div>
                        <hr class="my-4">
                        <h6 class="mb-3" style="color: var(--text-primary);"><i class="bi bi-receipt-cutoff me-2"></i>Détails des frais</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type_frais" class="form-label">Type de frais *</label>
                                <select class="form-select" id="type_frais" name="type_frais" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Hébergement">Hébergement</option>
                                    <option value="Restauration">Restauration</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="montant" class="form-label">Montant (€) *</label>
                                <input type="number" step="0.01" class="form-control" id="montant" name="montant" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Détails supplémentaires..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="justificatif" class="form-label">Justificatif (PDF, Image)</label>
                            <input type="file" class="form-control" id="justificatif" name="justificatif" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Formats acceptés : PDF, JPG, PNG (Max 5MB)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Annuler</button>
                    <button type="button" class="btn btn-primary rounded-pill" onclick="submitDemande()"><i class="bi bi-check-circle me-1"></i> Enregistrer</button>
                </div>
            </div>
        </div>


    <!-- Modal prévisualisation image -->
    <div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview()">
        <span class="image-preview-close">&times;</span>
        <img id="previewImage" src="" alt="Prévisualisation">
    </div>


<?php require_once BASE_PATH . 'includes/footer.php'; ?>