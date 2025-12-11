<?php
require_once 'c:/MAMP/htdocs/smart-expense-management/config.php';
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    echo $stmt->rowCount() > 0 ? 'Table exists' : 'Table missing';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
