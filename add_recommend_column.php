<?php
require_once 'config/config.php';

try {
    global $pdo;
    
    // Add is_recommended column
    $pdo->exec("ALTER TABLE review_products ADD COLUMN is_recommended TINYINT(1) DEFAULT 1");
    echo "Added is_recommended column successfully.\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
         echo "Column is_recommended already exists.\n";
    } else {
         echo "Error: " . $e->getMessage() . "\n";
    }
}
