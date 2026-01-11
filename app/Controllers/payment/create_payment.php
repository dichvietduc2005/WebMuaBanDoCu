<?php
ob_start(); // Start output buffering at the very beginning

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * Description of vnpay_ajax
 *
 * @author xonv
 */
require_once(__DIR__ . "/../../../config/config.php");
require_once(__DIR__ . "/../../helpers.php");
// Autoloader sẽ tự động load CartModel, CartController, và Auth

// ===== KIỂM TRA ĐĂNG NHẬP =====
session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // Nếu là AJAX request, trả về lỗi JSON
    if (empty($_POST['redirect'])) {
        header('Content-Type: application/json');
        echo json_encode(['code' => '98', 'message' => 'Bạn cần đăng nhập để thanh toán.', 'data' => null]);
        exit;
    }
      // Nếu là form submission trực tiếp, chuyển hướng đến trang đăng nhập
    $_SESSION['login_redirect_url'] = '/WebMuaBanDoCu/app/View/checkout/index.php';
    $_SESSION['error_message'] = 'Bạn cần đăng nhập để thanh toán.';
    header('Location: /WebMuaBanDoCu/app/router.php?controller=user&action=login');
    exit;
}
// ===== KẾT THÚC KIỂM TRA ĐĂNG NHẬP =====

// Khai báo global variables từ config
global $vnp_TmnCode, $vnp_HashSecret, $vnp_Url, $vnp_Returnurl, $pdo;

$vnp_TxnRef = $_POST['order_id']; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
$vnp_OrderInfo = $_POST['order_desc'];
$vnp_OrderType = $_POST['order_type'] ?? 'billpayment';
$vnp_Amount = $_POST['amount'] * 100;
$vnp_Locale = $_POST['language'];
$vnp_BankCode = $_POST['bank_code'];
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
//Add Params of 2.0.1 Version
$vnp_ExpireDate = $_POST['txtexpire']; // Đọc giá trị từ POST
//Billing
$vnp_Bill_Mobile = $_POST['txt_billing_mobile'];
$vnp_Bill_Email = $_POST['txt_billing_email'];
$fullName = trim($_POST['txt_billing_fullname']);
if (isset($fullName) && trim($fullName) != '') {
    $name = explode(' ', $fullName);
    $vnp_Bill_FirstName = array_shift($name);
    $vnp_Bill_LastName = array_pop($name);
}
$vnp_Bill_Address=$_POST['txt_inv_addr1'];
$vnp_Bill_City=$_POST['txt_bill_city'];
$vnp_Bill_Country=$_POST['txt_bill_country'];
$vnp_Bill_State=$_POST['txt_bill_state'];

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef
);

// Thêm vnp_ExpireDate vào $inputData nếu có giá trị và không rỗng
if (isset($vnp_ExpireDate) && $vnp_ExpireDate != "") {
    $inputData['vnp_ExpireDate'] = $vnp_ExpireDate;
}

// Chỉ thêm các tham số không rỗng để tránh lỗi hash
if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

// Thêm billing info nếu có (chỉ thêm các tham số THỰC SỰ có giá trị)
if (!empty($vnp_Bill_Mobile)) $inputData['vnp_Bill_Mobile'] = $vnp_Bill_Mobile;
if (!empty($vnp_Bill_Email)) $inputData['vnp_Bill_Email'] = $vnp_Bill_Email;
if (!empty($vnp_Bill_FirstName)) $inputData['vnp_Bill_FirstName'] = $vnp_Bill_FirstName;
if (!empty($vnp_Bill_LastName)) $inputData['vnp_Bill_LastName'] = $vnp_Bill_LastName;
if (!empty($vnp_Bill_Address)) $inputData['vnp_Bill_Address'] = $vnp_Bill_Address;
if (!empty($vnp_Bill_City)) $inputData['vnp_Bill_City'] = $vnp_Bill_City;
if (!empty($vnp_Bill_Country)) $inputData['vnp_Bill_Country'] = $vnp_Bill_Country;
if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
    $inputData['vnp_Bill_State'] = $vnp_Bill_State;
}

// LOGGING POINT 1: Before sorting and hashing
log_vnpay_debug_data("CREATE_PAYMENT - BEFORE HASH", $inputData + get_vnpay_config_for_logging());


ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url_original = $vnp_Url; // Store original VNPAY URL for logging
$vnp_Url = $vnp_Url . "?" . $query;
$vnpSecureHash = ""; // Initialize
if (isset($vnp_HashSecret)) {
    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}



$returnData = array('code' => '00'
    , 'message' => 'success'
    , 'data' => $vnp_Url);

// ===== INSERT THÔNG TIN ĐƠN HÀNG VÀO DATABASE =====
$order_created_successfully = false;
try {
    // 💾 LƯU THÔNG TIN NGƯỜI DÙNG (NẾU ĐƯỢC CHỌN)
    if (isset($_POST['save_info']) && $_POST['save_info'] == '1') {
        $specificAddress = $_POST['specific_address'] ?? '';
        $update_user_stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, 
                phone = ?, 
                address = ? 
            WHERE id = ?
        ");
        $update_user_stmt->execute([
            $fullName,
            $vnp_Bill_Mobile,
            $specificAddress, // Lưu địa chỉ cụ thể để dễ tái sử dụng
            $user_id
        ]);
        error_log("Đã cập nhật thông tin người dùng ID $user_id theo yêu cầu lưu thông tin.");
    }

    // $user_id đã được lấy ở trên và chắc chắn có giá trị
    // Lấy thông tin giỏ hàng bằng CartController mới
    $cartController = new CartController($pdo);
    $cartItems = $cartController->getCartItems();
    $cartTotal = $cartController->getCartTotal();

    if (empty($cartItems)) {
        throw new Exception("Giỏ hàng trống. Không thể tạo đơn hàng.");
    }

    // Tạo đơn hàng trong database
    $order_number = $vnp_TxnRef; // Sử dụng mã đã tạo từ form

    // Lấy thông tin người mua và ghi chú từ form
    $shipping_address_full = trim($vnp_Bill_Address . ', ' . $vnp_Bill_City);
    $customer_order_notes = isset($_POST['order_notes']) ? trim($_POST['order_notes']) : '';

    // Kết hợp địa chỉ giao hàng và ghi chú khách hàng vào ghi chú đơn hàng cho DB
    // $vnp_OrderInfo is the general description for VNPAY from $_POST['order_desc']
    $order_notes_for_db = $vnp_OrderInfo; 

    if (!empty($customer_order_notes)) {
        $order_notes_for_db .= "\nGhi chú khách hàng: " . $customer_order_notes;
    }
    if (!empty($shipping_address_full) && $shipping_address_full !== ',') {
        $order_notes_for_db .= "\nĐịa chỉ giao hàng: " . $shipping_address_full;
    }
    if (!empty($vnp_Bill_Mobile)) {
        $order_notes_for_db .= "\nSĐT người nhận: " . $vnp_Bill_Mobile;
    }


    // Bắt đầu transaction
    $pdo->beginTransaction();

    // Insert vào bảng orders
    // Giả sử bảng orders có các cột: order_number, buyer_id (NOT NULL), total_amount, status,
    // payment_method, payment_status, notes, created_at, updated_at
    // Loại bỏ cột `shipping_address`
    $stmt_order = $pdo->prepare("
        INSERT INTO orders (
            order_number, buyer_id, total_amount, status,
            payment_method, payment_status, notes,
            created_at, updated_at
        ) VALUES (?, ?, ?, 'pending', 'vnpay', 'pending', ?, NOW(), NOW())
    ");
    
    $stmt_order->execute([
        $order_number,
        $user_id, 
        $cartTotal,
        $order_notes_for_db // Sử dụng ghi chú đã kết hợp đầy đủ cho DB
    ]);

    $order_id = $pdo->lastInsertId();
    if (!$order_id) {
        throw new Exception("Không thể tạo đơn hàng chính.");
    }

    // Insert các sản phẩm vào bảng order_items
    // Giả sử bảng order_items có các cột: order_id, product_id, product_title, product_price, quantity, subtotal, created_at
    foreach ($cartItems as $item) {
        // Đảm bảo $item['product_title'] và $item['added_price'] (hoặc current_price) tồn tại từ getCartItems()
        $product_title = $item['product_title'] ?? 'N/A'; // Sử dụng product_title
        $product_price = $item['added_price'] ?? 0; // SỬ DỤNG added_price ĐỂ LẤY GIÁ LÚC THÊM VÀO GIỎ
        $quantity = $item['quantity'] ?? 0;
        $subtotal = $product_price * $quantity;

        $stmt_item = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_title, product_price, quantity, subtotal, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt_item->execute([
            $order_id,
            $item['product_id'],
            $product_title,
            $product_price,
            $quantity,
            $subtotal
        ]);
        if ($stmt_item->rowCount() == 0) {
            throw new Exception("Không thể lưu chi tiết sản phẩm (ID: {$item['product_id']}) cho đơn hàng.");
        }

        // ✅ KHÔNG trừ stock ở đây - chỉ trừ khi thanh toán thành công
        // Kiểm tra stock có đủ không (nhưng không trừ)
        $check_stock_stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $check_stock_stmt->execute([$item['product_id']]);
        $current_stock = $check_stock_stmt->fetchColumn();
        
        if ($current_stock < $quantity) {
            throw new Exception("Sản phẩm (ID: {$item['product_id']}) không đủ số lượng. Còn lại: $current_stock, yêu cầu: $quantity");
        }
    }

    // Nếu mọi thứ thành công, commit transaction
    $pdo->commit();
    
    // Log user action
    if (function_exists('log_user_action')) {
        log_user_action($pdo, $user_id, 'create_order', "Tạo đơn hàng: $order_number", [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total_amount' => $cartTotal,
            'payment_method' => 'vnpay',
            'items_count' => count($cartItems)
        ]);
    }
    
    $order_created_successfully = true;
    error_log("Đã tạo đơn hàng (chưa xóa giỏ hàng): Order ID = $order_id, Order Number = $order_number cho user_id = $user_id.");

} catch (Exception $e) {
    // Nếu có lỗi, rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();    }
    error_log("Lỗi khi tạo đơn hàng cho user_id = $user_id: " . $e->getMessage() . " | Dữ liệu POST: " . print_r($_POST, true));
    log_vnpay_debug_data("CREATE_PAYMENT - ORDER CREATION FAILED", ["error" => $e->getMessage(), "post_data" => $_POST]); // <--- LOG LỖI TẠO ĐƠN HÀNG
      // Xử lý lỗi và không redirect sang VNPAY
    if (isset($_POST['redirect'])) {
        $_SESSION['checkout_error_message'] = "Lỗi khi tạo đơn hàng: " . $e->getMessage();
        header('Location: /WebMuaBanDoCu/app/View/checkout/index.php');
        exit;
    } else {
        echo json_encode(['code' => '99', 'message' => 'Lỗi khi tạo đơn hàng: ' . $e->getMessage(), 'data' => null]);
        exit;
    }
}
// ===== KẾT THÚC INSERT ĐƠN HÀNG =====

// Chỉ redirect hoặc trả về JSON nếu đơn hàng được tạo thành công
if ($order_created_successfully) {
    if (isset($_POST['redirect'])) {
        // Check if $vnp_Url is valid before redirecting
        if (empty($vnp_Url) || !filter_var($vnp_Url, FILTER_VALIDATE_URL)) {            error_log("VNPAY Create Payment Error: Invalid or empty VNPAY URL. URL was: '" . $vnp_Url . "'");
            log_vnpay_debug_data("CREATE_PAYMENT - INVALID VNPAY URL", ["vnp_Url" => $vnp_Url]); // <--- LOG URL KHÔNG HỢP LỆ
            $_SESSION['checkout_error_message'] = "Lỗi nghiêm trọng: Không thể tạo URL thanh toán VNPAY. Vui lòng liên hệ quản trị viên.";
            header('Location: /WebMuaBanDoCu/app/View/checkout/index.php');
            exit;
        }
        header('Location: ' . $vnp_Url);
        exit;
    } else {
        // AJAX response
        header('Content-Type: application/json');
        echo json_encode($returnData);
        exit;
    }
} else {
    // Order creation failed, $order_created_successfully is false.
    // The error message should have been set in the catch block.
    if (isset($_POST['redirect'])) {
        if (empty($_SESSION['checkout_error_message'])) {
            // Fallback error message if not set by the catch block
            $_SESSION['checkout_error_message'] = "Không thể khởi tạo thanh toán. Đã xảy ra lỗi không xác định.";
        }        header('Location: /WebMuaBanDoCu/app/View/checkout/index.php');
        exit;
    } else {
        // AJAX response for failure
        header('Content-Type: application/json');
        // Try to use the message from session if available, otherwise a generic one.
        $errorMessage = 'Không thể khởi tạo thanh toán do lỗi tạo đơn hàng.';
        if (isset($_SESSION['checkout_error_message'])) {
            $errorMessage = $_SESSION['checkout_error_message'];
            unset($_SESSION['checkout_error_message']); // Clear after use
        }
        echo json_encode(['code' => '99', 'message' => $errorMessage, 'data' => null]);
        exit;
    }
}

?>