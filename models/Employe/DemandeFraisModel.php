<?php

namespace Models\Employe;

// AJOUT DE L'ANTISLASH DANS L'IMPORTATION pour référencer la classe globale
use \PDO; 
use \Exception;

class DemandeFraisModel {
    private PDO $pdo; 
    private string $basePath; 

    // Constructeur : reçoit l'objet PDO et le chemin d'upload
    public function __construct(PDO $pdo, string $basePath) {
        $this->pdo = $pdo;
        $this->basePath = $basePath;
    }

    // =============================================================
    // =========== 1. CRUD (MÉTHODES APPELÉES PAR LE CONTRÔLEUR) ============
    // =============================================================
    
    /**
     * Insère l'en-tête principal de la demande dans la table 'demande_frais'.
     */
    public function createDemande(int $userId, array $data): int 
    {
        $sql = "INSERT INTO demande_frais 
                     (user_id, objet_mission, lieu_deplacement, date_depart, date_retour, statut, created_at) 
                VALUES (?, ?, ?, ?, ?, 'En attente', NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $executed = $stmt->execute([
            $userId,
            $data['objet_mission'],
            $data['lieu_deplacement'] ?? null,
            $data['date_depart'],
            $data['date_retour']
        ]);
        
        return $executed ? (int)$this->pdo->lastInsertId() : 0;
    }

    /**
     * Insère un détail (ligne de dépense) pour une demande donnée.
     */
    public function addDetailFrais(int $demandeId, array $detail): bool
    {
        $sql = "INSERT INTO details_frais 
                     (demande_id, categorie_id, montant, description, justificatif_path) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $demandeId,
            $detail['categorie_id'], 
            $detail['montant'],
            $detail['description'] ?? null,
            $detail['justificatif_path'] ?? null
        ]);
    }
    
    // =============================================================
    // =========== 2. REQUÊTES POUR TABLEAU DE BORD & LISTE COMPLÈTE ============
    // =============================================================

    /**
     * Récupère les statistiques de statut (En attente, Validées, Rejetées) pour un utilisateur.
     */
    public function getDemandeStatsByEmploye(int $userId): array {
        // Logique de votre méthode getUserStats existante
        $sql = "SELECT 
                    SUM(CASE WHEN statut = 'En attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut IN ('Validée Manager', 'Approuvée Compta', 'Payée') THEN 1 ELSE 0 END) as validees,
                    SUM(CASE WHEN statut IN ('Rejetée Manager', 'Rejetée Compta') THEN 1 ELSE 0 END) as rejetees
                FROM demande_frais 
                WHERE user_id = :user_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats = $stmt->fetch() ?: [];

        return [
            'en_attente' => (int)($stats['en_attente'] ?? 0),
            'validees' => (int)($stats['validees'] ?? 0), 
            'rejetees' => (int)($stats['rejetees'] ?? 0),
        ];
    }

    /**
     * Calcule le montant total remboursé pour l'employé.
     */
    public function getTotalReimbursedAmount(int $userId): float {
        // Calcule la somme des montants des détails de frais pour les demandes Payées
        $sql = "SELECT SUM(df.montant) AS total_paye 
                FROM details_frais df
                JOIN demande_frais d ON df.demande_id = d.id
                WHERE d.user_id = :user_id AND d.statut = 'Payée'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return (float)($stmt->fetchColumn() ?? 0.0);
    }
  /**
     * Récupère les demandes de frais pour un utilisateur donné, avec une limite optionnelle.
     */
    public function getDemandesByEmployeId(int $userId, ?int $limit = null): array {
        
        $sql = "SELECT 
                    d.*,
                    (SELECT SUM(df.montant) FROM details_frais df WHERE df.demande_id = d.id) as montant_total
                FROM demande_frais d 
                WHERE d.user_id = :user_id 
                ORDER BY d.created_at DESC";
                
        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT :limit"; 
        }
        
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT); 
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // =============================================================
// =========== 3. REQUÊTE POUR PAGE DE DÉTAILS (CORRIGÉ) ============
// =============================================================

/**
 * Récupère la demande principale et les lignes de détails associées.
 */
public function getDemandeDetailsWithLines(int $demandeId, int $userId): ?array {
    
    // --- 1. Récupération de l'en-tête de la demande (y compris le montant total calculé) ---
    // (Cette partie est correcte)
    $demandeSql = "
        SELECT 
            d.*,
            (SELECT SUM(df.montant) FROM details_frais df WHERE df.demande_id = d.id) AS montant_total
        FROM demande_frais d
        WHERE d.id = :demande_id AND d.user_id = :user_id
    ";
   
    
    $stmt = $this->pdo->prepare($demandeSql);
    $stmt->execute([':demande_id' => $demandeId, ':user_id' => $userId]);
    $demandeFrais = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demandeFrais) {
        return null; // La demande n'existe pas ou n'appartient pas à cet utilisateur.
    }
    
    // --- 2. Récupération des lignes de détails (CORRIGÉ PROPREMENT) ---
// --- 2. Récupération des lignes de détails (CORRIGÉ PROPREMENT) ---
    $detailsSql = "
        SELECT 
            df.id AS id_detail_frais,  
            df.demande_id,
            df.date_depense,
            df.montant,
            df.description,
            df.justificatif_path,
            df.categorie_id AS id_categorie_frais,
            c.nom AS categorie_nom
        FROM details_frais df
        JOIN categories_frais c ON df.categorie_id = c.id
        WHERE df.demande_id = :demande_id
        ORDER BY df.date_depense ASC
    ";
    $stmtDetails = $this->pdo->prepare($detailsSql);
    $stmtDetails->execute([':demande_id' => $demandeId]);
    $detailsFrais = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
    
    // --- 3. Retour du résultat complet ---
    return [
        'demande_frais' => $demandeFrais,
        'details_frais' => $detailsFrais
    ];
}
}