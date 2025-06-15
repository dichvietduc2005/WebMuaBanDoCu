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
        }

        .user-actions {
            display: flex;
            gap: 15px;
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

        /* Responsive Design */
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
                    <a href="#" class="nav-link active"><i class="fas fa-home"></i> Trang chủ</a>
                    <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> Giỏ hàng <span class="cart-count">3</span></a>
                    <a href="#" class="nav-link"><i class="fas fa-store"></i> Đăng bán</a>
                    <a href="#" class="nav-link"><i class="fas fa-history"></i> Lịch sử</a>
                </nav>
                
                <form class="search-form">
                    <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                    <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="user-actions">
                    <a href="#" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                    <a href="#" class="btn btn-primary"><i class="fas fa-user-plus"></i> Đăng ký</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <div class="container">
        <div class="hero">
            <div class="hero-content">
                <h2>Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường</h2>
                <p>Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý. Hàng ngàn sản phẩm chất lượng đang chờ bạn!</p>
                <div class="hero-buttons">
                    <a href="#" class="hero-btn btn-white"><i class="fas fa-shopping-bag"></i> Mua sắm ngay</a>
                    <a href="#" class="hero-btn btn-transparent"><i class="fas fa-store"></i> Đăng bán đồ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="#" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="products-grid">
                <!-- Product 1 -->
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge">Mới</span>
                        <img src="https://via.placeholder.com/300x220/e9ecef/6c757d?text=iPhone+12" alt="iPhone 12">
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">iPhone 12 Pro Max 128GB</h3>
                        <div class="product-price">12.500.000 ₫</div>
                        <div class="product-meta">
                            <div class="product-condition"><i class="fas fa-star"></i> Như mới</div>
                            <div class="product-stock"><i class="fas fa-box"></i> Còn 2 sản phẩm</div>
                        </div>
                        <div class="product-actions">
                            <form class="add-to-cart-form">
                                <input type="number" min="1" value="1" class="quantity-input">
                                <button type="submit" class="btn-add-to-cart"><i class="fas fa-cart-plus"></i> Thêm vào giỏ</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Product 2 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/300x220/e9ecef/6c757d?text=Máy+Tính+Bảng" alt="Máy tính bảng">
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">iPad Pro 11 inch 2021</h3>
                        <div class="product-price">10.900.000 ₫</div>
                        <div class="product-meta">
                            <div class="product-condition"><i class="fas fa-star"></i> Tốt</div>
                            <div class="product-stock"><i class="fas fa-box"></i> Còn 5 sản phẩm</div>
                        </div>
                        <div class="product-actions">
                            <form class="add-to-cart-form">
                                <input type="number" min="1" value="1" class="quantity-input">
                                <button type="submit" class="btn-add-to-cart"><i class="fas fa-cart-plus"></i> Thêm vào giỏ</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Product 3 -->
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge">Giảm 15%</span>
                        <img src="https://via.placeholder.com/300x220/e9ecef/6c757d?text=Laptop" alt="Laptop">
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">Laptop Dell XPS 13 9310</h3>
                        <div class="product-price">21.750.000 ₫</div>
                        <div class="product-meta">
                            <div class="product-condition"><i class="fas fa-star"></i> Mới</div>
                            <div class="product-stock"><i class="fas fa-box"></i> Còn 1 sản phẩm</div>
                        </div>
                        <div class="product-actions">
                            <form class="add-to-cart-form">
                                <input type="number" min="1" value="1" class="quantity-input">
                                <button type="submit" class="btn-add-to-cart"><i class="fas fa-cart-plus"></i> Thêm vào giỏ</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Product 4 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/300x220/e9ecef/6c757d?text=Máy+Ảnh" alt="Máy ảnh">
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">Máy Ảnh Sony A7 III</h3>
                        <div class="product-price">25.000.000 ₫</div>
                        <div class="product-meta">
                            <div class="product-condition"><i class="fas fa-star"></i> Tốt</div>
                            <div class="product-stock"><i class="fas fa-box"></i> Còn 3 sản phẩm</div>
                        </div>
                        <div class="product-actions">
                            <form class="add-to-cart-form">
                                <input type="number" min="1" value="1" class="quantity-input">
                                <button type="submit" class="btn-add-to-cart"><i class="fas fa-cart-plus"></i> Thêm vào giỏ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Categories Section -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Danh mục sản phẩm</h2>
                <a href="#" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="categories-grid">
                <div class="category-card">
                    <i class="fas fa-mobile-alt category-icon"></i>
                    <div class="category-name">Điện thoại</div>
                </div>
                
                <div class="category-card">
                    <i class="fas fa-laptop category-icon"></i>
                    <div class="category-name">Laptop</div>
                </div>
                
                <div class="category-card">
                    <i class="fas fa-tablet-alt category-icon"></i>
                    <div class="category-name">Máy tính bảng</div>
                </div>
                
                <div class="category-card">
                    <i class="fas fa-camera category-icon"></i>
                    <div class="category-name">Máy ảnh</div>
                </div>
                
                <div class="category-card">
                    <i class="fas fa-headphones category-icon"></i>
                    <div class="category-name">Phụ kiện</div>
                </div>
                
                <div class="category-card">
                    <i class="fas fa-tshirt category-icon"></i>
                    <div class="category-name">Thời trang</div>
                </div>
                
                <div class="category-card">
                    <i class="fas fa-book category-icon"></i>
                    <div class="category-name">Sách</div>
                </div>
                
                <div class="category-card">
                    <i class="fas fa-gamepad category-icon"></i>
                    <div class="category-name">Đồ chơi</div>
                </div>
            </div>
        </section>
        
        <!-- Recent Orders -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Đơn hàng gần đây</h2>
            </div>
            
            <div class="orders-grid">
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">#ORD-2023-00125</div>
                        <div class="order-date">15/06/2023</div>
                    </div>
                    
                    <div class="order-status">
                        <div class="status-badge status-confirmed">Đã xác nhận</div>
                        <div class="status-badge status-pending">Chờ thanh toán</div>
                    </div>
                    
                    <div class="order-total">Tổng tiền: <strong>15.850.000 ₫</strong></div>
                    
                    <div class="order-actions">
                        <button class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Xem chi tiết</button>
                        <button class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Hủy đơn</button>
                    </div>
                </div>
                
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">#ORD-2023-00124</div>
                        <div class="order-date">14/06/2023</div>
                    </div>
                    
                    <div class="order-status">
                        <div class="status-badge status-delivered">Đã giao hàng</div>
                        <div class="status-badge status-confirmed">Đã thanh toán</div>
                    </div>
                    
                    <div class="order-total">Tổng tiền: <strong>8.250.000 ₫</strong></div>
                    
                    <div class="order-actions">
                        <button class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Xem chi tiết</button>
                        <button class="btn btn-outline btn-sm"><i class="fas fa-redo"></i> Mua lại</button>
                    </div>
                </div>
                
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">#ORD-2023-00122</div>
                        <div class="order-date">12/06/2023</div>
                    </div>
                    
                    <div class="order-status">
                        <div class="status-badge status-cancelled">Đã hủy</div>
                    </div>
                    
                    <div class="order-total">Tổng tiền: <strong>5.500.000 ₫</strong></div>
                    
                    <div class="order-actions">
                        <button class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Xem chi tiết</button>
                        <button class="btn btn-outline btn-sm"><i class="fas fa-shopping-cart"></i> Đặt lại</button>
                    </div>
                </div>
            </div>
        </section>
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
    </footer>

    <script>
        // Simple JavaScript for interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add to cart buttons
            const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Create a toast notification
                    const toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.innerHTML = `
                        <div class="toast-header">
                            <i class="fas fa-check-circle"></i>
                            <strong>Thêm vào giỏ hàng thành công</strong>
                            <button type="button" class="close" onclick="this.parentElement.parentElement.remove()">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="toast-body">
                            Sản phẩm đã được thêm vào giỏ hàng của bạn.
                        </div>
                    `;
                    
                    document.body.appendChild(toast);
                    
                    // Remove toast after 3 seconds
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                });
            });
            
            // Category cards animation
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