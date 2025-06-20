<?php
require_once __DIR__ . '/../../config/config.php';
require_once "../../app/Controllers/extra/ExtraController.php";
// Helper functions
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}

if (!function_exists('getConditionText')) {
    function getConditionText($condition) {
        $conditions = [
            'new' => 'Mới',
            'like_new' => 'Như mới',
            'good' => 'Tốt',
            'fair' => 'Khá',
            'poor' => 'Cũ'
        ];
        return $conditions[$condition] ?? 'Không xác định';
    }
}

if (!function_exists('getStatusText')) {
    function getStatusText($status) {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy',
            'success' => 'Thành công'
        ];
        return $statuses[$status] ?? 'Không xác định';
    }
}

if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        $badges = [
            'pending' => 'badge-warning',
            'confirmed' => 'badge-info',
            'shipping' => 'badge-primary',
            'delivered' => 'badge-success',
            'cancelled' => 'badge-danger',
            'success' => 'badge-success',
            'paid' => 'badge-success',
            'failed' => 'badge-danger'
        ];
        return $badges[$status] ?? 'badge-secondary';
    }
}

// Debug: Kiểm tra kết nối database
if (!isset($pdo)) {
    die("Database connection not found!");
}

try {
    // Lấy sản phẩm nổi bật từ database
    $stmt = $pdo->prepare("
        SELECT p.*, pi.image_path, c.name as category_name 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active' AND p.featured = 1 AND p.stock_quantity > 0
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: In ra số lượng sản phẩm
    error_log("Featured products count: " . count($featured_products));

    // Lấy danh mục
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: In ra số lượng danh mục
    error_log("Categories count: " . count($categories));

    // Lấy sản phẩm thường (không nổi bật)
    $stmt = $pdo->prepare("
        SELECT p.*, pi.image_path, c.name as category_name 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active' AND p.featured = 0 AND p.stock_quantity > 0
        ORDER BY p.created_at DESC 
        LIMIT 12
    ");
    $stmt->execute();
    $regular_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: In ra số lượng sản phẩm thường
    error_log("Regular products count: " . count($regular_products));

} catch (PDOException $e) {
    error_log("Database error in Home.php: " . $e->getMessage());
    $featured_products = [];
    $categories = [];
}

// Lấy đơn hàng gần đây (nếu user đã đăng nhập)
$recent_orders = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.buyer_id = ? 
        GROUP BY o.id
        ORDER BY o.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Đếm số sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT SUM(ci.quantity) as total_quantity
        FROM carts c 
        JOIN cart_items ci ON c.id = ci.cart_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cart_count = $result['total_quantity'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Mua Bán Đồ Cũ</title>    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/index.css">
<body>    <!-- Header với layout giống chợTỐT -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center w-100">
                <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center me-4" href="TrangChu.php">
                    <i class="fas fa-recycle text-primary me-2" style="font-size: 28px;"></i>
                    <h1 class="mb-0 fw-bold text-gradient" style="font-size: 24px;">Mua Bán đồ cũ</h1>
                </a>

                <!-- Categories Dropdown -->
                <div class="dropdown me-3">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bars me-2"></i>
                        <span>Danh mục</span>
                    </button>
                    <ul class="dropdown-menu">                        <?php foreach ($categories as $category): ?>
                        <li><a class="dropdown-item" href="product/category.php?slug=<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Location Dropdown -->                <!-- Search Form - Expanded -->
                <form id="search-form" class="flex-grow-1 me-4" method="GET" action="extra/search.php" style="max-width: 500px;">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="search-input" name="keyword" 
                               placeholder="Tìm kiếm sản phẩm" 
                               value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Right side actions -->
                <div class="d-flex align-items-center gap-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Notifications -->
                        <button class="btn btn-link text-dark p-2" title="Thông báo">
                            <i class="fas fa-bell" style="font-size: 20px;"></i>
                        </button>

                        <!-- Messages -->
                        <button class="btn btn-link text-dark p-2" title="Tin nhắn">
                            <i class="fas fa-comment" style="font-size: 20px;"></i>
                        </button>                        <!-- Cart -->
                        <a href="cart/index.php" class="btn btn-link text-dark p-2 position-relative" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>

                        <!-- Account Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i>
                                <span>Quản lý tin</span>
                            </button>                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="product/Product.php"><i class="fas fa-box me-2"></i>Tin đăng của tôi</a></li>
                                <li><a class="dropdown-item" href="order/order_history.php"><i class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>

                        <!-- Account Info Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i>
                                <span>Tài khoản</span>
                            </button>                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>                        <!-- Post Ad Button -->                        <a href="product/sell.php" class="btn btn-warning fw-bold px-4 py-2">
                            <i class="fas fa-plus me-2"></i>ĐĂNG TIN
                        </a>

                    <?php else: ?>
                        <!-- Guest user buttons -->                        <a href="user/login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </a>
                        <a href="user/register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký
                        </a>                        <a href="product/sell.php" class="btn btn-warning fw-bold px-4">
                            <i class="fas fa-plus me-2"></i>ĐĂNG TIN
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container">
        <div class="hero">
            <div class="hero-content">
                <h2>Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường</h2>
                <p>Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý. Hàng ngàn sản phẩm chất lượng đang chờ bạn!</p>                <div class="hero-buttons">
                    <a href="#featured-products" class="hero-btn btn-white"><i class="fas fa-shopping-bag"></i> Mua sắm ngay</a>
                    <a href="product/sell.php" class="hero-btn btn-transparent"><i class="fas fa-store"></i> Đăng bán đồ</a>
                </div>
            </div>
        </div>
    </div>    <!-- Featured Products -->
    <div class="container">
        <section class="section" id="featured-products">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                        <a href="product/products.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="products-grid">
                <?php if (empty($featured_products)): ?>
                    <div class="no-products" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3>Chưa có sản phẩm nổi bật</h3>
                        <p>Hãy quay lại sau để xem các sản phẩm mới nhất!</p>
                    </div>                <?php else: ?>                    <?php foreach ($featured_products as $product): ?>
                    <div class="product-card" style="cursor: pointer;" onclick="window.location.href='product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                        <div class="product-image" style="position: relative;">
                            <?php if ($product['featured']): ?>
                                <span class="product-badge">Nổi bật</span>
                            <?php endif; ?>
                            <?php if ($product['image_path']): ?>
                                <img src="../../public/<?php echo htmlspecialchars($product['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                                     style="width: 100%; height: 220px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 220px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                    <i class="fas fa-image" style="font-size: 48px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                            <div class="product-meta">
                                <div class="product-condition">
                                    <i class="fas fa-star"></i> <?php echo getConditionText($product['condition_status']); ?>
                                </div>
                                <div class="product-stock">
                                    <i class="fas fa-box"></i> 
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        Còn <?php echo $product['stock_quantity']; ?> sản phẩm
                                    <?php else: ?>
                                        <span class="text-danger fw-bold">Hết hàng</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="product-actions" onclick="event.stopPropagation();">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <form class="add-to-cart-form" onsubmit="addToCart(event, <?php echo $product['id']; ?>)">
                                        <input type="number" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1" class="quantity-input" name="quantity">
                                        <button type="submit" class="btn-add-to-cart">
                                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn-add-to-cart btn-disabled" disabled>
                                        <i class="fas fa-ban"></i> Hết hàng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Regular Products -->
        <section class="section" id="regular-products">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm mới nhất</h2>
                <a href="product/products.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="products-grid">
                <?php if (empty($regular_products)): ?>
                    <div class="no-products" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3>Chưa có sản phẩm</h3>
                        <p>Hãy quay lại sau để xem các sản phẩm mới nhất!</p>
                    </div>                <?php else: ?>
                    <?php foreach ($regular_products as $product): ?>
                    <div class="product-card" style="cursor: pointer;" onclick="window.location.href='product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                        <div class="product-image" style="position: relative;">
                            <?php if ($product['image_path']): ?>
                                <img src="../../public/<?php echo htmlspecialchars($product['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                                     style="width: 100%; height: 220px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 220px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                    <i class="fas fa-image" style="font-size: 48px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                            <div class="product-meta">
                                <div class="product-condition">
                                    <i class="fas fa-star"></i> <?php echo getConditionText($product['condition_status']); ?>
                                </div>
                                <div class="product-stock">
                                    <i class="fas fa-box"></i> 
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        Còn <?php echo $product['stock_quantity']; ?> sản phẩm
                                    <?php else: ?>
                                        <span class="text-danger fw-bold">Hết hàng</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="product-actions" onclick="event.stopPropagation();">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <form class="add-to-cart-form" onsubmit="addToCart(event, <?php echo $product['id']; ?>)">
                                        <input type="number" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1" class="quantity-input" name="quantity">
                                        <button type="submit" class="btn-add-to-cart">
                                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn-add-to-cart btn-disabled" disabled>
                                        <i class="fas fa-ban"></i> Hết hàng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>          <!-- Categories Section -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Danh mục sản phẩm</h2>
                <a href="product/categories.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="categories-grid">
                <?php 
                $category_icons = [
                    'dien-thoai-may-tinh-bang' => 'fas fa-mobile-alt',
                    'laptop-may-tinh' => 'fas fa-laptop',
                    'thoi-trang-phu-kien' => 'fas fa-tshirt',
                    'do-gia-dung-noi-that' => 'fas fa-home',
                    'xe-co-phuong-tien' => 'fas fa-motorcycle',
                    'sach-van-phong-pham' => 'fas fa-book',
                    'the-thao-giai-tri' => 'fas fa-gamepad',
                    'dien-may-cong-nghe' => 'fas fa-tv',
                    'me-va-be' => 'fas fa-baby'
                ];
                
                foreach ($categories as $category): 
                    $icon = $category_icons[$category['slug']] ?? 'fas fa-cube';
                ?>
                <div class="category-card" onclick="window.location.href='product/category.php?slug=<?php echo $category['slug']; ?>'">
                    <i class="<?php echo $icon; ?> category-icon"></i>
                    <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
          <!-- Recent Orders -->
        <?php if (isset($_SESSION['user_id']) && !empty($recent_orders)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Đơn hàng gần đây</h2>
                <a href="order/order_history.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="orders-grid">
                <?php foreach ($recent_orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                        <div class="order-date"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    
                    <div class="order-status">
                        <div class="status-badge <?php echo getStatusBadge($order['status']); ?>">
                            <?php echo getStatusText($order['status']); ?>
                        </div>
                        <?php if ($order['payment_status']): ?>
                        <div class="status-badge <?php echo getStatusBadge($order['payment_status']); ?>">
                            <?php 
                            $payment_statuses = [
                                'pending' => 'Chờ thanh toán',
                                'paid' => 'Đã thanh toán',
                                'failed' => 'Thanh toán thất bại'
                            ];
                            echo $payment_statuses[$order['payment_status']] ?? $order['payment_status'];
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-total">
                        Tổng tiền: <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                        <br><small><?php echo $order['item_count']; ?> sản phẩm</small>
                    </div>
                    
                    <div class="order-actions">
                        <a href="order/order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                        <?php if ($order['status'] == 'pending'): ?>
                        <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="btn btn-outline btn-sm">
                            <i class="fas fa-times"></i> Hủy đơn
                        </button>
                        <?php elseif ($order['status'] == 'success'): ?>
                        <button onclick="reorder(<?php echo $order['id']; ?>)" class="btn btn-outline btn-sm">
                            <i class="fas fa-redo"></i> Mua lại
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Về chúng tôi</h3>
                    <p>MuaBán Đồ Cũ là nền tảng kết nối người mua và người bán đồ đã qua sử dụng uy tín, chất lượng hàng đầu Việt Nam.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Liên kết nhanh</h3>
                    <ul class="footer-links">
                        <li><a href="TrangChu.php"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>                        <li><a href="#"><i class="fas fa-chevron-right"></i> Giới thiệu</a></li>                        <li><a href="product/products.php"><i class="fas fa-chevron-right"></i> Sản phẩm</a></li>
                        <li><a href="product/sell.php"><i class="fas fa-chevron-right"></i> Đăng bán</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Danh mục</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Điện thoại</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Laptop</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Máy tính bảng</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Máy ảnh</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Phụ kiện</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Liên hệ</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Phường 12, Hồ Chí Minh</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>0945554902</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>nguyenthinhk52005@gmail.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Thứ 2 - Chủ nhật: 8:00 - 22:00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2025 Mua Bán Đồ Cũ. Tất cả quyền được bảo lưu.
            </div>
        </div>    </footer>
    <!-- jQuery phải load trước các script khác -->    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../public/assets/js/main.js"></script>
    <script src="../../public/assets/js/search.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>