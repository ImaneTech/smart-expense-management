<?php
// Fichier: controller/notifications_api.php (CORRIGÉ POUR LA PORTÉE PDO)
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

// 2. Inclusions et Dépendances
require_once __DIR__ . '/../../config.php'; 
require_once BASE_PATH . 'models/NotificationModel.php';


// ----------------------------------------------------
// 3. CORRECTION CLÉ : Rendre $pdo accessible
// ----------------------------------------------------
global $pdo; // Cette ligne rend la variable $pdo (définie dans config.php) disponible dans ce script.


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
            $notifications = $notificationModel->listerNotifications($user_id, 8);
            
            // Marquage comme lues
            $notificationModel->marquerCommeLues($user_id);
            
            $response = ['notifications' => $notifications];
            break;
            
        default:
            http_response_code(400);
            $response = ['error' => 'Action non valide.'];
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    // En production, ne jamais afficher $e->getMessage()
    $response = ['error' => 'Erreur de base de données (vérifiez les requêtes SQL).']; 
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'Erreur serveur: ' . $e->getMessage()];
}

echo json_encode($response);
exit;