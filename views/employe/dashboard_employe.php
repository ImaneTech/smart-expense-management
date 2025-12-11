<?php
// ... (PHP code de vérification de session, de la logique de devise, etc., reste inchangé) ...
// 8. INCLUSION DU HEADER
require_once BASE_PATH . 'includes/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_employe.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/demandes_employe.css">

<script>
    // Variables utilisées par demandes_employe.js / dashboard_employe.js
    const USER_ID = <?= $user_id ?>;
    const CURRENCY_SYMBOL = '<?= htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') ?>';
    // ******************************************************
    // ** CORRECTION CLÉ 1 : La limite est définie à 6. **
    const DEFAULT_ROW_LIMIT = 6; 
    // ******************************************************
    
    // Utilisation de window.BASE_URL pour éviter les erreurs de redéclaration
    if (typeof window.BASE_URL === 'undefined') {
        window.BASE_URL = '<?= BASE_URL ?>';
    }

    if (typeof bootstrap === 'undefined') {
        console.warn("Bootstrap JS n'est pas chargé. Les modales ne fonctionneront pas.");
    }
</script>

<script src="<?= BASE_URL ?>assets/js/demandes_employe.js"></script> 


<div class="container-fluid p-4" style="min-height: 100vh; display: flex; flex-direction: column;">
    
    <div class="page-header d-flex justify-content-between align-items-center mb-3"> 
        <div>
            <h1 class="page-title fw-bold m-0" style="color: #32325d;"><i class="bi bi-speedometer me-2"></i>Tableau de bord</h1>
            <p class="page-subtitle text-muted small mt-1">Aperçu de vos demandes de frais</p>
        </div>
        <a href="<?= BASE_URL ?>views/employe/create_demande.php" class="btn btn-go-green"> 
            <i class="bi bi-plus-circle me-2"></i>Nouvelle Demande
        </a>
    </div>

    <div class="row g-4 mb-4">
        
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm border-0 h-100 stat-card" style="background-color: var(--status-pending-bg) !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold mb-1 stat-number" id="stat-attente" style="color: var(--text-primary); font-size: 2.5rem;">0</h2>
                        <p class="text-muted fw-bold mb-0 small text-uppercase" style="letter-spacing: 1px;">En Attente</p>
                        <small class="fw-bold mt-2 d-block" style="color: var(--status-pending-text);">
                            En cours de traitement
                        </small>
                    </div>
                    <div class="stat-icon"> 
                        <img src="<?= BASE_URL ?>assets/img/pending2.png" alt="Icône En Attente" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm border-0 h-100 stat-card" style="background-color: var(--status-approved-bg) !important;">
                 <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold mb-1 stat-number" id="stat-validees" style="color: var(--text-primary); font-size: 2.5rem;">0</h2>
                        <p class="text-muted fw-bold mb-0 small text-uppercase" style="letter-spacing: 1px;">Validées</p>
                        <small class="fw-bold mt-2 d-block" style="color: var(--status-approved-text);">
                            Approuvées
                        </small>
                    </div>
                    <div class="stat-icon">
                        <img src="<?= BASE_URL ?>assets/img/check-circle.png" alt="Icône Validée" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm border-0 h-100 stat-card" style="background-color: var(--status-rejected-bg) !important;">
                 <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold mb-1 stat-number" id="stat-rejetees" style="color: var(--text-primary); font-size: 2.5rem;">0</h2>
                        <p class="text-muted fw-bold mb-0 small text-uppercase" style="letter-spacing: 1px;">Rejetées</p>
                        <small class="fw-bold mt-2 d-block" style="color: var(--status-rejected-text);">
                            À réviser
                        </small>
                    </div>
                    <div class="stat-icon">
                        <img src="<?= BASE_URL ?>assets/img/rejected.png" alt="Icône Rejetée" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="table-container flex-grow-1">
        <div class="table-header p-0">
            <div class="table-header-left">
                <i class="bi bi-list-ul"></i>
                <h5 style="color: #32325d; font-weight: bold;">Demandes Récentes</h5>
            </div>
            
            <a href="<?= BASE_URL ?>views/employe/employe_demandes.php" class="btn btn-go-green btn-sm px-3 py-2">
                Voir tout
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 25%;">Objet Mission</th>
                        <th style="width: 15%;">Date Départ</th>
                        <th style="width: 15%;">Date Retour</th>
                        <th style="width: 15%;">Statut</th>
                        <th style="width: 15%;">Montant Total (<?= htmlspecialchars($currencySymbol) ?>)</th>
                        <th class="pe-4" style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody id="demandes-tbody">
                    </tbody>
            </table>
        </div>
    </div>
    </div>

