<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Fichier: controller/notifications_api.php 
header('Content-Type: application/json');

// ----------------------------------------------------
// 1. Initialisation & Sécurité
// ----------------------------------------------------
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Accès non autorisé.']);
    exit;
}
$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/../config.php'; 
// 2. Inclusions et Dépendances
require_once BASE_PATH . 'Models/NotificationModel.php';


// ----------------------------------------------------
// 3 Rendre $pdo accessible
// ----------------------------------------------------
global $pdo; 


// 4. Utilisation
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Vérification de sécurité améliorée pour diagnostiquer le 500
    http_response_code(500);
    echo json_encode(['error' => 'Erreur Critique: Connexion \$pdo non définie après inclusion de config.php.']);
    exit;
}
$notificationModel = new Notification($pdo);
$action = $_GET['action'] ?? '';
$response = [];

try {
    switch ($action) {
        
        case 'count':
            $count = $notificationModel->compterNonLues($user_id);
            $response = ['total' => $count];
            break;

        case 'list':
            // Retourne seulement la liste. NE MARQUE PAS COMME LU ICI.
            $notifications = $notificationModel->listerNotifications($user_id, 8);
            $response = ['notifications' => $notifications];
            break;
            
        case 'mark_as_read': 
            $success = $notificationModel->marquerCommeLues($user_id);
            // Après le marquage, on renvoie le nouveau compte
            $count = $notificationModel->compterNonLues($user_id); 
            $response = ['success' => $success, 'total' => $count];
            break;
            
        default:
            http_response_code(400);
            $response = ['error' => 'Action non valide.'];
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);

    $response = ['error' => 'Erreur de base de données (vérifiez les requêtes SQL).']; 
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'Erreur serveur: ' . $e->getMessage()];
}

echo json_encode($response);
exit;