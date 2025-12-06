<?php
// ---------------------------------------------------------
// Sécurité : Démarrer la session en premier
// ---------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------
// Rôle utilisateur (fallback = employe)
// ---------------------------------------------------------
$role = $_SESSION['role'] ?? 'employe';

// ---------------------------------------------------------
// URL dynamique du profil selon le rôle
// ---------------------------------------------------------
switch ($role) {
    case 'manager':
        $profile_url = BASE_URL . 'views/manager/profile.php';
        $settings_target = 'views/manager/settings_manager.php?tab=notifications';
        break;

    case 'admin':
        $profile_url = BASE_URL . 'views/admin/profile.php';
        $settings_target = 'views/admin/settings_admin.php?tab=notifications'; 
        break;

    case 'employe':
    default:
        $profile_url = BASE_URL . 'views/employe/profile.php';
        $settings_target = 'views/employe/settings_employe.php?tab=notifications'; 
        break;
}
?>
?>


<nav class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo">
            <span class="brand-text">GoTrackr</span>
        </div>
        <i class='bx bx-chevron-right toggle-sidebar'></i>
    </div>

    <div class="menu-bar">
        <div class="menu">
            <ul class="menu-links">
                
                <li class="nav-link search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="text" placeholder="Rechercher...">
                </li>

                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/dashboard.php">
                        <i class='bx bx-home-alt icon'></i>
                        <span class="text nav-text">Tableau de bord</span>
                    </a>
                </li>

                <?php if($role == 'manager'): ?>
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/manager/demandes_liste.php">
                        <i class='bx bx-check-shield icon'></i>
                        <span class="text nav-text">Demandes de frais</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/manager/recherche_avancee.php">
                        <i class='bx bx-group icon'></i>
                        <span class="text nav-text">Recherche avancée</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/manager/historique.php">
                        <i class='bx bx-history icon'></i> <span class="text nav-text">Historique</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if($role == 'admin'): ?>
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/admin/all-requests.php">
                        <i class='bx bx-file icon'></i>
                        <span class="text nav-text">Toutes Demandes</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/admin/users.php">
                        <i class='bx bx-user-plus icon'></i>
                        <span class="text nav-text">Utilisateurs</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/admin/categories.php">
                        <i class='bx bx-category icon'></i>
                        <span class="text nav-text">Catégories Frais</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="bottom-content">
            <ul class="menu-links"> 
                
                <li>
                    <a href="<?= $profile_url ?>">
                        <i class='bx bx-user icon'></i>
                        <span class="text nav-text">Mon Profil</span>
                    </a>
                </li>
                
                <li>
                    <a href="#" id="notif-sidebar-link" data-bs-toggle="modal" data-bs-target="#notificationModal">
                        <i class='bx bx-bell icon'></i>
                        <span class="text nav-text">Notifications</span>
                        <span class="badge rounded-pill bg-danger" 
                              id="notif-count" 
                              style="display: none; position: absolute; right: 20px;">
                        </span>
                    </a>
                </li>
                
                <li>
                    <a href="<?= BASE_URL ?>views/settings.php">
                        <i class='bx bx-cog icon'></i>
                        <span class="text nav-text">Paramètres</span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>views/auth/logout.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Déconnexion</span>
                    </a>
                </li>
            
                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Mode sombre</span>

                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
            </ul> 
        </div> 
    </div>
</nav>

<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="background-color: var(--card-bg); color: var(--text-color);">
            <div class="modal-header" style="border-bottom-color: var(--table-border);">
                <h5 class="modal-title fw-bold" id="notificationModalLabel" style="color: var(--secondary-color);">
                    <i class='bx bx-bell me-2'></i> Centre de Notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="notif-modal-body">
                <p class="text-center text-muted p-4">Chargement des notifications...</p>
            </div>
            <div class="modal-footer justify-content-center" style="border-top-color: var(--table-border);">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                
                <a href="<?= BASE_URL . $settings_target ?>" 
                   class="btn btn-sm" 
                   style="background-color: var(--primary-color); color: white;">
                   Voir tout l'historique
                </a>
            </div>
        </div>
    </div>
</div>