<?php
// Sử dụng đường dẫn tuyệt đối thay vì tương đối để tránh lỗi khi được gọi từ router
$config_path = __DIR__ . '/../../../config/config.php';
require_once($config_path);
require_once(__DIR__ . '/../../helpers.php'); // For helper functions
require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';


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

if ($payment_successful && isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

$page_title = $payment_successful ? "Thanh toán thành công" : "Thanh toán thất bại";

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Cửa Hàng Đồ Cũ</title>    <link href="/WebMuaBanDoCu/public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/WebMuaBanDoCu/public/assets/css/checkout.css" rel="stylesheet"> <!-- You might want a specific success page CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
<?php renderHeader($pdo); ?>
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
                    <a href="/WebMuaBanDoCu/app/View/Home.php">Tiếp tục mua sắm</a>
                    <?php if ($payment_successful && $app_order_id !== 'N/A'): ?>
                        <a href="/WebMuaBanDoCu/app/View/order/order_history.php">Xem lịch sử đơn hàng</a>
                    <?php else: ?>
                         <a href="/WebMuaBanDoCu/app/View/cart/index.php">Xem lại giỏ hàng</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php footer(); ?>
    <script src="/WebMuaBanDoCu/public/assets/js/jquery-1.11.3.min.js"></script>
    <script src="/WebMuaBanDoCu/public/assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>