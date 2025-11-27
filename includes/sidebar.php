<?php
// 1. Démarrage de la session (si elle n'est pas déjà active)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Récupération du rôle (par défaut 'employe' si non défini)
// Assurez-vous que lors du login, vous faites : $_SESSION['role'] = 'admin'; (ou autre)
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'employe';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar GoTrackr</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>
    <nav class="sidebar">
        
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="../assets/img/logo3.png" alt="Logo" class="logo">
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
                        <a href="dashboard.php">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Tableau de bord</span>
                        </a>
                    </li>

                    <?php if($role == 'employe' || $role == 'manager'): ?>
                    <li class="nav-link">
                        <a href="add-expense.php">
                            <i class='bx bx-plus-circle icon'></i>
                            <span class="text nav-text">Nouvelle Demande</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="my-expenses.php">
                            <i class='bx bx-list-ul icon'></i>
                            <span class="text nav-text">Mes Frais</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if($role == 'manager'): ?>
                    <li class="nav-link">
                        <a href="team-requests.php">
                            <i class='bx bx-check-shield icon'></i>
                            <span class="text nav-text">Validation Équipe</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="my-team.php">
                            <i class='bx bx-group icon'></i>
                            <span class="text nav-text">Mon Équipe</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if($role == 'admin'): ?>
                    <li class="nav-link">
                        <a href="all-requests.php">
                            <i class='bx bx-file icon'></i>
                            <span class="text nav-text">Toutes Demandes</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="users.php">
                            <i class='bx bx-user-plus icon'></i>
                            <span class="text nav-text">Utilisateurs</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="categories.php">
                            <i class='bx bx-category icon'></i>
                            <span class="text nav-text">Catégories Frais</span>
                        </a>
                    </li>
                    <?php endif; ?>

                </ul>
            </div>

            <div class="bottom-content">
                
                <li>
                    <a href="profile.php">
                        <i class='bx bx-user icon'></i>
                        <span class="text nav-text">Mon Profil</span>
                    </a>
                </li>

                <?php if($role == 'admin'): ?>
                <li>
                    <a href="settings.php">
                        <i class='bx bx-cog icon'></i>
                        <span class="text nav-text">Configuration</span>
                    </a>
                </li>
                <?php endif; ?>

                <li>
                    <a href="logout.php">
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

    <script src="../assets/js/sidebar.js"></script>
</body>
</html>