<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Debug Database Connection</h2>";

try {
    // Test kết nối
    echo "<p><strong>Database connection:</strong> OK</p>";
    
    // Kiểm tra bảng categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch();
    echo "<p><strong>Total categories:</strong> " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT * FROM categories LIMIT 5");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Categories:</h3><pre>";
    print_r($categories);
    echo "</pre>";
    
    // Kiểm tra bảng products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch();
    echo "<p><strong>Total products:</strong> " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $result = $stmt->fetch();
    echo "<p><strong>Active products:</strong> " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE status = 'active' AND featured = 1");
    $result = $stmt->fetch();
    echo "<p><strong>Featured products:</strong> " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' LIMIT 5");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Products:</h3><pre>";
    print_r($products);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p><strong>Database error:</strong> " . $e->getMessage() . "</p>";
}
?>
