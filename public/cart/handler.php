<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\vnpay_php\cart_handler.php
require_once("../../config/config.php"); // For $pdo, session_start(), etc.
require_once('../../modules/cart/functions.php'); // For database-driven cart functions

// Lấy user_id hiện tại (nếu đã đăng nhập)
$user_id = get_current_logged_in_user_id(); 
// Biến $pdo đã được khởi tạo trong config.php

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0);
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : (isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1);

$response = ['success' => false, 'message' => 'Hành động không hợp lệ.'];

if (!isset($pdo)) {
    $response = ['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.'];
} else if (empty($action)) {
    $response = ['success' => false, 'message' => 'Hành động không được chỉ định.'];
} else {
    switch ($action) {
        case 'add':
            if ($productId > 0 && $quantity > 0) {
                if (addToCart($pdo, $productId, $quantity, $user_id)) {
                    $response = ['success' => true, 'message' => 'Sản phẩm đã được thêm vào giỏ.', 'item_count' => getCartItemCount($pdo, $user_id)];
                } else {
                    $response = ['success' => false, 'message' => 'Không thể thêm sản phẩm. Sản phẩm có thể không tồn tại, hết hàng hoặc số lượng không hợp lệ.'];
                }
            } else {
                 $response = ['success' => false, 'message' => 'Thông tin sản phẩm hoặc số lượng không hợp lệ.'];
            }
            break;
        case 'update':
            if ($productId > 0) { // Số lượng sẽ được xử lý trong hàm updateCartQuantity (nếu <=0 sẽ xóa)
                if (updateCartQuantity($pdo, $productId, $quantity, $user_id)) {
                    $response = ['success' => true, 'message' => 'Giỏ hàng đã được cập nhật.', 'item_count' => getCartItemCount($pdo, $user_id), 'total' => getCartTotal($pdo, $user_id)];
                } else {
                    $response = ['success' => false, 'message' => 'Không thể cập nhật giỏ hàng. Sản phẩm có thể hết hàng.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.'];
            }
            break;
        case 'remove':
            if ($productId > 0) {
                if (removeFromCart($pdo, $productId, $user_id)) {
                    $response = ['success' => true, 'message' => 'Sản phẩm đã được xóa khỏi giỏ.', 'item_count' => getCartItemCount($pdo, $user_id), 'total' => getCartTotal($pdo, $user_id)];
                } else {
                    $response = ['success' => false, 'message' => 'Không thể xóa sản phẩm.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.'];
            }
            break;
        case 'clear':
            if (clearCart($pdo, $user_id)) {
                $response = ['success' => true, 'message' => 'Giỏ hàng đã được xóa sạch.', 'item_count' => 0, 'total' => 0];
            } else {
                $response = ['success' => false, 'message' => 'Không thể xóa sạch giỏ hàng.'];
            }
            break;
        default:
            $response = ['success' => false, 'message' => 'Hành động không xác định.'];
            break;
    }
}

// Trả về JSON nếu là AJAX request, ngược lại redirect
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Mặc định redirect về trang giỏ hàng
    // Nếu action là 'add', có thể muốn redirect lại trang sản phẩm hoặc trang trước đó
    // Ví dụ: header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'cart_view.php'));
    header('Location: cart_view.php');
}
exit;
?>