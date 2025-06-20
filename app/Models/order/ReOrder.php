<?php
require_once(__DIR__ . '/../../../config/config.php'); // For $pdo, session_start(), etc.
require_once(__DIR__ . '/../../Controllers/cart/CartController.php'); // For database-driven cart functions with guest support
require_once(__DIR__ . '/../../Controllers/order/OrderController.php'); // For order-related functions

// Kiểm tra đăng nhập
if (!isUserLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
    exit();
}

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method không được hỗ trợ.']);
    exit();
}

// Lấy order_id từ POST data
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ.']);
    exit();
}

$current_user_id = get_current_logged_in_user_id();

try {
    // Lấy thông tin đơn hàng
    $stmt = $pdo->prepare("
        SELECT o.*, oi.product_id, oi.quantity, p.title, p.price, p.stock_quantity, p.status
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.buyer_id = ?
    ");
    $stmt->execute([$order_id, $current_user_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($order_items)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập.'
        ]);
        exit();
    }
    
    $added_items = 0;
    $unavailable_items = [];
    
    // Thêm từng sản phẩm vào giỏ hàng
    foreach ($order_items as $item) {
        // Kiểm tra sản phẩm còn tồn tại và đủ số lượng
        if ($item['status'] !== 'active') {
            $unavailable_items[] = $item['title'] . ' (không còn bán)';
            continue;
        }
        
        if ($item['stock_quantity'] < $item['quantity']) {
            $unavailable_items[] = $item['title'] . ' (chỉ còn ' . $item['stock_quantity'] . ' sản phẩm)';
            // Thêm số lượng có sẵn
            if ($item['stock_quantity'] > 0) {
                $result = addToCart($pdo, $item['product_id'], $item['stock_quantity'], $current_user_id);
                if ($result) {
                    $added_items++;
                }
            }
        } else {
            // Thêm đủ số lượng như đơn hàng cũ
            $result = addToCart($pdo, $item['product_id'], $item['quantity'], $current_user_id);
            if ($result) {
                $added_items++;
            }
        }
    }
    
    $cart_count = getCartItemCount($pdo, $current_user_id);
    
    if ($added_items > 0) {
        $message = "Đã thêm {$added_items} sản phẩm vào giỏ hàng.";
        if (!empty($unavailable_items)) {
            $message .= " Một số sản phẩm không khả dụng: " . implode(', ', $unavailable_items);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'cart_count' => $cart_count,
            'added_items' => $added_items,
            'unavailable_items' => $unavailable_items
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể thêm sản phẩm nào vào giỏ hàng. ' . 
                         (!empty($unavailable_items) ? 'Lý do: ' . implode(', ', $unavailable_items) : '')
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error reordering: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại sau.'
    ]);
}
?>
