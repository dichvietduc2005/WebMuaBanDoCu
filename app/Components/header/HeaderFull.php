<?php
// HeaderFull.php - Full header for desktop (≥992px)
// Mobile/Tablet will use HeaderSimple.php

function renderHeaderFull($pdo, $categories = [], $cart_count = 0, $unread_notifications = 0)
{
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
    
    <!-- Header Component CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/components/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/header-mobile-fix.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/notifications.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/footer.css">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top d-none d-lg-block" id="mainHeader" style="min-height: auto;">
        <div class="container-fluid px-2 px-sm-3 px-lg-4" style="padding-top: 4px; padding-bottom: 4px;">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center me-auto me-lg-4 text-decoration-none" href="<?php echo BASE_URL; ?>public/index.php?page=home" style="transition: none; opacity: 1; padding: 0;">
                <i class="fas fa-recycle text-primary me-2" style="font-size: clamp(20px, 5vw, 26px);"></i>
                <h1 class="mb-0 fw-bold text-gradient d-none d-sm-inline"
                    style="font-size: clamp(18px, 4vw, 24px); color: #2563eb; line-height: 1.2;">HIHand Shop</h1>
                <h1 class="mb-0 fw-bold text-gradient d-sm-none" style="font-size: clamp(14px, 3vw, 18px); color: #2563eb; line-height: 1.2;">
                    HIHand</h1>
            </a>

            <!-- Main Content -->
            <div class="d-flex flex-row align-items-center w-100 gap-2" style="justify-content: space-between;">
                <!-- Left: Categories Button -->
                <div style="display: flex; gap: 12px; align-items: center;">
                        <!-- Categories Button -->
                        <div class="dropdown">
                            <button
                                class="btn d-flex align-items-center categories-btn"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                style="height: 44px; padding: 0 16px; border-radius: 22px; border: none; background: white; color: #4f46e5; font-weight: 600; font-size: 14px; transition: all 0.3s ease; white-space: nowrap; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                                <i class="fas fa-list" style="font-size: 14px; margin-right: 8px;"></i>
                                <span>Danh mục</span>
                            </button>
                            <ul class="dropdown-menu categories-dropdown-menu"
                                style="min-width: 280px; max-width: 350px; border-radius: 12px; border: none; box-shadow: 0 12px 40px rgba(0,0,0,0.12); padding: 0; margin-top: 8px;">
                                
                                <!-- All Categories Header -->
                                <li style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: white; border-radius: 12px 12px 0 0;">
                                    <a href="<?php echo BASE_URL; ?>public/index.php?page=products"
                                       style="display: flex; align-items: center; justify-content: space-between; text-decoration: none; color: #1f2937; font-weight: 600; font-size: 14px;">
                                        <span>Tất cả danh mục</span>
                                        <i class="fas fa-check" style="color: #fbbf24; font-size: 16px;"></i>
                                    </a>
                                </li>
                                
                                <?php if (!empty($categories)): ?>
                                    <div style="max-height: 380px; overflow-y: auto; padding: 8px 0;">
                                        <?php 
                                        $cat_icons = [
                                            'am-nhac-nhac-cu' => 'fas fa-music',
                                            'dien-thoai-may-tinh-bang' => 'fas fa-mobile-alt',
                                            'laptop-may-tinh' => 'fas fa-laptop',
                                            'thoi-trang-phu-kien' => 'fas fa-bag-shopping',
                                            'do-gia-dung-noi-that' => 'fas fa-chair',
                                            'xe-co-phuong-tien' => 'fas fa-car',
                                            'sach-van-phong-pham' => 'fas fa-book',
                                            'the-thao-giai-tri' => 'fas fa-dumbbell',
                                            'dien-may-cong-nghe' => 'fas fa-tv',
                                            'me-va-be' => 'fas fa-baby-carriage',
                                            'suc-khoe-lam-dep' => 'fas fa-spa',
                                            'thu-cung-phu-kien' => 'fas fa-paw',
                                            'am-thuc' => 'fas fa-burger'
                                        ];
                                        $current_slug = $_GET['slug'] ?? '';
                                        foreach ($categories as $index => $category): 
                                            $icon = $cat_icons[$category['slug']] ?? 'fas fa-box';
                                            $isActive = ($current_slug === $category['slug']) ? 'background: #f3f4f6; color: #4f46e5; border-left: 3px solid #4f46e5;' : 'border-left: 3px solid transparent;';
                                        ?>
                                            <li>
                                                <a href="<?php echo BASE_URL; ?>app/View/product/category.php?slug=<?php echo htmlspecialchars($category['slug'] ?? ''); ?>"
                                                   class="category-dropdown-item"
                                                   style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; font-size: 14px; color: #4b5563; font-weight: 500; text-decoration: none; cursor: pointer; <?php echo $isActive; ?> transition: all 0.2s ease;">
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <i class="<?php echo $icon; ?>" style="width: 20px; text-align: center; opacity: 0.8;"></i>
                                                        <span><?php echo htmlspecialchars($category['name'] ?? 'Unnamed'); ?></span>
                                                    </div>
                                                    <i class="fas fa-chevron-right" style="font-size: 10px; opacity: 0.3;"></i>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <li style="padding: 18px 16px; text-align: center; color: #9ca3af; font-size: 13px;">
                                        <i class="fas fa-inbox me-2"></i>Chưa có danh mục
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                </div>
                
                    <!-- Search Form - Modern Rounded Style -->
                    <div class="desktop-search-section">
                        <form id="search-form2" class="desktop-search-form" method="GET"
                            action="<?php echo BASE_URL; ?>app/View/extra/search_advanced.php" onsubmit="return validateSearchForm(event);">
                            <input type="text" id="search-input-desktop" name="q"
                                placeholder="Bạn tìm gì hôm nay?"
                                class="desktop-search-input"
                                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                                autocomplete="off">
                            <button type="submit" class="desktop-search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        
                        <!-- Search Suggestions - Hidden to focus on dynamic placeholder -->
                        <!-- <div class="search-suggestions-container">
                            <span class="suggestion-tag" onclick="fillSearch('Xe máy giá rẻ')">Xe máy giá rẻ</span>
                            <span class="suggestion-tag" onclick="fillSearch('Dream Thái')">Dream Thái</span>
                            <span class="suggestion-tag" onclick="fillSearch('iPhone 15')">iPhone 15</span>
                            <span class="suggestion-tag" onclick="fillSearch('Đồ gia dụng')">Đồ gia dụng</span>
                        </div> -->
                    </div>
                    
                    <script>
                    function fillSearch(keyword) {
                        document.getElementById('search-input-desktop').value = keyword;
                        document.getElementById('search-form2').submit();
                    }
                    </script>
                    
                    <script>
                    function validateSearchForm(event) {
                        const input = document.getElementById('search-input-desktop').value.trim();
                        if (input === '') {
                            event.preventDefault();
                            alert('Vui lòng nhập từ khóa tìm kiếm');
                            return false;
                        }
                        return true;
                    }
                    </script>
                </div>

                <!-- Right: Actions -->
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Notifications -->
                        <a href="<?php echo BASE_URL; ?>app/View/extra/notifications.php"
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



                        <!-- Cart -->
                        <a href="<?php echo BASE_URL; ?>app/View/cart/index.php"
                            class="btn btn-link text-dark p-1 position-relative" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                            <?php if ($cart_count > 0): ?>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"
                                    style="font-size: 12px; padding: 0.2em 0.4em;">
                                    <?php echo min($cart_count, 9); ?>
                                    <?php echo $cart_count > 9 ? '+' : ''; ?>
                                </span>
                            <?php endif; ?>
                        </a>

                        <!-- Account Dropdown -->
                        <div class="dropdown">
                            <button
                                class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center justify-content-center"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                style="height: 42px; font-size: 16px; border-radius: 8px; border: none;">
                                <i class="fas fa-user-circle me-2" style="font-size: 18px;"></i>
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
                                    echo '<li><a class="dropdown-item" href="' . BASE_URL . 'app/View/admin/QuanLyTaiKhoanView.php"><i class="fas fa-solid fa-medal me-2"></i>Quản lý tài khoản</a></li>';
                                    echo '<li><a class="dropdown-item" href="' . BASE_URL . 'app/View/admin/DanhSachBoxChatView.php"><i class="fas fa-solid fa-envelope me-2"></i>Xem tin nhắn từ người dùng</a></li>';
                                    echo '<li><a class="dropdown-item" href="' . BASE_URL . 'app/View/admin/products.php"><i class="fas fa-solid fa-check me-2"></i>Duyệt sản phẩm</a></li>';
                                    echo '<li><a class="dropdown-item" href="' . BASE_URL . 'app/View/admin/manage_products.php"><i class="fas fa-cogs me-2"></i>Quản lý sản phẩm</a></li>';
                                }
                                ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>app/View/product/Product.php"><i
                                            class="fas fa-box me-2"></i>Tin đăng của tôi</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>app/View/order/order_history.php"><i
                                            class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>app/View/user/ProfileUserView.php"><i
                                            class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>public/index.php?page=logout"><i
                                            class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>

                         <!-- Post Ad Button -->
                         <a href="<?php echo BASE_URL; ?>app/View/product/sell.php"
                            class="btn btn-warning d-flex align-items-center justify-content-center fw-bold"
                            title="Đăng tin bán hàng"
                            style="height: 44px; min-width: 140px; padding: 0 20px; font-size: 15px; line-height: 1; gap: 8px; border-radius: 22px; border: none; transition: all 0.3s ease;">
                            <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
                            <span style="line-height: 1; font-weight: 700;">ĐĂNG TIN</span>
                        </a>
                    <?php else: ?>
                        <!-- Guest user dropdown -->
                        <div class="dropdown">
                            <button
                                class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center justify-content-center"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                style="height: 44px; font-size: 15px; border-radius: 22px; border: 1px solid #e5e7eb; padding: 0 16px; background: white; color: #374151; font-weight: 600;">
                                <i class="fas fa-user-circle me-2" style="font-size: 20px;"></i>
                                <span>Tài khoản</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end guest-dropdown-menu">
                                <li>
                                    <a href="<?php echo BASE_URL; ?>public/index.php?page=login" class="btn btn-primary mb-2">Đăng nhập</a>
                                </li>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>public/index.php?page=register" class="btn btn-outline-primary">Đăng kỷ tài khoản</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-question-circle me-2"></i>Trợ giúp</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <script>
    // Auto-hide header on scroll
    (function() {
        const header = document.getElementById('mainHeader');
        if (!header) return;
        
        let lastScrollTop = 0;
        let scrollThreshold = 100; // Scroll 100px before hiding
        let isScrolling = false;
        
        window.addEventListener('scroll', function() {
            if (isScrolling) return;
            
            isScrolling = true;
            requestAnimationFrame(function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > scrollThreshold) {
                    // Scrolling down - hide header
                    if (scrollTop > lastScrollTop) {
                        header.style.transform = 'translateY(-100%)';
                        header.style.transition = 'transform 0.3s ease-in-out';
                    } 
                    // Scrolling up - show header
                    else {
                        header.style.transform = 'translateY(0)';
                        header.style.transition = 'transform 0.3s ease-in-out';
                    }
                } else {
                    // Near top - always show
                    header.style.transform = 'translateY(0)';
                    header.style.transition = 'transform 0.3s ease-in-out';
                }
                
                lastScrollTop = scrollTop;
                isScrolling = false;
            });
        });
        
        // Ensure header is visible on page load
        header.style.transition = 'transform 0.3s ease-in-out';
    })();
    </script>

    <!-- Notifications Popup JS -->
    <script src="<?php echo BASE_URL; ?>public/assets/js/notifications.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/search-autocomplete.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/cart-count-realtime.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/search-placeholder-animation.js"></script>
    
    <!-- Global userId variable for chat system -->
    <script>
        window.userId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
        window.baseUrl = '<?php echo BASE_URL; ?>';
        let userId = window.userId;
    </script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/components/header.js"></script>
    
    <?php
}

