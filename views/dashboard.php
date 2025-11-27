<?php
session_start();

// 1. Sécurité : Vérifier si connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Récupération du rôle
$role = $_SESSION['role']; // 'admin', 'manager', ou 'employe'

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GoTrackr - Tableau de bord</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <section class="home-section">
        <div class="main-content">
            
            <?php
            // 4. LOGIQUE D'AFFICHAGE DYNAMIQUE
            // On charge un fichier différent selon le rôle
            switch($role) {
                case 'admin':
                    include __DIR__ . '/admin/dashboard_admin.php';
                    break;
                
                case 'manager':
                    include __DIR__ . '/manager/dashboard_manager.php';
                    break;
                
                case 'employe':
                default:
                    include __DIR__ . '/employe/dashboard_employe.php';
                    break;
            }
            ?>

        </div>
    </section>

    <script src="../assets/js/sidebar.js"></script>
</body>
</html>