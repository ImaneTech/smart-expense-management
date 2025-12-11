<?php
// Fichier: views/manager/settings_manager.php (CORRIGÉ)

if (session_status() === PHP_SESSION_NONE) session_start();

// ---------------------------------------------------------------------
// 1. INCLUSIONS CRUCIALES & DÉFINITION DE LA CONNEXION PDO
// ---------------------------------------------------------------------

// CHARGE CONFIG.PHP: DOIT définir $pdo immédiatement.
require_once __DIR__ . '/../../config.php';

// Si $pdo n'est pas encore défini après config.php, cela signifie que
// config.php n'initialise pas la connexion directement. Si c'est le cas,
// le problème vient de config.php lui-même. 

// ---------------------------------------------------------------------
// 2. CHARGEMENT DES CLASSES QUI DÉPENDENT DE $pdo
// ---------------------------------------------------------------------

// Nous chargeons les classes avant les includes de la vue pour garantir que la classe est connue.
require_once BASE_PATH . 'Controllers/SettingsController.php';
require_once BASE_PATH . 'Controllers/TeamController.php';
require_once BASE_PATH . 'Models/NotificationModel.php'; 

// ---------------------------------------------------------------------
// 3. CHARGEMENT DES VUES ET UTILITAIRES RESTANTS
// ---------------------------------------------------------------------

require_once BASE_PATH . 'includes/header.php'; // Header et sidebar
require_once BASE_PATH . 'includes/flash.php'; 

// --- SÉCURITÉ ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') { 
    header('Location: ' . BASE_URL . 'views/auth/login.php'); 
    exit; 
}

// MAINTENANT $pdo est garanti d'être défini (par config.php):
$settingsController = new SettingsController($pdo); 
$teamController = new TeamController($pdo, $_SESSION['user_id']);

// --- TRAITEMENT POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. THEME
    if (isset($_POST['type']) && $_POST['type'] === 'display') {
        $theme = $_POST['theme'] ?? 'light';
        $settingsController->updateDisplaySettings($_SESSION['user_id'], $theme);
        setFlash('success', 'Thème mis à jour.');
        header('Location: settings_manager.php?tab=display'); exit;
    }

    // 2. DEVISE
    if (isset($_POST['type']) && $_POST['type'] === 'preferences') {
        $currency = $_POST['currency'] ?? 'MAD';
        $settingsController->updateInputPreferences($_SESSION['user_id'], $currency);
        setFlash('success', 'Devise enregistrée.');
        header('Location: settings_manager.php?tab=prefs'); exit;
    }

    // 3. AJOUT MEMBRES (Logique stricte de ajouter_membre.php)
    if (isset($_POST['add_members_action'])) {
        $memberIds = filter_input(INPUT_POST, 'member_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $validIds = [];
        if (!empty($memberIds)) {
            foreach ($memberIds as $id) {
                $validIds[] = (int) $id;
            }
        }

        if (!empty($validIds)) {
            $res = $teamController->addMembersToTeam($validIds);
            if (isset($res['error'])) {
                setFlash('danger', $res['error']);
            } else {
                setFlash('success', count($validIds) . ' collaborateur(s) ajouté(s).');
            }
        } else {
            setFlash('warning', 'Aucun membre sélectionné.');
        }
        header('Location: settings_manager.php?tab=team'); exit;
    }
}

// --- TRAITEMENT GET (Suppression) ---
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $res = $teamController->removeMemberFromTeam((int)$_GET['id']);
    if (isset($res['success'])) {
        setFlash('success', 'Membre retiré de l\'équipe.');
    } else {
        setFlash('danger', 'Erreur lors du retrait.');
    }
    header('Location: settings_manager.php?tab=team'); exit;
}

// --- DONNÉES ---
$userSettings = $settingsController->getSettings($_SESSION['user_id']);
$currentTheme = $userSettings['theme'] ?? 'light';
$currentCurrency = $userSettings['preferred_currency'] ?? 'MAD'; 

$teamMembers = $teamController->getAllTeamMembers();
$availableEmployees = $teamController->getAvailableEmployees();

$activeTab = $_GET['tab'] ?? 'display';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/table_layout.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ========================================
    STYLES CSS (Réutilisés du code fourni)
    ========================================
*/
    .page-header-title { color: var(--text-color); font-weight: 700; margin-bottom: 1.5rem; }
    .settings-card { background-color: var(--card-bg); border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    
    /* Tabs */
    .nav-tabs-custom { border-bottom: 2px solid #eaecf4; padding: 0 1rem; background-color: var(--card-bg); }
    .nav-tabs-custom .nav-link { border: none; color: var(--text-color); font-weight: 600; padding: 1rem 1.5rem; transition: all 0.3s ease; background: transparent; position: relative; }
    .nav-tabs-custom .nav-link:hover { color: var(--primary-color); background-color: rgba(var(--primary-color-rgb), 0.05); }
    .nav-tabs-custom .nav-link.active { color: var(--primary-color); }
    .nav-tabs-custom .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: var(--primary-color); border-top-left-radius: 3px; border-top-right-radius: 3px; }
    .nav-tabs-custom .nav-link i { margin-right: 8px; font-size: 1.1rem; }

    /* Theme buttons */
    .theme-selector-label { cursor: pointer; border: 2px solid #e4e9f7; border-radius: 12px; padding: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 600; color: var(--text-color); transition: all 0.3s; }
    .btn-check:checked + .theme-selector-label { border-color: var(--primary-color); background-color: rgba(var(--primary-color-rgb), 0.05); color: var(--primary-color); }
    .btn-theme { background-color: var(--primary-color); color: #fff; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; transition: transform 0.2s; }
    .btn-theme:hover { background-color: #65a63b; color: #fff; transform: translateY(-2px); }
    
    .tab-content-area { padding: 2rem; }
    
    /* Design spécifique Team (Modal & Table) */
    .bg-theme-light-green { background-color: #f1f8e9 !important; }
    .bg-theme-primary-soft { background-color: #e8f5e9 !important; color: #2e7d32 !important; }
    .selected-card { border: 2px solid #76BD46 !important; background-color: #e8f5e9 !important; }
    
    .badge-theme { padding: 5px 10px; border-radius: 12px; font-size: 0.75rem; }
    .badge-wait { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .badge-valid { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }

    /* Design Avatar & Role (Tableau) */
    .avatar-theme { width: 40px; height: 40px; background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; }
    .badge-role { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
    .badge-role-employe { background-color: #f1f8e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .badge-role-manager { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

    /* Boutons actions */
    .btn-icon-soft { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; text-decoration: none; }
    .btn-icon-soft:hover { background-color: #f1f3f5; transform: translateY(-2px); }
</style>

<div class="container mt-4 mb-5">
    <div class="d-flex align-items-center mb-4">
        <h2 class="page-header-title mb-0"><i class="bi bi-gear-fill text-theme-primary me-2"></i> Paramètres Manager</h2>
    </div>
    
    <div class="card settings-card">
        
        <ul class="nav nav-tabs nav-tabs-custom" id="settingsTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link <?= $activeTab=='display'?'active':'' ?>" id="display-tab" data-bs-toggle="tab" data-bs-target="#display" type="button"><i class="bi bi-palette me-2"></i> Affichage</button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab=='prefs'?'active':'' ?>" id="prefs-tab" data-bs-toggle="tab" data-bs-target="#prefs" type="button"><i class="bi bi-sliders me-2"></i> Préférences</button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab=='team'?'active':'' ?>" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button"><i class="bi bi-people-fill me-2"></i> Mon Équipe</button>
            </li>
            <li class="nav-item">
                <a href="settings_manager.php?tab=notifications" class="nav-link <?= $activeTab=='notifications'?'active':'' ?>" id="notifications-tab"><i class="fas fa-bell me-2"></i> Notifications</a>
            </li>
        </ul>

        <div class="tab-content tab-content-area" id="settingsTabContent">
            
            <div class="tab-pane fade <?= $activeTab=='display'?'show active':'' ?>" id="display" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="type" value="display">
                    <h5 class="fw-bold mb-4" style="color: var(--text-color);">Thème de l'interface</h5>
                    <div class="row mb-4">
                        <div class="col-md-9 offset-md-1">
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="theme" id="themeLight" value="light" <?= $currentTheme == 'light' ? 'checked' : '' ?>>
                                    <label class="theme-selector-label shadow-sm" for="themeLight"><i class="bi bi-sun fs-4"></i> Clair</label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="theme" id="themeDark" value="dark" <?= $currentTheme == 'dark' ? 'checked' : '' ?>>
                                    <label class="theme-selector-label shadow-sm" for="themeDark"><i class="bi bi-moon-stars fs-4"></i> Sombre</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" class="btn btn-theme shadow-sm">Appliquer</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade <?= $activeTab=='prefs'?'show active':'' ?>" id="prefs" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="type" value="preferences">
                    <div class="row mb-4 align-items-center">
                        <label class="col-md-3 col-form-label text-muted fw-bold">Devise par défaut</label>
                        <div class="col-md-6">
                            <select class="form-select form-select-lg" name="currency">
                                <option value="MAD" <?= $currentCurrency == 'MAD' ? 'selected' : '' ?>>MAD (Dirham)</option>
                                <option value="EUR" <?= $currentCurrency == 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                                <option value="USD" <?= $currentCurrency == 'USD' ? 'selected' : '' ?>>USD (Dollar)</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" class="btn btn-theme shadow-sm">Enregistrer</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade <?= $activeTab=='team'?'show active':'' ?>" id="team" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0" style="color: var(--text-color);">Membres de mon équipe</h5>
                    <button type="button" class="btn fw-bold rounded-pill px-4 py-2 btn-link-theme-primary" style="font-size: 0.9rem;" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="fas fa-user-plus me-2"></i> Ajouter un Membre
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="modern-table" style="table-layout: auto; width: 100%;">
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
                            <?php if (empty($teamMembers)): ?>
                                <tr style="height: 200px;">
                                    <td colspan="5" class="text-center text-muted align-middle">
                                        <h6 class="text-dark fw-bold m-0">Aucun membre d'équipe trouvé.</h6>
                                        <p class="text-muted small m-0">Cliquez sur ajouter pour commencer.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teamMembers as $member): 
                                    $statusText = 'Actif';
                                    $badgeStatusClass = 'badge-valid';
                                    $roleBadgeClass = ($member['role'] === 'manager') ? 'badge-role-manager' : 'badge-role-employe';
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-theme me-3">
                                                    <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <span class="table-primary-text fw-bold"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($member['email']) ?></td>
                                        <td>
                                            <span class="badge-role <?= $roleBadgeClass ?>">
                                                <?= htmlspecialchars(ucfirst($member['role'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-theme <?= $badgeStatusClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="settings_manager.php?action=remove&id=<?= $member['id'] ?>"
                                               title="Retirer de l'équipe"
                                               class="btn-icon-soft text-danger btn-delete-member">
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
            
            <div class="tab-pane fade <?= $activeTab=='notifications'?'show active':'' ?>" id="notifications" role="tabpanel">
                <?php 
                    // Inclusion du contenu de la page d'historique de notification
                    require_once BASE_PATH . 'views/historique_notif.php';
                ?>
             </div>

        </div>
    </div>
</div>

<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" style="color: #32325d;">Ajouter des collaborateurs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <form method="POST">
                    <input type="hidden" name="add_members_action" value="1">
                    
                    <?php if (empty($availableEmployees)): ?>
    
                        <div class="text-center py-5 px-3 rounded-4" style="background-color: #f1f8e9; border: 2px dashed #aed581;">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle shadow-sm" 
                                style="width: 70px; height: 70px; background-color: #fff;">
                                <i class="bi bi-person-check-fill" style="font-size: 2rem; color: #76BD46;"></i>
                            </div>
                            
                            <h5 class="fw-bold" style="color: #2e7d32;">Tout est à jour !</h5>
                            <p class="text-muted mb-0 small">
                                Tous les employés sont déjà affectés à une équipe.<br>
                                Aucun collaborateur n'est disponible pour le moment.
                            </p>
                        </div>
    
                    <?php else: ?>
                        <p class="text-muted mb-3">Sélectionnez les employés à ajouter :</p>
                        
                        <div class="row g-3" style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($availableEmployees as $employee): ?>
                                <div class="col-md-6">
                                    <label for="member_<?= $employee['id'] ?>" class="d-flex p-3 bg-theme-light-green team-member-card w-100"
                                            style="border-radius: 12px; cursor: pointer; height: 100%; position: relative; border: 1px solid #eee;">
                                        
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

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn fw-bold rounded-pill px-4 py-2 btn-link-theme-primary" style="font-size: 0.9rem;">
                                <i class="fas fa-user-plus me-2"></i> Ajouter à l'Équipe
                            </button>
                        </div>
                    <?php endif; ?>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete-member');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const link = this.getAttribute('href');

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Ce membre sera retiré de votre équipe.",
                icon: 'warning',
                showCancelButton: true,
                
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                
                confirmButtonText: 'Oui, retirer',
                cancelButtonText: 'Annuler',
                
                background: '#fff5f5', 
                
                customClass: {
                    popup: 'rounded-4 shadow-lg border border-danger', 
                    title: 'fw-bold text-danger',
                    
                    confirmButton: 'btn btn-danger rounded-pill px-5 py-3 fs-5 me-3 fw-bold shadow-sm', 
                    cancelButton: 'btn btn-secondary rounded-pill px-5 py-3 fs-5 fw-bold shadow-sm'
                },
                buttonsStyling: false,
                iconColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
});
</script>

<?php 
displayFlash(); 
require_once BASE_PATH . 'includes/footer.php'; 
?>