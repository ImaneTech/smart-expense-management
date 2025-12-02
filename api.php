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

require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/CategorieFrais.php';
require_once __DIR__ . '/models/DetailsFrais.php';
require_once __DIR__ . '/models/HistoriqueStatus.php';
require_once __DIR__ . '/models/Demande.php';

$userSrv = new User('', '', '', '', '', '', '');
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
            $rows = $pdo->query("SELECT statut_actuel, COUNT(*) AS total FROM demandedefrais GROUP BY statut_actuel")->fetchAll(PDO::FETCH_ASSOC);
            $out = ['validees_manager' => 0, 'en_attente' => 0, 'rejetees' => 0];
            foreach ($rows as $r) {
                $s = $r['statut_actuel'];
                $t = (int)$r['total'];
                if ($s === 'validé') $out['validees_manager'] += $t;
                elseif ($s === 'en cours') $out['en_attente'] += $t;
                elseif ($s === 'rejeté') $out['rejetees'] += $t;
            }
            echo json_encode($out);
            break;

        // ---------------- GET DEMANDES ----------------
        case 'get_demandes':
            $statut = $_GET['statut'] ?? null;
            // map front statut -> DB statut
            if ($statut === 'en_attente') $statutDB = 'en cours';
            elseif ($statut === 'validee_manager' || $statut === 'validee_admin') $statutDB = 'validé';
            elseif ($statut === 'rejetee') $statutDB = 'rejeté';
            else $statutDB = null;

            $rows = $demandeSrv->getAll($statutDB);

            $out = array_map(function($d) use ($pdo) {
                $statutFront = $d['statut_actuel'] === 'en cours' ? 'en_attente' : ($d['statut_actuel'] === 'validé' ? 'validee_manager' : 'rejetee');
                
                // Récupérer le premier justificatif de la demande
                $justifStmt = $pdo->prepare("SELECT justificatif FROM detailsfrais WHERE demande_id = ? AND justificatif IS NOT NULL LIMIT 1");
                $justifStmt->execute([$d['id']]);
                $justificatif = $justifStmt->fetchColumn();
                
                return [
                    'id' => (int)$d['id'],
                    'utilisateur' => $d['visiteur_nom'] ?? $d['visiteur_id'],
                    'objectif' => $d['objectif'],
                    'date' => $d['date_creation'],
                    'montant_total' => (float)$d['montant_total'],
                    'justificatif' => $justificatif ?: null,
                    'statut' => $statutFront
                ];
            }, $rows);

            echo json_encode($out);
            break;

        // ---------------- CREATE ----------------
        case 'create':
            // Ton front envoie : utilisateur (nom), objectif, montant
            $utilisateurName = trim($_POST['utilisateur'] ?? '');
            $objectif = trim($_POST['objectif'] ?? '');
            $montant = (float)($_POST['montant'] ?? 0);

            if ($utilisateurName === '' || $objectif === '') {
                echo json_encode(['success' => false, 'message' => 'Utilisateur et objectif requis']);
                break;
            }

            // Trouver visiteur par nom, sinon créer un visiteur minimal
            $visiteur = $pdo->prepare("SELECT * FROM visiteur WHERE nom = ? LIMIT 1");
            $visiteur->execute([$utilisateurName]);
            $v = $visiteur->fetch(PDO::FETCH_ASSOC);
            if ($v) {
                $visiteur_id = (int)$v['id'];
            } else {
                // créer visiteur minimal (email fictif, matricule aléatoire)
                $email = strtolower(preg_replace('/\s+/', '.', $utilisateurName)) . '@example.local';
                $matricule = 'M' . time();
                $stmt = $pdo->prepare("INSERT INTO visiteur (nom, email, password, matricule) VALUES (?, ?, ?, ?)");
                // password par défaut vide hashé — recommande de changer plus tard
                $defaultHash = password_hash('changeme', PASSWORD_DEFAULT);
                $stmt->execute([$utilisateurName, $email, $defaultHash, $matricule]);
                $visiteur_id = (int)$pdo->lastInsertId();
            }

            // créer la demande
            $demande_id = $demandeSrv->creer($visiteur_id, $objectif, date('Y-m-d'));
            if ($demande_id === false) {
                echo json_encode(['success' => false]);
                break;
            }

            // si montant > 0, ajouter un détail simple pour refléter montant_total
            if ($montant > 0) {
                // prends la première catégorie disponible ou crée "Autre"
                $cat = $pdo->query("SELECT id FROM categoriefrais LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($cat) $catId = (int)$cat['id'];
                else {
                    $pdo->prepare("INSERT INTO categoriefrais (nom, description) VALUES (?, ?)")->execute(['Autre','Catégorie automatique']);
                    $catId = (int)$pdo->lastInsertId();
                }
                $detailsSrv->addDetail($demande_id, $catId, $montant, 'Montant initial', null);
                $demandeSrv->majMontantTotal($demande_id);
            }

            echo json_encode(['success' => true, 'id' => $demande_id]);
            break;

        // ---------------- UPDATE STATUS ----------------
        case 'update_status':
            $id = (int)($_POST['id'] ?? 0);
            $statut = $_POST['statut'] ?? '';
            $utilisateur = $_POST['utilisateur'] ?? 'system';

            if ($statut === 'validee_manager' || $statut === 'validee_admin') $statutDB = 'validé';
            elseif ($statut === 'en_attente') $statutDB = 'en cours';
            elseif ($statut === 'rejetee') $statutDB = 'rejeté';
            else $statutDB = $statut;

            $ok = $demandeSrv->changerStatut($id, $statutDB, $utilisateur);
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
            fputcsv($out, ['ID','Visiteur','Objectif','Date mission','Montant total','Statut','Date creation']);
            $rows = $demandeSrv->getAll(null);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['id'],
                    $r['visiteur_nom'] ?? $r['visiteur_id'],
                    $r['objectif'],
                    $r['date_mission'],
                    $r['montant_total'],
                    $r['statut_actuel'],
                    $r['date_creation']
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
            $updateStmt = $pdo->prepare("UPDATE detailsfrais SET justificatif = ? WHERE demande_id = ? LIMIT 1");
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
            $montant = (float)($_POST['montant'] ?? 0);
            $desc = $_POST['description'] ?? null;
            $justif = $_POST['justificatif'] ?? null;
            $ok = $detailsSrv->addDetail($demande_id, $categorie_id, $montant, $desc, $justif);
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
            $role = $_POST['role'] ?? 'visiteur';
            $u = $userSrv->login($email, $password, $role);
            if ($u) echo json_encode(['success' => true, 'user' => $u]);
            else echo json_encode(['success' => false, 'message' => 'Credentials invalides']);
            break;

        default:
            echo json_encode(['error' => 'Action inconnue']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}