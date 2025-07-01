<?php
/* Payment Notify
 * IPN URL: Ghi nhận kết quả thanh toán từ VNPAY
 * Các bước thực hiện:
 * Kiểm tra checksum 
 * Tìm giao dịch trong database
 * Kiểm tra số tiền giữa hai hệ thống
 * Kiểm tra tình trạng của giao dịch trước khi cập nhật
 * Cập nhật kết quả vào Database
 * Trả kết quả ghi nhận lại cho VNPAY
 */

require_once(__DIR__ . "/../../../config/config.php");
require_once(__DIR__ . "/vnpay_debug_logger.php"); // <--- THÊM DÒNG NÀY

// Load CartController và dependencies
require_once(__DIR__ . "/../cart/CartController.php");
require_once(__DIR__ . "/../../Models/cart/CartModel.php"); 

$inputData = array();
$returnData = array();

// LOGGING POINT 1: Received GET data from VNPAY for IPN
log_vnpay_debug_data("IPN_URL - RECEIVED DATA", 
    [
        "get_params" => $_GET,
        "vnp_TmnCode_config" => $vnp_TmnCode, // From config.php
        "vnp_HashSecret_config_partial" => isset($vnp_HashSecret) ? substr($vnp_HashSecret, 0, 5) . '...'. substr($vnp_HashSecret, -5) : 'NOT_SET' // From config.php
    ]
);

foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash_received = $inputData['vnp_SecureHash'];
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
$vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
$vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
$vnp_Amount_from_vnpay = $inputData['vnp_Amount']/100; // Số tiền thanh toán VNPAY phản hồi

$Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
$order_number = $inputData['vnp_TxnRef']; // Sử dụng order_number thay vì orderId



try {
    if ($secureHash_calculated == $vnp_SecureHash_received) {
        log_vnpay_debug_data("IPN_URL - SIGNATURE VALID", ["order_number" => $order_number]);
        
        $stmt = $pdo->prepare("SELECT id, total_amount, status, payment_status, buyer_id FROM orders WHERE order_number = ?");
        $stmt->execute([$order_number]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order != NULL) {
            log_vnpay_debug_data("IPN_URL - ORDER FOUND", ["order_number" => $order_number, "order_details" => $order]);
            
            // Convert database total_amount to a comparable format (float or integer)
            $order_total_amount_db = floatval($order["total_amount"]);

            if($order_total_amount_db == $vnp_Amount_from_vnpay) {
                log_vnpay_debug_data("IPN_URL - AMOUNT MATCHES", ["order_number" => $order_number, "db_amount" => $order_total_amount_db, "vnpay_amount" => $vnp_Amount_from_vnpay]);

                if ($order["payment_status"] == 'pending' || $order["payment_status"] == '') {
                    log_vnpay_debug_data("IPN_URL - ORDER STATUS IS PENDING/EMPTY", ["order_number" => $order_number, "current_payment_status" => $order["payment_status"]]);
                    
                    if ($inputData['vnp_ResponseCode'] == '00') {
                        $Status = 1; // Trạng thái thanh toán thành công
                        log_vnpay_debug_data("IPN_URL - VNPAY RESPONSE CODE 00 (SUCCESS)", ["order_number" => $order_number]);
                        
                        // ✅ TRỪNG STOCK KHI THANH TOÁN THÀNH CÔNG (giống return.php)
                        // Lấy danh sách sản phẩm cần trừ stock trước
                        $stmt_get_items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                        $stmt_get_items->execute([$order['id']]);
                        $order_items_for_stock = $stmt_get_items->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Trừ stock cho từng sản phẩm
                        foreach ($order_items_for_stock as $item) {
                            $update_stock_stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
                            $update_stock_stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                            
                            if ($update_stock_stmt->rowCount() == 0) {
                                error_log("IPN WARNING: Không thể trừ stock cho product_id {$item['product_id']} với quantity {$item['quantity']} - có thể stock không đủ");
                            }
                        }
                        
                        $stmt_update = $pdo->prepare("UPDATE orders SET status = 'success', payment_status = 'paid', vnpay_transaction_id = ?, updated_at = NOW() WHERE id = ?");
                        $stmt_update->execute([$vnpTranId, $order['id']]);
                        log_vnpay_debug_data("IPN_URL - DB UPDATED TO SUCCESS/PAID", ["order_number" => $order_number, "order_id" => $order['id']]);
                        
                        // Gửi thông báo cho người bán
                        $stmt_items = $pdo->prepare("SELECT oi.product_id, oi.product_title, p.user_id as seller_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                        $stmt_items->execute([$order['id']]);
                        $notification_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($notification_items as $item) {
                            $seller_id = $item['seller_id'];
                            $product_title = $item['product_title'];
                            $message = "Sản phẩm <b>$product_title</b> của bạn đã được bán và thanh toán thành công!";
                            $stmt_noti = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                            $stmt_noti->execute([$seller_id, $message]);
                        }
                        
                        if ($order['buyer_id']) {
                            try {
                                // Set session user_id để CartController có thể clear đúng cart
                                $_SESSION['user_id'] = $order['buyer_id'];
                                $cartController = new CartController($pdo);
                                $cartController->clearCart();
                                error_log("IPN: Cleared cart for user_id: {$order['buyer_id']} after successful payment for order: {$order['id']}");
                                log_vnpay_debug_data("IPN_URL - CART CLEARED", ["order_number" => $order_number, "buyer_id" => $order['buyer_id']]);
                            } catch (Exception $e) {
                                error_log("IPN: Error clearing cart for user {$order['buyer_id']}: " . $e->getMessage());
                                log_vnpay_debug_data("IPN_URL - CART CLEAR ERROR", ["order_number" => $order_number, "buyer_id" => $order['buyer_id'], "error" => $e->getMessage()]);
                            }
                        }
                        
                    } else {
                        $Status = 2; // Trạng thái thanh toán thất bại / lỗi
                        log_vnpay_debug_data("IPN_URL - VNPAY RESPONSE CODE NOT 00 (FAILED)", ["order_number" => $order_number, "vnp_ResponseCode" => $inputData['vnp_ResponseCode']]);
                        
                        $stmt_update = $pdo->prepare("UPDATE orders SET status = 'failed', payment_status = 'failed', updated_at = NOW() WHERE id = ?");
                        $stmt_update->execute([$order['id']]);
                        log_vnpay_debug_data("IPN_URL - DB UPDATED TO FAILED", ["order_number" => $order_number, "order_id" => $order['id']]);
                    }
                    
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    log_vnpay_debug_data("IPN_URL - ORDER ALREADY CONFIRMED/PROCESSED", ["order_number" => $order_number, "current_payment_status" => $order["payment_status"]]);
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            }
            else {
                log_vnpay_debug_data("IPN_URL - INVALID AMOUNT", ["order_number" => $order_number, "db_amount" => $order_total_amount_db, "vnpay_amount" => $vnp_Amount_from_vnpay]);
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'Invalid amount';
            }
        } else {
            log_vnpay_debug_data("IPN_URL - ORDER NOT FOUND IN DB", ["order_number" => $order_number]);
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        }
    } else {
        log_vnpay_debug_data("IPN_URL - INVALID SIGNATURE", 
            [
                "order_number" => $order_number,
                "hash_data_calculated_from" => $hashData,
                "secure_hash_calculated" => $secureHash_calculated,
                "secure_hash_received_from_vnpay" => $vnp_SecureHash_received,
                "full_get_params" => $_GET
            ]
        );
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
    }
} catch (Exception $e) {
    log_vnpay_debug_data("IPN_URL - EXCEPTION", ["order_number" => $order_number, "error_message" => $e->getMessage(), "exception_trace" => $e->getTraceAsString()]);
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknown error'; // Corrected typo from Unknow to Unknown
}

// LOGGING POINT 3: Response to VNPAY for IPN
log_vnpay_debug_data("IPN_URL - RESPONSE TO VNPAY", $returnData);

//Trả lại VNPAY theo định dạng JSON
echo json_encode($returnData);
?>
