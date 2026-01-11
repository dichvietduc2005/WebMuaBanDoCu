<?php
// check_db.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check Categories
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE status = 'active'");
    $catCount = $stmt->fetchColumn();
    echo "Active Categories: " . $catCount . "\n";
    
    // Check Products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $prodActiveCount = $stmt->fetchColumn();
    echo "Active Products: " . $prodActiveCount . "\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active' AND featured = 1");
    $prodFeaturedCount = $stmt->fetchColumn();
    echo "Featured Products: " . $prodFeaturedCount . "\n";
    
    // Check raw products table to see if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProd = $stmt->fetchColumn();
    echo "Total Products (any status): " . $totalProd . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
