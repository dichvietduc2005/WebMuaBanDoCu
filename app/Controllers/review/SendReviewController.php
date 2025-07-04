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
    
    $stmt = $pdo->prepare("INSERT INTO review_products (user_id, product_id, content, sent_at, username) VALUES (?, ?, ?, NOW(),?)");
    $stmt->execute([$userId, $product_id, $content,$username]);
    
    echo "success";
} catch (PDOException $e) {
    echo "" . $e->getMessage();
}

?>