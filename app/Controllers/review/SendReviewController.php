<?php
require_once '../../../config/config.php';

$userId = $_SESSION['user_id'] ?? false;

if (!$userId) {
    header('Location: /WebMuaBanDoCu/app/View/user/login.php');
    exit;
}

try {
    
    $product_id = $_POST['product_id'];
    $content = $_POST['content'];
    $username = $_SESSION['username'];
    
    // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM review_products WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$userId, $product_id]);
    $existingReview = $checkStmt->fetchColumn();
    
    if ($existingReview > 0) {
        echo "Bạn đã đánh giá sản phẩm này rồi. Mỗi sản phẩm chỉ được đánh giá một lần.";
        exit;
    }
    
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    
    // Validate rating
    if ($rating < 1) $rating = 1;
    if ($rating > 5) $rating = 5;

    $is_recommended = isset($_POST['is_recommended']) ? (int)$_POST['is_recommended'] : 1;

    $stmt = $pdo->prepare("INSERT INTO review_products (user_id, product_id, content, rating, is_recommended, sent_at, username) VALUES (?, ?, ?, ?, ?, NOW(),?)");
    $stmt->execute([$userId, $product_id, $content, $rating, $is_recommended, $username]);
    
    // Log user action
    if (function_exists('log_user_action')) {
        $productStmt = $pdo->prepare("SELECT title FROM products WHERE id = ?");
        $productStmt->execute([$product_id]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);
        
        log_user_action($pdo, $userId, 'review_product', "Đánh giá sản phẩm: " . ($product['title'] ?? 'ID ' . $product_id), [
            'product_id' => $product_id,
            'product_title' => $product['title'] ?? null,
            'review_length' => strlen($content)
        ]);
    }
    
    echo "success";
} catch (PDOException $e) {
    // Xử lý lỗi duplicate entry
    if ($e->getCode() == 23000 && strpos($e->getMessage(), 'ux_user_product') !== false) {
        echo "Bạn đã đánh giá sản phẩm này rồi. Mỗi sản phẩm chỉ được đánh giá một lần.";
    } else {
        echo "Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại sau.";
    }
}

?>