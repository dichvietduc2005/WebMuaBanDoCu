<?php
require_once __DIR__ . '/../config/config.php';

try {
    $stmt = $pdo->query("SHOW CREATE TABLE users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "=== SCHEMA ===\n";
    echo $result['Create Table'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>