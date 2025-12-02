<?php
namespace Controllers;

use Models\DemandeFrais;
use Models\DetailsFrais;
use Models\HistoriqueStatus;

class DemandeFraisController extends Controller {
    private $demandeModel;
    private $detailsModel;
    private $historiqueModel;
    
    public function __construct() {
        $this->demandeModel = new DemandeFrais();
        $this->detailsModel = new DetailsFrais();
        $this->historiqueModel = new HistoriqueStatus();
    }
    
    /**
     * GET /api/demandes
     */
    public function index() {
        try {
            $demandes = $this->demandeModel->getAllWithDetails();
            $this->success(['demandes' => $demandes]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/demandes/{id}
     */
    public function show($id) {
        try {
            $demande = $this->demandeModel->getDemandeComplete($id);
            
            if (!$demande) {
                $this->error('Demande non trouvée', 404);
            }
            
            // Récupérer l'historique
            $demande['historique'] = $this->historiqueModel->getByDemande($id);
            
            $this->success(['demande' => $demande]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/demandes
     */
    public function store() {
        try {
            $data = $this->getJsonInput();
            
            // Validation
            $errors = $this->demandeModel->validate($data);
            if (!empty($errors)) {
                $this->error(implode(', ', $errors), 400);
            }
            
            // Créer la demande
            $data['statut'] = DemandeFrais::STATUT_EN_ATTENTE;
            $demandeId = $this->demandeModel->create($data);
            
            // Créer les détails si fournis
            if (isset($data['details']) && is_array($data['details'])) {
                foreach ($data['details'] as $detail) {
                    $detail['demandeid'] = $demandeId;
                    $this->detailsModel->create($detail);
                }
            }
            
            $this->success(
                ['id' => $demandeId],
                'Demande créée avec succès'
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * PUT /api/demandes/{id}
     */
    public function update($id) {
        try {
            $demande = $this->demandeModel->find($id);
            
            if (!$demande) {
                $this->error('Demande non trouvée', 404);
            }
            
            $data = $this->getJsonInput();
            
            // Validation
            $errors = $this->demandeModel->validate($data);
            if (!empty($errors)) {
                $this->error(implode(', ', $errors), 400);
            }
            
            $this->demandeModel->update($id, $data);
            
            $this->success([], 'Demande mise à jour avec succès');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * DELETE /api/demandes/{id}
     */
    public function destroy($id) {
        try {
            $demande = $this->demandeModel->find($id);
            
            if (!$demande) {
                $this->error('Demande non trouvée', 404);
            }
            
            // Supprimer les détails associés
            $details = $this->detailsModel->where('demandeid', $id);
            foreach ($details as $detail) {
                $this->detailsModel->delete($detail['id']);
            }
            
            // Supprimer la demande
            $this->demandeModel->delete($id);
            
            $this->success([], 'Demande supprimée avec succès');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/demandes/{id}/valider-manager
     */
    public function validerManager($id) {
        try {
            $demande = $this->demandeModel->find($id);
            
            if (!$demande) {
                $this->error('Demande non trouvée', 404);
            }
            
            $ancienStatut = $demande['statut'];
            $this->demandeModel->validerParManager($id);
            
            // Créer l'historique
            $this->historiqueModel->creerHistorique(
                $id,
                null,
                $ancienStatut,
                DemandeFrais::STATUT_VALIDE_MANAGER,
                $_SESSION['user_id'] ?? 1,
                false
            );
            
            $this->success([], 'Demande validée par le manager avec succès');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/demandes/{id}/rejeter-manager
     */
    public function rejeterManager($id) {
        try {
            $demande = $this->demandeModel->find($id);
            
            if (!$demande) {
                $this->error('Demande non trouvée', 404);
            }
            
            $ancienStatut = $demande['statut'];
            $this->demandeModel->rejeterParManager($id);
            
            // Créer l'historique
            $this->historiqueModel->creerHistorique(
                $id,
                null,
                $ancienStatut,
                DemandeFrais::STATUT_REJETE_MANAGER,
                $_SESSION['user_id'] ?? 1,
                false
            );
            
            $this->success([], 'Demande rejetée par le manager avec succès');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/demandes/{id}/valider-admin
     */
    public function validerAdmin($id) {
        try {
            $demande = $this->demandeModel->find($id);
            
            if (!$demande) {
                $this->error('Demande non trouvée', 404);
            }
            
            $ancienStatut = $demande['statut'];
            $this->demandeModel->validerParAdmin($id);
            
            // Créer l'historique
            $this->historiqueModel->creerHistorique(
                $id,
                null,
                $ancienStatut,
                DemandeFrais::STATUT_VALIDE_ADMIN,
                $_SESSION['user_id'] ?? 1,
                true
            );
            
            $this->success([], 'Demande validée par l\'admin avec succès');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/demandes/{id}/rejeter-admin
     */
    public function rejeterAdmin($id) {
        try {
            $demande = $this->demandeModel->find($id);
            
            if (!$demande) {
                $this->error('Demande non trouvée', 404);
            }
            
            $ancienStatut = $demande['statut'];
            $this->demandeModel->rejeterParAdmin($id);
            
            // Créer l'historique
            $this->historiqueModel->creerHistorique(
                $id,
                null,
                $ancienStatut,
                DemandeFrais::STATUT_REJETE_ADMIN,
                $_SESSION['user_id'] ?? 1,
                true
            );
            
            $this->success([], 'Demande rejetée par l\'admin avec succès');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/statistiques
     */
    public function statistiques() {
        try {
            $stats = $this->demandeModel->getStatistiques();
            $this->success($stats);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
}

// Continuer dans le prochain message avec les fichiers restants...
?>