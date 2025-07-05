<?php
// Check if session already started before starting a new one
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
require_once(__DIR__ . '/../../helpers.php');

// Log session for debugging
error_log("Payment return - Session ID: " . session_id());
error_log("Payment return - User ID in session: " . ($_SESSION['user_id'] ?? 'Not logged in'));

$vnp_SecureHash_received = $_GET['vnp_SecureHash'] ?? '';
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$order_number_from_vnpay = $_GET['vnp_TxnRef'] ?? null;

// Debug session information - temporary
// error_log("RETURN.PHP: Session ID: " . session_id());
// error_log("RETURN.PHP: Session data: " . print_r($_SESSION, true));

// LOGGING POINT 1: Received data from VNPAY
// log_vnpay_debug_data("RETURN_URL - RECEIVED DATA", 
//     [
//         "get_params" => $_GET,
//         "vnp_TmnCode_config" => $vnp_TmnCode, // From config.php
//         "vnp_HashSecret_config_partial" => substr($vnp_HashSecret, 0, 5) . '...'. substr($vnp_HashSecret, -5) // From config.php
//     ]
// );

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash_calculated = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$payment_successful = false;
$payment_message = "";

// LOGGING POINT 2: Before signature comparison
// log_vnpay_debug_data("RETURN_URL - BEFORE SIG CHECK", 
//     [
//         "order_number" => $order_number_from_vnpay,
//         "vnp_ResponseCode" => $_GET['vnp_ResponseCode'] ?? 'N/A'
//     ],
//     $hashData, 
//     $secureHash_calculated, 
//     $vnp_SecureHash_received
// );

if ($secureHash_calculated == $vnp_SecureHash_received) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        $payment_successful = true;
        $payment_message = "Thanh toán thành công!";
        // log_vnpay_debug_data("RETURN_URL - SUCCESS", ["order_number" => $order_number_from_vnpay, "message" => $payment_message]);

        if ($order_number_from_vnpay) {
            try {
                $stmt_check_order = $pdo->prepare("SELECT id, status, payment_status, buyer_id FROM orders WHERE order_number = ?");
                $stmt_check_order->execute([$order_number_from_vnpay]);
                $current_order_data = $stmt_check_order->fetch(PDO::FETCH_ASSOC);

                if ($current_order_data && ($current_order_data['payment_status'] == 'pending' || $current_order_data['payment_status'] == '')) {
                    $order_id = $current_order_data['id'];
                    
                    // ✅ TRỪNG STOCK KHI THANH TOÁN THÀNH CÔNG
                    // Lấy danh sách sản phẩm cần trừ stock trước
                    $stmt_get_items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $stmt_get_items->execute([$order_id]);
                    $order_items_for_stock = $stmt_get_items->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Trừ stock cho từng sản phẩm
                    foreach ($order_items_for_stock as $item) {
                        $update_stock_stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
                        $update_stock_stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                        
                        if ($update_stock_stmt->rowCount() == 0) {
                            error_log("WARNING: Không thể trừ stock cho product_id {$item['product_id']} với quantity {$item['quantity']} - có thể stock không đủ");
                        }
                    }
                    
                    // Cập nhật trạng thái đơn hàng
                    $stmt_update_order = $pdo->prepare("UPDATE orders SET status = 'success', payment_status = 'paid', updated_at = NOW() WHERE id = ?");
                    $stmt_update_order->execute([$order_id]);

                     // Lấy danh sách sản phẩm trong đơn hàng và gửi thông báo cho từng người bán
                    $stmt_items = $pdo->prepare("SELECT oi.product_id, oi.product_title, p.user_id as seller_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                    $stmt_items->execute([$order_id]);
                    $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($order_items as $item) {
                        $seller_id = $item['seller_id'];
                        $product_title = $item['product_title'];
                        $product_id = $item['product_id'];
                        $message = "Sản phẩm <b>$product_title</b> của bạn đã được bán và thanh toán thành công!";
                        $stmt_noti = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $stmt_noti->execute([$seller_id, $message]);
                        
                        // Cập nhật trạng thái sản phẩm thành 'sold'
                        $stmt_update_product = $pdo->prepare("UPDATE products SET status = 'sold' WHERE id = ?");
                        $stmt_update_product->execute([$product_id]);
                        error_log("Updated product_id: $product_id status to 'sold'");
                    }
                    
                    // Cập nhật trạng thái giỏ hàng thành sold và ẩn đi thay vì xóa
                    $buyer_id = $current_order_data['buyer_id'];
                    if ($buyer_id) {
                        try {
                            // Kiểm tra cấu trúc bảng cart_items
                            $columns_check = $pdo->query("SHOW COLUMNS FROM cart_items");
                            $columns = $columns_check->fetchAll(PDO::FETCH_COLUMN);
                            
                            $has_status = in_array('status', $columns);
                            $has_hidden = in_array('is_hidden', $columns);
                            
                            if ($has_status && $has_hidden) {
                                // Cập nhật trạng thái nếu cả hai column đều tồn tại
                                $stmt_update_cart = $pdo->prepare("UPDATE cart_items ci 
                                                                JOIN carts c ON ci.cart_id = c.id 
                                                                SET ci.status = 'sold', ci.is_hidden = 1
                                                                WHERE c.user_id = ?");
                                $stmt_update_cart->execute([$buyer_id]);
                                $affected_rows = $stmt_update_cart->rowCount();
                                error_log("Updated $affected_rows cart items to 'sold' status for user_id: $buyer_id");
                            } else {
                                // Fallback: xóa nếu không có columns cần thiết
                                $stmt_delete_cart_items = $pdo->prepare("DELETE ci FROM cart_items ci 
                                                                        JOIN carts c ON ci.cart_id = c.id 
                                                                        WHERE c.user_id = ?");
                                $stmt_delete_cart_items->execute([$buyer_id]);
                                $affected_rows = $stmt_delete_cart_items->rowCount();
                                error_log("Deleted $affected_rows cart items for user_id: $buyer_id (fallback - missing columns)");
                            }
                        } catch (Exception $e) {
                            error_log("Error updating cart status for user $buyer_id: " . $e->getMessage());
                        }
                    }
                }

            } catch (PDOException $e) {
                error_log("VNPay Return: DB error during tentative order update for order_number: " . $order_number_from_vnpay . " - " . $e->getMessage());
                // log_vnpay_debug_data("RETURN_URL - DB UPDATE ERROR (SUCCESS CASE)", ["order_number" => $order_number_from_vnpay, "error" => $e->getMessage()]);
            }
        }
    } else {
        $payment_message = "Thanh toán không thành công. Mã lỗi VNPAY: " . htmlspecialchars($_GET['vnp_ResponseCode']);
        // log_vnpay_debug_data("RETURN_URL - FAILED (VNPAY ERROR CODE)", ["order_number" => $order_number_from_vnpay, "vnp_ResponseCode" => $_GET['vnp_ResponseCode'], "message" => $payment_message]);
        if ($order_number_from_vnpay) {
            try {
                              $stmt_update_order = $pdo->prepare("UPDATE orders SET status = 'failed', payment_status = 'failed', updated_at = NOW() WHERE order_number = ?");
             $stmt_update_order->execute([$order_number_from_vnpay]);
            } catch (PDOException $e) {
                 error_log("VNPay Return: DB error updating failed payment status for order_number: " . $order_number_from_vnpay . " - " . $e->getMessage());
                //  log_vnpay_debug_data("RETURN_URL - DB UPDATE ERROR (FAIL CASE)", ["order_number" => $order_number_from_vnpay, "error" => $e->getMessage()]);
            }
        }
    }
} else {
    $payment_message = "Chữ ký không hợp lệ. Giao dịch có thể đã bị thay đổi.";
    error_log("VNPay Return: Invalid signature for order_number: " . $order_number_from_vnpay . " GET data: " . print_r($_GET, true));
}

$redirectParams = [];
$redirectParams['vnp_TxnRef'] = $_GET['vnp_TxnRef'] ?? '';
$redirectParams['vnp_Amount'] = $_GET['vnp_Amount'] ?? '';
$redirectParams['vnp_OrderInfo'] = $_GET['vnp_OrderInfo'] ?? '';
$redirectParams['vnp_ResponseCode'] = $_GET['vnp_ResponseCode'] ?? '';
$redirectParams['vnp_TransactionNo'] = $_GET['vnp_TransactionNo'] ?? '';
$redirectParams['vnp_BankCode'] = $_GET['vnp_BankCode'] ?? '';
$redirectParams['vnp_PayDate'] = $_GET['vnp_PayDate'] ?? '';
$redirectParams['payment_status_message'] = $payment_message;
$redirectParams['payment_successful'] = $payment_successful ? '1' : '0';
if ($order_number_from_vnpay) {
    $redirectParams['app_order_id'] = $order_number_from_vnpay;
}

$queryString = http_build_query($redirectParams);
$successPageUrl = '/WebMuaBanDoCu/app/View/payment/success.php';

// Debug log
error_log("VNPAY Return - Redirecting to: " . $successPageUrl . '?' . $queryString);
error_log("VNPAY Return - Current session ID: " . session_id());
error_log("VNPAY Return - User ID before redirect: " . ($_SESSION['user_id'] ?? 'Not set'));

// Clear any output buffer before redirect
if (ob_get_level()) {
    ob_end_clean();
}

header('Location: ' . $successPageUrl . '?' . $queryString);
exit;
?>