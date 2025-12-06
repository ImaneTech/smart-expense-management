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
        // Compter par rôle
        $stmtEmployes = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employe'");
        $employes = $stmtEmployes->fetchColumn();
        
        $stmtManagers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'manager'");
        $managers = $stmtManagers->fetchColumn();
        
        $stmtAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $admins = $stmtAdmins->fetchColumn();
        
        echo json_encode([
            'total' => $employes + $managers + $admins,
            'employes' => $employes,
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
        
        if (!$role || $role === 'all') {
            $stmt = $pdo->query("SELECT id, first_name, last_name, email, phone, role, department, manager_id, created_at FROM users ORDER BY created_at DESC");
        } else {
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, role, department, manager_id, created_at FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->execute([$role]);
        }
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ajouter le nom complet pour chaque utilisateur
        foreach ($users as &$user) {
            $user['nom'] = $user['first_name'] . ' ' . $user['last_name'];
        }
        
        echo json_encode($users);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function createUser($pdo) {
    try {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'] ?? 'employe';
        $department = $_POST['department'] ?? '';
        $password = $_POST['password'] ?? '';
        $manager_id = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($department)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
            return;
        }

        // Vérifier si l'email existe déjà
        $checkEmail = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        if ($checkEmail->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            return;
        }

        // Hash du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertion
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone, password, role, department, manager_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $phone,
            $hashedPassword,
            $role,
            $department,
            $manager_id
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
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'] ?? 'employe';
        $department = $_POST['department'] ?? '';
        $manager_id = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($department)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
            return;
        }

        // Vérifier si l'email existe déjà pour un autre utilisateur
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->execute([$email, $id]);
        if ($checkEmail->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            return;
        }

        // Mise à jour
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, phone = ?, role = ?, department = ?, manager_id = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $phone,
            $role,
            $department,
            $manager_id,
            $id
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur modifié avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function deleteUser($pdo) {
    try {
        $id = $_POST['id'] ?? 0;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
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
        $users = $pdo->query("
            SELECT id, first_name, last_name, email, phone, role, department, created_at 
            FROM users 
            ORDER BY created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Création du fichier CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=utilisateurs_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, ['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Rôle', 'Département', 'Date création']);
        
        // Données
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['first_name'],
                $user['last_name'],
                $user['email'],
                $user['phone'],
                $user['role'],
                $user['department'],
                $user['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>