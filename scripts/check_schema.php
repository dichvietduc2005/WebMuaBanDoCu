<?php
require_once 'config/config.php';

try {
    global $pdo;
    $stmt = $pdo->query("DESCRIBE review_products");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in review_products:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
