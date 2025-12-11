<?php
require_once 'config.php';

try {
    echo "Connected to database: $dbname\n";

    $categories = [
        ['nom' => 'Transport', 'description' => 'Frais de déplacement (Taxi, Train, Avion, etc.)'],
        ['nom' => 'Repas', 'description' => 'Déjeuners et dîners d\'affaires'],
        ['nom' => 'Hébergement', 'description' => 'Nuitées hôtel'],
        ['nom' => 'Autre', 'description' => 'Autres dépenses diverses']
    ];

    $stmt = $pdo->prepare("INSERT INTO categories_frais (nom, description) VALUES (:nom, :description)");

    foreach ($categories as $cat) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM categories_frais WHERE nom = ?");
        $check->execute([$cat['nom']]);
        if ($check->fetchColumn() == 0) {
            $stmt->execute($cat);
            echo "Inserted category: " . $cat['nom'] . "\n";
        } else {
            echo "Category already exists: " . $cat['nom'] . "\n";
        }
    }
    
    echo "\nSeeding complete.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
