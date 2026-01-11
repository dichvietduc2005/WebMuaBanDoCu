<?php
require_once '../../../config/config.php';

header('Content-Type: application/json');

// Không cần đăng nhập để xem reviews, chỉ cần đăng nhập để gửi review
// Nếu chưa đăng nhập, vẫn trả về reviews nhưng không cho phép gửi review mới

try {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM review_products WHERE product_id = ? ORDER BY sent_at ASC");
    $stmt->execute([$product_id]);

    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($reviews);

} catch (PDOException $e) {
    error_log("LoadReviewsController error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading reviews']);
}

?>