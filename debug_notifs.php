<?php
require_once 'c:/MAMP/htdocs/smart-expense-management/config.php';
try {
    $stmt = $pdo->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
