<?php
// Config already starts session, so we don't need to start it again
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

// Lấy order_id từ POST data (hỗ trợ cả form data và JSON)
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : 'Khách hàng hủy đơn';

// Nếu không có trong POST, thử lấy từ JSON
if (!$order_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input && isset($input['order_id'])) {
        $order_id = (int)$input['order_id'];
        $reason = isset($input['reason']) ? trim($input['reason']) : 'Khách hàng hủy đơn';
    }
}

if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ.']);
    exit();
}
$current_user_id = get_current_logged_in_user_id();

try {
    // Thực hiện hủy đơn hàng
    $result = cancelOrder($pdo, $order_id, $current_user_id, $reason);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Đơn hàng đã được hủy thành công.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể hủy đơn hàng. Đơn hàng có thể không tồn tại hoặc không thể hủy ở trạng thái hiện tại.'
        ]);
    }
} catch (Exception $e) {
    error_log("Error cancelling order: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại sau.'
    ]);
}
?>
