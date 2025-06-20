<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Update Products - Set some as regular (not featured)</h2>";

try {
    // Cập nhật 2 sản phẩm thành không nổi bật
    $stmt = $pdo->prepare("UPDATE products SET featured = 0 WHERE id IN (16, 18)");
    $result = $stmt->execute();
    
    if ($result) {
        echo "<p>✓ Updated products 16 and 18 to not featured</p>";
    }
    
    // Kiểm tra kết quả
    $stmt = $pdo->query("SELECT id, title, featured FROM products ORDER BY featured DESC, id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Products Status:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Featured</th></tr>";
    
    foreach ($products as $product) {
        $featured_text = $product['featured'] ? 'Nổi bật' : 'Thường';
        $featured_color = $product['featured'] ? 'style="background-color: #ffffcc;"' : '';
        echo "<tr $featured_color>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['title']}</td>";
        echo "<td>$featured_text</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Thống kê
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE featured = 1");
    $featured_count = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE featured = 0");
    $regular_count = $stmt->fetch()['total'];
    
    echo "<p><strong>Featured products:</strong> $featured_count</p>";
    echo "<p><strong>Regular products:</strong> $regular_count</p>";
    
} catch (PDOException $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
