<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Inclusion de la classe CategorieFrais
require_once __DIR__ . '/models/CategorieFrais.php';

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

$categorieService = new CategorieFrais($pdo);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_categories':
        getCategories($categorieService);
        break;
    
    case 'create':
        createCategorie($categorieService);
        break;
    
    case 'update':
        updateCategorie($pdo);
        break;
    
    case 'delete':
        deleteCategorie($pdo);
        break;
    
    case 'export':
        exportCategories($categorieService);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}

function getCategories($categorieService) {
    try {
        $categories = $categorieService->getAll();
        echo json_encode($categories);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function createCategorie($categorieService) {
    try {
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? null;

        if (empty($nom)) {
            echo json_encode(['success' => false, 'message' => 'Le nom de la catégorie est obligatoire']);
            return;
        }

        $result = $categorieService->create($nom, $description);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Catégorie créée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function updateCategorie($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? null;

        if (empty($nom)) {
            echo json_encode(['success' => false, 'message' => 'Le nom de la catégorie est obligatoire']);
            return;
        }

        $stmt = $pdo->prepare("UPDATE categories_frais SET nom = ?, description = ? WHERE id = ?");
        $result = $stmt->execute([$nom, $description, $id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Catégorie modifiée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function deleteCategorie($pdo) {
    try {
        $id = $_POST['id'] ?? 0;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID catégorie manquant']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM categories_frais WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Catégorie supprimée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function exportCategories($categorieService) {
    try {
        $categories = $categorieService->getAll();

        // Création du fichier CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=categories_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, ['ID', 'Nom', 'Description']);
        
        // Données
        foreach ($categories as $categorie) {
            fputcsv($output, [
                $categorie['id'],
                $categorie['nom'],
                $categorie['description']
            ]);
        }
        
        fclose($output);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>