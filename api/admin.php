<?php
// DANS api/admin.php

// --- GESTION CORS ET EN-TÊTE ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// NOTE: L'en-tête JSON est géré par les contrôleurs.
        
require_once __DIR__ . '/../config.php'; 

// --- INCLUSIONS DES CONTRÔLEURS ET MODÈLES ---
// Inclusions Demandes
require_once __DIR__ . '/../Controllers/DemandeController.php'; 
require_once __DIR__ . '/../Models/DemandeModel.php'; 

// Inclusions Utilisateurs
require_once __DIR__ . '/../Controllers/UserController.php'; 
require_once __DIR__ . '/../Models/UserModel.php'; 

//  POUR LES CATÉGORIES
require_once __DIR__ . '/../Controllers/CategorieController.php'; 
require_once __DIR__ . '/../Models/CategorieModel.php'; 


// Vérifier la connexion DB ($pdo doit être défini dans config.php)
if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base de données.']);
    exit;
}

// --- ROUTAGE PRINCIPAL ---
$action = $_REQUEST['action'] ?? null;
$requestData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

if (str_starts_with($action, 'user_')) {
    // 1. Déléguer au Contrôleur d'Utilisateurs
    $userAction = substr($action, 5);
    $controller = new UserController($pdo); 
    $controller->handleApiRequest($userAction, $requestData, $requestData); 

} elseif (str_starts_with($action, 'cat_')) {
    // 2.Déléguer au Contrôleur des Catégories
    $catAction = substr($action, 4); 
    $controller = new CategorieController($pdo, false); // false = no auth check for API
    $controller->handleApiRequest($catAction, $requestData, $requestData); 

} else {
    // 3. Déléguer au Contrôleur Admin (Par défaut pour ce fichier)
    require_once __DIR__ . '/../Controllers/AdminController.php';
    $controller = new AdminController($pdo); 
    $controller->handleApiRequest($action, $requestData);
}