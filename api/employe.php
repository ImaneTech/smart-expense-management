<?php
// =============================================
// ===============  API EMPLOYÉ  ===============
// =============================================

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


    // =========================================================
    //                    ROUTES / ACTIONS
    // =========================================================

    if ($action === 'getDemandeStats') {


        $sql = "SELECT statut, COUNT(*) AS total FROM demande_frais WHERE user_id = ? GROUP BY statut";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = ['en_attente' => 0, 'validees' => 0, 'rejetees' => 0];
        foreach ($rows as $r) {
            if ($r['statut'] === 'En attente') {
                $stats['en_attente'] += (int)$r['total'];
            } elseif ($r['statut'] === 'Validée' || $r['statut'] === 'Validée Manager') {
                $stats['validees'] += (int)$r['total'];
            } elseif ($r['statut'] === 'Rejetée' || $r['statut'] === 'Rejetée Manager') {
                $stats['rejetees'] += (int)$r['total'];
            }
        }

        echo json_encode(['success' => true, 'stats' => $stats]);

    } elseif ($action === 'getRecentDemandes') {

        $limit = (int)($_REQUEST['limit'] ?? 6);
        
        $sql = "SELECT df.*, 
                       (SELECT SUM(det.montant) FROM details_frais det WHERE det.demande_id = df.id) AS montant_total
                FROM demande_frais df
                WHERE df.user_id = ?
                ORDER BY df.created_at DESC
                LIMIT " . $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employeId]);
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'demandes' => $demandes]);

    } elseif ($action === 'getDemandes') {

        $sql = "SELECT df.*, 
                       (SELECT SUM(det.montant) FROM details_frais det WHERE det.demande_id = df.id) AS montant_total
                FROM demande_frais df
                WHERE df.user_id = ?
                ORDER BY df.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employeId]);
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'demandes' => $demandes]);

    } elseif ($action === 'submitDemande') {

        echo json_encode(['success' => false, 'message' => 'Not implemented yet']);

    } elseif ($action === 'getDashboardData') {

        $statsData = [];
        $sql = "SELECT statut, COUNT(*) AS total FROM demande_frais WHERE user_id = ? GROUP BY statut";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $r) {
            $statsData[$r['statut']] = (int)$r['total'];
        }

        echo json_encode(['success' => true, 'data' => ['stats' => $statsData]]);

    } elseif ($action === 'deleteDemande') {

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


        $stmt = $pdo->prepare("SELECT statut FROM demande_frais WHERE id = ? AND user_id = ?");
        $stmt->execute([$demandeId, $employeId]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$demande) {
            http_response_code(403);
            echo json_encode(['error' => 'Demande introuvable ou accès refusé.']);
            exit;
        }

        if ($demande['statut'] !== 'En attente') {
            http_response_code(403);
            echo json_encode(['error' => 'Seules les demandes en attente peuvent être supprimées.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM demande_frais WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$demandeId, $employeId]);

    if ($success) {
            require_once __DIR__ . '/../includes/flash.php';
            setFlash('success', 'Demande supprimée avec succès !');
            echo json_encode(['success' => true, 'message' => 'Demande supprimée avec succès.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la suppression.']);
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
