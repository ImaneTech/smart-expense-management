<?php
require 'config.php';
$stmt = $pdo->query("SELECT DISTINCT statut FROM demande_frais");
$statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($statuses as $s) {
    echo "'" . $s . "'\n";
}
