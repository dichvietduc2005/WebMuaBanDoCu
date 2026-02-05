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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/order-history.css">
    <!-- Mobile Responsive CSS for Order Pages -->
    <!-- <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-order-pages.css"> -->
    <!-- User chat styles are now included in Header.php -->
    
</head>
<body>
     <?php
    renderHeader($pdo);
    ?>
    <div class="modern-container">
        <!-- Breadcrumb -->
        <!-- <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="../index.php">Trang chủ</a>
            <span class="breadcrumb-separator">/</span>
            <span>Lịch sử đơn hàng</span>
        </nav>       -->
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
                <a href="../../../public/index.php" class="btn-primary">
                    <i class="fas fa-shopping-bag"></i> Bắt đầu mua sắm
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                    <div class="order-header">
                        <div class="order-main-info">
                            <?php if (!empty($order['first_product_image'])): ?>
                                <img src="../../../public/<?php echo htmlspecialchars($order['first_product_image']); ?>" 
                                     alt="Order Product" 
                                     class="order-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="order-placeholder" style="display: none;">
                                    <i class="fas fa-box"></i>
                                </div>
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
                                Xem chi tiết
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
    <script>userId = <?php echo $_SESSION['user_id'] ?></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/user_chat_system.js"> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <?php footer(); ?>
</body>
</html>
