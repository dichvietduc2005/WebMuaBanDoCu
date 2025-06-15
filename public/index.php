<?php
require_once('../config/config.php'); // For $pdo and session_start()
require_once('../modules/cart/functions.php'); // For cart functions
require_once('../modules/order/functions.php'); // For order functions

// Lấy user_id hiện tại (nếu đã đăng nhập)
$user_id = get_current_user_id();
$cartItemCount = getCartItemCount($pdo, $user_id);

// Lấy đơn hàng gần đây nếu đã đăng nhập
$recent_orders = [];
if ($user_id) {
    try {
        $recent_orders = getOrdersByUserId($pdo, $user_id, 3, 0); // Lấy 3 đơn hàng gần nhất
    } catch (Exception $e) {
        error_log("Error getting recent orders: " . $e->getMessage());
        $recent_orders = [];
    }
}


// Lấy danh sách sản phẩm từ database
$products = [];
try {
    // Lấy sản phẩm và hình ảnh đầu tiên của mỗi sản phẩm
    $stmt = $pdo->query("
        SELECT p.*, pi.image_path 
        FROM products p
        LEFT JOIN (
            SELECT product_id, image_path, ROW_NUMBER() OVER(PARTITION BY product_id ORDER BY is_primary DESC, id ASC) as rn
            FROM product_images
        ) pi ON p.id = pi.product_id AND pi.rn = 1
        WHERE p.status = 'active' AND p.stock_quantity > 0
        ORDER BY p.created_at DESC
        LIMIT 20 
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Trang chủ - Mua bán đồ cũ</title>
    <!-- Bootstrap core CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- Custom styles for this template -->
    <link href="../assets/css/jumbotron-narrow.css" rel="stylesheet">  
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/order.css">
    <script src="../assets/js/jquery-1.11.3.min.js"></script>
    
</head>

<body>
    <div class="container">
        <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li class="active"><a href="index.php">Trang chủ</a></li>
                    <li><a href="cart/index.php">Giỏ hàng (<span id="header-cart-count"><?php echo $cartItemCount; ?></span>)</a></li>
                    <?php if ($user_id): ?>
                        <li><a href="user/sell_item.php">Đăng bán</a></li> 
                        <li><a href="user/order_history.php">Lịch sử đơn hàng</a></li>
                        <li><a href="user/logout.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="user/login.php">Đăng nhập</a></li>
                        <li><a href="user/register.php">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <h3 class="text-muted">Web Mua Bán Đồ Cũ</h3>
            <form class="navbar-form navbar-left" role="search" action="search.php" method="GET">
                <div class="form-group">
                    <input type="text" name="query" class="form-control" placeholder="Tìm kiếm sản phẩm...">
                </div>
                <button type="submit" class="btn btn-default">Tìm kiếm</button>
            </form>
        </div>

        <div class="jumbotron">
            <h1>Chào mừng!</h1>
            <p class="lead">Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng.</p>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h2>Sản phẩm mới nhất</h2>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars(!empty($product['image_path']) && file_exists('../' . $product['image_path']) ? '../' . $product['image_path'] : '../assets/images/default_product_image.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>">
                            <div class="product-info">
                                <div class="product-title"><?php echo htmlspecialchars($product['title']); ?></div>
                                <div class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</div>
                                <div class="product-condition">
                                    Tình trạng: 
                                    <?php
                                    $conditions = [
                                        'new' => 'Mới',
                                        'like_new' => 'Như mới', 
                                        'good' => 'Tốt',
                                        'fair' => 'Khá',
                                        'poor' => 'Cũ'
                                    ];
                                    echo $conditions[$product['condition_status']] ?? $product['condition_status'];
                                    ?>
                                    | Còn lại: <?php echo $product['stock_quantity']; ?> sản phẩm
                                </div>
                                <!-- Form để thêm vào giỏ hàng -->
                                <form action="../modules/cart/handler.php" method="POST" class="add-to-cart-form form-add-to-cart">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="quantity_<?php echo $product['id']; ?>" style="font-size: 12px;">Số lượng:</label>
                                        <input type="number" 
                                               id="quantity_<?php echo $product['id']; ?>"
                                               name="quantity" 
                                               value="1" 
                                               min="1" 
                                               max="<?php echo $product['stock_quantity']; ?>"
                                               style="width: 60px; text-align:center;" 
                                               class="form-control form-control-sm">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Thêm vào giỏ</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-12">
                    <div class="alert alert-info">
                        <strong>Chưa có sản phẩm nào!</strong> Vui lòng thêm dữ liệu mẫu bằng cách chạy file SQL trong thư mục data.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Phần Khám phá danh mục -->
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title">Khám phá danh mục</h2>
            </div>
        </div>
        <div class="row category-grid">
            <!-- Danh sách các danh mục -->
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=1">
                    <img src="../assets/images/categories/bat_dong_san.png" alt="Bất động sản">
                    <span>Bất động sản</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=2">
                    <img src="../assets/images/categories/xe_co.png" alt="Xe cộ">
                    <span>Xe cộ</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=3">
                    <img src="../assets/images/categories/do_dien_tu.png" alt="Đồ điện tử">
                    <span>Đồ điện tử</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=4">
                    <img src="../assets/images/categories/do_gia_dung.png" alt="Đồ gia dụng, nội thất">
                    <span>Đồ gia dụng, nội thất</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=5">
                    <img src="../assets/images/categories/giai_tri_the_thao.png" alt="Giải trí, Thể thao">
                    <span>Giải trí, Thể thao</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=6">
                    <img src="../assets/images/categories/me_va_be.png" alt="Mẹ và bé">
                    <span>Mẹ và bé</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=7">
                    <img src="../assets/images/categories/dich_vu_du_lich.png" alt="Dịch vụ, Du lịch">
                    <span>Dịch vụ, Du lịch</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=8">
                    <img src="../assets/images/categories/viec_lam.png" alt="Việc làm">
                    <span>Việc làm</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=9">
                    <img src="../assets/images/categories/thu_cung.png" alt="Thú cưng">
                    <span>Thú cưng</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=10">
                    <img src="../assets/images/categories/tu_lanh_may_giat.png" alt="Tủ lạnh, máy giặt">
                    <span>Tủ lạnh, máy giặt</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=11">
                    <img src="../assets/images/categories/do_van_phong.png" alt="Đồ dùng văn phòng">
                    <span>Đồ dùng văn phòng</span>
                </a>
            </div>
             <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=12">
                    <img src="../assets/images/categories/thoi_trang.png" alt="Thời trang, Đồ dùng cá nhân">
                    <span>Thời trang, Đồ dùng cá nhân</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=13">
                    <img src="../assets/images/categories/do_an_thuc_pham.png" alt="Đồ ăn, thực phẩm">
                    <span>Đồ ăn, thực phẩm</span>
                </a>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 category-item">
                <a href="category.php?id=14">
                    <img src="../assets/images/categories/dich_vu_cham_soc_nha_cua.png" alt="Dịch vụ chăm sóc nhà cửa">
                    <span>Dịch vụ chăm sóc nhà cửa</span>
                </a>
            </div>
        </div>
        <!-- Kết thúc Phần Khám phá danh mục -->


        <!-- Phần hiển thị lịch sử đơn hàng gần đây -->
        <?php if ($user_id && !empty($recent_orders)): ?>
        <hr style="margin: 40px 0;">
        <div class="row">
            <div class="col-lg-12">
                <h2>Đơn hàng gần đây của bạn</h2>
                <p class="text-muted">Xem các đơn hàng bạn đã đặt gần đây</p>
            </div>
        </div>
        
        <div class="recent-orders-section">
            <?php foreach ($recent_orders as $order): ?>
            <div class="order-summary-card">
                <div class="order-header">
                    <div class="order-number">
                        <strong>Đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>
                    <div class="order-date">
                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </div>
                </div>
                
                <div class="order-status">
                    <span class="badge badge-<?php echo getOrderStatusClass($order['status']); ?>">
                        <?php echo getOrderStatusText($order['status']); ?>
                    </span>
                    <span class="badge badge-<?php echo getPaymentStatusClass($order['payment_status']); ?>">
                        <?php echo getPaymentStatusText($order['payment_status']); ?>
                    </span>
                </div>
                
                <div class="order-total">
                    Tổng tiền: <strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</strong>
                </div>
                
                <div class="order-actions">
                    <a href="user/order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                        Xem chi tiết
                    </a>
                    <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger cancel-order-btn" 
                            data-order-id="<?php echo $order['id']; ?>"
                            data-order-number="<?php echo htmlspecialchars($order['order_number']); ?>">
                        Hủy đơn
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="text-center" style="margin-top: 20px;">
                <a href="user/order_history.php" class="btn btn-outline-primary">
                    Xem tất cả đơn hàng →
                </a>
            </div>
        </div>
        <?php endif; ?>

        <footer class="footer" style="margin-top: 30px;">
            <p>&copy; Web Mua Ban Do Cu <?php echo date('Y')?></p>
        </footer>

    </div> <!-- /container -->

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <script>
    $(document).ready(function() {
        // Hàm tạo và hiển thị toast notification
        function showToast(message, type = 'success') {
            var toastId = 'toast-' + Date.now();
            var iconHtml = type === 'success' ? '✓' : '✗';
            var title = type === 'success' ? 'Thành công' : 'Lỗi';
            
            var toastHtml = `
                <div class="toast ${type === 'error' ? 'error' : ''}" id="${toastId}">
                    <div class="toast-header">
                        <span>${iconHtml} ${title}</span>
                        <button type="button" class="toast-close" onclick="closeToast('${toastId}')">&times;</button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            $('#toast-container').prepend(toastHtml);
            $('#' + toastId).fadeIn(300);
            
            // Tự động ẩn sau 4 giây
            setTimeout(function() {
                closeToast(toastId);
            }, 4000);
        }
        
        // Hàm đóng toast
        window.closeToast = function(toastId) {
            var toast = $('#' + toastId);
            toast.addClass('hiding');
            setTimeout(function() {
                toast.remove();
            }, 300);
        }

        // Script để xử lý thêm vào giỏ hàng bằng AJAX
        $('.form-add-to-cart').on('submit', function(e) {
            e.preventDefault(); // Ngăn chặn submit form truyền thống
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.text();
            
            // Disable button và hiển thị loading
            submitBtn.prop('disabled', true).text('Đang thêm...');
            
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Hiển thị toast thành công
                        showToast(response.message, 'success');
                        // Cập nhật số lượng trên icon giỏ hàng ở header
                        $('#header-cart-count').text(response.item_count);
                        
                        // Reset button
                        submitBtn.prop('disabled', false).text(originalText);
                    } else {
                        // Hiển thị toast lỗi
                        showToast(response.message, 'error');
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    showToast('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.', 'error');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    });
    </script>

</body>
</html>
