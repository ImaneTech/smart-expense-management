<?php

// Autoriser les requêtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/CategorieFrais.php';
require_once __DIR__ . '/models/DetailsFrais.php';
require_once __DIR__ . '/models/HistoriqueStatus.php';
require_once __DIR__ . '/models/Demande.php';

$userModel = new UserModel($pdo);
$categorieSrv = new CategorieFrais($pdo);
$detailsSrv = new DetailsFrais($pdo);
$historiqueSrv = new HistoriqueStatus($pdo);
$demandeSrv = new Demande($pdo);

// Action attendu par ton front-end
$action = $_REQUEST['action'] ?? null;

try {
    switch ($action) {

        // ---------------- STATS ----------------
        case 'get_stats':
            $rows = $pdo->query("SELECT statut, COUNT(*) AS total FROM demande_frais GROUP BY statut")->fetchAll(PDO::FETCH_ASSOC);
            $out = ['validees_manager' => 0, 'en_attente' => 0, 'rejetees' => 0];
            foreach ($rows as $r) {
                $s = $r['statut'];
                $t = (int)$r['total'];
                if ($s === 'Validée Manager' || $s === 'Approuvée Compta' || $s === 'Payée') $out['validees_manager'] += $t;
                elseif ($s === 'En attente') $out['en_attente'] += $t;
                elseif ($s === 'Rejetée Manager') $out['rejetees'] += $t;
            }
            echo json_encode($out);
            break;

        // ---------------- GET DEMANDE BY ID ----------------
        case 'get_demande_by_id':
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM demande_frais WHERE id = ?");
            $stmt->execute([$id]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($demande);
            break;

        // ---------------- UPDATE DEMANDE ----------------
        case 'update_demande':
            error_log("UPDATE_DEMANDE - POST data: " . print_r($_POST, true));
            
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id === 0) {
                echo json_encode(['success' => false, 'message' => 'ID manquant']);
                break;
            }
            
            $user_id = (int)($_POST['user_id'] ?? 0);
            $objet_mission = $_POST['objet_mission'] ?? '';
            $lieu_deplacement = $_POST['lieu_deplacement'] ?? '';
            $date_depart = $_POST['date_depart'] ?? '';
            $date_retour = $_POST['date_retour'] ?? '';
            $statut = $_POST['statut'] ?? 'En attente';
            $manager_id = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;
            $manager_id_validation = !empty($_POST['manager_id_validation']) ? (int)$_POST['manager_id_validation'] : null;
            $date_traitement = !empty($_POST['date_traitement']) ? $_POST['date_traitement'] : null;
            $commentaire_manager = $_POST['commentaire_manager'] ?? null;

            if (!$user_id || !$objet_mission || !$lieu_deplacement || !$date_depart || !$date_retour) {
                echo json_encode(['success' => false, 'message' => 'Données obligatoires manquantes']);
                break;
            }

            try {
                $stmt = $pdo->prepare("
                  UPDATE demande_frais 
                  SET user_id = ?, objet_mission = ?, lieu_deplacement = ?, 
                  date_depart = ?, date_retour = ?, statut = ?,
                  manager_id = ?, manager_id_validation = ?, 
                  date_traitement = ?, commentaire_manager = ?
                  WHERE id = ?
                ");

                $ok = $stmt->execute([
                   $user_id, $objet_mission, $lieu_deplacement,
                   $date_depart, $date_retour, $statut,
                   $manager_id, $manager_id_validation,
                   $date_traitement, $commentaire_manager, $id
                ]);

                if ($ok) {
                    echo json_encode(['success' => true, 'message' => 'Demande mise à jour']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour']);
                }
            } catch (PDOException $e) {
                error_log("Erreur SQL: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            break;

        // ---------------- GET DEMANDES ----------------
        case 'get_demandes':
            $statut = $_GET['statut'] ?? null;
            // map front statut -> DB statut
            if ($statut === 'en_attente') $statutDB = 'En attente';
            elseif ($statut === 'validee_manager') $statutDB = 'Validée Manager';
            elseif ($statut === 'validee_admin') $statutDB = 'Approuvée Compta';
            elseif ($statut === 'rejetee') $statutDB = 'Rejetée Manager';
            else $statutDB = null;

            $rows = $demandeSrv->getAll($statutDB);

            $out = array_map(function($d) use ($pdo) {
                $statutFront = match($d['statut']) {
                    'En attente' => 'en_attente',
                    'Validée Manager' => 'validee_manager',
                    'Approuvée Compta' => 'validee_admin',
                    'Rejetée Manager' => 'rejetee',
                    'Payée' => 'payee',
                    default => 'en_attente'
                };
                
                // Récupérer le premier justificatif de la demande
                $justifStmt = $pdo->prepare("SELECT justificatif_path FROM details_frais WHERE demande_id = ? AND justificatif_path IS NOT NULL LIMIT 1");
                $justifStmt->execute([$d['id']]);
                $justificatif = $justifStmt->fetchColumn();
                
                // Calculer le montant total
                $montantStmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM details_frais WHERE demande_id = ?");
                $montantStmt->execute([$d['id']]);
                $montantTotal = $montantStmt->fetchColumn();
                
                return [
                    'id' => (int)$d['id'],
                    'user_id' => (int)$d['user_id'],
                    'utilisateur' => $d['user_nom'] ?? ($d['first_name'] . ' ' . $d['last_name']),
                    'utilisateur_nom' => $d['user_nom'] ?? ($d['first_name'] . ' ' . $d['last_name']),
                    'objectif' => $d['objet_mission'],
                    'objet_mission' => $d['objet_mission'],
                    'date' => $d['created_at'],
                    'created_at' => $d['created_at'],
                    'montant_total' => (float)$montantTotal,
                    'justificatif' => $justificatif ?: null,
                    'statut' => $statutFront,
                    'lieu_deplacement' => $d['lieu_deplacement'],
                    'date_depart' => $d['date_depart'],
                    'date_retour' => $d['date_retour'],
                    'manager_id' => $d['manager_id'] ?? null,
                    'manager_id_validation' => $d['manager_id_validation'] ?? null,
                    'date_traitement' => $d['date_traitement'] ?? null,
                    'commentaire_manager' => $d['commentaire_manager'] ?? null
                ];
            }, $rows);

            echo json_encode($out);
            break;

        // ---------------- CREATE ----------------
        case 'create':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $objet_mission = trim($_POST['objet_mission'] ?? $_POST['objectif'] ?? '');
            $lieu_deplacement = trim($_POST['lieu_deplacement'] ?? '');
            $date_depart = $_POST['date_depart'] ?? date('Y-m-d');
            $date_retour = $_POST['date_retour'] ?? date('Y-m-d');
            $montant = (float)($_POST['montant'] ?? 0);
            $statut = $_POST['statut'] ?? 'En attente';
            $manager_id = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;

            if ($user_id === 0 || $objet_mission === '') {
                echo json_encode(['success' => false, 'message' => 'User ID et objectif requis']);
                break;
            }

            try {
                // Créer la demande avec tous les champs
                $stmt = $pdo->prepare("
                    INSERT INTO demande_frais (user_id, objet_mission, lieu_deplacement, date_depart, date_retour, statut, manager_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$user_id, $objet_mission, $lieu_deplacement, $date_depart, $date_retour, $statut, $manager_id]);
                $demande_id = (int)$pdo->lastInsertId();

                if ($demande_id === 0) {
                    echo json_encode(['success' => false, 'message' => 'Erreur création demande']);
                    break;
                }

                // si montant > 0, ajouter un détail simple
                if ($montant > 0) {
                    $cat = $pdo->query("SELECT id FROM categories_frais LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                    if ($cat) $catId = (int)$cat['id'];
                    else {
                        $pdo->prepare("INSERT INTO categories_frais (nom, description) VALUES (?, ?)")->execute(['Autre','Catégorie automatique']);
                        $catId = (int)$pdo->lastInsertId();
                    }
                    $detailsSrv->addDetail($demande_id, $catId, $date_depart, $montant, 'Montant initial', null);
                }

                echo json_encode(['success' => true, 'id' => $demande_id]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
            }
            break;

        // ---------------- UPDATE STATUS ----------------
        case 'update_status':
            $id = (int)($_POST['id'] ?? 0);
            $statut = $_POST['statut'] ?? '';
            $user_id = (int)($_POST['user_id'] ?? 0);

            if ($statut === 'validee_manager') $statutDB = 'Validée Manager';
            elseif ($statut === 'en_attente') $statutDB = 'En attente';
            elseif ($statut === 'rejetee') $statutDB = 'Rejetée Manager';
            else $statutDB = $statut;

            $ok = $demandeSrv->changerStatut($id, $statutDB, $user_id);
            echo json_encode(['success' => (bool)$ok]);
            break;

        // ---------------- DELETE ----------------
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            $ok = $demandeSrv->supprimer($id);
            echo json_encode(['success' => (bool)$ok]);
            break;

        // ---------------- EXPORT CSV ----------------
        case 'export':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=demandes.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Utilisateur','Objet mission','Lieu','Date départ','Date retour','Montant total','Statut','Date création']);
            $rows = $demandeSrv->getAll(null);
            foreach ($rows as $r) {
                $montantStmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM details_frais WHERE demande_id = ?");
                $montantStmt->execute([$r['id']]);
                $montantTotal = $montantStmt->fetchColumn();
                
                fputcsv($out, [
                    $r['id'],
                    $r['user_nom'] ?? ($r['first_name'] . ' ' . $r['last_name']),
                    $r['objet_mission'],
                    $r['lieu_deplacement'],
                    $r['date_depart'],
                    $r['date_retour'],
                    $montantTotal,
                    $r['statut'],
                    $r['created_at']
                ]);
            }
            fclose($out);
            exit;
            break;

        // ---------------- UPLOAD JUSTIFICATIF ----------------
        case 'upload_justificatif':
            if (!isset($_FILES['justificatif']) || !isset($_POST['demande_id'])) {
                echo json_encode(['success' => false, 'message' => 'Fichier ou demande_id manquant']);
                break;
            }
            if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
            $demande_id = (int)$_POST['demande_id'];
            $file = $_FILES['justificatif'];
            $allowed = ['image/png','image/jpeg','application/pdf'];
            if (!in_array($file['type'], $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Type non autorisé']);
                break;
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'justif_'.$demande_id.'_'.time().'.'.$ext;
            $dest = __DIR__ . '/uploads/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                echo json_encode(['success' => false, 'message' => 'Erreur upload']);
                break;
            }
            
            // Mettre à jour le premier détail de la demande avec le justificatif
            $updateStmt = $pdo->prepare("UPDATE details_frais SET justificatif_path = ? WHERE demande_id = ? LIMIT 1");
            $updateStmt->execute([$filename, $demande_id]);
            
            echo json_encode(['success' => true, 'filename' => $filename]);
            break;

        // ---------------- CATEGORIES ----------------
        case 'categories':
            echo json_encode($categorieSrv->getAll());
            break;

        // ---------------- ADD DETAIL ----------------
        case 'add_detail':
            $demande_id = (int)($_POST['demande_id'] ?? 0);
            $categorie_id = (int)($_POST['categorie_id'] ?? 0);
            $date_depense = $_POST['date_depense'] ?? date('Y-m-d');
            $montant = (float)($_POST['montant'] ?? 0);
            $desc = $_POST['description'] ?? null;
            $justif = $_POST['justificatif_path'] ?? null;
            $ok = $detailsSrv->addDetail($demande_id, $categorie_id, $date_depense, $montant, $desc, $justif);
            if ($ok) {
                $total = $demandeSrv->majMontantTotal($demande_id);
                echo json_encode(['success' => true, 'montant_total' => $total]);
            } else echo json_encode(['success' => false]);
            break;

        // ---------------- DETAILS ----------------
        case 'details':
            $demande_id = (int)($_GET['demande_id'] ?? 0);
            echo json_encode($detailsSrv->getByDemande($demande_id));
            break;

        // ---------------- LOGIN ----------------
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $user = $userModel->findUserByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']); // Ne pas renvoyer le mot de passe
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Credentials invalides']);
            }
            break;

        default:
            echo json_encode(['error' => 'Action inconnue: ' . $action]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erreur API: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}