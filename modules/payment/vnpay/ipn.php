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
require_once(__DIR__ . "/../../cart/functions.php"); 

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

$vnp_SecureHash = $inputData['vnp_SecureHash'];
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

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
$vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
$vnp_Amount = $inputData['vnp_Amount']/100; // Số tiền thanh toán VNPAY phản hồi

$Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
$order_number = $inputData['vnp_TxnRef']; // Sử dụng order_number thay vì orderId

// LOGGING POINT 2: Before signature comparison for IPN
log_vnpay_debug_data("IPN_URL - BEFORE SIG CHECK", 
    [
        "order_number" => $order_number,
        "vnp_ResponseCode" => $inputData['vnp_ResponseCode'] ?? 'N/A',
        "vnp_TransactionNo" => $vnpTranId,
        "amount_from_vnpay_calculated" => $vnp_Amount_from_vnpay
    ],
    $hashData, 
    $secureHash_calculated, 
    $vnp_SecureHash_received
);

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
                        
                        $stmt_update = $pdo->prepare("UPDATE orders SET status = 'success', payment_status = 'paid', vnpay_transaction_id = ?, updated_at = NOW() WHERE id = ?");
                        $stmt_update->execute([$vnpTranId, $order['id']]);
                        log_vnpay_debug_data("IPN_URL - DB UPDATED TO SUCCESS/PAID", ["order_number" => $order_number, "order_id" => $order['id']]);
                        
                        if ($order['buyer_id']) {
                            clearCart($pdo, $order['buyer_id']);
                            error_log("IPN: Cleared cart for user_id: {$order['buyer_id']} after successful payment for order: {$order['id']}");
                            log_vnpay_debug_data("IPN_URL - CART CLEARED", ["order_number" => $order_number, "buyer_id" => $order['buyer_id']]);
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
