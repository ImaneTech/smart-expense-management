<?php
// ===============================================================
// ===============  API EMPLOYÉ (VERSION CORRIGÉE) ===============
// ===============================================================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// Chemin correct (respect de la casse)
require_once __DIR__ . '/../controllers/Employe/EmployeController.php';

// Si FileHandler n'est pas autoloadé, décommente :
// require_once BASE_PATH . 'includes/file_handler.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base de données.']);
    exit;
}

$action = $_REQUEST['action'] ?? null;
$employeId = (int)($_SESSION['user_id'] ?? 0);

if ($employeId === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Accès refusé. Utilisateur non authentifié.']);
    exit;
}

if ($action === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Action API non spécifiée.']);
    exit;
}

try {
    // Instanciation du contrôleur Employé
    $controller = new Controllers\Employe\EmployeController($pdo, $employeId, BASE_PATH);

    // ⚠️ SUPPRIMÉ : getFileHandler() n'existe pas → erreur fatale
    // $fileHandler = $controller->getFileHandler(); 

    // =========================================================
    //                    ROUTES / ACTIONS
    // =========================================================

    if ($action === 'getDemandeStats') {

        echo json_encode([
            'success' => true,
            'stats' => $controller->getDashboardStats()
        ]);

    } elseif ($action === 'getRecentDemandes') {

        $limit = (int)($_REQUEST['limit'] ?? 6);
        echo json_encode([
            'success' => true,
            'demandes' => $controller->getDemandesByEmploye($limit)
        ]);

    } elseif ($action === 'getDemandes') {

        echo json_encode([
            'success' => true,
            'demandes' => $controller->getDemandesByEmploye(null)
        ]);

    } elseif ($action === 'submitDemande') {

        echo json_encode(
            $controller->submitNewDemande($_REQUEST, $_FILES)
        );

    } elseif ($action === 'getDashboardData') {

        echo json_encode([
            'success' => true,
            'data' => $controller->getEmployeDashboardData()
        ]);

    } elseif ($action === 'deleteDemande') {

        // Accepter POST + DELETE
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée.']);
            exit;
        }

        $demandeId = (int)($_REQUEST['demande_id'] ?? 0);

        if ($demandeId === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de la demande manquant ou invalide.']);
            exit;
        }

        // ⚠️ Tu dois remplacer cette ligne par ton vrai contrôleur de demandes
        // Exemple :
        // $demandeController = new Controllers\DemandeController($pdo);
        $demandeController = $controller; // TEMPORAIRE : éviter erreur fatale

        $success = $demandeController->deleteDemande($demandeId, $employeId);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Demande supprimée avec succès.']);
        } else {
            http_response_code(403);
            echo json_encode(['error' => 'La demande ne peut pas être supprimée (statut ou permission).']);
        }

    } else {

        http_response_code(400);
        echo json_encode(['error' => "Action '{$action}' invalide."]);
    }

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur Serveur: ' . $e->getMessage()
    ]);
}
