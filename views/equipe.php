<?php
// views/equipe.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'controllers/TeamController.php';
require_once BASE_PATH . 'includes/header.php';

// 1. Définir l'ID du Manager (Source unique de vérité)
// Utilisez 8 si la session est un problème pour le débogage, sinon utilisez la variable de session.
$managerId = $_SESSION['user_id'] ?? 1;
// $managerId = 8; // DÉCOMMENTER POUR DEBUGGER AVEC ID 8 FORCÉ

// 2. Instancier le contrôleur
$controller = new TeamController($pdo, $managerId);

// 3. LOGIQUE POUR RETIRER UN MEMBRE DE L'ÉQUIPE (Gestion de l'action 'remove')
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $memberIdToRemove = (int) $_GET['id'];

    // Appel du contrôleur pour supprimer l'association
    $removeResult = $controller->removeMemberFromTeam($memberIdToRemove);

    if (isset($removeResult['success'])) {
        header('Location: equipe.php?status=removed');
        exit;
    } else {
        // En cas d'erreur de suppression, afficher un message d'erreur
        $removeError = $removeResult['error'] ?? 'Une erreur inconnue est survenue lors du retrait.';
    }
}

// Récupération des membres de l'équipe (après toute suppression potentielle)
$team_members = $controller->getAllTeamMembers();

$pageTitle = "Gestion de l'Équipe";
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="container-fluid p-4" style="min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold m-0" style="color: #32325d;"><?= $pageTitle ?></h1>
        <a href="ajouter_membre.php"
            class="btn fw-bold rounded-pill px-4 py-2 btn-link-theme-primary"
            style="font-size: 0.9rem;">
            <i class="fas fa-user-plus me-2"></i> Ajouter un Membre
        </a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'team_updated'): ?>
        <div class="alert alert-success">Membre(s) ajouté(s) à l'équipe avec succès !</div>
    <?php endif; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] === 'removed'): ?>
        <div class="alert alert-warning">Le membre a été retiré de votre équipe.</div>
    <?php endif; ?>
    <?php if (isset($removeError)): ?>
        <div class="alert alert-danger">Erreur de retrait : <?= htmlspecialchars($removeError) ?></div>
    <?php endif; ?>


    <div class="card shadow-sm border-0 custom-table-card">

        <div class="table-responsive">
            <table class="modern-table" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($team_members)): ?>
                        <tr style="height: 300px;">
                            <td colspan="5" class="text-center text-muted align-middle">
                                <div style="padding-top: 50px; padding-bottom: 50px;">
                                    <h6 class="text-dark fw-bold m-0">Aucun membre d'équipe trouvé.</h6>
                                    <p class="text-muted small m-0">Veuillez ajouter des membres pour gérer l'équipe.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($team_members as $member):
                            $statusText = 'Actif';
                            $badgeClass = 'badge-valid';
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar-initials me-3"><?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?></span>
                                        <div>
                                            <span class="table-primary-text"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></span>

                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($member['email']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($member['role'])) ?></td>
                                <td>
                                    <span class="badge badge-theme <?= $badgeClass ?>"><?= $statusText ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="equipe.php?action=remove&id=<?= $member['id'] ?>"
                                        title="Retirer de l'équipe"
                                        class="btn-action-icon text-danger"
                                        onclick="return confirm('Êtes-vous sûr de vouloir retirer <?= htmlspecialchars($member['first_name']) ?> de votre équipe ? (Le compte existera toujours)');">
                                        <i class="fas fa-user-times"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>