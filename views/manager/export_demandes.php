<?php
// export_demandes.php - CODE FINAL CORRIGÉ (Optimisé pour l'affichage en colonnes)

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'controllers/DemandeController.php';
require_once BASE_PATH . 'controllers/UserController.php';

// ⚠️ Définition de la fonction getCurrencySymbol()
function getCurrencySymbol(string $code): string {
    return match (strtoupper($code)) {
        'EUR' => '€',
        'USD' => '$',
        'MAD' => 'Dhs',
        'GBP' => '£',
        default => '€',
    };
}
// ----------------------------------------------------

// ⚠️ Assurez-vous que $pdo est disponible et initialisé par config.php
if (!isset($pdo)) {
    die("Erreur Fatale : La variable \$pdo n'est pas définie après l'inclusion de config.php.");
}

$controller = new DemandeController($pdo);
$userController = new UserController($pdo);

// 1. Récupérer le statut de filtre depuis l'URL
$statutFiltre = $_GET['statut'] ?? 'toutes';

// 2. Charger les demandes
$demandes = $controller->getDemandesList($statutFiltre);

// 3. Récupérer le symbole de devise pour affichage
$managerId = $controller->getManagerId(); 
$managerCurrencyCode = $userController->getPreferredCurrency($managerId);
$currencySymbol = getCurrencySymbol($managerCurrencyCode);


// 4. Configuration des en-têtes pour le téléchargement CSV
$filename = "demandes_" . str_replace(' ', '_', strtolower($statutFiltre)) . "_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Expires: 0");
header("Pragma: public");

// Ouvrir le flux de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM (Byte Order Mark) pour la compatibilité Excel
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// **CHANGEMENT CLÉ 1 : Définir le séparateur de champ sur la virgule ','**
$delimiter = ','; 
// **CHANGEMENT CLÉ 2 : Définir le caractère d'encadrement sur les guillemets doubles '"'**
$enclosure = '"';


// 5. Définir les en-têtes de la colonne
$header = [
    'Nom Complet Employé',
    'Email Employé',
    'Objet Mission',
    'Date de Départ',
    'Montant Total (' . $currencySymbol . ')',
    'Statut de la Demande'
];
fputcsv($output, $header, $delimiter, $enclosure); 


// 6. Remplir le fichier CSV avec les données
if (!empty($demandes)) {
    foreach ($demandes as $demande) {
        $row = [
            $demande['first_name'] . ' ' . $demande['last_name'],
            $demande['email'],
            $demande['objet_mission'],
            date('d/m/Y', strtotime($demande['date_depart'])),
            // **CHANGEMENT CLÉ 3 : Utilisation du point '.' comme séparateur décimal (standard CSV)**
            number_format($demande['total_calcule'] ?? 0, 2, '.', ''), 
            $demande['statut'] ?? 'Inconnu'
        ];
        fputcsv($output, $row, $delimiter, $enclosure);
    }
}

// Fermer le flux
fclose($output);
exit; 
?>