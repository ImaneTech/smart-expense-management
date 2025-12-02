<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <style>body{font-family: sans-serif; padding: 30px; line-height: 1.6;} .box{background: #f4f4f4; padding: 20px; border: 1px solid #ddd; border-radius: 8px;} code{background: #333; color: #0f0; padding: 2px 5px;}</style>
    <title>Test Rôle</title>
</head>
<body>
    <h1>Outil de Diagnostic Rôle</h1>
    
    <div class="box">
        <h3>État de la Session Actuelle :</h3>
        <?php if(isset($_SESSION['user_id'])): ?>
            <p><strong>ID Utilisateur :</strong> <?php echo $_SESSION['user_id']; ?></p>
            <p><strong>Nom :</strong> <?php echo $_SESSION['first_name']; ?></p>
            <p><strong>Rôle détecté :</strong> <span style="font-size: 20px; color: blue; font-weight: bold;"><?php echo $_SESSION['role']; ?></span></p>
        <?php else: ?>
            <p style="color: red;">Aucun utilisateur connecté.</p>
        <?php endif; ?>
    </div>

    <h3>Ce que la Sidebar devrait afficher avec ce rôle :</h3>
    <ul>
        <?php if(isset($_SESSION['role'])): $r = $_SESSION['role']; ?>
            
            <li>Menu "Nouvelle Demande" : 
                <?php echo ($r == 'employe' || $r == 'manager') ? '✅ VISIBLE' : '❌ CACHÉ'; ?>
            </li>
            
            <li>Menu "Validation Équipe" : 
                <?php echo ($r == 'manager') ? '✅ VISIBLE' : '❌ CACHÉ'; ?>
            </li>

            <li>Menu "Utilisateurs / Config" : 
                <?php echo ($r == 'admin') ? '✅ VISIBLE' : '❌ CACHÉ'; ?>
            </li>

        <?php endif; ?>
    </ul>
    
    <br>
    <a href="login.php">Aller au Login</a> | <a href="dashboard.php">Aller au Dashboard</a> | <a href="logout.php">Se Déconnecter</a>
</body>
</html>