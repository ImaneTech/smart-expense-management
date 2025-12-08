<?php
use App\Controllers\ManagerController; 

// --- GESTION CORS ET EN-TÊTE ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// --- INCLUSIONS ET INITIALISATION ---
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/ManagerController.php'; // Contrôleur spécifique Manager

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base de données.']);
    exit;
}

// --- ROUTAGE PRINCIPAL ---
$action = $_REQUEST['action'] ?? null;
$requestData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

// L'ID du manager DOIT être récupéré de la session/JWT
// SIMULATION : Remplacez ceci par une récupération de session réelle
$managerId = 42; // $_SESSION['user_id'] ou autre

// DÉLÉGUER À UN CONTRÔLEUR
$controller = new ManagerController($pdo, $managerId); 
$controller->handleApiRequest($action, $requestData);

// Note : ManagerController devra forcer un filtre 'manager_id = $managerId' 
// sur les requêtes pour assurer la sécurité.