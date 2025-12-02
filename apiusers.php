<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration de la base de données
$host = 'localhost';
$dbname = 'gestion_frais_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_stats':
        getStats($pdo);
        break;
    
    case 'get_users':
        getUsers($pdo);
        break;
    
    case 'create':
        createUser($pdo);
        break;
    
    case 'update':
        updateUser($pdo);
        break;
    
    case 'delete':
        deleteUser($pdo);
        break;
    
    case 'export':
        exportUsers($pdo);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}

function getStats($pdo) {
    try {
        // Compter les visiteurs
        $stmtVisiteurs = $pdo->query("SELECT COUNT(*) FROM visiteur");
        $visiteurs = $stmtVisiteurs->fetchColumn();
        
        // Compter les managers
        $stmtManagers = $pdo->query("SELECT COUNT(*) FROM manager");
        $managers = $stmtManagers->fetchColumn();
        
        // Compter les admins
        $stmtAdmins = $pdo->query("SELECT COUNT(*) FROM admin");
        $admins = $stmtAdmins->fetchColumn();
        
        echo json_encode([
            'total' => $visiteurs + $managers + $admins,
            'visiteurs' => $visiteurs,
            'managers' => $managers,
            'admins' => $admins
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getUsers($pdo) {
    try {
        $role = $_GET['role'] ?? null;
        $users = [];
        
        if (!$role || $role === 'all') {
            // Récupérer tous les utilisateurs de toutes les tables
            $visiteurs = $pdo->query("SELECT id, nom, email, 'visiteur' AS role FROM visiteur")->fetchAll(PDO::FETCH_ASSOC);
            $managers = $pdo->query("SELECT id, nom, email, 'manager' AS role FROM manager")->fetchAll(PDO::FETCH_ASSOC);
            $admins = $pdo->query("SELECT id, nom, email, 'admin' AS role FROM admin")->fetchAll(PDO::FETCH_ASSOC);
            
            $users = array_merge($visiteurs, $managers, $admins);
        } else if ($role === 'visiteur') {
            $users = $pdo->query("SELECT id, nom, email, 'visiteur' AS role FROM visiteur")->fetchAll(PDO::FETCH_ASSOC);
        } else if ($role === 'manager') {
            $users = $pdo->query("SELECT id, nom, email, 'manager' AS role FROM manager")->fetchAll(PDO::FETCH_ASSOC);
        } else if ($role === 'admin') {
            $users = $pdo->query("SELECT id, nom, email, 'admin' AS role FROM admin")->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode($users);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function createUser($pdo) {
    try {
        $nom = $_POST['nom'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'visiteur';
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($nom) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
            return;
        }

        // Vérifier si l'email existe déjà dans les 3 tables
        $checkEmail = $pdo->prepare("
            SELECT email FROM visiteur WHERE email = ?
            UNION
            SELECT email FROM manager WHERE email = ?
            UNION
            SELECT email FROM admin WHERE email = ?
        ");
        $checkEmail->execute([$email, $email, $email]);
        if ($checkEmail->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            return;
        }

        // Hash du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Déterminer la table selon le rôle
        $table = '';
        switch ($role) {
            case 'visiteur':
                $table = 'visiteur';
                break;
            case 'manager':
                $table = 'manager';
                break;
            case 'admin':
                $table = 'admin';
                break;
            default:
                $table = 'visiteur';
        }

        // Insertion dans la table appropriée
        $stmt = $pdo->prepare("
            INSERT INTO $table (nom, email, password) 
            VALUES (?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $nom,
            $email,
            $hashedPassword
        ]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function updateUser($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $nom = $_POST['nom'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'visiteur';
        $old_role = $_POST['old_role'] ?? $role;

        // Validation
        if (empty($nom) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
            return;
        }

        // Déterminer les tables
        $oldTable = '';
        $newTable = '';
        
        switch ($old_role) {
            case 'visiteur': $oldTable = 'visiteur'; break;
            case 'manager': $oldTable = 'manager'; break;
            case 'admin': $oldTable = 'admin'; break;
        }
        
        switch ($role) {
            case 'visiteur': $newTable = 'visiteur'; break;
            case 'manager': $newTable = 'manager'; break;
            case 'admin': $newTable = 'admin'; break;
        }

        // Si le rôle a changé, on doit copier vers la nouvelle table et supprimer de l'ancienne
        if ($oldTable !== $newTable) {
            // Récupérer le mot de passe de l'ancienne table
            $stmtPassword = $pdo->prepare("SELECT password FROM $oldTable WHERE id = ?");
            $stmtPassword->execute([$id]);
            $oldPassword = $stmtPassword->fetchColumn();

            // Insérer dans la nouvelle table
            $stmtInsert = $pdo->prepare("
                INSERT INTO $newTable (nom, email, password) 
                VALUES (?, ?, ?)
            ");
            $stmtInsert->execute([
                $nom,
                $email,
                $oldPassword
            ]);

            // Supprimer de l'ancienne table
            $stmtDelete = $pdo->prepare("DELETE FROM $oldTable WHERE id = ?");
            $stmtDelete->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Utilisateur modifié avec succès (rôle changé)']);
        } else {
            // Mise à jour simple dans la même table
            $stmt = $pdo->prepare("
                UPDATE $newTable 
                SET nom = ?, email = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $nom,
                $email,
                $id
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Utilisateur modifié avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function deleteUser($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $role = $_POST['role'] ?? '';

        if (!$id || !$role) {
            echo json_encode(['success' => false, 'message' => 'ID et rôle utilisateur manquants']);
            return;
        }

        // Déterminer la table
        $table = '';
        switch ($role) {
            case 'visiteur': $table = 'visiteur'; break;
            case 'manager': $table = 'manager'; break;
            case 'admin': $table = 'admin'; break;
            default:
                echo json_encode(['success' => false, 'message' => 'Rôle invalide']);
                return;
        }

        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function exportUsers($pdo) {
    try {
        // Récupérer tous les utilisateurs
        $visiteurs = $pdo->query("SELECT id, nom, email, 'Visiteur' AS role FROM visiteur")->fetchAll(PDO::FETCH_ASSOC);
        $managers = $pdo->query("SELECT id, nom, email, 'Manager' AS role FROM manager")->fetchAll(PDO::FETCH_ASSOC);
        $admins = $pdo->query("SELECT id, nom, email, 'Admin' AS role FROM admin")->fetchAll(PDO::FETCH_ASSOC);
        
        $users = array_merge($visiteurs, $managers, $admins);

        // Création du fichier CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=utilisateurs_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, ['ID', 'Nom', 'Email', 'Rôle']);
        
        // Données
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['nom'],
                $user['email'],
                $user['role']
            ]);
        }
        
        fclose($output);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>