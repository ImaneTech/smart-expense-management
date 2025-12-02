<<<<<<< HEAD
session_start();

// 1. Redirection si pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Récupération des infos
$role = $_SESSION['role'];
$name = $_SESSION['first_name'] ?? 'Utilisateur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard</title>
    
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">

    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; background: #f4f4f4; }
        
        .home-section {
            position: relative;
            left: 250px; /* Largeur sidebar ouverte */
            width: calc(100% - 250px);
            padding: 50px;
            transition: all 0.3s ease;
        }

        /* Ajustement quand la sidebar se ferme */
        .sidebar.close ~ .home-section {
            left: 88px;
            width: calc(100% - 88px);
        }

        .box-test {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>


    <section class="home-section">
        
        <h1>Test de connexion</h1>
        <p>Bonjour, <strong><?php echo htmlspecialchars($name); ?></strong>.</p>
        
        <div class="box-test">
            <h3>Diagnostic Rôle :</h3>
            <p>Votre rôle en base de données est : <span style="font-size: 20px; font-weight: bold; color: blue; text-transform: uppercase;"><?php echo $role; ?></span></p>
            
            <hr>

            <?php if($role === 'admin'): ?>
                <p style="color: red; font-weight: bold;">🔴 VUE ADMIN : Je vois tout (Utilisateurs, Config).</p>
            <?php elseif($role === 'manager'): ?>
                <p style="color: orange; font-weight: bold;">🟠 VUE MANAGER : Je vois la validation d'équipe.</p>
            <?php else: ?>
                <p style="color: green; font-weight: bold;">🟢 VUE EMPLOYÉ : Je vois mes demandes.</p>
            <?php endif; ?>
        </div>

    </section>

    <script src="../../assets/js/sidebar.js"></script>

</body>
</html>