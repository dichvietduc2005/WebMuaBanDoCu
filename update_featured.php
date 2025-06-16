<?php
require_once 'config/config.php';

echo "<h2>Update Featured Products</h2>";

try {
    // Update some products to be featured
    $stmt = $pdo->prepare('UPDATE products SET featured = 1 WHERE id IN (1, 2)');
    $stmt->execute();
    echo "<p>✅ Updated products to be featured.</p>";

    // Check featured products again
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE featured = 1');
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "<p>✅ Found {$count} featured products now.</p>";
    
    // List featured products
    $stmt = $pdo->prepare('SELECT id, title, price, featured FROM products WHERE featured = 1');
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Featured Products:</h3>";
    echo "<ul>";
    foreach ($products as $product) {
        echo "<li>ID: {$product['id']} - {$product['title']} - " . number_format($product['price']) . " VNĐ</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='public/TrangChu.php'>➡️ Go to homepage to see featured products</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
