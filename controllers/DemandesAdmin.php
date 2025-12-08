<?php
// controllers/DemandeService.php (Ceci remplace DemandeController pour une architecture PHP simple)

// Assurez-vous que vos Models sont inclus !
require_once BASE_PATH . 'models/Demande.php';
require_once BASE_PATH . 'models/DetailsFrais.php'; 
// ... autres Models

class DemandeService {
    private $demandeModel;
    private $detailsModel;

    public function __construct(PDO $pdo) {
        $this->demandeModel = new Demande($pdo);
        $this->detailsModel = new DetailsFrais($pdo);
    }
    
    // --- Méthodes privées d'aide (à placer dans les Models pour une architecture propre) ---
    
    private function getMontantTotal(int $demandeId) : float {
        // En principe, cette méthode devrait être dans DetailsFraisModel
        $stmt = $this->demandeModel->pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM details_frais WHERE demande_id = ?");
        $stmt->execute([$demandeId]);
        return (float)$stmt->fetchColumn();
    }
    
    private function getJustificatif(int $demandeId) : ?string {
         // En principe, cette méthode devrait être dans DetailsFraisModel
        $stmt = $this->demandeModel->pdo->prepare("SELECT justificatif_path FROM details_frais WHERE demande_id = ? AND justificatif_path IS NOT NULL LIMIT 1");
        $stmt->execute([$demandeId]);
        return $stmt->fetchColumn();
    }
    
    // --- Méthode Principale pour la Vue ---

    public function getDashboardStats(): array {
        // Similaire à getStats, mais retourne le tableau PHP directement
        $rows = $this->demandeModel->pdo->query("SELECT statut, COUNT(*) AS total FROM demande_frais GROUP BY statut")->fetchAll(PDO::FETCH_ASSOC);
        
        $out = ['validees_manager' => 0, 'en_attente' => 0, 'rejetees' => 0];
        
        foreach ($rows as $r) {
            $s = $r['statut'];
            $t = (int)$r['total'];
            
            if (in_array($s, ['Validée Manager', 'Approuvée Compta', 'Payée'])) {
                $out['validees_manager'] += $t;
            } elseif ($s === 'En attente') {
                $out['en_attente'] += $t;
            } elseif ($s === 'Rejetée Manager') {
                $out['rejetees'] += $t;
            }
        }
        return $out;
    }

    public function getAllDemandesForView(string $statutDB = null): array {
        $rows = $this->demandeModel->getAll($statutDB); // Votre méthode getAll du Model
        
        // Traitement pour enrichir les données (Calculs et Justificatifs)
        $out = [];
        foreach ($rows as $d) {
            $montantTotal = $this->getMontantTotal($d['id']);
            $justificatif = $this->getJustificatif($d['id']);
            
            // Map le statut DB vers un label lisible ou une classe CSS pour la Vue
            $statutLabel = match($d['statut']) {
                'En attente' => ['label' => 'En attente', 'class' => 'warning'],
                'Validée Manager' => ['label' => 'Validée Manager', 'class' => 'success'],
                'Approuvée Compta' => ['label' => 'Approuvée Compta', 'class' => 'success'],
                'Rejetée Manager' => ['label' => 'Rejetée Manager', 'class' => 'danger'],
                default => ['label' => $d['statut'], 'class' => 'secondary']
            };

            $out[] = array_merge($d, [
                'utilisateur_nom' => $d['first_name'] . ' ' . $d['last_name'],
                'montant_total' => $montantTotal,
                'justificatif' => $justificatif,
                'statut_info' => $statutLabel
            ]);
        }
        return $out;
    }
    
    // Vous aurez besoin d'autres méthodes pour gérer les actions POST (create, update, delete)
    // Par exemple:
    public function handlePostAction(string $action, array $data) : array {
        // Ici, vous traitez les actions POST de votre formulaire.
        // C'est l'équivalent du switch($action) dans l'API, mais sans envoyer de JSON.
        // Vous retournez un statut de succès pour le système de flash message.
        
        if ($action === 'create') {
            // Logique de création...
            // $this->demandeModel->create(...);
            return ['success' => true, 'message' => 'Demande créée'];
        }
        // ... autres actions
        return ['success' => false, 'message' => 'Action non traitée'];
    }
}