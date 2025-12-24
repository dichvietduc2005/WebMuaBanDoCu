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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/unified-product-cards.css">
    <!-- Product Card Compact Optimization -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/product-card-compact.css">
    <!-- Home Page Improvements - Hero & Product Cards -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/home-improvements.css">
    
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
                <a href="<?php echo BASE_URL; ?>public/index.php?page=products" class="view-all">Xem tất cả <i
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
                        <div class="product-card"
                            onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                            <div class="product-image">
                                <?php if ($product['featured']): ?>
                                    <span class="product-badge">Nổi bật</span>
                                <?php endif; ?>
                                
                                <!-- Cart icon on right -->
                                <?php if ($product['stock_quantity'] > 0): ?>
                                <button type="button" class="cart-icon-btn" onclick="event.stopPropagation(); addToCart(event, <?php echo $product['id']; ?>)" title="Thêm vào giỏ">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
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
                                <!-- Spec Tags (Category + Condition) -->
                                <div class="product-specs">
                                    <?php if (!empty($product['category_name'])): ?>
                                        <span class="spec-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <?php endif; ?>
                                    <span class="spec-tag"><?php echo getConditionText($product['condition_status']); ?></span>
                                </div>
                                
                                <!-- Product Title (Blue) -->
                                <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                
                                <!-- Price Section -->
                                <div class="product-price-section">
                                    <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                        <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                        <span class="discount-percent">-<?php echo round((1 - $product['price']/$product['original_price']) * 100); ?>%</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Rating & Sales -->
                                <div class="product-rating">
                                    <span class="stars">
                                        <i class="fas fa-star"></i>
                                        <?php echo isset($product['rating']) ? number_format($product['rating'], 1) : '5.0'; ?>
                                    </span>
                                    <span class="separator">•</span>
                                    <span class="sales">Đã bán <?php echo isset($product['sales_count']) ? number_format($product['sales_count']) : rand(10, 500); ?></span>
                                </div>
                            </div>
                            
                            <!-- Quick Add Button (appears on hover) -->
                            <div class="product-hover-action" onclick="event.stopPropagation();">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <button type="button" class="btn-quick-add" onclick="addToCart(event, <?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                    </button>
                                <?php else: ?>
                                    <button class="btn-quick-add disabled" disabled>
                                        <i class="fas fa-ban"></i> Hết hàng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="regular-products">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm mới nhất</h2>
                <a href="<?php echo BASE_URL; ?>public/index.php?page=products" class="view-all">Xem tất cả <i
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
                        <div class="product-card"
                            onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                            <div class="product-image">
                                <!-- Cart icon on right -->
                                <?php if ($product['stock_quantity'] > 0): ?>
                                <button type="button" class="cart-icon-btn" onclick="event.stopPropagation(); addToCart(event, <?php echo $product['id']; ?>)" title="Thêm vào giỏ">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
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
                                <!-- Spec Tags (Category + Condition) -->
                                <div class="product-specs">
                                    <?php if (!empty($product['category_name'])): ?>
                                        <span class="spec-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <?php endif; ?>
                                    <span class="spec-tag"><?php echo getConditionText($product['condition_status']); ?></span>
                                </div>
                                
                                <!-- Product Title (Blue) -->
                                <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                
                                <!-- Price Section -->
                                <div class="product-price-section">
                                    <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                        <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                        <span class="discount-percent">-<?php echo round((1 - $product['price']/$product['original_price']) * 100); ?>%</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Rating & Sales -->
                                <div class="product-rating">
                                    <span class="stars">
                                        <i class="fas fa-star"></i>
                                        <?php echo isset($product['rating']) ? number_format($product['rating'], 1) : '5.0'; ?>
                                    </span>
                                    <span class="separator">•</span>
                                    <span class="sales">Đã bán <?php echo isset($product['sales_count']) ? number_format($product['sales_count']) : rand(10, 500); ?></span>
                                </div>
                            </div>
                            
                            <!-- Quick Add Button (appears on hover) -->
                            <div class="product-hover-action" onclick="event.stopPropagation();">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <button type="button" class="btn-quick-add" onclick="addToCart(event, <?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                    </button>
                                <?php else: ?>
                                    <button class="btn-quick-add disabled" disabled>
                                        <i class="fas fa-ban"></i> Hết hàng
                                    </button>
                                <?php endif; ?>
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
                // Map slug -> FontAwesome icon
                $icon_map = [
                    'am-nhac-nhac-cu' => 'fas fa-music',
                    'dien-thoai-may-tinh-bang' => 'fas fa-mobile-alt',
                    'laptop-may-tinh' => 'fas fa-laptop',
                    'thoi-trang-phu-kien' => 'fas fa-tshirt',
                    'do-gia-dung-noi-that' => 'fas fa-home',
                    'xe-co-phuong-tien' => 'fas fa-motorcycle',
                    'sach-van-phong-pham' => 'fas fa-book',
                    'the-thao-giai-tri' => 'fas fa-football-ball',
                    'dien-may-cong-nghe' => 'fas fa-tv',
                    'me-va-be' => 'fas fa-baby',
                    'suc-khoe-lam-dep' => 'fas fa-heart',
                    'thu-cung-phu-kien' => 'fas fa-paw',
                    'am-thuc' => 'fas fa-utensils'
                ];

                foreach ($categories as $category):
                    $icon = $icon_map[$category['slug']] ?? 'fas fa-cube';
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
                                
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <?php renderFooter(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/components/header.js"></script>
</body>

</html>