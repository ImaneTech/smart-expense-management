<?php

// Assurez-vous que la session est démarrée au tout début
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php';
require_once BASE_PATH . 'includes/header.php';

$controller = new DemandeController($pdo);

// 1. Appel UNIQUE de la méthode correcte
$data = $controller->getDashboardData();

// 2. Extraction des variables en utilisant les clés du tableau retourné
// Remarque : Le Contrôleur doit s'assurer que les clés ici correspondent à ce qu'il retourne.
// Je suppose que le Contrôleur renvoie: ['stats' => [...], 'latest' => [...], 'team' => [...]]
$stats = $data['stats'];
$latest = $data['latest']; // Liste des demandes récentes
$team_members = $data['team']; // Liste des membres d'équipe


?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_manager.css">
<script src="<?= BASE_URL ?>assets/js/dashboard_manager.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css">

<div class="container-fluid p-4" style="min-height: 100vh; display: flex; flex-direction: column;">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold m-0" style="color: #32325d;">Tableau de Bord</h1>
        <span class="text-muted small">Aperçu de la semaine</span>
    </div>

<div class="row g-4 mb-5">
    <h3 class="fw-bold mb-3" style="color: #32325d;">Statistiques générales</h3>
    
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background-color: #FFF8E1 !important; border-radius: 20px;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="fw-bold mb-1 counter" data-target="<?= $stats['pending'] ?? 0 ?>" style="color: #32325d; font-size: 2.5rem;">0</h2>
                    <p class="text-muted fw-bold mb-0 small text-uppercase" style="letter-spacing: 1px;">En Attente</p>
                    <small class="text-dark-warning fw-bold mt-2 d-block counter is-amount" data-target="<?= $stats['amount_pending'] ?? 0.00 ?>">
                        0 <?= $currencySymbol ?> est. </small>
                </div>
                <div class="icon-box">
                    <img src="<?= BASE_URL ?>assets/img/pending_icon.png" alt="Pending" width="80" style="opacity: 0.9;">
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background-color: #E8F5E9 !important; border-radius: 20px;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="fw-bold mb-1 counter" data-target="<?= $stats['validated'] ?? 0 ?>" style="color: #32325d; font-size: 2.5rem;">0</h2>
                    <p class="text-muted fw-bold mb-0 small text-uppercase" style="letter-spacing: 1px;">Validés</p>
                    <small class="text-success fw-bold mt-2 d-block">
                        Approuvé
                    </small>
                </div>
                <div class="icon-box">
                    <img src="<?= BASE_URL ?>assets/img/approve_icon.png" alt="Validated" width="80" style="opacity: 0.9;">
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background-color: #FFEBEE !important; border-radius: 20px;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="fw-bold mb-1 counter" data-target="<?= $stats['rejected'] ?? 0 ?>" style="color: #32325d; font-size: 2.5rem;">0</h2>
                    <p class="text-muted fw-bold mb-0 small text-uppercase" style="letter-spacing: 1px;">Rejetés</p>
                    <small class="text-danger fw-bold mt-2 d-block">
                        Attention
                    </small>
                </div>
                <div class="icon-box">
                    <img src="<?= BASE_URL ?>assets/img/decline.png" alt="Rejected" width="80" style="opacity: 0.9; transform:scale(1.3);">
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background-color: #E3F2FD !important; border-radius: 20px;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="fw-bold mb-1 counter" data-target="<?= $stats['team_size'] ?? 0 ?>" style="color: #32325d; font-size: 2.5rem;">0</h2>
                    <p class="text-muted fw-bold mb-0 small text-uppercase" style="letter-spacing: 1px;">Équipe</p>
                    <small class="text-primary fw-bold mt-2 d-block">
                        Actifs
                    </small>
                </div>
                <div class="icon-box">
                    <img src="<?= BASE_URL ?>assets/img/team_icon.png" alt="Team" width="80" style="opacity: 0.9;">
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="row flex-grow-1">
        <div class="col-lg-8 mb-4 h-100">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold m-0" style="color: #32325d; font-size: 1.25rem;">Demandes Récentes</h3>

                <a href="manager/demandes_liste.php" class="btn fw-bold rounded-pill px-4 py-2 btn-link-theme-primary" style="font-size: 0.9rem;">
                    Voir tout
                </a>
            </div>

            <div class="table-responsive h-100" style="max-height: 500px; overflow-y: auto;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Employé</th>
                            <th>Mission</th>
                            <th>Dates</th>
                            <th>Budget</th>
                            <th class="text-end pe-4">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($latest)): ?>
                            <tr class="empty-state-row">
                                <td colspan="5">
                                    <div class="empty-state-card">
                                        <h6 class="text-dark fw-bold">Tout est à jour !</h6>
                                        <p class="text-muted small">Aucune demande en attente.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                          <?php foreach (array_slice($latest, 0, 4) as $d): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <span class="table-primary-text"><?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?></span>
                                                <span class="table-sub-text"><?= htmlspecialchars($d['email']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="table-primary-text"><?= htmlspecialchars($d['objet_mission']) ?></span></td>
                                    <td><span class="table-primary-text"><?= date('d/m/Y', strtotime($d['date_depart'])) ?></span></td>
                                    <td><span class="text-highlight"><?= number_format($d['total_calcule'], 2) ?> €</span></td>
                                    <td class="text-end pe-4">
                                        <a href="details_demande.php?id=<?= $d['id'] ?>" class="btn-action-icon ms-auto">
                                            <i class='fa fa-chevron-right'></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

      <div class="col-lg-4 mb-4 h-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold m-0" style="color: #32325d; font-size: 1.25rem;">Mon Équipe</h3>
    </div>
    <div class="card shadow-sm border-0 h-100" style="background-color: var(--card-bg); border-radius: 16px; max-height: 500px; overflow-y: auto;">
        <div class="card-body p-4">
            <?php
            if (!empty($team_members)):
            ?>
                <?php foreach (array_slice($team_members, 0, 3) as $member): ?>

                    <div class="d-flex align-items-center p-3 mb-3 position-relative member-card shadow-sm"
                        style="border-radius: 12px; transition: all 0.3s ease; background-color: #f1f8e9; border: 1px solid #dcedc8;">

                        <div class="me-3 fw-bold rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px; 
                            background-color: #e8f5e9; 
                            color: #43a047; 
                            border: 1px solid #c8e6c9; 
                            font-size: 1.1rem;">
                            <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                        </div>

                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                            </div>
                            <div style="color: #6c757d; font-size: 0.85rem;">
                                <?= htmlspecialchars($member['email']) ?>
                            </div>
                        </div>

                        <a href="mailto:<?= htmlspecialchars($member['email']) ?>"
                            class="btn btn-sm bg-white rounded-circle shadow-sm border"
                            style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-envelope-o text-muted"></i>
                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>
                <div class="alert alert-info text-center">
                    Votre équipe est vide. Veuillez ajouter des membres via la page de gestion d'équipe.
                </div>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="manager/equipe.php" class="btn fw-bold rounded-pill px-4 py-2 btn-link-theme-primary" style="font-size: 0.9rem;">
                    Voir toute l'équipe
                </a>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>


<?php require_once BASE_PATH . 'includes/footer.php'; ?>