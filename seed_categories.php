<?php
require_once 'config.php';

try {
    echo "Connected to database: $dbname\n";

    // Catégories 
    $categories = [
        ['nom' => 'Transport', 'description' => 'Frais liés aux déplacements professionnels (train, avion, taxi).'],
        ['nom' => 'Hébergement', 'description' => 'Frais pour les hôtels et logements lors des missions.'],
        ['nom' => 'Restauration', 'description' => 'Repas pris lors des déplacements professionnels.'],
        ['nom' => 'Matériel IT', 'description' => 'Achat de matériel informatique nécessaire aux missions.'],
        ['nom' => 'Formation', 'description' => 'Inscription à des conférences ou formations techniques.'],
        ['nom' => 'Divers', 'description' => 'Autres dépenses professionnelles imprévues.']
    ];

    $stmt = $pdo->prepare("INSERT INTO categories_frais (nom, description) VALUES (:nom, :description)");

    foreach ($categories as $cat) {
        // Vérifie si la catégorie existe déjà
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
