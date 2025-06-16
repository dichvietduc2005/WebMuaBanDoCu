<?php
// filepath: c:\\wamp64\\www\\Web_MuaBanDoCu\\modules\\cart\\handler.php

require_once(__DIR__ . '/../../config/config.php'); // For $pdo, session_start(), etc.
require_once(__DIR__ . '/functions.php'); // For database-driven cart functions with guest support

// Lấy user_id hiện tại (có thể null cho guest users)
$user_id = get_current_user_id(); 
$is_guest = !$user_id;

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
                try {                    $result = addToCart($pdo, $productId, $quantity, $user_id);
                    if ($result) {
                        $item_count = getCartItemCount($pdo, $user_id);
                        $response = [
                            'success' => true, 
                            'message' => 'Sản phẩm đã được thêm vào giỏ.', 
                            'cart_count' => $item_count,
                            'is_guest' => $is_guest
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'Không thể thêm sản phẩm. Vui lòng thử lại.'];
                    }
                } catch (Exception $e) {
                    $response = ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                 $response = ['success' => false, 'message' => 'Thông tin sản phẩm hoặc số lượng không hợp lệ.'];
            }
            break;
            
        case 'update':
            if ($productId > 0) {
                try {                    $result = updateCartItemQuantity($pdo, $productId, $quantity, $user_id);
                    if ($result) {
                        $item_count = getCartItemCount($pdo, $user_id);
                        $total = getCartTotal($pdo, $user_id);
                        $response = [
                            'success' => true, 
                            'message' => 'Giỏ hàng đã được cập nhật.', 
                            'cart_count' => $item_count, 
                            'total' => $total
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'Không thể cập nhật giỏ hàng.'];
                    }
                } catch (Exception $e) {
                    $response = ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.'];
            }
            break;
            
        case 'remove':
            if ($productId > 0) {
                try {
                    $result = removeCartItem($pdo, $productId, $user_id);                    if ($result) {
                        $item_count = getCartItemCount($pdo, $user_id);
                        $total = getCartTotal($pdo, $user_id);
                        $response = [
                            'success' => true, 
                            'message' => 'Sản phẩm đã được xóa khỏi giỏ.', 
                            'cart_count' => $item_count, 
                            'total' => $total
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'Không thể xóa sản phẩm.'];
                    }
                } catch (Exception $e) {
                    $response = ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.'];
            }
            break;
            
        case 'clear':
            try {
                $result = clearCart($pdo, $user_id);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Giỏ hàng đã được xóa sạch.', 'cart_count' => 0, 'total' => 0];
                } else {
                    $response = ['success' => false, 'message' => 'Không thể xóa sạch giỏ hàng.'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
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
    // Mặc định redirect về trang giỏ hàng nếu không phải AJAX
    if ($action === 'add' && isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: ../../public/cart/index.php');
    }
}
exit;
?>
