<?php
require_once "config.php";

// Récupération des demandes depuis la base de données
$stmt = $pdo->query("SELECT * FROM demandedefrais ORDER BY id ASC");
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Comptage des demandes validées et en attente
$validatedCount = 0;
$pendingCount = 0;
foreach ($demandes as $demande) {
    if ($demande['statut'] == 'Validé') $validatedCount++;
    if ($demande['statut'] == 'En cours') $pendingCount++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/
    css/style1.css">
</head>
<body class="bg-dark text-light">

<div class="container-fluid p-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 bg-secondary rounded p-3">
            <h5>Menu</h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link active rounded bg-success text-dark">Accueil</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link text-light rounded bg-light bg-opacity-25">Gestion des utilisateurs</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-light rounded bg-light bg-opacity-25">Gestion des frais</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10">
            <!-- Header -->
            <div class="d-flex justify-content-end align-items-center mb-3">
                <span class="me-2">Bonjour, Btissam</span>
                <button class="btn btn-success btn-sm">Déconnexion</button>
            </div>

            <!-- Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Demandes validées (Managers)</h5>
                            <p class="card-text fs-4"><?php echo $validatedCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Demandes en attente (Admin)</h5>
                            <p class="card-text fs-4"><?php echo $pendingCount; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <h5>Toutes les Demandes</h5>
            <table class="table table-striped table-hover table-dark">
                <thead class="table-light text-dark">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Objectif</th>
                        <th>Date de mission</th>
                        <th>Montant Total en DH</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande): ?>
                    <tr>
                        <td><?php echo $demande['id']; ?></td>
                        <td><?php echo $demande['user_id']; ?></td>
                        <td><?php echo $demande['objectif']; ?></td>
                        <td><?php echo $demande['date_mission']; ?></td>
                        <td><?php echo $demande['montant_total']; ?></td>
                        <td>
                            <?php
                                $badgeClass = "secondary";
                                if ($demande['statut'] == "Validé") $badgeClass = "success";
                                if ($demande['statut'] == "Rejeté") $badgeClass = "danger";
                                if ($demande['statut'] == "En cours") $badgeClass = "warning";
                            ?>
                            <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo $demande['statut']; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Actions -->
            <div class="mt-3">
                <h5>Actions</h5>
                <div class="btn-group-vertical">
                    <button class="btn btn-primary">Voir Détails</button>
                    <button class="btn btn-success">Approuver</button>
                    <button class="btn btn-danger">Rejeter</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS + Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
