<?php
// app/View/Home.php
// View chỉ hiển thị dữ liệu được truyền từ Controller

// Safety check: Ensure config is loaded if accessed directly or via legacy redirects
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../config/config.php';
}

// Các biến: $featured_products, $regular_products, $categories, $recent_orders, $cart_count, $unread_notifications

// Ensure variables are defined BEFORE including Header to prevent data loss
$featured_products = $featured_products ?? [];
$regular_products = $regular_products ?? [];
$categories = $categories ?? [];
$recent_orders = $recent_orders ?? [];
$cart_count = $cart_count ?? 0;
$unread_notifications = $unread_notifications ?? 0;

// Gọi header và footer từ components - AFTER defining variables
require_once __DIR__ . '/../Components/header/Header.php';
require_once __DIR__ . '/../Components/footer/Footer.php';

// Note: Logic for fetching these variables must be in HomeController only. View should not fetch data.
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
    <!-- Load Tailwind (via admin-style.css) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/admin-style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/index.css">
    <!-- Mobile & Tablet Responsive Enhancement - MUST LOAD AFTER header CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-responsive-enhanced.css">
    <!-- Unified Product Card Styles (Home & Search) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/unified-product-cards.css?v=<?php echo time(); ?>">
    <!-- Product Card Compact Optimization (Disabled to allow Woodmart design) -->
    <!-- <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/product-card-compact.css"> -->
    <!-- Home Page Improvements - Hero & Product Cards -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/home-improvements.css">
    <!-- Recent Orders Enhanced Styling -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/recent-orders-enhanced.css">
    <!-- Chat Widget Modern Styles -->

    
    <?php
    // Render frontend theme styles from admin theme customization
    require_once __DIR__ . '/../Components/frontend/FrontendThemeRenderer.php';
    $frontendTheme = new FrontendThemeRenderer();
    $frontendTheme->renderThemeStyles();
    ?>

</head>

<body>
    <?php 
    global $pdo;
    if (!isset($pdo)) {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
    }
    // Ensure categories is an array and not empty
    $categories = is_array($categories) ? $categories : [];
    renderHeader($pdo, $categories, $cart_count, $unread_notifications); 
    ?>

    <?php 
    // Render banner slider if enabled in admin theme customization
    echo $frontendTheme->renderBannerSlider();
    
    // Get dynamic hero content from theme settings
    $heroContent = $frontendTheme->getHeroContent();
    ?>
    <div class="container">
        <div class="hero">
            <div class="hero-content">
                <h2><?= htmlspecialchars($heroContent['title']) ?></h2>
                <p><?= htmlspecialchars($heroContent['subtitle']) ?></p>
                <div class="hero-buttons">
                    <a href="#featured-products" class="hero-btn btn-white"><i class="fas fa-shopping-bag"></i> <?= htmlspecialchars($heroContent['button1_text']) ?></a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>app/View/product/sell.php" class="hero-btn btn-transparent"><i
                                class="fas fa-store"></i> <?= htmlspecialchars($heroContent['button2_text']) ?></a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=login" class="hero-btn btn-transparent"><i
                                class="fas fa-sign-in-alt"></i> Đăng nhập / Đăng ký</a>
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
                <!-- <a href="<?php echo BASE_URL; ?>public/index.php?page=products" class="view-all">Xem tất cả <i
                        class="fas fa-arrow-right"></i></a> -->
            </div>

            <div class="products-grid">
                <?php if (empty($featured_products)): ?>
                    <div class="no-products"
                        style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3>Chưa có sản phẩm nổi bật</h3>
                        <p>Hãy quay lại sau để xem các sản phẩm mới nhất!</p>
                    </div> <?php else: ?> <?php foreach ($featured_products as $product): ?>
                        <div class="product-card"
                            onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                            <div class="product-image">
                                <?php if ($product['featured']): ?>
                                    <span class="product-badge">Nổi bật</span>
                                <?php endif; ?>
                                
                                <?php if ($product['image_path']): ?>
                                    <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-content">
                                <div class="product-specs">
                                    <?php if (!empty($product['category_name'])): ?>
                                        <span class="spec-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <?php endif; ?>
                                    <span class="spec-tag"><?php echo getConditionText($product['condition_status']); ?></span>
                                </div>
                                
                                <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                
                                <div class="product-price-section">
                                    <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                        <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                        <span class="discount-percent">-<?php echo round((1 - $product['price']/$product['original_price']) * 100); ?>%</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-meta" style="display: flex; justify-content: space-between; font-size: 11px; color: #9ca3af; margin-bottom: 8px;">
                                    <span class="location"><i class="fas fa-location-dot me-1"></i><?php echo htmlspecialchars($product['location'] ?? 'Toàn quốc'); ?></span>
                                    <span class="time"><i class="far fa-clock me-1"></i><?php echo isset($product['created_at']) ? date('d/m/Y', strtotime($product['created_at'])) : 'Vừa xong'; ?></span>
                                </div>
                                
                                <div class="product-footer">
                                    <div class="product-rating">
                                        <span class="stars">
                                            <i class="fas fa-star"></i>
                                            <?php echo isset($product['rating']) && $product['rating'] > 0 ? number_format($product['rating'], 1) : '4.5'; ?>
                                        </span>
                                        <span class="separator">•</span>
                                        <span class="sales">Đã bán <?php echo isset($product['sales_count']) && $product['sales_count'] > 0 ? number_format($product['sales_count']) : '0'; ?></span>
                                    </div>
                                    
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <button type="button" class="btn-add-cart-footer" onclick="event.stopPropagation(); addToCart(event, <?php echo $product['id']; ?>)" title="Thêm vào giỏ">
                                        <i class="fas fa-cart-plus"></i>
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
                <!-- <a href="<?php echo BASE_URL; ?>public/index.php?page=products" class="view-all">Xem tất cả <i
                        class="fas fa-arrow-right"></i></a> -->
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
                        <div class="product-card"
                            onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                            <div class="product-image">
                                <?php if ($product['image_path']): ?>
                                    <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-content">
                                <div class="product-specs">
                                    <?php if (!empty($product['category_name'])): ?>
                                        <span class="spec-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <?php endif; ?>
                                    <span class="spec-tag"><?php echo getConditionText($product['condition_status']); ?></span>
                                </div>
                                
                                <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                
                                <div class="product-price-section">
                                    <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                        <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                        <span class="discount-percent">-<?php echo round((1 - $product['price']/$product['original_price']) * 100); ?>%</span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-meta" style="display: flex; justify-content: space-between; font-size: 11px; color: #9ca3af; margin-bottom: 8px;">
                                    <span class="location"><i class="fas fa-location-dot me-1"></i><?php echo htmlspecialchars($product['location'] ?? 'Toàn quốc'); ?></span>
                                    <span class="time"><i class="far fa-clock me-1"></i><?php echo isset($product['created_at']) ? ViewHelper::truncate(date('d/m/Y', strtotime($product['created_at'])), 10) : 'Vừa xong'; ?></span>
                                </div>
                                
                                <div class="product-footer">
                                    <div class="product-rating">
                                        <span class="stars">
                                            <i class="fas fa-star"></i>
                                            <?php echo isset($product['rating']) && $product['rating'] > 0 ? number_format($product['rating'], 1) : '5.0'; ?>
                                        </span>
                                        <span class="separator">•</span>
                                        <span class="sales">Đã bán <?php echo isset($product['sales_count']) && $product['sales_count'] > 0 ? number_format($product['sales_count']) : rand(0, 50); ?></span>
                                    </div>
                                    
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <button type="button" class="btn-add-cart-footer" onclick="event.stopPropagation(); addToCart(event, <?php echo $product['id']; ?>)" title="Thêm vào giỏ">
                                        <i class="fas fa-cart-plus"></i>
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
                <!-- <a href="<?php echo BASE_URL; ?>app/View/product/categories.php" class="view-all">Xem tất cả <i
                        class="fas fa-arrow-right"></i></a> -->
            </div>

            <div class="categories-grid">
                <?php
                // Map slug -> FontAwesome icon (template style - simplified)
                $category_styles = [
                    'am-nhac-nhac-cu' => ['icon' => 'fas fa-music'],
                    'dien-thoai-may-tinh-bang' => ['icon' => 'fas fa-mobile-alt'],
                    'laptop-may-tinh' => ['icon' => 'fas fa-laptop'],
                    'thoi-trang-phu-kien' => ['icon' => 'fas fa-bag-shopping'],
                    'do-gia-dung-noi-that' => ['icon' => 'fas fa-chair'],
                    'xe-co-phuong-tien' => ['icon' => 'fas fa-car'],
                    'sach-van-phong-pham' => ['icon' => 'fas fa-book'],
                    'the-thao-giai-tri' => ['icon' => 'fas fa-dumbbell'],
                    'dien-may-cong-nghe' => ['icon' => 'fas fa-tv'],
                    'me-va-be' => ['icon' => 'fas fa-baby-carriage'],
                    'suc-khoe-lam-dep' => ['icon' => 'fas fa-spa'],
                    'thu-cung-phu-kien' => ['icon' => 'fas fa-paw'],
                    'am-thuc' => ['icon' => 'fas fa-burger']
                ];

                foreach ($categories as $category):
                    $style = $category_styles[$category['slug']] ?? ['icon' => 'fas fa-box'];
                ?>
                    <div class="category-card"
                        onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/category.php?slug=<?php echo $category['slug']; ?>'">
                        <div class="category-icon-box">
                            <i class="<?php echo $style['icon']; ?>"></i>
                        </div>
                        <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <!-- <?php if (isset($_SESSION['user_id']) && !empty($recent_orders)): ?>
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
                                <div class="order-number">
                                    <i class="fas fa-hashtag"></i><?php echo htmlspecialchars($order['order_number']); ?>
                                </div>
                                <div class="order-date">
                                    <i class="fas fa-calendar-alt"></i><?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                </div>
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
                                <strong <?php if (strlen(formatPrice($order['total_amount'])) > 20) echo 'class="long-price"'; ?>>
                                    <i class="fas fa-wallet"></i><?php echo formatPrice($order['total_amount']); ?>
                                </strong>
                                <small>
                                    <i class="fas fa-box"></i><?php echo $order['item_count']; ?> sản phẩm
                                </small>
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
                                
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?> -->
    </div>

    <?php require_once __DIR__ . '/components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/components/header.js"></script>

</body>

</html>