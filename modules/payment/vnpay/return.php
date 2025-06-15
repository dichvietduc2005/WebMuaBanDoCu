<?php
require_once(__DIR__ . '/../../../config/config.php');
require_once(__DIR__ . '/../../cart/functions.php'); // For cart clearing

$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$order_number_from_vnpay = $_GET['vnp_TxnRef'] ?? null;

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
$payment_successful = false;
$payment_message = "";

if ($secureHash == $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        $payment_successful = true;
        $payment_message = "Thanh toán thành công!";

        if ($order_number_from_vnpay) {
            try {
                $stmt_check_order = $pdo->prepare("SELECT id, status, payment_status, buyer_id FROM orders WHERE order_number = ?");
                $stmt_check_order->execute([$order_number_from_vnpay]);
                $current_order_data = $stmt_check_order->fetch(PDO::FETCH_ASSOC);

                if ($current_order_data && ($current_order_data['payment_status'] == 'pending' || $current_order_data['payment_status'] == '')) {
                    $order_id = $current_order_data['id'];
                    $stmt_update_order = $pdo->prepare("UPDATE orders SET status = 'processing', payment_status = 'paid_via_return', updated_at = NOW() WHERE id = ?");
                    $stmt_update_order->execute([$order_id]);
                    
                    // Xóa giỏ hàng sau khi thanh toán thành công
                    $buyer_id = $current_order_data['buyer_id'];
                    if ($buyer_id) {
                        // Logged-in user - clear by user_id
                        clearCart($pdo, $buyer_id);
                        error_log("Cleared cart for user_id: $buyer_id after successful payment for order: $order_id (order_number: $order_number_from_vnpay)");
                    } else {
                        // Guest user - clear by session
                        clearCart($pdo, null);
                        error_log("Cleared guest cart after successful payment for order: $order_id (order_number: $order_number_from_vnpay)");
                    }
                }

            } catch (PDOException $e) {
                error_log("VNPay Return: DB error during tentative order update for order_number: " . $order_number_from_vnpay . " - " . $e->getMessage());
            }
        }
    } else {
        $payment_message = "Thanh toán không thành công. Mã lỗi VNPAY: " . htmlspecialchars($_GET['vnp_ResponseCode']);
        if ($order_number_from_vnpay) {
            try {
                 $stmt_update_order = $pdo->prepare("UPDATE orders SET status = 'payment_failed', payment_status = 'failed_via_return', updated_at = NOW() WHERE order_number = ?");
                 $stmt_update_order->execute([$order_number_from_vnpay]);
            } catch (PDOException $e) {
                 error_log("VNPay Return: DB error updating failed payment status for order_number: " . $order_number_from_vnpay . " - " . $e->getMessage());
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
$successPageUrl = '../../../public/payment/success.php';

header('Location: ' . $successPageUrl . '?' . $queryString);
exit;
?>
