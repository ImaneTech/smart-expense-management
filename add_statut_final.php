<?php
require_once 'config.php';

try {
    // Add statut_final column if it doesn't exist
    $sql = "SHOW COLUMNS FROM demande_frais LIKE 'statut_final'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE demande_frais ADD COLUMN statut_final VARCHAR(50) DEFAULT 'En attente' AFTER statut";
        $pdo->exec($sql);
        echo "Column 'statut_final' added successfully.\n";
        
        // Initialize existing records
        // If statut is 'En attente', statut_final = 'En attente'
        // If statut is 'Validée Manager', statut_final = 'En attente' (waiting for Admin)
        // If statut is 'Rejetée Manager', statut_final = 'Rejetée'
        // If statut is 'Approuvée Compta' or 'Payée', statut_final = 'Validée'
        
        $pdo->exec("UPDATE demande_frais SET statut_final = 'En attente' WHERE statut IN ('En attente', 'Validée Manager')");
        $pdo->exec("UPDATE demande_frais SET statut_final = 'Rejetée' WHERE statut = 'Rejetée Manager'");
        $pdo->exec("UPDATE demande_frais SET statut_final = 'Validée' WHERE statut IN ('Approuvée Compta', 'Payée', 'validee_admin')");
        
        echo "Existing records initialized.\n";
    } else {
        echo "Column 'statut_final' already exists.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
