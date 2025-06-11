<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * Description of vnpay_ajax
 *
 * @author xonv
 */
require_once('../../../config/config.php');
require_once("cart_functions.php");

$vnp_TxnRef = $_POST['order_id']; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
$vnp_OrderInfo = $_POST['order_desc'];
$vnp_OrderType = $_POST['order_type'];
$vnp_Amount = $_POST['amount'] * 100;
$vnp_Locale = $_POST['language'];
$vnp_BankCode = $_POST['bank_code'];
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
//Add Params of 2.0.1 Version
$vnp_ExpireDate = $_POST['txtexpire'];
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

//var_dump($inputData);
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

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}
$returnData = array('code' => '00'
    , 'message' => 'success'
    , 'data' => $vnp_Url);

// ===== INSERT THÔNG TIN ĐơN HÀNG VÀO DATABASE =====
try {
    // Lấy user_id hiện tại
    $user_id = get_current_logged_in_user_id();
    
    // Lấy thông tin giỏ hàng
    $cartItems = getCartContents($pdo, $user_id);
    $cartTotal = getCartTotal($pdo, $user_id);
    
    if (empty($cartItems)) {
        throw new Exception("Giỏ hàng trống");
    }
    
    // Tạo đơn hàng trong database
    $order_number = $vnp_TxnRef; // Sử dụng mã đã tạo từ form
    
    // Lấy thông tin người mua từ form
    $buyer_name = $vnp_Bill_FirstName . ' ' . $vnp_Bill_LastName;
    $buyer_email = $vnp_Bill_Email;
    $buyer_phone = $vnp_Bill_Mobile;
    $shipping_address = $vnp_Bill_Address . ', ' . $vnp_Bill_City;
    
    // Insert vào bảng orders
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, buyer_id, total_amount, status, 
            payment_method, payment_status, shipping_address, notes,
            buyer_name, buyer_email, buyer_phone,
            created_at, updated_at
        ) VALUES (?, ?, ?, 'pending', 'vnpay', 'pending', ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $order_number,
        $user_id, // Có thể NULL nếu guest checkout
        $cartTotal,
        $shipping_address,
        $vnp_OrderInfo, // notes
        $buyer_name,
        $buyer_email,
        $buyer_phone
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert các sản phẩm vào bảng order_items
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, quantity, price, product_name
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['added_price'],
            $item['name']
        ]);
    }
    
    // Xóa giỏ hàng sau khi tạo đơn hàng thành công
    clearCart($pdo, $user_id);
    
    error_log("Đã tạo đơn hàng thành công: Order ID = $order_id, Order Number = $order_number");
    
} catch (Exception $e) {
    error_log("Lỗi khi tạo đơn hàng: " . $e->getMessage());
    // Có thể chuyển hướng về trang lỗi hoặc hiển thị thông báo lỗi
    // Tùy thuộc vào yêu cầu, có thể dừng redirect đến VNPAY nếu không tạo được đơn hàng
}
// ===== KẾT THÚC INSERT ĐƠN HÀNG =====

    if (isset($_POST['redirect'])) {
        header('Location: ' . $vnp_Url);
        die();
    } else {
        echo json_encode($returnData);
    }


    // chèn thông tin ở đây, khi nhấn mua hàng thì sẽ chuyển đến đây, và sau đó chuyển đến trang thanh toán của VNPAY
    // Ghi thông tin giao dịch vào database
    //index chỉ làm mẫu các thông số
    //Khách nhấn "Mua hàng", form submit về vnpay_create_payment.php (method POST hoặc GET).

// Lấy dữ liệu đơn hàng từ form.

// Lưu vào DB (trạng thái pending).

// Tạo link thanh toán VNPAY.

// Redirect sang VNPAY.