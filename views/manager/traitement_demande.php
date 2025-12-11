<?php
// views/traitement_demande.php
// Ce script doit être accessible directement par le POST des formulaires

require_once __DIR__ . '/../../config.php';

// Assurez-vous que c'est le bon contrôleur que vous utilisez, 
// selon que vous ayez choisi 'DemandeController' ou 'ManagerController'
// Utilisation de DemandeController comme dans votre dernier prompt corrigé
require_once BASE_PATH . 'Controllers/DemandeController.php';

// Initialisation du contrôleur (gère l'authentification et les dépendances)
// Utilisez $pdo si c'est la variable de connexion à la base de données
// NOTE: Assurez-vous que $pdo est défini par config.php
$controller = new DemandeController($pdo); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demande_id'], $_POST['action'])) {
    
    // NOTE : Les variables $demandeId, $action, $commentaire ne sont pas utilisées
    // directement ici car le contrôleur utilise $_POST.
    // $demandeId = (int)$_POST['demande_id'];
    // $action = $_POST['action'];
    // $commentaire = $_POST['commentaire_manager'] ?? null; 

    // Le contrôleur exécute TOUTE la logique (mise à jour, notification, messages, redirection)
    $controller->traiterDemandeAction($_POST);
    
    // Le code suivant est retiré car il est géré par $controller->traiterDemandeAction($_POST);
    /* if ($success) {
        $message = ($action === 'valider') 
            ? "La demande #$demandeId a été Validée avec succès." 
            : "La demande #$demandeId a été Rejetée.";
        $_SESSION['message'] = $message; // Utilise 'message' pour l'affichage dans la page de détails
    } else {
        // Le contrôleur est censé gérer la validation avant d'appeler le modèle
        $_SESSION['error_message'] = "Erreur lors du traitement. La demande n'est plus 'En attente' ou l'accès est refusé.";
    }
    
    // Rediriger vers la page de détails de la demande
    header("Location: details_demande.php?id=$demandeId");
    exit;
    */
}

// Si accès direct sans POST ou si le contrôleur n'a pas redirigé (ce qui ne devrait pas arriver)
header('Location: demandes_liste.php');
exit;