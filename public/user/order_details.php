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

// Lấy ID đơn hàng từ URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?> - Web Mua Bán Đồ Cũ</title>
    
    <!-- Bootstrap CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Order CSS -->
    <link href="../../assets/css/order.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="order-details-container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../public/index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="order_history.php">Lịch sử đơn hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chi tiết đơn hàng</li>
            </ol>
        </nav>

        <!-- Order Details Card -->
        <div class="order-details-card fade-in">
            <!-- Header -->
            <div class="order-details-header">
                <h1><i class="fas fa-receipt"></i> Đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <span class="badge <?php echo $status['class']; ?> fs-6">
                        <?php echo $status['text']; ?>
                    </span>
                    <span class="badge <?php echo $payment_status['class']; ?> fs-6">
                        <?php echo $payment_status['text']; ?>
                    </span>
                </div>
            </div>

            <div class="order-details-body">
                <!-- Order Information -->
                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Thông tin đơn hàng
                    </h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Mã đơn hàng</span>
                            <span class="detail-value">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Ngày đặt hàng</span>
                            <span class="detail-value"><?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Cập nhật lần cuối</span>
                            <span class="detail-value"><?php echo date('d/m/Y H:i:s', strtotime($order['updated_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Trạng thái đơn hàng</span>
                            <span class="detail-value">
                                <span class="badge <?php echo $status['class']; ?>">
                                    <?php echo $status['text']; ?>
                                </span>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phương thức thanh toán</span>
                            <span class="detail-value"><?php echo $payment_method; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Trạng thái thanh toán</span>
                            <span class="detail-value">
                                <span class="badge <?php echo $payment_status['class']; ?>">
                                    <?php echo $payment_status['text']; ?>
                                </span>
                            </span>
                        </div>
                    </div>                </div>

                <!-- Notes Section -->
                <div class="detail-section">
                    <?php if (!empty($order['notes'])): ?>
                        <div class="detail-item mb-3">
                            <span class="detail-label">Ghi chú đơn hàng</span>
                            <span class="detail-value"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Order Items -->
                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-box"></i>
                        Sản phẩm đã đặt (<?php echo count($order['items']); ?> món)
                    </h2>
                    
                    <div class="table-responsive">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td data-label="Sản phẩm">
                                            <div class="product-info">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="../../<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_title']); ?>" 
                                                         class="product-image">
                                                <?php else: ?>
                                                    <div class="product-placeholder">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="product-name">
                                                        <?php echo htmlspecialchars($item['product_title']); ?>
                                                    </h6>
                                                    <?php if ($item['product_id'] && !empty($item['current_product_title'])): ?>
                                                        <small class="text-muted">
                                                            <a href="../../public/product.php?id=<?php echo $item['product_id']; ?>" target="_blank">
                                                                Xem sản phẩm hiện tại
                                                            </a>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">Sản phẩm không còn tồn tại</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Đơn giá">
                                            <span class="price"><?php echo number_format($item['product_price']); ?> VND</span>
                                        </td>
                                        <td data-label="Số lượng">
                                            <span class="quantity"><?php echo $item['quantity']; ?></span>
                                        </td>
                                        <td data-label="Thành tiền">
                                            <span class="price"><?php echo number_format($item['subtotal']); ?> VND</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Total -->
                    <div class="order-total">
                        <h4>Tổng cộng: <span class="total-amount"><?php echo number_format($order['total_amount']); ?> VND</span></h4>
                    </div>
                </div>

                <!-- Actions -->
                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-cogs"></i>
                        Thao tác
                    </h2>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="order_history.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        
                        <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                            <button class="btn btn-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                <i class="fas fa-times"></i> Hủy đơn hàng
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'completed'): ?>
                            <button class="btn btn-primary" onclick="reorderItems()">
                                <i class="fas fa-redo"></i> Đặt lại đơn hàng
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-secondary" onclick="printOrder()">
                            <i class="fas fa-print"></i> In đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function cancelOrder(orderId) {
            const reason = prompt('Vui lòng nhập lý do hủy đơn hàng:');
            if (reason !== null && reason.trim() !== '') {
                // Gửi AJAX request để hủy đơn hàng
                fetch('../../modules/order/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        reason: reason.trim()
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

        function reorderItems() {
            if (confirm('Bạn có muốn thêm tất cả sản phẩm trong đơn hàng này vào giỏ hàng không?')) {
                // Thực hiện logic thêm lại sản phẩm vào giỏ hàng
                alert('Chức năng đang được phát triển');
            }
        }

        function printOrder() {
            window.print();
        }

        // Animation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.order-details-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }
        });

        // Print styles
        window.addEventListener('beforeprint', function() {
            document.body.classList.add('printing');
        });

        window.addEventListener('afterprint', function() {
            document.body.classList.remove('printing');
        });
    </script>

    <style>
        /* Print styles */
        @media print {
            .breadcrumb,
            .detail-section:last-child,
            .btn {
                display: none !important;
            }
            
            .order-details-container {
                max-width: none;
                margin: 0;
                padding: 0;
            }
            
            .order-details-card {
                box-shadow: none;
                border: none;
            }
            
            .order-details-header {
                background: #f8f9fa !important;
                color: #333 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .badge {
                border: 1px solid #333 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</body>
</html>
