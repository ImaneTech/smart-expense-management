<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
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
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de connexion BDD'
    ]));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        
        // ============================================
        // RÉCUPÉRER TOUTES LES DEMANDES D'UN UTILISATEUR
        // ============================================
        case 'get_all_user_demandes':
            $user_id = $_GET['user_id'] ?? null;
            
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
                    ORDER BY d.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            die(json_encode($demandes ?: []));
        
        // ============================================
        // AJOUTER UNE NOUVELLE DEMANDE
        // ============================================
        case 'add_demande':
            $user_id = $_POST['user_id'] ?? null;
            
            if (!$user_id) {
                throw new Exception('ID utilisateur manquant');
            }
            
            // Validation des champs requis
            $required_fields = ['objet_mission', 'lieu_deplacement', 'date_depart', 'date_retour', 'type_frais', 'montant'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Le champ {$field} est requis");
                }
            }
            
            // Gérer l'upload du justificatif
            $justificatif_path = null;
            if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
                // Vérifier la taille du fichier (5MB max)
                if ($_FILES['justificatif']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Le fichier ne doit pas dépasser 5MB');
                }
                
                // Vérifier l'extension
                $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
                $file_ext = strtolower(pathinfo($_FILES['justificatif']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($file_ext, $allowed_extensions)) {
                    throw new Exception('Format de fichier non autorisé');
                }
                
                // Créer le dossier uploads s'il n'existe pas
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Générer un nom unique
                $file_name = 'justif_' . uniqid() . '_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;
                
                // Déplacer le fichier
                if (move_uploaded_file($_FILES['justificatif']['tmp_name'], $file_path)) {
                    $justificatif_path = $file_name;
                } else {
                    throw new Exception('Erreur lors de l\'upload du fichier');
                }
            }
            
            // Commencer une transaction
            $pdo->beginTransaction();
            
            try {
                // Insérer la demande de frais
                $sql = "INSERT INTO demande_frais 
                        (user_id, objet_mission, lieu_deplacement, date_depart, date_retour, statut, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'En attente', NOW())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $user_id,
                    $_POST['objet_mission'],
                    $_POST['lieu_deplacement'],
                    $_POST['date_depart'],
                    $_POST['date_retour']
                ]);
                
                $demande_id = $pdo->lastInsertId();
                
                // Insérer le détail des frais
                $sql = "INSERT INTO details_frais 
                        (demande_id, type_frais, montant, description, justificatif_path) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $demande_id,
                    $_POST['type_frais'],
                    floatval($_POST['montant']),
                    $_POST['description'] ?? null,
                    $justificatif_path
                ]);
                
                // Valider la transaction
                $pdo->commit();
                
                die(json_encode([
                    'success' => true,
                    'message' => 'Demande créée avec succès',
                    'demande_id' => $demande_id
                ]));
                
            } catch (Exception $e) {
                // Annuler la transaction en cas d'erreur
                $pdo->rollBack();
                
                // Supprimer le fichier uploadé si la transaction échoue
                if ($justificatif_path && file_exists($upload_dir . $justificatif_path)) {
                    unlink($upload_dir . $justificatif_path);
                }
                
                throw $e;
            }
        
        // ============================================
        // ACTION NON RECONNUE
        // ============================================
        default:
            http_response_code(400);
            die(json_encode([
                'success' => false,
                'message' => 'Action non reconnue',
                'action' => $action
            ]));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]));
}
?>