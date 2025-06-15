<?php
// Config already starts session, so we don't need to start it again
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../modules/order/functions.php');
require_once(__DIR__ . '/../../modules/cart/functions.php');

// Kiểm tra đăng nhập
$current_user_id = get_current_user_id();
if (!$current_user_id) {
    header('Location: ../login.php');
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - Web Mua Bán Đồ Cũ</title>
    
    <!-- Bootstrap CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Order CSS -->
    <link href="../../assets/css/order.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="order-container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../public/index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lịch sử đơn hàng</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="order-header">
            <h1><i class="fas fa-shopping-bag"></i> Lịch sử đơn hàng</h1>
            <p>Quản lý và theo dõi tất cả đơn hàng của bạn</p>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?php echo $total_orders; ?></h3>
                        <p class="mb-0">Tổng đơn hàng</p>
                    </div>
                </div>
            </div>            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">0</h3>
                        <p class="mb-0">Chờ xác nhận</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?php echo $success_count; ?></h3>
                        <p class="mb-0">Hoàn thành</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger"><?php echo $failed_count; ?></h3>
                        <p class="mb-0">Đã hủy</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="order-list">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm!</p>
                    <a href="../../public/index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Bắt đầu mua sắm
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php
                    $status = formatOrderStatus($order['status']);
                    $payment_status = formatPaymentStatus($order['payment_status']);
                    $payment_method = formatPaymentMethod($order['payment_method']);
                    ?>
                    <div class="order-card fade-in">
                        <div class="order-card-header">
                            <div>
                                <h3 class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></h3>
                                <p class="order-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <div class="order-status-group">
                                <span class="badge <?php echo $status['class']; ?>">
                                    <?php echo $status['text']; ?>
                                </span>
                                <span class="badge <?php echo $payment_status['class']; ?>">
                                    <?php echo $payment_status['text']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-card-body">
                            <div class="order-summary">
                                <div class="summary-item">
                                    <span class="label">Số sản phẩm</span>
                                    <span class="value"><?php echo $order['item_count']; ?> món</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Tổng tiền</span>
                                    <span class="value price"><?php echo number_format($order['total_amount']); ?> VND</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Phương thức thanh toán</span>
                                    <span class="value"><?php echo $payment_method; ?></span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Cập nhật lần cuối</span>
                                    <span class="value"><?php echo date('d/m/Y', strtotime($order['updated_at'])); ?></span>
                                </div>
                            </div>

                            <div class="order-actions">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </a>
                                
                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                    <button class="btn btn-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-times"></i> Hủy đơn
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Phân trang đơn hàng" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
                // Gửi AJAX request để hủy đơn hàng
                fetch('../../modules/order/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        reason: 'Khách hàng hủy đơn'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đơn hàng đã được hủy thành công');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra khi hủy đơn hàng');
                    console.error('Error:', error);
                });
            }
        }

        // Animation for cards when loading
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.order-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });
        });
    </script>
</body>
</html>
