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

require_once(__DIR__ . "/../../../config/config.php");
require_once(__DIR__ . "/../../cart/functions.php"); 

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
$order_number = $inputData['vnp_TxnRef']; // Sử dụng order_number thay vì orderId

try {
    //Check Orderid    
    //Kiểm tra checksum của dữ liệu
    if ($secureHash == $vnp_SecureHash) {
        //Lấy thông tin đơn hàng từ database bằng order_number
        $stmt = $pdo->prepare("SELECT id, total_amount, status, payment_status, buyer_id FROM orders WHERE order_number = ?");
        $stmt->execute([$order_number]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order != NULL) {
            if($order["total_amount"] == $vnp_Amount) //Kiểm tra số tiền thanh toán
            {
                if ($order["payment_status"] == 'pending' || $order["payment_status"] == '') {
                    if ($inputData['vnp_ResponseCode'] == '00') {
                        $Status = 1; // Trạng thái thanh toán thành công
                        
                        // Cập nhật trạng thái đơn hàng
                        $stmt_update = $pdo->prepare("UPDATE orders SET status = 'success', payment_status = 'paid', vnpay_transaction_id = ?, updated_at = NOW() WHERE id = ?");
                        $stmt_update->execute([$vnpTranId, $order['id']]);
                        
                        // Xóa giỏ hàng sau khi thanh toán thành công
                        if ($order['buyer_id']) {
                            clearCart($pdo, $order['buyer_id']);
                            error_log("IPN: Cleared cart for user_id: {$order['buyer_id']} after successful payment for order: {$order['id']}");
                        }
                        
                    } else {
                        $Status = 2; // Trạng thái thanh toán thất bại / lỗi
                        
                        // Cập nhật trạng thái thất bại
                        $stmt_update = $pdo->prepare("UPDATE orders SET status = 'failed', payment_status = 'failed', updated_at = NOW() WHERE id = ?");
                        $stmt_update->execute([$order['id']]);
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
