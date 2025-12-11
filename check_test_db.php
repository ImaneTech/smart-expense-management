<?php
require_once 'config.php';

try {
    echo "Connected to database: $dbname on $host\n";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "CRITICAL: Database '$dbname' is EMPTY (No tables found).\n";
        exit;
    }
    
    echo "Tables found: " . implode(", ", $tables) . "\n";
    
    // Check categories
    if (in_array('categories_frais', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM categories_frais")->fetchColumn();
        echo "Categories count: $count\n";
        if ($count == 0) {
             echo "CRITICAL: 'categories_frais' is empty. Seeding required.\n";
        }
    } else {
        echo "CRITICAL: Table 'categories_frais' is MISSING.\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
