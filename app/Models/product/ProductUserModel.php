<?php
require_once(__DIR__ . '/../../../config/config.php'); // For $pdo, session_start(), etc.
require_once(__DIR__ . '/../../Controllers/product/ProductUserController.php'); // For product-related functions
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/user/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;
$id = $_GET['id'] ?? $_POST['id'] ?? null;

// AJAX sửa sản phẩm
if ($action === 'edit_ajax' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    updateProduct($pdo, $user_id, $id, $_POST);
    // Lấy lại trạng thái mới
    $product = getProductById($pdo, $user_id, $id);
    echo json_encode(['success' => true, 'status' => $product['status']]);
    exit;
}

// AJAX xóa sản phẩm
if ($action === 'delete_ajax' && $id) {
    deleteUserProduct($pdo, $user_id, $id);
    echo json_encode(['success' => true]);
    exit;
}

?>