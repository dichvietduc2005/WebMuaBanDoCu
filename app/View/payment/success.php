<?php
session_start();
require_once '../../../config/config.php';
require_once('../../helpers.php'); // For helper functions

// Retrieve VNPAY parameters from URL
$vnp_TxnRef = htmlspecialchars($_GET['vnp_TxnRef'] ?? 'N/A');
$vnp_Amount = htmlspecialchars($_GET['vnp_Amount'] ?? '0');
$vnp_OrderInfo = htmlspecialchars($_GET['vnp_OrderInfo'] ?? 'N/A');
$vnp_ResponseCode = htmlspecialchars($_GET['vnp_ResponseCode'] ?? 'N/A');
$vnp_TransactionNo = htmlspecialchars($_GET['vnp_TransactionNo'] ?? 'N/A');
$vnp_BankCode = htmlspecialchars($_GET['vnp_BankCode'] ?? 'N/A');
$vnp_PayDate = htmlspecialchars($_GET['vnp_PayDate'] ?? 'N/A');

// Custom parameters we added in return.php
$payment_status_message = htmlspecialchars($_GET['payment_status_message'] ?? 'Không có thông tin trạng thái.');
$payment_successful = isset($_GET['payment_successful']) && $_GET['payment_successful'] === '1';
$app_order_id = htmlspecialchars($_GET['app_order_id'] ?? $vnp_TxnRef); // Use app_order_id if available

// Cập nhật trạng thái đơn hàng dựa trên kết quả thanh toán
if ($payment_successful && $vnp_ResponseCode === '00') {
    // Thanh toán thành công
    $order_status = 'success';
    $payment_status = 'paid';
} else {
    // Thanh toán thất bại
    $order_status = 'failed';
    $payment_status = 'failed';
}

// Cập nhật trạng thái trong database
updateOrderStatusByNumber($pdo, $vnp_TxnRef, $order_status, $payment_status, $vnp_TransactionNo);

// Format amount (VNPAY amount is x100)
$display_amount = number_format((int)$vnp_Amount / 100, 0, ',', '.') . ' VNĐ';

// Format PayDate (YYYYMMDDHHMMSS to d/m/Y H:i:s)
$formatted_pay_date = 'N/A';
if (strlen($vnp_PayDate) == 14) {
    try {
        $date_obj = DateTime::createFromFormat('YmdHis', $vnp_PayDate);
        if ($date_obj) {
            $formatted_pay_date = $date_obj->format('d/m/Y H:i:s');
        }
    } catch (Exception $e) {
        // Keep 'N/A'
    }
}

// Clear session-based cart if payment was successful and you use a session cart for display
// The database cart is cleared by ipn.php
if ($payment_successful && isset($_SESSION['cart'])) {
    // If you want to be absolutely sure the cart displayed to the user is empty NOW,
    // you can clear the session cart.
    // However, if your cart display always fetches from DB (via getCartContents),
    // this might not be strictly necessary as the DB cart (cart_items) should be empty.
    // unset($_SESSION['cart']); // Example: if your cart is stored directly in $_SESSION['cart']
    
    // If get_or_create_cart_id relies on $_SESSION['user_id'] or session_id()
    // and clearCart() uses that, you could call clearCart here for the current user's session cart.
    // This depends on how your cart functions are tied to the session.
    // For now, we assume ipn.php has handled the persistent cart data.
}

$page_title = $payment_successful ? "Thanh toán thành công" : "Thanh toán thất bại";

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Cửa Hàng Đồ Cũ</title>    <link href="../../../public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../public/assets/css/checkout.css" rel="stylesheet"> <!-- You might want a specific success page CSS -->
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; }
        .container { max-width: 800px; margin-top: 50px; }
        .card { border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .card-header { background-color: <?php echo $payment_successful ? '#28a745' : '#dc3545'; ?>; color: white; text-align: center; padding: 15px; }
        .card-header h4 { margin: 0; font-size: 1.5rem; }
        .card-body { padding: 30px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: bold; width: 40%; }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        .footer-links { text-align: center; margin-top: 30px; }
        .footer-links a { margin: 0 10px; color: #007bff; text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4><?php echo $page_title; ?></h4>
            </div>
            <div class="card-body">
                <p class="<?php echo $payment_successful ? 'text-success' : 'text-danger'; ?>" style="font-size: 1.1rem; text-align: center; margin-bottom: 25px;">
                    <?php echo $payment_status_message; ?>
                </p>

                <table class="info-table">
                    <tr>
                        <td>Mã đơn hàng của bạn:</td>
                        <td><?php echo $app_order_id; ?></td>
                    </tr>
                    <tr>
                        <td>Số tiền thanh toán:</td>
                        <td><?php echo $display_amount; ?></td>
                    </tr>
                    <tr>
                        <td>Nội dung thanh toán:</td>
                        <td><?php echo $vnp_OrderInfo; ?></td>
                    </tr>
                    <?php if ($payment_successful): ?>
                    <tr>
                        <td>Mã giao dịch VNPAY:</td>
                        <td><?php echo $vnp_TransactionNo; ?></td>
                    </tr>
                    <tr>
                        <td>Ngân hàng:</td>
                        <td><?php echo $vnp_BankCode; ?></td>
                    </tr>
                    <tr>
                        <td>Thời gian thanh toán:</td>
                        <td><?php echo $formatted_pay_date; ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Mã phản hồi VNPAY:</td>
                        <td><?php echo $vnp_ResponseCode; ?></td>
                    </tr>
                </table>

                <?php if ($payment_successful): ?>
                    <p style="text-align: center;">Cảm ơn bạn đã mua hàng! Đơn hàng của bạn đang được xử lý.</p>
                    <p style="text-align: center;">Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email <a href="mailto:support@example.com">support@example.com</a> hoặc số điện thoại 0123-456-789, cung cấp mã đơn hàng của bạn.</p>
                <?php else: ?>
                    <p style="text-align: center;">Nếu bạn tin rằng có lỗi xảy ra, vui lòng thử lại hoặc liên hệ với bộ phận hỗ trợ của chúng tôi.</p>
                <?php endif; ?>

                <div class="footer-links">
                    <a href="/WebMuaBanDoCu/public/TrangChu.php">Tiếp tục mua sắm</a>
                    <?php if ($payment_successful && $app_order_id !== 'N/A'): ?>
                        <!-- You could link to an order details page if you have one -->
                        <!-- <a href="/Web_MuaBanDoCu/public/order/details.php?order_id=<?php echo urlencode($app_order_id); ?>">Xem chi tiết đơn hàng</a> -->
                    <?php else: ?>
                         <a href="/WebMuaBanDoCu/public/cart/">Xem lại giỏ hàng</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/jquery-1.11.3.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
</body>
</html>
