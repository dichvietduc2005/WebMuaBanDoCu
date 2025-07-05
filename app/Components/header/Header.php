<?php
require_once __DIR__ . '/../../../config/config.php';

function renderHeader($pdo, $categories = [], $cart_count = 0, $unread_notifications = 0)
{
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initial cart count for server-side rendering (will be updated by JavaScript)
    $cart_count = 0;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("
            SELECT SUM(ci.quantity) as total_quantity 
            FROM carts c 
            JOIN cart_items ci ON c.id = ci.cart_id 
            WHERE c.user_id = ? 
            AND (ci.status IS NULL OR ci.status != 'sold')
            AND (ci.is_hidden IS NULL OR ci.is_hidden = 0)
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cart_count = $result['total_quantity'] ?? 0;
    }

    $unread_notifications = 0;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $unread_notifications = (int) $stmt->fetchColumn();
    }

    // Display error messages if any
    if (isset($_SESSION['checkout_error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <strong>Lỗi!</strong> ' . htmlspecialchars($_SESSION['checkout_error_message']) .
            '</div>';
        unset($_SESSION['checkout_error_message']);
    }

    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <strong>Thông báo!</strong> ' . htmlspecialchars($_SESSION['error_message']) .
            '</div>';
        unset($_SESSION['error_message']);
    }
    ?>
    <?php require_once __DIR__ . '/../../View/user/ChatView.php'; ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid px-2 px-sm-3 px-lg-4">
            <!-- Mobile Toggler -->
            <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Logo (visible on all screens) -->
            <a class="navbar-brand d-flex align-items-center me-auto me-lg-4" href="/WebMuaBanDoCu/app/View/Home.php">
                <i class="fas fa-recycle text-primary me-2" style="font-size: clamp(24px, 6vw, 32px);"></i>
                <h1 class="mb-0 fw-bold text-gradient d-none d-sm-inline"
                    style="font-size: clamp(20px, 5vw, 28px); color: #2563eb;">HIHand Shop</h1>
                <h1 class="mb-0 fw-bold text-gradient d-sm-none" style="font-size: clamp(16px, 4vw, 22px); color: #2563eb;">
                    HIHand</h1>
            </a>

            <!-- Mobile Icons -->
            <div class="d-flex align-items-center d-lg-none ms-auto me-1 gap-1">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Notifications -->
                    <a href="/WebMuaBanDoCu/app/View/extra/notifications.php"
                        class="btn btn-link text-dark position-relative rounded-circle d-flex align-items-center justify-content-center notifications-bell"
                        title="Thông báo">
                        <i class="fas fa-bell" style="font-size: clamp(18px, 4.5vw, 22px); color: #374151;"></i>
                        <?php if ($unread_notifications > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size: clamp(10px, 2.5vw, 12px); padding: 0.25em 0.5em; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                <?php echo min($unread_notifications, 99); ?>             <?php echo $unread_notifications > 99 ? '+' : ''; ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Messages -->
                    <button class="btn btn-link text-dark position-relative rounded-circle d-flex align-items-center justify-content-center"
                        title="Tin nhắn" id="button-chat-mobile" onclick="toggleChat()"
                        style="width: 44px; height: 44px; padding: 0;">
                        <i class="fas fa-comment" style="font-size: clamp(18px, 4.5vw, 22px); color: #374151;"></i>
                    </button>

                    <!-- Cart -->
                    <a href="/WebMuaBanDoCu/app/View/cart/index.php"
                        class="btn btn-link text-dark position-relative rounded-circle d-flex align-items-center justify-content-center"
                        title="Giỏ hàng" style="width: 44px; height: 44px; padding: 0;">
                        <i class="fas fa-shopping-cart" style="font-size: clamp(18px, 4.5vw, 22px); color: #374151;"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"
                                style="font-size: clamp(10px, 2.5vw, 12px); padding: 0.25em 0.5em; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                <?php echo min($cart_count, 99); ?>             <?php echo $cart_count > 99 ? '+' : ''; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Main Content -->
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarMain">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center w-100 gap-2">
                    
                    <!-- Search Form with Categories Button - Full width on mobile -->
                    <div class="w-100 mb-2 mb-lg-0 me-lg-3 position-relative">
                        <div class="d-flex gap-2 align-items-center">
                            <!-- Categories Button - Separate from search -->
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center categories-btn"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="height: 50px; min-width: 56px; padding: 0 16px; border-radius: 16px; border: 0; background: white; transition: all 0.3s ease; white-space: nowrap; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);">
                                    <i class="fas fa-th-large" style="font-size: 18px; color: #3b82f6;"></i>
                                    <span class="ms-2 d-none d-md-inline fw-semibold" style="color: #3b82f6; font-size: 15px;">Danh mục</span>
                                </button>
                                <ul class="dropdown-menu categories-dropdown-menu"
                                    style="max-height: 65vh; overflow-y: auto; border-radius: 20px; border: none; box-shadow: 0 12px 40px rgba(0,0,0,0.15); padding: 16px; min-width: 300px; margin-top: 8px; ">
                                    <li class="dropdown-header" style="padding: 16px 20px; font-weight: 700; color: #1f2937; background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 16px; margin-bottom: 12px; text-align: center; font-size: 16px;">
                                        Danh mục sản phẩm
                                    </li>
                                    <?php foreach ($categories as $category): ?>
                                        <li>
                                            <a class="dropdown-item category-item"
                                                style="font-size: 16px; padding: 16px 20px; transition: all 0.3s ease; border-radius: 12px; margin: 4px 0; color: #374151; font-weight: 500;"
                                                href="<?php echo BASE_URL; ?>app/View/product/category.php?slug=<?php echo $category['slug']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- Search Form -->
                            <form id="search-form2" class="flex-grow-1 position-relative" method="GET"
                                action="/WebMuaBanDoCu/app/View/extra/search_advanced.php">
                                <div class="input-group search-modern"
                                    style="height: 50px; position: relative; min-height: 50px; max-height: 50px;">
                                    <input type="text" class="form-control search-input-modern" id="search-input" name="q"
                                        placeholder="Tìm sản phẩm..."
                                        style="height: 50px; min-height: 50px; max-height: 50px; font-size: 16px; border-radius: 25px 0 0 25px; border: 2px solid #e5e7eb; padding-left: 20px; padding-right: 50px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);"
                                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                                        autocomplete="off">

                                    <!-- Nút xóa -->
                                    <button type="button" class="btn btn-clear-search position-absolute"
                                        style="right: 70px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; display: none; height: 36px; width: 36px; border-radius: 50%; transition: all 0.3s ease;"
                                        onclick="document.getElementById('search-input').value = ''; document.getElementById('search-input').focus();">
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <button class="btn btn-primary search-btn-modern" type="submit"
                                        style="height: 50px; min-height: 50px; max-height: 50px; width: 64px; min-width: 64px; border-radius: 0 25px 25px 0; border: none; padding: 0 20px; background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);">
                                        <i class="fas fa-search" style="font-size: 16px;"></i>
                                    </button>
                                </div>

                                <!-- Gợi ý tìm kiếm -->
                                <div class="search-suggestions position-absolute bg-white rounded shadow-lg mt-1 w-100"
                                    style="z-index: 1100; display: none; max-height: 300px; overflow-y: auto; border-radius: 16px; border: 1px solid #e5e7eb;">
                                    <div class="list-group">
                                        <!-- Các gợi ý sẽ được thêm bằng JavaScript -->
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right side actions - Stack vertically on mobile -->
                    <div
                        class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2 ms-lg-auto w-100 w-lg-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Desktop Icons - hidden on mobile -->
                            <div class="d-none d-lg-flex align-items-center gap-2">
                                <!-- Notifications -->
                                <a href="/WebMuaBanDoCu/app/View/extra/notifications.php"
                                    class="btn btn-link text-dark p-1 position-relative notifications-bell" title="Thông báo">
                                    <i class="fas fa-bell" style="font-size: 20px;"></i>
                                    <?php if ($unread_notifications > 0): ?>
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                            style="font-size: 12px; padding: 0.2em 0.4em;">
                                            <?php echo min($unread_notifications, 99); ?>
                                            <?php echo $unread_notifications > 99 ? '+' : ''; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>

                                <!-- Messages -->
                                <button class="btn btn-link text-dark p-1" title="Tin nhắn" id="button-chat"
                                    onclick="toggleChat()">
                                    <i class="fas fa-comment" style="font-size: 20px;"></i>
                                </button>

                                <!-- Cart -->
                                <a href="/WebMuaBanDoCu/app/View/cart/index.php"
                                    class="btn btn-link text-dark p-1 position-relative" title="Giỏ hàng">
                                    <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                                    <?php if ($cart_count > 0): ?>
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"
                                            style="font-size: 12px; padding: 0.2em 0.4em;">
                                            <?php echo min($cart_count, 9); ?>             <?php echo $cart_count > 9 ? '+' : ''; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <!-- Mobile Account Dropdown -->
                            <div class="d-lg-none w-100">
                                <div class="dropdown mb-3">
                                    <button
                                        class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center justify-content-center w-100 mobile-account-btn"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="height: 52px; font-size: clamp(14px, 3.5vw, 16px); border-radius: 16px; border: 2px solid #e5e7eb; background: white; transition: all 0.3s ease; color: #374151;">
                                        <i class="fas fa-user-circle me-2" style="font-size: clamp(16px, 4vw, 18px); color: #6b7280;"></i>
                                        <span class="fw-semibold">Tài khoản</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end mobile-dropdown-menu">
                                        <li>
                                            <h6 class="dropdown-header">Xin
                                                chào<?php echo $_SESSION['user_role'] == 'admin' ? ' admin' : '' ?>,
                                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                            </h6>
                                        </li>
                                        <?php
                                        if ($_SESSION['user_role'] == 'admin') {
                                            echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/QuanLyTaiKhoanView.php"><i class="fas fa-solid fa-medal me-2"></i>Quản lý tài khoản</a></li>';
                                            echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/DanhSachBoxChatView.php"><i class="fas fa-solid fa-envelope me-2"></i>Xem tin nhắn từ người dùng</a></li>';
                                            echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/products.php"><i class="fas fa-solid fa-check me-2"></i>Duyệt sản phẩm</a></li>';
                                            echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/manage_products.php"><i class="fas fa-cogs me-2"></i>Quản lý sản phẩm</a></li>';   
                                        }
                                        ?>
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/product/Product.php"><i
                                                    class="fas fa-box me-2"></i>Tin đăng của tôi</a></li>
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/order/order_history.php"><i
                                                    class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/user/ProfileUserView.php"><i
                                                    class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/user/logout.php"><i
                                                    class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Desktop Account Dropdown -->
                            <div class="dropdown mb-2 mb-lg-0 w-100 w-lg-auto d-none d-lg-block">
                                <button
                                    class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center justify-content-center w-100"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="height: 42px; font-size: min(16px, 4vw); border-radius: 8px; border: none;">
                                    <i class="fas fa-user-circle me-2" style="font-size: min(18px, 4.5vw);"></i>
                                    <span>Tài khoản</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end account-dropdown-menu">
                                    <li>
                                        <h6 class="dropdown-header">Xin
                                            chào<?php echo $_SESSION['user_role'] == 'admin' ? ' admin' : '' ?>,
                                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                        </h6>
                                    </li>
                                    <?php
                                    if ($_SESSION['user_role'] == 'admin') {
                                        echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/QuanLyTaiKhoanView.php"><i class="fas fa-solid fa-medal me-2"></i>Quản lý tài khoản</a></li>';
                                        echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/DanhSachBoxChatView.php"><i class="fas fa-solid fa-envelope me-2"></i>Xem tin nhắn từ người dùng</a></li>';
                                        echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/products.php"><i class="fas fa-solid fa-check me-2"></i>Duyệt sản phẩm</a></li>';
                                        echo '<li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/admin/manage_products.php"><i class="fas fa-cogs me-2"></i>Quản lý sản phẩm</a></li>';
                                    }
                                    ?>
                                    <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/product/Product.php"><i
                                                class="fas fa-box me-2"></i>Tin đăng của tôi</a></li>
                                    <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/order/order_history.php"><i
                                                class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                                    <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/user/ProfileUserView.php"><i
                                                class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                    
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/user/logout.php"><i
                                                class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                </ul>
                            </div>

                            <!-- Post Ad Button - Full width on mobile -->
                            <a href="/WebMuaBanDoCu/app/View/product/sell.php"
                                class="btn btn-warning d-flex align-items-center justify-content-center fw-bold mb-2 mb-lg-0 w-100 w-lg-auto shadow"
                                title="Đăng tin bán hàng"
                                style="height: 50px; min-width: 136px; padding: 0 20px; font-size: clamp(15px, 3.8vw, 17px); line-height: 1; gap: 8px; border-radius: 12px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border: none; transition: all 0.3s ease;">
                                <i class="fas fa-plus-circle" style="font-size: clamp(16px, 4vw, 18px);"></i>
                                <span style="line-height: 1; font-weight: 600;">Đăng Tin</span>
                            </a>
                        <?php else: ?>
                            <!-- Guest user buttons -->
                            <div class="d-flex flex-column flex-lg-row gap-3 w-100">
                                <a href="/WebMuaBanDoCu/public/index.php?page=login"
                                    class="btn btn-primary border-0 d-flex align-items-center justify-content-center fw-semibold w-100 shadow-sm"
                                    style="height: 48px; padding: 0 20px; font-size: clamp(14px, 3.5vw, 16px); line-height: 1; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); transition: all 0.3s ease;">
                                    <i class="fas fa-sign-in-alt me-2" style="font-size: clamp(16px, 4vw, 18px);"></i>Đăng nhập
                                </a>
                                <a href="/WebMuaBanDoCu/public/index.php?page=register"
                                    class="btn btn-success border-0 d-flex align-items-center justify-content-center fw-semibold w-100 shadow-sm"
                                    style="height: 48px; padding: 0 20px; font-size: clamp(14px, 3.5vw, 16px); line-height: 1; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); transition: all 0.3s ease;">
                                    <i class="fas fa-user-plus me-2" style="font-size: clamp(16px, 4vw, 18px);"></i>Đăng ký
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Notifications Popup CSS -->
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/notifications.css">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/user_box_chat.css?v=1.2">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/footer.css">
    



    <!-- Notifications Popup JS -->
    <script src="/WebMuaBanDoCu/public/assets/js/notifications.js"></script>
    <script src="/WebMuaBanDoCu/public/assets/js/search-autocomplete.js"></script>

    <style>
        /* Enhanced Mobile Responsive Styles */
        :root {
            --primary-blue: #3b82f6;
            --primary-dark: #2563eb;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --text-gray: #374151;
            --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Mobile Navigation Styles */
        @media (max-width: 576px) {
            .navbar {
                padding: 0.75rem 0;
                min-height: 70px;
            }

            .navbar-collapse {
                max-height: 75vh;
                overflow-y: auto;
                background-color: white;
                padding: 20px 15px;
                margin-top: 10px;
                box-shadow: var(--shadow-medium);
                border-radius: 0 0 16px 16px;
                border-top: 3px solid var(--primary-blue);
            }

            .navbar-collapse::-webkit-scrollbar {
                width: 8px;
            }

            .navbar-collapse::-webkit-scrollbar-track {
                background: #f8fafc;
                border-radius: 10px;
            }

            .navbar-collapse::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #cbd5e1, #94a3b8);
                border-radius: 10px;
            }

            .navbar-collapse::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #94a3b8, #64748b);
            }

            /* Mobile button improvements */
            .btn:hover {
                transform: translateY(-1px);
                box-shadow: var(--shadow-medium);
            }

            /* Touch target improvements */
            .btn,
            .dropdown-toggle {
                min-height: 48px;
                touch-action: manipulation;
            }

            /* Improve text readability */
            .navbar-nav .nav-link {
                font-size: clamp(16px, 4vw, 18px);
                padding: 12px 16px;
                font-weight: 500;
            }
        }

        /* CSS cho thanh tìm kiếm hiện đại */
        .search-modern {
            border-radius: 25px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            height: 50px !important;
            min-height: 50px !important;
            max-height: 50px !important;
            display: flex !important;
            flex-wrap: nowrap !important;
        }

        .search-input-modern {
            flex: 1 1 auto !important;
        }

        .search-btn-modern {
            flex: 0 0 auto !important;
        }

        /* Categories Button & Dropdown Styles */
        .categories-btn {
            font-weight: 600 !important;
            letter-spacing: 0.5px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1) !important;
        }

        .categories-btn:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            color: white !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.25) !important;
        }

        .categories-btn:hover i,
        .categories-btn:hover span {
            color: white !important;
        }

        .categories-dropdown-menu {
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            animation: fadeInDown 0.3s ease !important;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom Scrollbar for Categories Dropdown */
        .categories-dropdown-menu::-webkit-scrollbar {
            width: 8px;
        }

        .categories-dropdown-menu::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        .categories-dropdown-menu::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #cbd5e1, #94a3b8);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .categories-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }

        .category-item {
            transition: all 0.3s ease !important;
        }

        .category-item:hover {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0) !important;
            color: #1e40af !important;
            transform: translateX(4px) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        }

        .category-item:active {
            transform: translateX(2px) !important;
        }

        /* Mobile Responsive for Categories */
        @media (max-width: 768px) {
            .categories-btn {
                height: 46px !important;
                min-width: 52px !important;
                border-radius: 14px !important;
                padding: 0 14px !important;
            }

            .categories-dropdown-menu {
                min-width: 320px !important;
                max-width: 90vw !important;
                margin-top: 4px !important;
                max-height: 60vh !important;
                left: 29% !important;
                right: auto !important;
                transform: none !important;
            }

            .category-item {
                font-size: 15px !important;
                padding: 14px 18px !important;
            }

            .search-modern {
                height: 46px !important;
                min-height: 46px !important;
                max-height: 46px !important;
            }
        }

        @media (max-width: 576px) {
            .categories-btn {
                height: 44px !important;
                min-width: 48px !important;
                border-radius: 12px !important;
                padding: 0 12px !important;
            }

            .categories-dropdown-menu {
                min-width: 280px !important;
                max-width: 95vw !important;
                padding: 12px !important;
                max-height: 55vh !important;
            }

            .category-item {
                font-size: 14px !important;
                padding: 12px 16px !important;
            }

            .search-modern {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
            }
        }
        .search-modern:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .search-input-modern,
        .search-btn-modern {
            outline: none;
            box-shadow: none !important;
        }

        .search-input-modern:focus {
            border-color: #a5b4fc !important;
            box-shadow: 0 0 0 3px rgba(165, 180, 252, 0.1) !important;
        }

        .search-btn-modern {
            transition: all 0.2s ease;
        }

        .search-btn-modern:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9) !important;
            transform: scale(1.02);
            box-shadow: 0 4px 16px rgba(79, 70, 229, 0.3) !important;
        }

        /* Clear Search Button */
        .btn-clear-search:hover {
            background: rgba(156, 163, 175, 0.1) !important;
            color: #6b7280 !important;
            transform: translateY(-50%) scale(1.1) !important;
        }


        @media (max-width: 768px) {
            .search-input-modern {
                font-size: 15px !important;
                padding-left: 18px !important;
                padding-right: 60px !important;
            }

            .search-btn-modern {
                width: 58px !important;
                min-width: 58px !important;
                padding: 0 18px !important;
            }

            .btn-clear-search {
                right: 66px !important;
                height: 34px !important;
                width: 34px !important;
            }
        }

        @media (max-width: 576px) {
            .search-input-modern {
                font-size: 14px !important;
                padding-left: 16px !important;
                padding-right: 56px !important;
            }

            .search-btn-modern {
                width: 54px !important;
                min-width: 54px !important;
                padding: 0 16px !important;
            }

            .btn-clear-search {
                right: 62px !important;
                height: 32px !important;
                width: 32px !important;
        }

        /* Additional responsive adjustments */
        @media (max-width: 768px) {
            .navbar-brand {
                margin-right: 0.75rem;
                flex-shrink: 0;
            }

            .navbar-toggler {
                padding: 0.75rem;
                font-size: clamp(18px, 4.5vw, 22px);
                border: 2px solid var(--primary-blue);
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .navbar-toggler:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            }

            .navbar-toggler:hover {
                background-color: rgba(59, 130, 246, 0.1);
            }

            /* Improve dropdown menu on tablets */
            .dropdown-menu {
                border-radius: 12px;
                border: none;
                box-shadow: var(--shadow-medium);
                padding: 8px;
            }

            .search-btn-modern i {
                font-size: 14px !important;
            }
        }
            .dropdown-item {
                border-radius: 8px;
                padding: 12px 16px;
                font-size: clamp(15px, 3.8vw, 17px);
                transition: all 0.2s ease;
            }

            .dropdown-item:hover {
                background-color: rgba(59, 130, 246, 0.1);
                transform: translateY(4px);
            }
        }

        /* Enhanced visual feedback */
        @media (max-width: 992px) {
            .btn-primary:hover {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                transform: translateY(-2px);
            }

            .btn-success:hover {
                background: linear-gradient(135deg, #059669, #047857);
                transform: translateY(-2px);
            }

            .btn-warning:hover {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                transform: translateY(-2px);
            }

            .btn-outline-primary:hover {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                border-color: transparent;
                color: white;
                transform: translateY(-1px);
            }
        }

        /* Hiệu ứng cho gợi ý tìm kiếm */
        .search-suggestions .list-group-item {
            border: none;
            border-radius: 0;
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .search-suggestions .list-group-item:hover {
            background-color: #f3f4f6;
            border-left-color: #4f46e5;
        }

        .search-suggestions .list-group-item:not(:last-child) {
            border-bottom: 1px solid #f1f5f9;
        }

        /* Hiệu ứng cho dropdown danh mục */
        .dropdown-toggle:hover {
            background-color: #e5e7eb !important;
            /* Màu xám nhạt, dịu mắt */
            border-color: #cbd5e1 !important;
            /* Màu xám viền trung tính */
            color: #374151 !important;
        }

        .dropdown-toggle:focus {
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1) !important;
        }

        .dropdown-item {
            border-radius: 6px;
            margin: 2px 8px;
        }

        .dropdown-item:hover {
            background-color: #f3f4f6 !important;
            transform: translateY(3px);
            color: #4f46e5 !important;
        }

        .dropdown-item:hover i {
            color: #4f46e5 !important;
        }

        /* Nút Categories mới */
        .categories-btn {
            transition: all 0.3s ease !important;
        }

        .categories-btn:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            border-color: #2563eb !important;
            transform: translateY(-1px) scale(1.02) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
        }

        .categories-btn:hover i,
        .categories-btn:hover span {
            color: white !important;
        }

        /* Categories Dropdown Menu */
        .categories-dropdown-menu {
            animation: slideDown 0.3s ease !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .category-item:hover {
            background: linear-gradient(135deg, #f0f9ff, #dbeafe) !important;
            transform: translateX(4px) !important;
            color: #1e40af !important;
        }

        .category-item:hover i {
            color: #3b82f6 !important;
            transform: scale(1.1) !important;
        }

        .category-item:hover .badge {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            color: white !important;
            transform: scale(1.05) !important;
        }

        /* Enhanced Categories Dropdown positioning and responsiveness */
        .categories-dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            margin-top: 8px !important;
            /* z-index: 1100 !important; */
            display: none !important;
        }

        .categories-dropdown-menu.show {
            display: block !important;
        }

        /* Ensure dropdown doesn't get covered by search box */
        .dropdown {
            position: relative;
            /* z-index: 1020; */
        }

        .search-modern {
            position: relative;
/        }

        /* Mobile responsive for categories dropdown positioning */
        @media (max-width: 768px) {
            .categories-dropdown-menu {
                position: fixed !important;
                top: auto !important;
                left: 29% !important;
                transform: translateX(-50%) !important;
                margin-top: 0 !important;
                bottom: auto !important;
                max-height: 60vh !important;
                overflow-y: auto !important;
            }
        }

        @media (max-width: 576px) {
            .categories-dropdown-menu {
                position: fixed !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                max-height: 70vh !important;
                width: 85vw !important;
                min-width: 280px !important;
                max-width: 320px !important;
            }
        }

        /* Hiệu ứng hover cho các nút và icon - Với !important */
        
        /* Hiệu ứng hover cho icon thông báo */
        .notifications-bell {
            transition: all 0.3s ease !important;
            border-radius: 50% !important;
            padding: 8px !important;
        }
        
        .notifications-bell:hover {
            background-color: rgba(59, 130, 246, 0.1) !important;
            transform: translateY(-2px) scale(1.05) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2) !important;
        }
        
        .notifications-bell:hover i {
            color: #3b82f6 !important;
            transform: rotate(15deg) !important;
        }
        
        /* Hiệu ứng hover cho icon tin nhắn */
        #button-chat, #button-chat-mobile {
            transition: all 0.3s ease !important;
            border-radius: 50% !important;
            padding: 8px !important;
        }
        
        #button-chat:hover {
            background-color: rgba(16, 185, 129, 0.1) !important;
            transform: translateY(-2px) scale(1.05) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2) !important;
        }
        
        #button-chat:hover i {
            color: #10b981 !important;
            transform: rotate(-5deg) !important;
        }
        
        #button-chat-mobile:hover {
            background-color: rgba(16, 185, 129, 0.1) !important;
            transform: translateY(-2px) scale(1.05) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2) !important;
        }
        
        #button-chat-mobile:hover i {
            color: #10b981 !important;
            transform: rotate(-5deg) !important;
        }
        
        /* Hiệu ứng hover cho icon giỏ hàng */
        .btn[title="Giỏ hàng"] {
            transition: all 0.3s ease !important;
            border-radius: 50% !important;
            padding: 8px !important;
        }
        
        .btn[title="Giỏ hàng"]:hover {
            background-color: rgba(245, 158, 11, 0.1) !important;
            transform: translateY(-2px) scale(1.05) !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2) !important;
        }
        
        .btn[title="Giỏ hàng"]:hover i {
            color: #f59e0b !important;
            transform: rotate(10deg) !important;
        }
        
        /* Hiệu ứng hover cho badge số lượng */
        .cart-count, .badge {
            transition: all 0.3s ease !important;
        }
        
        .btn:hover .cart-count,
        .btn:hover .badge {
            transform: scale(1.1) !important;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3) !important;
        }
        
        /* Hiệu ứng hover cho nút đăng tin */
        .btn-warning[title="Đăng tin bán hàng"] {
            transition: all 0.3s ease !important;
        }
        
        .btn-warning[title="Đăng tin bán hàng"]:hover {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            transform: translateY(-3px) scale(1.02) !important;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4) !important;
        }
        
        .btn-warning[title="Đăng tin bán hàng"]:hover i {
            transform: rotate(180deg) !important;
        }
        
        /* Hiệu ứng hover cho nút đăng nhập/đăng ký */
        .btn-primary[href*="login"] {
            transition: all 0.3s ease !important;
        }
        
        .btn-primary[href*="login"]:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3) !important;
        }
        
        .btn-success[href*="register"] {
            transition: all 0.3s ease !important;
        }
        
        .btn-success[href*="register"]:hover {
            background: linear-gradient(135deg, #059669, #047857) !important;
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3) !important;
        }
        
        /* Hiệu ứng hover cho dropdown tài khoản */
        .btn-outline-secondary.dropdown-toggle {
            transition: all 0.3s ease !important;
        }
        
        .btn-outline-secondary.dropdown-toggle:hover {
            background-color: #f3f4f6 !important;
            border-color: #9ca3af !important;
            color: #374151 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(156, 163, 175, 0.2) !important;
        }
        
        .btn-outline-secondary.dropdown-toggle:hover i {
            color: #6b7280 !important;
        }
        
        /* Hiệu ứng hover cho mobile account button */
        .mobile-account-btn:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            border-color: #cbd5e1 !important;
            transform: translateY(-1px) scale(1.02) !important;
            box-shadow: 0 4px 12px rgba(148, 163, 184, 0.2) !important;
        }
        
        .mobile-account-btn:hover i {
            color: #4f46e5 !important;
        }
        
        /* CSS responsive cho dropdown mobile - Cải thiện */
        @media (max-width: 991px) {
            .mobile-dropdown-menu {
                position: fixed !important;
                top: auto !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: 92vw !important;
                max-width: 380px !important;
                margin-top: 0.5rem !important;
                border-radius: 20px !important;
                border: none !important;
                box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2) !important;
                background: white !important;
                /* z-index: 1060 !important; */
                padding: 16px !important;
                max-height: 80vh !important;
                overflow-y: auto !important;
            }
            
            .mobile-dropdown-menu .dropdown-header {
                padding: 16px 20px !important;
                font-size: 16px !important;
                font-weight: 700 !important;
                color: #1f2937 !important;
                background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
                border-radius: 16px !important;
                margin-bottom: 12px !important;
                text-align: center !important;
                border: 2px solid #bae6fd !important;
            }
            
            .mobile-dropdown-menu .dropdown-item {
                padding: 18px 20px !important;
                font-size: 16px !important;
                font-weight: 500 !important;
                border-radius: 12px !important;
                margin: 6px 0 !important;
                transition: all 0.3s ease !important;
                color: #374151 !important;
                min-height: 56px !important;
                display: flex !important;
                align-items: center !important;
                line-height: 1.4 !important;
            }
            
            .mobile-dropdown-menu .dropdown-item:hover, 
            .mobile-dropdown-menu .dropdown-item:focus {
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
                color: white !important;
                transform: translateY(8px) scale(1.02) !important;
                box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4) !important;
            }
            
            .mobile-dropdown-menu .dropdown-item:hover i, 
            .mobile-dropdown-menu .dropdown-item:focus i {
                color: white !important;
                transform: scale(1.15) !important;
            }
            
            .mobile-dropdown-menu .dropdown-item i {
                width: 24px !important;
                font-size: 18px !important;
                transition: all 0.3s ease !important;
                margin-right: 12px !important;
            }
            
            .mobile-dropdown-menu .dropdown-divider {
                margin: 16px 0 !important;
                opacity: 0.2 !important;
                border-top: 2px solid #e5e7eb !important;
            }
            
            /* Scrollbar cho mobile dropdown */
            .mobile-dropdown-menu::-webkit-scrollbar {
                width: 6px !important;
            }
            
            .mobile-dropdown-menu::-webkit-scrollbar-track {
                background: #f1f5f9 !important;
                border-radius: 10px !important;
            }
            
            .mobile-dropdown-menu::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #cbd5e1, #94a3b8) !important;
                border-radius: 10px !important;
            }
            
            .mobile-dropdown-menu::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #94a3b8, #64748b) !important;
            }
        }
        
        /* Hiệu ứng cho mobile layout */
        @media (max-width: 767px) {
            .mobile-account-btn .fw-semibold {
                font-size: 14px !important;
            }
            
            .mobile-dropdown-menu {
                width: 96vw !important;
                max-width: 340px !important;
            }
            
            .mobile-dropdown-menu .dropdown-item {
                padding: 20px 22px !important;
                font-size: 17px !important;
                min-height: 60px !important;
            }
            
            .mobile-dropdown-menu .dropdown-header {
                padding: 18px 22px !important;
                font-size: 17px !important;
            }
            
            /* Categories trên mobile nhỏ */
            .categories-btn span {
                display: none !important;
            }
            
            .categories-btn {
                min-width: 44px !important;
                padding: 0 8px !important;
            }
        }
        
        /* Tablet responsive */
        @media (min-width: 768px) and (max-width: 991px) {
            .mobile-dropdown-menu {
                width: 80vw !important;
                max-width: 400px !important;
            }
        }
        
        /* Hiệu ứng hover cho mobile icons */
        @media (max-width: 991px) {
            .d-lg-none .btn[title="Thông báo"] {
                transition: all 0.3s ease !important;
            }
            
            .d-lg-none .btn[title="Thông báo"]:hover {
                background-color: rgba(59, 130, 246, 0.1) !important;
                transform: translateY(-2px) scale(1.1) !important;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2) !important;
            }
            
            .d-lg-none .btn[title="Giỏ hàng"] {
                transition: all 0.3s ease !important;
            }
            
            .d-lg-none .btn[title="Giỏ hàng"]:hover {
                background-color: rgba(245, 158, 11, 0.1) !important;
                transform: translateY(-2px) scale(1.1) !important;
                box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2) !important;
            }
        }
        
        /* Hiệu ứng hover cho navbar toggler */
        .navbar-toggler {
            transition: all 0.3s ease !important;
        }
        
        .navbar-toggler:hover {
            background-color: rgba(59, 130, 246, 0.1) !important;
            transform: scale(1.05) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2) !important;
        }
        
        /* Hiệu ứng hover cho logo */
        .navbar-brand {
            transition: all 0.3s ease !important;
        }
        
        .navbar-brand:hover {
            transform: scale(1.05) !important;
        }
        
        .navbar-brand:hover i {
            color: #2563eb !important;
            transform: rotate(15deg) !important;
        }
        
        /* Hiệu ứng hover cho nút xóa tìm kiếm */
        .btn-clear-search {
            transition: all 0.3s ease !important;
        }
        
        .btn-clear-search:hover {
            background-color: rgba(156, 163, 175, 0.1) !important;
            transform: scale(1.1) !important;
            color: #ef4444 !important;
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }

        /* Account Dropdown Styles (desktop) */
        .account-dropdown-menu {
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            animation: fadeInDown 0.3s ease !important;
            background: rgba(255,255,255,0.8) !important;
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15) !important;
        }

        .account-dropdown-menu::-webkit-scrollbar {
            width: 8px;
        }
        .account-dropdown-menu::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }
        .account-dropdown-menu::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #cbd5e1, #94a3b8);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .account-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }

        /* Add blur to mobile dropdown */
        .mobile-dropdown-menu {
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
        }
    </style>
    
    <!-- Real-time Cart Count Script -->
    <script src="/WebMuaBanDoCu/public/assets/js/cart-count-realtime.js"></script>
    
    <?php
}
