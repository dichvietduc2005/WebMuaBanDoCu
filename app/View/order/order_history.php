<?php
// Config already starts session, so we don't need to start it again
require_once '../../../config/config.php';
require_once('../../Controllers/auth_helper.php'); // For Auth functions

// Load OrderController và CartController
require_once('../../Controllers/order/OrderController.php');
require_once('../../Controllers/cart/CartController.php');

include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

// Kiểm tra đăng nhập bằng Auth helper
$user = requireLogin();
$current_user_id = $user['user_id'];

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Lấy danh sách đơn hàng
$orders = getOrdersByUserId($pdo, $current_user_id, $limit, $offset);
$total_orders = getOrderCountByStatus($pdo, $current_user_id);
$total_pages = ceil($total_orders / $limit);

// Lấy thống kê nhanh  
$success_count = getOrderCountByStatus($pdo, $current_user_id, 'success');
$failed_count = getOrderCountByStatus($pdo, $current_user_id, 'failed');
$pending_count = getOrderCountByStatus($pdo, $current_user_id, 'pending');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
        }
        
        .modern-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .section-header {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
          .order-item {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.2s ease;
            border: 1px solid #f0f0f0;
        }
        
        .order-item:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .order-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-main-info {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            flex: 1;
        }
        
        .order-image {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            object-fit: cover;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .order-details {
            flex: 1;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        
        .order-date {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .order-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 12px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #666;
        }
        
        .meta-item i {
            font-size: 12px;
            color: #999;
        }
        
        .order-status-section {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .payment-paid {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .payment-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .payment-failed {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 16px;
        }
        
        .order-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
        }
        
        .view-details-btn {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .view-details-btn:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .order-notes-preview {
            font-size: 13px;
            color: #666;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 8px;
            border-left: 3px solid #007bff;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .order-placeholder {
            width: 70px;
            height: 70px;
            background: #f0f0f0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .stats-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #f0f0f0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
        }
        
        .empty-state-icon {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            font-weight: 600;
            color: #666;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            color: #999;
            margin: 0 8px;
        }
          .order-placeholder {
            width: 70px;
            height: 70px;
            background: #f0f0f0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .stats-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #f0f0f0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @media (max-width: 768px) {
            .modern-container {
                padding: 15px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .order-main-info {
                width: 100%;
                margin-bottom: 12px;
            }
            
            .order-actions {
                align-items: flex-start;
                width: 100%;
            }
            
            .order-meta {
                flex-direction: column;
                gap: 8px;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }
    </style>
</head>
<body>
     <?php
    renderHeader($pdo);
    ?>
    <div class="modern-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="../TrangChu.php">Trang chủ</a>
            <span class="breadcrumb-separator">/</span>
            <span>Lịch sử đơn hàng</span>
        </nav>        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Lịch sử đơn hàng</h1>
            <p class="page-subtitle">Theo dõi và quản lý tất cả đơn hàng của bạn</p>
        </div><!-- Order Statistics -->
        <div class="stats-section">
            <div class="section-header">Thống kê đơn hàng</div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $success_count; ?></div>
                    <div class="stat-label">Thành công</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $failed_count; ?></div>
                    <div class="stat-label">Thất bại</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $pending_count; ?></div>
                    <div class="stat-label">Chờ xử lý</div>
                </div>
            </div>
        </div>

        <!-- Past Orders Section -->
        <div class="section-header">Lịch sử đơn hàng</div>

        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Chưa có đơn hàng nào</h3>
                <p>Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm!</p>
                <a href="../../../public/TrangChu.php" class="btn-primary">
                    <i class="fas fa-shopping-bag"></i> Bắt đầu mua sắm
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                    <div class="order-header">
                        <div class="order-main-info">
                            <?php if (!empty($order['first_product_image']) && file_exists("../../" . $order['first_product_image'])): ?>
                                <img src="../../<?php echo htmlspecialchars($order['first_product_image']); ?>" 
                                     alt="Order Product" 
                                     class="order-image">
                            <?php else: ?>
                                <div class="order-placeholder">
                                    <i class="fas fa-box"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="order-details">
                                <div class="order-number">
                                    Đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?>
                                </div>
                                <div class="order-date">
                                    <i class="fas fa-calendar"></i>
                                    Đặt hàng: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </div>
                                
                                <div class="order-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-box"></i>
                                        <span><?php echo $order['item_count']; ?> sản phẩm</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-credit-card"></i>
                                        <span><?php echo $order['payment_method'] == 'vnpay' ? 'VNPay' : ucfirst($order['payment_method']); ?></span>
                                    </div>
                                    <?php if ($order['updated_at'] != $order['created_at']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span>Cập nhật: <?php echo date('d/m/Y H:i', strtotime($order['updated_at'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="order-status-section">
                                    <?php
                                    $statusClass = '';
                                    switch($order['status']) {
                                        case 'success':
                                            $statusClass = 'status-success';
                                            $statusText = 'Thành công';
                                            break;
                                        case 'pending':
                                            $statusClass = 'status-pending';
                                            $statusText = 'Chờ xử lý';
                                            break;
                                        case 'failed':
                                            $statusClass = 'status-failed';
                                            $statusText = 'Thất bại';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'status-cancelled';
                                            $statusText = 'Đã hủy';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                            $statusText = ucfirst($order['status']);
                                    }
                                    
                                    $paymentClass = '';
                                    switch($order['payment_status']) {
                                        case 'paid':
                                        case 'paid_via_return':
                                            $paymentClass = 'payment-paid';
                                            $paymentText = 'Đã thanh toán';
                                            break;
                                        case 'pending':
                                            $paymentClass = 'payment-pending';
                                            $paymentText = 'Chờ thanh toán';
                                            break;
                                        case 'failed':
                                            $paymentClass = 'payment-failed';
                                            $paymentText = 'Thanh toán thất bại';
                                            break;
                                        default:
                                            $paymentClass = 'payment-pending';
                                            $paymentText = ucfirst($order['payment_status']);
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                    <span class="status-badge <?php echo $paymentClass; ?>">
                                        <?php echo $paymentText; ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($order['notes'])): ?>
                                    <div class="order-notes-preview">
                                        <strong>Ghi chú:</strong> <?php echo mb_substr(htmlspecialchars($order['notes']), 0, 100); ?><?php echo mb_strlen($order['notes']) > 100 ? '...' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Tổng tiền:</span>
                                    <span style="font-weight: 600; color: #007bff;"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</span>
                                </div>
                            </div>
                            
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="view-details-btn">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           style="padding: 8px 12px; background: <?php echo $i == $page ? '#007bff' : '#f8f9fa'; ?>; 
                                  color: <?php echo $i == $page ? 'white' : '#666'; ?>; 
                                  border-radius: 6px; text-decoration: none; font-size: 14px;">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <?php footer(); ?>
</body>
</html>
