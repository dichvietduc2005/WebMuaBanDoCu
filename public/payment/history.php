<?php
require_once("../../config/config.php");
require_once("../../modules/cart/functions.php");

// Kiểm tra người dùng đã đăng nhập
$user_id = get_current_logged_in_user_id();
if (!$user_id) {
    header("Location: ../index.php");
    exit();
}

// Lấy lịch sử thanh toán
$payment_history = [];
try {
    $stmt = $pdo->prepare("
        SELECT ph.*, o.total_amount, o.status as order_status, o.created_at as order_date
        FROM payment_history ph
        JOIN orders o ON ph.order_id = o.id
        WHERE o.user_id = ? OR o.session_id = ?
        ORDER BY ph.created_at DESC
    ");
    $stmt->execute([$user_id, session_id()]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $payment_history = [];
}

$cartItemCount = getCartItemCount($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Lịch sử thanh toán - Mua bán đồ cũ</title>
    <!-- Bootstrap core CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- Custom styles for this template -->
    <link href="../../assets/css/jumbotron-narrow.css" rel="stylesheet">  
    <script src="../../assets/js/jquery-1.11.3.min.js"></script>
    <style>
        .status-success { color: #5cb85c; font-weight: bold; }
        .status-failed { color: #d9534f; font-weight: bold; }
        .status-pending { color: #f0ad4e; font-weight: bold; }
        .payment-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
        }
        .payment-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .payment-amount {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li><a href="../index.php">Trang chủ</a></li>
                    <li><a href="../cart/index.php">Giỏ hàng (<span><?php echo $cartItemCount; ?></span>)</a></li>
                    <li class="active"><a href="history.php">Lịch sử GD</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">Web Mua Bán Đồ Cũ</h3>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h2>Lịch sử thanh toán</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($payment_history)): ?>
                    <?php foreach ($payment_history as $payment): ?>
                        <div class="payment-card">
                            <div class="payment-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Mã đơn hàng:</strong> #<?php echo htmlspecialchars($payment['order_id']); ?>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <strong>Ngày:</strong> <?php echo date('d/m/Y H:i', strtotime($payment['order_date'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Số tiền:</strong><br>
                                    <span class="payment-amount"><?php echo number_format($payment['total_amount'], 0, ',', '.'); ?> VNĐ</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Phương thức:</strong><br>
                                    <?php echo strtoupper($payment['payment_method']); ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Trạng thái thanh toán:</strong><br>
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch($payment['status']) {
                                        case 'completed':
                                            $statusClass = 'status-success';
                                            $statusText = 'Thành công';
                                            break;
                                        case 'failed':
                                            $statusClass = 'status-failed';
                                            $statusText = 'Thất bại';
                                            break;
                                        case 'pending':
                                            $statusClass = 'status-pending';
                                            $statusText = 'Đang xử lý';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                            $statusText = ucfirst($payment['status']);
                                    }
                                    ?>
                                    <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Trạng thái đơn hàng:</strong><br>
                                    <?php
                                    $orderStatusClass = '';
                                    $orderStatusText = '';
                                    switch($payment['order_status']) {
                                        case 'completed':
                                            $orderStatusClass = 'status-success';
                                            $orderStatusText = 'Hoàn thành';
                                            break;
                                        case 'cancelled':
                                            $orderStatusClass = 'status-failed';
                                            $orderStatusText = 'Đã hủy';
                                            break;
                                        case 'pending':
                                            $orderStatusClass = 'status-pending';
                                            $orderStatusText = 'Đang xử lý';
                                            break;
                                        default:
                                            $orderStatusClass = 'status-pending';
                                            $orderStatusText = ucfirst($payment['order_status']);
                                    }
                                    ?>
                                    <span class="<?php echo $orderStatusClass; ?>"><?php echo $orderStatusText; ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($payment['transaction_id'])): ?>
                            <div style="margin-top: 10px;">
                                <small class="text-muted">
                                    <strong>Mã giao dịch:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?>
                                    <?php if (!empty($payment['response_message'])): ?>
                                    | <strong>Thông báo:</strong> <?php echo htmlspecialchars($payment['response_message']); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>Chưa có lịch sử thanh toán!</strong> Bạn chưa thực hiện giao dịch nào.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row" style="margin-top: 20px;">
            <div class="col-lg-12">
                <a href="../index.php" class="btn btn-default">Về trang chủ</a>
                <a href="../cart/index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
        </div>

        <footer class="footer" style="margin-top: 30px;">
            <p>&copy; Web Mua Ban Do Cu <?php echo date('Y')?></p>
        </footer>

    </div> <!-- /container -->

</body>
</html>
