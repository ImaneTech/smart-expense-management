<?php
session_start();
// Vérification d'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-expense-management/views/auth/login.php');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
// Inclusions de configuration et d'entête
require_once __DIR__ . '/../../config.php';
// L'inclusion de header.php doit normalement ouvrir <body>
require_once BASE_PATH . 'includes/header.php'; 

// Récupération des données utilisateur de la session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$role = $_SESSION['role'] ?? 'employe';

// Définition de la constante BASE_URL si elle n'existe pas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smart-expense-management/');
}
?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_employe.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
<script src="<?= BASE_URL ?>assets/js/dashboard_employe.js"></script>

<div class="container-fluid p-4 main-content-bg">

    <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="page-title"><i class="bi bi-receipt me-2"></i>Mes Demandes de Frais</h1>
            <p class="page-subtitle">Bienvenue, <?= htmlspecialchars($user_name) ?> - Suivi de vos demandes</p>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-attente">0</div>
                            <div class="stat-label">En attente</div>
                            <small class="text-warning fw-bold mt-2 d-block">En cours de traitement</small>
                        </div>
                        <div class="stat-icon warning"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-validees">0</div>
                            <div class="stat-label">Validées</div>
                            <small class="text-success fw-bold mt-2 d-block">Approuvées</small>
                        </div>
                        <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card danger">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-number" id="stat-rejetees">0</div>
                            <div class="stat-label">Rejetées</div>
                            <small class="text-danger fw-bold mt-2 d-block">À réviser</small>
                        </div>
                        <div class="stat-icon danger"><i class="bi bi-x-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <i class="bi bi-clock-history"></i>
                <h5>Mes 3 dernières demandes - Vue complète</h5>
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

        <div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview()">
            <span class="image-preview-close">&times;</span>
            <img id="previewImage" src="" alt="Prévisualisation">
        </div>
    </main>

    <?php 
    require_once BASE_PATH . 'includes/footer.php'; 
    ?>