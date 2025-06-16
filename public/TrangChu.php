<?php
require_once '../config/config.php';

// Lấy sản phẩm nổi bật từ database
$stmt = $pdo->prepare("
    SELECT p.*, pi.image_path, c.name as category_name 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active' AND p.featured = 1 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

function getConditionText($condition) {
    $conditions = [
        'new' => 'Mới',
        'like_new' => 'Như mới',
        'good' => 'Tốt',
        'fair' => 'Khá tốt',
        'poor' => 'Cần sửa chữa'
    ];
    return $conditions[$condition] ?? $condition;
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'status-pending',
        'success' => 'status-confirmed',
        'failed' => 'status-cancelled'
    ];
    return $badges[$status] ?? 'status-pending';
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'Chờ xử lý',
        'success' => 'Thành công',
        'failed' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Mua Bán Đồ Cũ - Giao Diện Hiện Đại</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3a86ff;
            --primary-dark: #2667cc;
            --secondary: #8338ec;
            --accent: #ff006e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #38b000;
            --danger: #ff595e;
            --warning: #ffca3a;
            --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .logo-icon {
            font-size: 28px;
            color: var(--primary);
        }

        .nav-container {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
        }

        .nav-link {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 8px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(58, 134, 255, 0.1);
            color: var(--primary);
        }

        .cart-link {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent);
            color: white;
            font-size: 11px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-form {
            display: flex;
            background: var(--light);
            border-radius: 30px;
            overflow: hidden;
            width: 300px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .search-input {
            flex: 1;
            border: none;
            padding: 12px 20px;
            background: transparent;
            font-size: 15px;
        }

        .search-input:focus {
            outline: none;
        }

        .search-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-button:hover {
            background: var(--primary-dark);
        }        .user-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-welcome {
            color: var(--dark);
            font-weight: 500;
            white-space: nowrap;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(58, 134, 255, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: rgba(58, 134, 255, 0.1);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: var(--border-radius);
            padding: 80px 60px;
            margin: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .hero h2 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
        }

        .hero-btn {
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-white {
            background: white;
            color: var(--primary);
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-transparent {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            backdrop-filter: blur(5px);
        }

        .btn-transparent:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Section Styles */
        .section {
            margin: 60px 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .view-all:hover {
            gap: 8px;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 220px;
            width: 100%;
            object-fit: cover;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }

        .product-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            font-size: 13px;
            color: var(--gray);
        }

        .product-condition {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-stock {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-actions {
            margin-top: 20px;
        }

        .add-to-cart-form {
            display: flex;
            gap: 10px;
        }

        .quantity-input {
            width: 80px;
            padding: 10px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            text-align: center;
        }

        .btn-add-to-cart {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-add-to-cart:hover {
            background: var(--primary-dark);
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px 15px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .category-card:hover .category-icon {
            color: white;
            transform: scale(1.1);
        }

        .category-icon {
            font-size: 36px;
            margin-bottom: 15px;
            color: var(--primary);
            transition: var(--transition);
        }

        .category-name {
            font-weight: 600;
            font-size: 16px;
        }

        /* Recent Orders */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }

        .order-number {
            font-weight: 600;
            font-size: 18px;
            color: var(--dark);
        }

        .order-date {
            color: var(--gray);
            font-size: 14px;
        }

        .order-status {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(255, 202, 58, 0.2);
            color: #b38f00;
        }

        .status-confirmed {
            background: rgba(56, 176, 0, 0.2);
            color: var(--success);
        }

        .status-delivered {
            background: rgba(58, 134, 255, 0.2);
            color: var(--primary);
        }

        .status-cancelled {
            background: rgba(255, 89, 94, 0.2);
            color: var(--danger);
        }

        .order-total {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 30px;
            margin-top: 60px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 18px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-links a:hover {
            color: white;
            gap: 12px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: #adb5bd;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #adb5bd;
            font-size: 14px;
        }

        /* Toast Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            overflow: hidden;
            animation: slideInRight 0.3s ease;
            max-width: 350px;
            margin-bottom: 10px;
        }
        .toast-success { border-left: 4px solid #38b000; }
        .toast-error { border-left: 4px solid #ff595e; }
        .toast-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .toast-success .toast-header i { color: #38b000; }
        .toast-error .toast-header i { color: #ff595e; }
        .toast-header strong { flex: 1; }
        .close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #6c757d;
        }
        .toast-body { 
            padding: 10px 20px 15px; 
            color: #6c757d; 
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @media (max-width: 992px) {
            .hero {
                padding: 60px 40px;
            }
            
            .hero h2 {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-container {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-form {
                width: 100%;
                max-width: 400px;
            }
            
            .hero {
                padding: 40px 20px;
                text-align: center;
            }
            
            .hero-content {
                margin: 0 auto;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 576px) {
            .nav-menu {
                display: none;
            }
            
            .hero h2 {
                font-size: 28px;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .user-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-recycle logo-icon"></i>
                <h1>MuaBán Đồ Cũ</h1>
            </div>
              <div class="nav-container">
                <nav class="nav-menu">
                    <a href="TrangChu.php" class="nav-link active"><i class="fas fa-home"></i> Trang chủ</a>
                    <a href="cart/" class="nav-link cart-link">
                        <i class="fas fa-shopping-cart"></i> Giỏ hàng 
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="nav-link"><i class="fas fa-store"></i> Đăng bán</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user/order_history.php" class="nav-link"><i class="fas fa-history"></i> Lịch sử</a>
                    <?php endif; ?>
                </nav>
                
                <form class="search-form" method="GET" action="search.php">
                    <input type="text" name="q" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                    <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="user-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="user-welcome">Xin chào, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                        <a href="user/logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    <?php else: ?>
                        <a href="user/login.php" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                        <a href="user/register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <div class="container">
        <div class="hero">
            <div class="hero-content">
                <h2>Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường</h2>
                <p>Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý. Hàng ngàn sản phẩm chất lượng đang chờ bạn!</p>                <div class="hero-buttons">
                    <a href="#featured-products" class="hero-btn btn-white"><i class="fas fa-shopping-bag"></i> Mua sắm ngay</a>
                    <a href="sell.php" class="hero-btn btn-transparent"><i class="fas fa-store"></i> Đăng bán đồ</a>
                </div>
            </div>
        </div>
    </div>    <!-- Featured Products -->
    <div class="container">
        <section class="section" id="featured-products">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="products.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="products-grid">
                <?php if (empty($featured_products)): ?>
                    <div class="no-products" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3>Chưa có sản phẩm nổi bật</h3>
                        <p>Hãy quay lại sau để xem các sản phẩm mới nhất!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image" style="position: relative;">
                            <?php if ($product['featured']): ?>
                                <span class="product-badge">Nổi bật</span>
                            <?php endif; ?>
                            <?php if ($product['image_path']): ?>
                                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" 
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
                                    <i class="fas fa-box"></i> Còn <?php echo $product['stock_quantity']; ?> sản phẩm
                                </div>
                            </div>
                            <div class="product-actions">
                                <form class="add-to-cart-form" onsubmit="addToCart(event, <?php echo $product['id']; ?>)">
                                    <input type="number" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1" class="quantity-input" name="quantity">
                                    <button type="submit" class="btn-add-to-cart">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
          <!-- Categories Section -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Danh mục sản phẩm</h2>
                <a href="categories.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
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
                <div class="category-card" onclick="window.location.href='category.php?slug=<?php echo $category['slug']; ?>'">
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
                <a href="user/order_history.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
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
                        <a href="user/order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">
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
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Giới thiệu</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Sản phẩm</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Đăng bán</a></li>
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
                            <span>123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>0987.654.321</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>hotro@muabandocu.vn</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Thứ 2 - Chủ nhật: 8:00 - 22:00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2023 MuaBán Đồ Cũ. Tất cả quyền được bảo lưu.
            </div>
        </div>
    </footer>    <script>
        // Add to cart function
        function addToCart(event, productId) {
            event.preventDefault();
            
            const form = event.target;
            const quantity = form.querySelector('.quantity-input').value;
            const button = form.querySelector('.btn-add-to-cart');
            
            // Disable button during request
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
              // Send AJAX request
            fetch('../modules/cart/handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=add&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    } else if (data.cart_count > 0) {
                        // Create cart count element if it doesn't exist
                        const cartLink = document.querySelector('.cart-link');
                        const span = document.createElement('span');
                        span.className = 'cart-count';
                        span.textContent = data.cart_count;
                        cartLink.appendChild(span);
                    }
                    
                    showToast('success', 'Thêm vào giỏ hàng thành công!', 'Sản phẩm đã được thêm vào giỏ hàng của bạn.');
                } else {
                    showToast('error', 'Lỗi!', data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Lỗi!', 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
            })
            .finally(() => {
                // Re-enable button
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-cart-plus"></i> Thêm vào giỏ';
            });
        }
          // Show toast notification
        function showToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div class="toast-header">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <strong>${title}</strong>
                    <button type="button" class="close" onclick="this.parentElement.parentElement.remove()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">${message}</div>
            `;
            
            document.body.appendChild(toast);
            
            // Remove toast after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 5000);
        }
        
        // Cancel order function
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                fetch('../modules/order/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', 'Thành công!', 'Đơn hàng đã được hủy.');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('error', 'Lỗi!', data.message || 'Không thể hủy đơn hàng.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Lỗi!', 'Đã xảy ra lỗi khi hủy đơn hàng.');
                });
            }
        }
        
        // Reorder function
        function reorder(orderId) {
            if (confirm('Bạn có muốn mua lại các sản phẩm trong đơn hàng này?')) {
                fetch('../modules/order/reorder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', 'Thành công!', 'Sản phẩm đã được thêm vào giỏ hàng.');
                        // Update cart count
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }
                    } else {
                        showToast('error', 'Lỗi!', data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Lỗi!', 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
                });
            }
        }

        // Category cards animation
        document.addEventListener('DOMContentLoaded', function() {
            const categoryCards = document.querySelectorAll('.category-card');
            categoryCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>