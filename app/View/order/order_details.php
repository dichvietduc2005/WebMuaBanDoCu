<?php
// Sử dụng đường dẫn tuyệt đối thay vì đường dẫn tương đối
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/WebMuaBanDoCu';
require_once $root_path . '/config/config.php';
require_once $root_path . '/app/Controllers/auth_helper.php'; // For Auth functions
// Thêm include OrderController để sử dụng hàm getOrderDetails()
require_once $root_path . '/app/Controllers/order/OrderController.php';
// Autoloader sẽ tự động load OrderController và CartController
require_once $root_path . '/app/Components/header/Header.php';
require_once $root_path . '/app/Components/footer/Footer.php';

// Kiểm tra đăng nhập bằng Auth helper
$user = requireLogin();
$current_user_id = $user['user_id'];

// Lấy ID đơn hàng từ URL
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$order_id) {
    header('Location: order_history.php');
    exit();
}

// Lấy chi tiết đơn hàng
$order = getOrderDetails($pdo, $order_id, $current_user_id);

if (!$order) {
    header('Location: order_history.php');
    exit();
}

// Format thông tin để hiển thị
$status = formatOrderStatus($order['status']);
$payment_status = formatPaymentStatus($order['payment_status']);
$payment_method = formatPaymentMethod($order['payment_method']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chi tiết đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?> - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container123 {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .order-header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .order-title {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .order-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .status-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .status-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .status-item:last-child {
            border-bottom: none;
        }

        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #666;
        }

        .status-icon.delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-icon.shipping {
            background: #cce7ff;
            color: #0066cc;
        }

        .status-content {
            flex: 1;
        }

        .status-title {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 2px;
        }

        .status-desc {
            font-size: 14px;
            color: #666;
        }

        .tracking-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .tracking-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .items-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items-table th {
            text-align: left;
            padding: 12px 8px;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .items-table td {
            padding: 15px 8px;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: top;
        }

        .product-info {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .product-sku {
            font-size: 12px;
            color: #999;
        }

        .price {
            font-weight: 600;
            color: #1a1a1a;
        }

        .quantity {
            text-align: center;
            font-weight: 500;
        }

        .summary-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 16px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }

        .address-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 15px;
        }

        .address-block h4 {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .address-text {
            color: #666;
            line-height: 1.5;
        }

        .actions-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-outline {
            background: white;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .breadcrumb {
            margin-bottom: 20px;
            color: #666;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .address-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .product-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .items-table {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <?php
    renderHeader($pdo);
    ?>
    <div class="container123">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?php echo $root_path; ?>/public/TrangChu.php">Trang chủ</a> /
            <a href="order_history.php">Lịch sử đơn hàng</a> /
            Chi tiết đơn hàng
        </nav>

        <!-- Order Header -->
        <div class="order-header">
            <h1 class="order-title">Chi Tiết Đơn Hàng</h1>
            <div class="order-meta">
                Đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?> • Đặt vào ngày
                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
            </div>
            <h2 class="section-title">Trạng Thái Đơn Hàng</h2>
            <div class="status-item">
                <div class="status-icon <?php echo strtolower($order['status']) === 'completed' ? 'delivered' : ''; ?>">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="status-content">
                    <div class="status-title">
                        <?php
                        $status_vietnamese = [
                            'pending' => 'Chờ xử lý',
                            'confirmed' => 'Đã xác nhận',
                            'processing' => 'Đang xử lý',
                            'shipping' => 'Đang giao hàng',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy'
                        ];
                        echo $status_vietnamese[$order['status']] ?? ucfirst($order['status']);
                        ?>
                    </div>
                    <div class="status-desc">
                        <?php if ($order['status'] === 'completed'): ?>
                            Đã giao vào ngày <?php echo date('d/m/Y', strtotime($order['updated_at'])); ?>
                        <?php else: ?>
                            Cập nhật vào ngày <?php echo date('d/m/Y', strtotime($order['updated_at'])); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tracking Information -->
        <?php if (!empty($order['tracking_number'])): ?>
            <div class="tracking-section">
                <h2 class="section-title">Thông Tin Vận Chuyển</h2>
                <div class="tracking-info">
                    <i class="fas fa-shipping-fast"></i>
                    <div>
                        <strong>Đang giao hàng</strong><br>
                        Mã vận đơn: <?php echo htmlspecialchars($order['tracking_number']); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Items Ordered -->
        <div class="items-section">
            <h2 class="section-title">Sản Phẩm Đã Đặt</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="../../../public/<?php echo htmlspecialchars($item['product_image']); ?>"
                                            alt="<?php echo htmlspecialchars($item['product_title']); ?>" 
                                            class="product-image"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="product-image"
                                            style="display: none; align-items: center; justify-content: center; background: #f0f0f0;">
                                            <i class="fas fa-image" style="color: #999;"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="product-image"
                                            style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                                            <i class="fas fa-image" style="color: #999;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-details">
                                        <div class="product-name"><?php echo htmlspecialchars($item['product_title']); ?>
                                        </div>
                                        <div class="product-sku">Mã SP: <?php echo htmlspecialchars($item['product_id']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="quantity"><?php echo $item['quantity']; ?></td>
                            <td class="price"><?php echo number_format($item['product_price'], 0, ',', '.'); ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Summary -->
        <div class="summary-section">
            <h2 class="section-title">Tổng Kết Đơn Hàng</h2>
            <div class="summary-row">
                <span>Tạm tính</span>
                <span><?php echo number_format(($order['total_amount'] - 10000 - 20000), 0, ',', '.'); ?> VNĐ</span>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển</span>
                <span>10.000 VNĐ</span>
            </div>
            <div class="summary-row">
                <span>Thuế</span>
                <span>20.000 VNĐ</span>
            </div>
            <div class="summary-row">
                <span>Tổng cộng</span>
                <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</span>
            </div>
        </div>

        <!-- Shipping & Billing Address
        <div class="address-section">
            <h2 class="section-title">Địa Chỉ Giao Hàng</h2>
            <div class="address-text">
                <?php if (!empty($order['shipping_address'])): ?>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                <?php else: ?>
                    <?php echo htmlspecialchars($user['username']); ?><br>
                    Địa chỉ sẽ được cập nhật<br>
                    Việt Nam
                <?php endif; ?>
            </div>

            <h2 class="section-title" style="margin-top: 30px;">Địa Chỉ Thanh Toán</h2>
            <div class="address-text">
                <?php if (!empty($order['billing_address'])): ?>
                    <?php echo nl2br(htmlspecialchars($order['billing_address'])); ?>
                <?php else: ?>
                    <?php echo htmlspecialchars($user['username']); ?><br>
                    Địa chỉ sẽ được cập nhật<br>
                    Việt Nam
                <?php endif; ?>
            </div>
        </div> -->

        <!-- Actions -->
        <div class="actions-section">
            <h2 class="section-title">Thao Tác</h2>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="order_history.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>

                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                    <button class="btn btn-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                        <i class="fas fa-times"></i> Hủy đơn hàng
                    </button>
                <?php endif; ?>

                <?php if ($order['status'] === 'completed'): ?>
                    <button class="btn btn-primary" onclick="reorderItems(<?php echo $order['id']; ?>)">
                        <i class="fas fa-redo"></i> Đặt lại
                    </button>
                <?php endif; ?>

                <button class="btn btn-outline" onclick="printOrder()">
                    <i class="fas fa-print"></i> In đơn hàng
                </button>
            </div>
        </div>
    </div>

    <script src="../../../public/assets/js/order_details.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let userId = <?php echo $_SESSION['user_id'] ?>
    </script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"></script>
    <?php footer(); ?>
</body>

</html>