<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
// Configuration base de données
$host = 'localhost';
$dbname = 'gestion_frais_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données',
        'error' => $e->getMessage()
    ]);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // Statistiques utilisateur
        case 'get_user_stats':
            $user_id = $_GET['user_id'] ?? null;
            
            if (!$user_id) {
                throw new Exception('ID utilisateur manquant');
            }
            
            $sql = "SELECT 
                        SUM(CASE WHEN statut = 'En attente' THEN 1 ELSE 0 END) as en_attente,
                        SUM(CASE WHEN statut IN ('Validée Manager', 'Approuvée Compta', 'Payée') THEN 1 ELSE 0 END) as validees,
                        SUM(CASE WHEN statut = 'Rejetée Manager' THEN 1 ELSE 0 END) as rejetees
                    FROM demande_frais 
                    WHERE user_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $stats = $stmt->fetch();
            
            echo json_encode([
                'en_attente' => (int)($stats['en_attente'] ?? 0),
                'validees' => (int)($stats['validees'] ?? 0),
                'rejetees' => (int)($stats['rejetees'] ?? 0)
            ]);
            break;
        
        // Récupérer les dernières demandes d'un utilisateur
        case 'get_user_recent_demandes':
            $user_id = $_GET['user_id'] ?? null;
            $limit = $_GET['limit'] ?? 3;
            
            if (!$user_id) {
                throw new Exception('ID utilisateur manquant');
            }
            
            $sql = "SELECT 
                        d.*,
                        (SELECT SUM(df.montant) 
                         FROM details_frais df 
                         WHERE df.demande_id = d.id) as montant_total,
                        (SELECT df.justificatif_path 
                         FROM details_frais df 
                         WHERE df.demande_id = d.id 
                         LIMIT 1) as justificatif
                    FROM demande_frais d 
                    WHERE d.user_id = ? 
                    ORDER BY d.created_at DESC 
                    LIMIT ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($demandes);
            break;
        
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Action non reconnue',
                'action' => $action
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
}
?>