<?php
// views/ajouter_membre.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'controllers/TeamController.php'; 
require_once BASE_PATH . 'includes/header.php'; 


// 1. DÉTERMINER L'ID DU MANAGER ACTUEL
// L'ID est passé au contrôleur pour filtrer
$managerId = $_SESSION['user_id'] ?? 1; // Assurez-vous que l'ID est correct

$controller = new TeamController($pdo, $managerId);
$error = '';

// 2. TRAITEMENT DE L'AJOUT D'ÉQUIPE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et récupération des IDs (tableau de IDs d'employés)
    $memberIds = filter_input(INPUT_POST, 'member_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    
    $validMemberIds = [];
    if (!empty($memberIds)) {
        foreach ($memberIds as $id) {
            $validMemberIds[] = (int) $id;
        }
    }

    $result = $controller->addMembersToTeam($validMemberIds);

    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        // Succès : Redirection vers la page d'équipe pour voir les changements
        header('Location: equipe.php?status=team_updated');
        exit;
    }
}
// 3. AFFICHAGE : RÉCUPÉRATION DES EMPLOYÉS DISPONIBLES
$availableEmployees = $controller->getAvailableEmployees();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">

<div class="container-fluid p-4" style="min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold m-0" style="color: #32325d;">Ajouter des Membres à l'Équipe</h1>
        <a href="equipe.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à l'Équipe
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= $error ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 custom-table-card p-4">
    <h5 class="card-title mb-1 fw-bold">Sélectionner les employés à ajouter à votre équipe :</h5>
    <p class="text-muted mb-4">
        Voici la liste des collaborateurs disponibles (non encore affectés à une équipe).
    </p>
    
    <form method="POST">
        
        <?php if (empty($availableEmployees)): ?>
            <div class="alert alert-info text-center py-4">
                Tous les employés éligibles sont déjà dans votre équipe ou aucun employé n'est disponible.
            </div>
        <?php else: ?>

            <div style="max-height: 400px; overflow-y: auto; padding-right: 15px;">
                <div class="row g-3"> 
                    <?php foreach ($availableEmployees as $employee): ?>
                        
                      <div class="col-lg-4 col-md-6">
    <label for="member_<?= $employee['id'] ?>" class="d-flex p-3 bg-theme-light-green team-member-card w-100"
           style="border-radius: 12px; cursor: pointer; height: 100%; position: relative;">
        
        <input class="form-check-input position-absolute top-0 end-0 mt-2 me-2" 
               type="checkbox" 
               name="member_ids[]" 
               value="<?= $employee['id'] ?>" 
               id="member_<?= $employee['id'] ?>"
               onchange="this.closest('label').classList.toggle('selected-card')"> 
        
        <div class="d-flex align-items-center justify-content-between w-100">
            
            <div class="d-flex align-items-center">
                
                <div class="me-3 fw-bold bg-theme-primary-soft rounded-circle p-2 text-theme-primary border d-flex align-items-center justify-content-center"
                    style="width: 38px; height: 38px;">
                    <?= strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)) ?>
                </div>
                
                <div>
                    <span class="fw-bold d-block"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></span>
                    <small class="text-muted d-block"><?= htmlspecialchars($employee['email']) ?></small>
                </div>
            </div>
            
            <span class="badge badge-theme badge-wait align-self-start ms-2">
                <?= htmlspecialchars(ucfirst($employee['role'])) ?>
            </span>
        </div>
    </label>
</div>
                        
                    <?php endforeach; ?>
                </div> 
            </div> <div class="d-flex justify-content-end mt-4">
                <button type="submit" 
                        class="btn fw-bold rounded-pill px-4 py-2 btn-link-theme-primary" 
                        style="font-size: 0.9rem;">
                    <i class="fas fa-user-plus me-2"></i> Ajouter à l'Équipe
                </button>
            </div>

        <?php endif; ?>
        
    </form>
    </div> </div>

<?php require_once BASE_PATH . 'includes/footer.php'; ?>