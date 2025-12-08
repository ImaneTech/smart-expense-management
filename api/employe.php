<?php
use Controllers\Employe\EmployeController; 

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
require_once __DIR__ . '/../Controllers/Employe/EmployeController.php'; // Contrôleur spécifique Employé
require_once __DIR__ . '/../Models/Employe/DemandeFraisModel.php';

// Vérification de la connexion PDO
if (!isset($pdo) || !$pdo instanceof \PDO) { // Utilisation de \PDO pour éviter les erreurs de namespace
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base de données.']);
    exit;
}

// --- ROUTAGE PRINCIPAL ---
$action = $_REQUEST['action'] ?? null;
$requestData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

// L'ID de l'employé DOIT être récupéré de la session/JWT
// SIMULATION : Remplacez ceci par une récupération de session réelle
$employeId = 15; // $_SESSION['user_id'] ou autre

// DÉLÉGUER À UN CONTRÔLEUR
$controller = new EmployeController($pdo, $employeId, BASE_PATH); // Les 3 arguments sont corrects
$controller->handleApiRequest($action, $requestData, $_FILES); // IMPORTANT : Passer $_FILES

// Note : EmployeController devra garantir que toutes les opérations 
// (CREATE, READ, UPDATE, DELETE) sont limitées à l'ID de l'employé connecté.