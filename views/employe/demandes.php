<?php
// views/employe/demandes.php

// Simuler les données (à remplacer par l'appel au contrôleur, ex: $demandes = $controller->getEmployeDemandes();)
if (!isset($demandes)) {
    $demandes = [
        [
            'id' => 101, 
            'objet_mission' => 'Réunion Commerciale Lyon', 
            'montant_total' => 450.00, 
            'date_depart' => '2025-11-20', 
            'statut' => 'Validée Manager'
        ],
        [
            'id' => 102, 
            'objet_mission' => 'Formation Sécurité', 
            'montant_total' => 210.50, 
            'date_depart' => '2025-11-18', 
            'statut' => 'En attente'
        ],
        [
            'id' => 103, 
            'objet_mission' => 'Audit site client Z', 
            'montant_total' => 590.25, 
            'date_depart' => '2025-11-15', 
            'statut' => 'Rejetée Manager'
        ],
        [
            'id' => 104, 
            'objet_mission' => 'Nouveau projet pilote', 
            'montant_total' => 0.00, 
            'date_depart' => '2025-11-25', 
            'statut' => 'Brouillon'
        ],
    ];
}

// Fonction utilitaire pour générer le badge de statut (copiée de la vue manager)
function getStatutBadge(string $statut): string {
    $class = '';
    switch ($statut) {
        case 'En attente':
            $class = 'badge-wait-pill';
            break;
        case 'Validée Manager':
        case 'Approuvée Compta': // Ajout des statuts finals
        case 'Payée':
            $class = 'badge-valid-pill';
            break;
        case 'Rejetée Manager':
            $class = 'badge-reject-pill';
            break;
        default: // Brouillon
            $class = 'bg-secondary text-white';
    }
    return "<span class='badge badge-lg {$class}'>{$statut}</span>";
}
?>

<div class="details-page-manager">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h2 class="text-theme-secondary">Mon Suivi des Demandes</h2>
        <a href="demandes_creation.php" class="btn btn-primary btn-action btn-sm">
            <i class="fas fa-plus"></i> Nouvelle demande
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success validation-info" role="alert">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="detail-card p-4">
                <h3 class="section-title-custom mb-4">
                    <i class="fas fa-history"></i> Historique et Statut
                </h3>
                
                <?php if (!empty($demandes)): ?>
                <div class="table-responsive">
                    <table class="table detail-table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Réf.</th>
                                <th scope="col">Objet de la Mission</th>
                                <th scope="col">Date Départ</th>
                                <th scope="col" class="text-end">Montant Total</th>
                                <th scope="col">Statut Actuel</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($demandes as $demande): ?>
                            <tr>
                                <td class="text-theme-secondary fw-bold"><?= $demande['id'] ?></td>
                                <td><?= htmlspecialchars($demande['objet_mission']) ?></td>
                                <td><?= date('d/m/Y', strtotime($demande['date_depart'])) ?></td>
                                <td class="text-end text-theme-primary fw-bold">
                                    <?= number_format($demande['montant_total'], 2, ',', ' ') ?> €
                                </td>
                                <td><?= getStatutBadge($demande['statut']) ?></td>
                                <td>
                                    <a href="demandes_detail.php?id=<?= $demande['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                        Consulter <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center no-frais" role="alert">
                    <i class="fas fa-folder-open"></i>
                    <p class="mb-0 fw-bold">Aucune demande de frais trouvée.</p>
                    <p class="small text-muted">Cliquez sur "Nouvelle demande" pour commencer.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>