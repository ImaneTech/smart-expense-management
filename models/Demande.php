<?php
 class Demande {

    public static function getAll($pdo) {
        $sql = "SELECT d.*, v.nom AS visiteur_nom 
                FROM DemandeDeFrais d
                JOIN Visiteur v ON d.visiteur_id = v.id
                ORDER BY d.date_creation DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }

    public static function updateStatus($pdo, $demande_id, $new_status, $user_type, $user_id) {

        // 1) Update statut dans demande
        $sql1 = "UPDATE DemandeDeFrais SET statut_actuel = ? WHERE id = ?";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$new_status, $demande_id]);

        // 2) Ajouter dans historique
        $sql2 = "INSERT INTO HistoriqueStatus(demande_id, nouveau_statut, utilisateur) 
                 VALUES (?, ?, ?)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$demande_id, $new_status, $user_type . " #" . $user_id]);
    }

}
?>
