<?php
require_once(__DIR__ . '/../../../config/config.php');// For $pdo, session_start(), etc.
require_once(__DIR__ . '/../../Controllers/admin/AdminController.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;


if ($id && $action) {
    // Lấy user_id và title của sản phẩm
    $stmt = $pdo->prepare('SELECT user_id, title FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $user_id = $product['user_id'];
        $title = $product['title'];
        if ($action === 'approve') {
            updateProductStatus($pdo, $id, 'active');
            $message = "Sản phẩm <b>$title</b> của bạn đã được admin duyệt và đăng bán.";
            $stmt_noti = $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
            $stmt_noti->execute([$user_id, $message]);
        } elseif ($action === 'reject') {
            updateProductStatus($pdo, $id, 'reject');
            $message = "Sản phẩm <b>$title</b> của bạn đã bị admin từ chối đăng bán.";
            $stmt_noti = $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
            $stmt_noti->execute([$user_id, $message]);
        } elseif ($action === 'delete') {
            deleteProduct($pdo, $id);
            $message = "Sản phẩm <b>$title</b> của bạn đã bị admin xóa khỏi hệ thống.";
            $stmt_noti = $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
            $stmt_noti->execute([$user_id, $message]);
        }
    }
}
header('Location: ../../View/admin/products.php');
exit;