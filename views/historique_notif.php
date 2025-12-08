<?php
// Fichier: views/historique_notif.php (Inclus dans settings_manager.php)

// --- Debugging (Optionnel) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// NOTE IMPORTANTE : Ce fichier est inclus DANS un autre fichier PHP (settings_manager.php).
// Il ne doit contenir AUCUNE vérification de session, ni définition de constantes (BASE_PATH, BASE_URL), 
// ni inclusion de fichiers déjà chargés (config.php, header.php).

// ----------------------------------------------------------------------
// CORRECTION CLÉ 1 : RETIRER L'INCLUSION DEPLACÉE DE CONFIG.PHP
// ----------------------------------------------------------------------

$erreur_dependance = null;

// ----------------------------------------------------------------------
// CORRECTION CLÉ 2 : Logique de dépendance spécifique
// ----------------------------------------------------------------------

if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Problème A: La connexion PDO est manquante ou n'est pas un objet PDO.
    $erreur_dependance = "La connexion à la base de données (\$pdo) est manquante. Vérifiez l'ordre des inclusions dans le fichier parent (settings_manager.php).";
} elseif (!isset($_SESSION['user_id'])) {
    // Problème B: La session utilisateur est manquante (l'utilisateur n'est pas authentifié).
    $erreur_dependance = "L'ID utilisateur de la session est manquant. L'utilisateur n'est pas identifié.";
}

if ($erreur_dependance) {
    // Afficher l'erreur spécifique
    echo "<div class='alert alert-danger'>
              <h4 class='alert-heading'>❌ Erreur Critique de Configuration</h4>
              <p>{$erreur_dependance}</p>
          </div>";
    return; // Arrête l'exécution de la vue
}

// ----------------------------------------------------------------------
// LOGIQUE PRINCIPALE (Exécutée si les dépendances sont OK)
// ----------------------------------------------------------------------

$user_id = $_SESSION['user_id'];

// Instanciation du Modèle (La classe Notification est incluse dans settings_manager.php)
$notificationModel = new Notification($pdo); 

// Récupération de l'historique complet 
$historique_notifications = $notificationModel->getAllNotifications($user_id);

// Optionnel: On marque ici toutes les notifications comme lues
$notificationModel->marquerCommeLues($user_id); 
?>

<div class="container-fluid p-4">
    <h5 class="fw-bold mb-4" style="color: var(--text-color);">Historique de vos alertes</h5>
    
    <div class="card shadow-sm border-0" style="background-color: var(--card-bg);">
        <div class="card-body p-4">

            <?php if (empty($historique_notifications)): ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class='bx bx-info-circle me-2'></i> Vous n'avez aucune notification dans votre historique.
                </div>
            <?php else: ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover" style="color: var(--text-color);">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 15%;">Date</th>
                                <th scope="col" style="width: 65%;">Message</th>
                                <th scope="col" class="text-center" style="width: 10%;">Statut</th>
                                <th scope="col" class="text-end" style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historique_notifications as $notif): 
                                $status_class = $notif['lue'] ? 'text-muted' : 'fw-bold text-dark';
                                $status_badge = $notif['lue'] ? '<span class="badge bg-secondary">Lue</span>' : '<span class="badge bg-warning text-dark">Non lue</span>';
                                $message_class = $notif['lue'] ? 'text-muted' : 'text-primary';
                            ?>
                                <tr class="<?= $notif['lue'] ? '' : 'table-light-custom' ?>">
                                    <td class="<?= $status_class ?>"><?= date('d/m/Y H:i', strtotime($notif['date_creation'])) ?></td>
                                    <td class="text-muted"><i class='bx bx-message-square-detail me-2 <?= $message_class ?>'></i><?= htmlspecialchars($notif['message']) ?></td>
                                    <td class="text-center"><?= $status_badge ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL . $notif['lien_url'] ?>" class="btn btn-sm" style="background-color: var(--secondary-color); color: white;">
                                            Voir Détail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>