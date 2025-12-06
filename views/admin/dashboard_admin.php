<?php

error_reporting(E_ALL);
ini_set("
",1);
ini_set("",1);
session_start();
// Définir le rôle (à adapter selon votre système d'authentification)
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Demandes - GoTrackr</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Boxicons pour la sidebar -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard_admin.css">

</head>


    <!-- CONTENU PRINCIPAL -->
    <div id="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-text"></i> Gestion des Demandes</h2>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i> Actions
                </button>
                <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#nouvelleDemandeModal">
                        <i class="bi bi-plus-circle"></i> Nouvelle demande
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData()">
                        <i class="bi bi-download"></i> Exporter
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i> Actualiser
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card success">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon success">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-validees">0</div>
                            <div class="text-muted">Demandes validées (Manager)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon warning">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-attente">0</div>
                            <div class="text-muted">Demandes en attente</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card danger">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon danger">
                            <i class="bi bi-x-lg"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-number" id="stat-rejetees">0</div>
                            <div class="text-muted">Demandes rejetées</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn btn-dark filter-btn active" onclick="filterDemandes('all', event)">
                <i class="bi bi-list"></i> Toutes
            </button>
            <button class="btn btn-outline-secondary filter-btn" onclick="filterDemandes('en_attente', event)">
                <i class="bi bi-clock"></i> En attente
            </button>
            <button class="btn btn-outline-success filter-btn" onclick="filterDemandes('validee_manager', event)">
                <i class="bi bi-check"></i> Validées Manager
            </button>
            <button class="btn btn-outline-primary filter-btn" onclick="filterDemandes('validee_admin', event)">
                <i class="bi bi-check-all"></i> Validées Admin
            </button>
            <button class="btn btn-outline-danger filter-btn" onclick="filterDemandes('rejetee', event)">
                <i class="bi bi-x"></i> Rejetées
            </button>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <h5 class="mb-3">
                Toutes les demandes <span class="badge bg-secondary" id="total-demandes">0</span>
            </h5>

            <div class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Objectif</th>
                            <th>Date</th>
                            <th>Montant Total</th>
                            <th>Justificatif</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody">
                        <tr><td colspan="8" class="text-center text-muted">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle Demande avec Upload -->
    <div class="modal fade" id="nouvelleDemandeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nouvelleDemandeForm">
                        <div class="mb-3">
                            <label class="form-label">Utilisateur</label>
                            <input type="text" class="form-control" id="utilisateur" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Objectif</label>
                            <textarea class="form-control" id="objectif" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Montant Total (€)</label>
                            <input type="number" step="0.01" class="form-control" id="montant" required>
                        </div>
                        
                        <!-- Zone d'upload du justificatif -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-paperclip"></i> Justificatif (optionnel)
                            </label>
                            <div class="upload-zone" id="uploadZone">
                                <i class="bi bi-cloud-upload" style="font-size: 48px; color: #6c757d;"></i>
                                <p class="mb-0 mt-2">Glissez un fichier ici ou cliquez pour sélectionner</p>
                                <small class="text-muted">Formats acceptés: PDF, JPG, PNG (max 5MB)</small>
                                <input type="file" id="justificatif" accept=".pdf,.jpg,.jpeg,.png" style="display:none">
                            </div>
                            <div class="file-preview" id="filePreview">
                                <i class="bi bi-file-earmark-check text-success"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeFile()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="createDemande()">
                        <i class="bi bi-check-circle"></i> Créer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
    <script src="<?= BASE_URL ?>assets/js/dashboard_admin.js"></script>
</body>
</html>