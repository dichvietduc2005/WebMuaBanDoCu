<?php
// Sử dụng bootstrap để tăng hiệu suất tải trang
require_once __DIR__ . '/../../config/bootstrap.php';

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
    $regular_products = [];
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
$unread_notifications = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_notifications = (int)$stmt->fetchColumn();
}

// Gọi header và footer từ components
require_once __DIR__ . '/../Components/header/Header.php';
require_once __DIR__ . '/../Components/footer/Footer.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/index.css">
</head>

<body>
    <?php renderHeader($pdo); ?>

    <div class="container">
        <div class="hero">
            <div class="hero-content">
                <h2>Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường</h2>
                <p>Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý. Hàng ngàn sản
                    phẩm chất lượng đang chờ bạn!</p>
                <div class="hero-buttons">
                    <a href="#featured-products" class="hero-btn btn-white"><i class="fas fa-shopping-bag"></i> Mua sắm
                        ngay</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>app/View/product/sell.php" class="hero-btn btn-transparent"><i
                            class="fas fa-store"></i> Đăng bán đồ</a>
                    <?php else: ?>
                    <button type="button" class="hero-btn btn-transparent" id="openClerkAuth"><i
                            class="fas fa-sign-in-alt"></i> Đăng nhập / Đăng ký</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="clerk-auth-container"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;">
        <div id="sign-in-widget-container" style="background: white; padding: 20px; border-radius: 8px;"></div>
    </div>


    <div class="container">
        <section class="section" id="featured-products">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="<?php echo BASE_URL; ?>app/View/product/products.php" class="view-all">Xem tất cả <i
                        class="fas fa-arrow-right"></i></a>
            </div>

            <div class="products-grid">
                <?php if (empty($featured_products)): ?>
                <div class="no-products"
                    style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                    <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>Chưa có sản phẩm nổi bật</h3>
                    <p>Hãy quay lại sau để xem các sản phẩm mới nhất!</p>
                </div> <?php else: ?> <?php foreach ($featured_products as $product): ?>
                <div class="product-card" style="cursor: pointer;"
                    onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                    <div class="product-image" style="position: relative;">
                        <?php if ($product['featured']): ?>
                        <span class="product-badge">Nổi bật</span>
                        <?php endif; ?>
                        <?php if ($product['image_path']): ?>
                        <img src="<?php echo BASE_URL . htmlspecialchars($product['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>"
                            style="width: 100%; height: 220px; object-fit: cover;">
                        <?php else: ?>
                        <div
                            style="width: 100%; height: 220px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            <i class="fas fa-image" style="font-size: 48px;"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                        <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                        <div class="product-meta">
                            <div class="product-condition">
                                <i class="fas fa-star"></i>
                                <?php echo getConditionText($product['condition_status']); ?>
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
                                <input type="number" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1"
                                    class="quantity-input" name="quantity">
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

        <section class="section" id="regular-products">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm mới nhất</h2>
                <a href="<?php echo BASE_URL; ?>app/View/product/products.php" class="view-all">Xem tất cả <i
                        class="fas fa-arrow-right"></i></a>
            </div>

            <div class="products-grid">
                <?php if (empty($regular_products)): ?>
                <div class="no-products"
                    style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                    <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>Chưa có sản phẩm</h3>
                    <p>Hãy quay lại sau để xem các sản phẩm mới nhất!</p>
                </div> <?php else: ?>
                <?php foreach ($regular_products as $product): ?>
                <div class="product-card" style="cursor: pointer;"
                    onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                    <div class="product-image" style="position: relative;">
                        <?php if ($product['image_path']): ?>

                        <img src="/WebMuaBanDoCu/public/<?php echo htmlspecialchars($product['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>"
                            style="width: 100%; height: 220px; object-fit: cover;">
                        <?php else: ?>
                        <div
                            style="width: 100%; height: 220px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            <i class="fas fa-image" style="font-size: 48px;"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                        <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                        <div class="product-meta">
                            <div class="product-condition">
                                <i class="fas fa-star"></i>
                                <?php echo getConditionText($product['condition_status']); ?>
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
                                <input type="number" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1"
                                    class="quantity-input" name="quantity">
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
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Danh mục sản phẩm</h2>
                <a href="<?php echo BASE_URL; ?>app/View/product/categories.php" class="view-all">Xem tất cả <i
                        class="fas fa-arrow-right"></i></a>
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
                <div class="category-card"
                    onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/category.php?slug=<?php echo $category['slug']; ?>'">
                    <i class="<?php echo $icon; ?> category-icon"></i>
                    <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php if (isset($_SESSION['user_id']) && !empty($recent_orders)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Đơn hàng gần đây</h2>
                <a href="<?php echo BASE_URL; ?>app/View/order/order_history.php" class="view-all">Xem tất cả <i
                        class="fas fa-arrow-right"></i></a>
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
                        <a href="<?php echo BASE_URL; ?>app/View/order/order_details.php?id=<?php echo $order['id']; ?>"
                            class="btn btn-outline btn-sm">
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

    <?php renderFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>
    <script>
    // Mã JavaScript xử lý thêm vào giỏ hàng
    function addToCart(event, productId) {
        event.preventDefault();
        const form = event.target;
        const quantity = form.querySelector('.quantity-input').value;

        $.ajax({
            url: '<?php echo BASE_URL; ?>app/Controllers/cart/CartController.php?action=add',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
                location.reload();
            },
            error: function() {
                alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng!');
            }
        });
    }
    </script>
</body>

</html>