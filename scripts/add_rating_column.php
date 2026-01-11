<?php
require_once 'config/config.php';

try {
    global $pdo;
    
    // Add rating column
    $pdo->exec("ALTER TABLE review_products ADD COLUMN rating INT DEFAULT 5");
    echo "Added rating column successfully.\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
         echo "Column rating already exists.\n";
    } else {
         echo "Error: " . $e->getMessage() . "\n";
    }
}
