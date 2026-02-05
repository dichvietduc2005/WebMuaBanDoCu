<?php
// Check if session already started before starting a new one
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sử dụng đường dẫn tuyệt đối thay vì tương đối để tránh lỗi khi được gọi từ router
$config_path = __DIR__ . '/../../../config/config.php';
require_once($config_path);
require_once(__DIR__ . '/../../helpers.php'); // For helper functions
require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';

// Log session info for debugging
error_log("Payment success page - Session ID: " . session_id());
error_log("Payment success page - User logged in: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'No'));
error_log("Payment success page - Session data: " . print_r($_SESSION, true));
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
    <title><?php echo $page_title; ?> - Cửa Hàng Đồ Cũ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --success-color: #00b894;
            --danger-color: #ff7675;
            --bg-color: #f0f2f5;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-color);
            color: #2d3436;
        }
        .container { max-width: 650px; padding: 40px 15px; }
        .success-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
            border: none;
            transition: transform 0.3s ease;
        }
        .success-header {
            padding: 40px 20px 20px;
            text-align: center;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .status-icon.success {
            background-color: rgba(0, 184, 148, 0.1);
            color: var(--success-color);
        }
        .status-icon.error {
            background-color: rgba(255, 118, 117, 0.1);
            color: var(--danger-color);
        }
        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .card-title {
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 10px;
        }
        .status-msg {
            color: #636e72;
            font-size: 1rem;
            margin-bottom: 30px;
        }
        .order-info-box {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #dfe6e9;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #636e72; font-weight: 500; }
        .info-value { color: #2d3436; font-weight: 600; text-align: right; }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }
        .btn-premium {
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-amazon {
            background: #FF9900;
            color: #000;
            border: none;
        }
        .btn-amazon:hover {
            background: #e68a00;
            transform: translateY(-2px);
        }
        .btn-outline-custom {
            border: 2px solid #dfe6e9;
            color: #2d3436;
        }
        .btn-outline-custom:hover {
            background: #f8f9fa;
            border-color: #b2bec3;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .info-value { text-align: left; }
            .status-icon { width: 60px; height: 60px; font-size: 30px; }
        }
    </style>
</head>
<body>
<?php renderHeader($pdo); ?>

<div class="container">
    <div class="success-card">
        <div class="success-header">
            <div class="status-icon <?php echo $payment_successful ? 'success' : 'error'; ?>">
                <i class="fas <?php echo $payment_successful ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            </div>
            <h2 class="card-title"><?php echo $page_title; ?></h2>
            <p class="status-msg"><?php echo $payment_status_message; ?></p>
        </div>

        <div class="card-body px-4 pb-4 px-md-5 pb-md-5">
            <div class="order-info-box">
                <div class="info-row">
                    <span class="info-label">Mã đơn hàng:</span>
                    <span class="info-value">#<?php echo $app_order_id; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Số tiền:</span>
                    <span class="info-value text-danger fs-5"><?php echo $display_amount; ?></span>
                </div>
                <?php if ($payment_successful): ?>
                <div class="info-row">
                    <span class="info-label">Ngân hàng:</span>
                    <span class="info-value"><?php echo $vnp_BankCode; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Thời gian:</span>
                    <span class="info-value"><?php echo $formatted_pay_date; ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Mô tả:</span>
                    <span class="info-value text-truncate" style="max-width: 250px;"><?php echo $vnp_OrderInfo; ?></span>
                </div>
            </div>

            <?php if ($payment_successful): ?>
                <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success p-3 rounded-4 mb-4">
                    <div class="d-flex gap-2">
                        <i class="fas fa-gift mt-1"></i>
                        <small>Cảm ơn bạn đã tin tưởng! Đơn hàng của bạn sẽ được xử lý trong vòng 24h tới.</small>
                    </div>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <?php if ($payment_successful): ?>
                    <a href="<?= BASE_URL ?>app/View/order/order_history.php" class="btn-premium btn-amazon shadow-sm">
                        <i class="fas fa-box-open"></i> Xem lịch sử đơn hàng
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>app/View/cart/index.php" class="btn-premium btn-amazon shadow-sm">
                        <i class="fas fa-shopping-cart"></i> Xem lại giỏ hàng
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>app/View/Home.php" class="btn-premium btn-outline-custom">
                    <i class="fas fa-search"></i> Tiếp tục mua sản phẩm khác
                </a>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">Cần hỗ trợ? <a href="mailto:support@muabandocu.vn" class="text-decoration-none">Liên hệ ngay</a></p>
            </div>
        </div>
    </div>
</div>

<?php footer(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
