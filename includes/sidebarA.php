<?php
// On récupère le rôle s'il n'est pas défini (sécurité)
// POUR TESTER : Décommentez la ligne suivante pour forcer le rôle admin
// $role = 'admin';

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin'; // ← Changé 'employe' en 'admin' par défaut
?>

<nav class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <img src="<?= BASE_URL ?>assets/img/logo3.png" alt="Logo" class="logo">
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

                <!-- SUPPRIMÉ : Bouton Tableau de bord -->

                <?php if($role == 'manager'): ?>
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/manager/demandes.php">
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
                        <i class='bx bx-history icon'></i>
                        <span class="text nav-text">Historique</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if($role == 'admin'): ?>
                
                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/admin/users.php">
                        <i class='bx bx-user-circle icon'></i>
                        <span class="text nav-text">Gestion Utilisateurs</span>
                    </a>
                </li>

                <li class="nav-link">
                    <a href="<?= BASE_URL ?>views/admin/all-requests.php">
                        <i class='bx bx-receipt icon'></i>
                        <span class="text nav-text">Gestion des Frais</span>
                    </a>
                </li>
                
                <?php endif; ?>

            </ul>
        </div>

        <div class="bottom-content">
            
            <li>
                <a href="<?= BASE_URL ?>views/profile.php">
                    <i class='bx bx-user icon'></i>
                    <span class="text nav-text">Mon Profil</span>
                </a>
            </li>

            <?php if($role == 'admin'): ?>
            <li>
                <a href="<?= BASE_URL ?>views/admin/settings.php">
                    <i class='bx bx-cog icon'></i>
                    <span class="text nav-text">Configuration</span>
                </a>
            </li>
            <?php endif; ?>

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
        </div>
    </div>
</nav>