<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/config.php';

try {
    // Check admin permission
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Không có quyền truy cập'
        ]);
        exit;
    }

    // Get product ID
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$product_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu mã sản phẩm'
        ]);
        exit;
    }

    // Get product details
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.title,
            p.description,
            p.price,
            p.condition_status,
            p.status,
            p.featured,
            p.created_at,
            u.username as seller_name,
            c.name as category_name
        FROM products p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm'
        ]);
        exit;
    }

    // Get product images
    $stmt = $pdo->prepare("
        SELECT image_path 
        FROM product_images 
        WHERE product_id = ? 
        ORDER BY id ASC
    ");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return success response
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => (int)$product['id'],
            'title' => $product['title'],
            'description' => $product['description'],
            'price' => (float)$product['price'],
            'condition_status' => $product['condition_status'],
            'status' => $product['status'],
            'featured' => (bool)$product['featured'],
            'seller_name' => $product['seller_name'],
            'category_name' => $product['category_name'],
            'created_at' => $product['created_at'],
            'images' => $images
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
?>
