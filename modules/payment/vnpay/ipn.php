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


 // không cần động đến file này

require_once("../../config/config.php");
$inputData = array();
$returnData = array();
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
$orderId = $inputData['vnp_TxnRef'];

try {
    //Check Orderid    
    //Kiểm tra checksum của dữ liệu
    if ($secureHash == $vnp_SecureHash) {
        // Lấy thông tin đơn hàng từ database
        $stmt = $pdo->prepare("SELECT id, total_amount, status FROM orders WHERE order_code = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order != NULL) {
            if($order["total_amount"] == $vnp_Amount) // Kiểm tra số tiền thanh toán
            {
                if ($order["status"] == 'pending') {
                    if ($inputData['vnp_ResponseCode'] == '00') {
                        $Status = 1; // Trạng thái thanh toán thành công
                        // Cập nhật trạng thái đơn hàng
                        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'paid', payment_method = 'vnpay', vnpay_transaction_id = ?, updated_at = NOW() WHERE id = ?");
                        $updateStmt->execute([$vnpTranId, $order['id']]);
                        
                        // Ghi log vào payment_history
                        $logStmt = $pdo->prepare("INSERT INTO payment_history (order_id, payment_method, amount, status, vnpay_response_code, vnpay_transaction_id, created_at) VALUES (?, 'vnpay', ?, 'success', ?, ?, NOW())");
                        $logStmt->execute([$order['id'], $vnp_Amount, $inputData['vnp_ResponseCode'], $vnpTranId]);
                    } else {
                        $Status = 2; // Trạng thái thanh toán thất bại / lỗi
                        // Cập nhật trạng thái đơn hàng thành failed
                        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'failed', updated_at = NOW() WHERE id = ?");
                        $updateStmt->execute([$order['id']]);
                        
                        // Ghi log vào payment_history
                        $logStmt = $pdo->prepare("INSERT INTO payment_history (order_id, payment_method, amount, status, vnpay_response_code, vnpay_transaction_id, created_at) VALUES (?, 'vnpay', ?, 'failed', ?, ?, NOW())");
                        $logStmt->execute([$order['id'], $vnp_Amount, $inputData['vnp_ResponseCode'], $vnpTranId]);
                    }
                    
                    //Trả kết quả về cho VNPAY: Website/APP TMĐT ghi nhận yêu cầu thành công                
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            }
            else {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'invalid amount';
            }
        } else {
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        }
    } else {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
    }
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknow error';
}
//Trả lại VNPAY theo định dạng JSON
echo json_encode($returnData);
