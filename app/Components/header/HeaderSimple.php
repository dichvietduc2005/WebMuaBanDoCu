<?php
// HeaderSimple.php - Simple and clean header design
// No need to require config.php as it's already loaded when Header.php is included

use App\Core\UrlHelper;

function renderHeaderSimple($pdo, $categories = [], $cart_count = 0, $unread_notifications = 0)
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

    $is_logged_in = isset($_SESSION['user_id']);
    ?>
    
    <!-- Header Simple CSS -->
    <link rel="stylesheet" href="<?php echo UrlHelper::css('header-simple.css'); ?>">
    <link rel="stylesheet" href="<?php echo UrlHelper::css('notifications.css'); ?>">
    <link rel="stylesheet" href="<?php echo UrlHelper::css('footer.css'); ?>">

    <header class="header-simple d-lg-none">
        <!-- Header Top Bar: Menu, Logo, Actions -->
        <div class="header-top-bar">
            <div class="header-top-container">
                <!-- Left: Hamburger Menu (Logged-in only) -->
                <div class="header-top-left">
                    <?php if ($is_logged_in): ?>
                        <button class="header-menu-btn" type="button" onclick="toggleHeaderMenu()" aria-label="Menu">
                            <i class="fas fa-bars"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Center: Logo -->
                <div class="header-top-center">
                    <a href="<?php echo UrlHelper::route('home'); ?>" class="header-logo">
                        <i class="fas fa-recycle header-logo-icon"></i>
                        <span class="header-logo-text">HIHand</span>
                    </a>
                </div>

                <!-- Right: Action Buttons -->
                <div class="header-top-right">
                    <div class="header-actions">
                <?php if ($is_logged_in): ?>
                    <!-- Notifications -->
                    <a href="<?php echo UrlHelper::to('app/View/extra/notifications.php'); ?>" 
                       class="header-icon-btn" 
                       title="Thông báo">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_notifications > 0): ?>
                            <span class="header-icon-badge"><?php echo min($unread_notifications, 99); ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Cart -->
                    <a href="<?php echo UrlHelper::to('app/View/cart/index.php'); ?>" 
                       class="header-icon-btn" 
                       title="Giỏ hàng">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="header-icon-badge"><?php echo min($cart_count, 99); ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Desktop Account Menu -->
                    <div class="dropdown d-none d-lg-block">
                        <button class="header-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">
                                    Xin chào<?php echo $_SESSION['user_role'] == 'admin' ? ' admin' : '' ?>, 
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </h6>
                            </li>
                            <?php if ($_SESSION['user_role'] == 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo UrlHelper::to('app/View/admin/QuanLyTaiKhoanView.php'); ?>">
                                    <i class="fas fa-solid fa-medal me-2"></i>Quản lý tài khoản</a></li>
                                <li><a class="dropdown-item" href="<?php echo UrlHelper::to('app/View/admin/DanhSachBoxChatView.php'); ?>">
                                    <i class="fas fa-solid fa-envelope me-2"></i>Xem tin nhắn từ người dùng</a></li>
                                <li><a class="dropdown-item" href="<?php echo UrlHelper::to('app/View/admin/products.php'); ?>">
                                    <i class="fas fa-solid fa-check me-2"></i>Duyệt sản phẩm</a></li>
                                <li><a class="dropdown-item" href="<?php echo UrlHelper::to('app/View/admin/manage_products.php'); ?>">
                                    <i class="fas fa-cogs me-2"></i>Quản lý sản phẩm</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo UrlHelper::to('app/View/product/Product.php'); ?>">
                                <i class="fas fa-box me-2"></i>Tin đăng của tôi</a></li>
                            <li><a class="dropdown-item" href="<?php echo UrlHelper::to('app/View/order/order_history.php'); ?>">
                                <i class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                            <li><a class="dropdown-item" href="<?php echo UrlHelper::to('app/View/user/ProfileUserView.php'); ?>">
                                <i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo UrlHelper::route('logout'); ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest: Login/Register Buttons -->
                    <div class="header-auth-buttons">
                        <a href="<?php echo UrlHelper::route('login'); ?>" class="header-btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Đăng nhập</span>
                        </a>
                        <a href="<?php echo UrlHelper::route('register'); ?>" class="header-btn-register">
                            <i class="fas fa-user-plus"></i>
                            <span>Đăng ký</span>
                        </a>
                    </div>
                <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Search Section: Orange Background -->
        <div class="header-search-section">
            <div class="header-search-container">
                <div class="header-search">
                    <form class="header-search-form" method="GET" action="<?php echo UrlHelper::to('app/View/extra/search_advanced.php'); ?>" id="headerSearchFormElement">
                        <input type="text" 
                               class="header-search-input" 
                               name="q" 
                               placeholder="Tìm sản phẩm..."
                               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                               autocomplete="off">
                        <button type="button" class="header-search-btn" id="headerSearchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    
                    <!-- Search Suggestions -->
                    <div class="header-search-suggestions" id="searchSuggestions">
                        <!-- Suggestions will be added by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Sidebar (Logged-in only) -->
        <?php if ($is_logged_in): ?>
        <div class="header-menu-dropdown" id="headerMenuDropdown" onclick="closeHeaderMenu(event)">
            <div class="header-menu-sidebar" onclick="event.stopPropagation()">
                <div class="header-menu-content">
                    <!-- User Info -->
                    <div class="header-menu-user">
                        <div class="header-menu-user-name">
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        </div>
                        <div class="header-menu-user-email">
                            <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                        </div>
                    </div>

                    <!-- Menu List -->
                    <ul class="header-menu-list">
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <li class="header-menu-item">
                                <a href="<?php echo UrlHelper::to('app/View/admin/QuanLyTaiKhoanView.php'); ?>" class="header-menu-link">
                                    <i class="fas fa-solid fa-medal"></i>
                                    <span>Quản lý tài khoản</span>
                                </a>
                            </li>
                            <li class="header-menu-item">
                                <a href="<?php echo UrlHelper::to('app/View/admin/DanhSachBoxChatView.php'); ?>" class="header-menu-link">
                                    <i class="fas fa-solid fa-envelope"></i>
                                    <span>Xem tin nhắn từ người dùng</span>
                                </a>
                            </li>
                            <li class="header-menu-item">
                                <a href="<?php echo UrlHelper::to('app/View/admin/products.php'); ?>" class="header-menu-link">
                                    <i class="fas fa-solid fa-check"></i>
                                    <span>Duyệt sản phẩm</span>
                                </a>
                            </li>
                            <li class="header-menu-item">
                                <a href="<?php echo UrlHelper::to('app/View/admin/manage_products.php'); ?>" class="header-menu-link">
                                    <i class="fas fa-cogs"></i>
                                    <span>Quản lý sản phẩm</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="header-menu-item">
                            <a href="<?php echo UrlHelper::to('app/View/product/Product.php'); ?>" class="header-menu-link">
                                <i class="fas fa-box"></i>
                                <span>Tin đăng của tôi</span>
                            </a>
                        </li>
                        <li class="header-menu-item">
                            <a href="<?php echo UrlHelper::to('app/View/order/order_history.php'); ?>" class="header-menu-link">
                                <i class="fas fa-history"></i>
                                <span>Lịch sử mua hàng</span>
                            </a>
                        </li>
                        <li class="header-menu-item">
                            <a href="<?php echo UrlHelper::to('app/View/user/ProfileUserView.php'); ?>" class="header-menu-link">
                                <i class="fas fa-user"></i>
                                <span>Thông tin cá nhân</span>
                            </a>
                        </li>
                        <li class="header-menu-item">
                            <a href="<?php echo UrlHelper::to('app/View/product/sell.php'); ?>" class="header-menu-link">
                                <i class="fas fa-plus-circle"></i>
                                <span>Đăng tin bán hàng</span>
                            </a>
                        </li>
                        <li class="header-menu-divider"></li>
                        <li class="header-menu-item">
                            <a href="<?php echo UrlHelper::route('logout'); ?>" class="header-menu-link">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </header>

    <!-- JavaScript for Header -->
    <script>
    function toggleHeaderMenu() {
        const dropdown = document.getElementById('headerMenuDropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    function closeHeaderMenu(event) {
        if (event.target.id === 'headerMenuDropdown') {
            const dropdown = document.getElementById('headerMenuDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }
    }

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const dropdown = document.getElementById('headerMenuDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    });

    // Search Expand Animation (Mobile)
    const searchForm = document.querySelector('.header-search-form');
    const searchInput = document.querySelector('.header-search-input');
    const searchBtn = document.getElementById('headerSearchBtn');
    const formElement = document.getElementById('headerSearchFormElement');
    
    if (searchForm && searchInput && searchBtn && formElement) {
        const isMobile = () => window.innerWidth <= 767;

        // Click button to toggle search expand or submit form
        searchBtn.addEventListener('click', function(e) {
            if (isMobile()) {
                if (!searchForm.classList.contains('active')) {
                    e.preventDefault();
                    e.stopPropagation();
                    searchForm.classList.add('active');
                    setTimeout(() => searchInput.focus(), 100);
                } else if (searchInput.value.trim()) {
                    // If already expanded and has text, submit form
                    formElement.submit();
                }
            } else {
                // On desktop, always submit
                if (searchInput.value.trim()) {
                    formElement.submit();
                }
            }
        });

        // Allow Enter key to submit form
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                formElement.submit();
            }
        });

        // Focus input to expand
        searchInput.addEventListener('focus', function() {
            if (isMobile()) {
                searchForm.classList.add('active');
            }
        });

        // Blur to collapse (only on mobile and no input value)
        searchInput.addEventListener('blur', function() {
            if (isMobile() && !this.value.trim()) {
                setTimeout(() => {
                    searchForm.classList.remove('active');
                }, 200);
            }
        });

        // Close search on escape (if empty)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMobile() && searchForm.classList.contains('active')) {
                if (!searchInput.value.trim()) {
                    searchForm.classList.remove('active');
                    searchInput.blur();
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 767) {
                searchForm.classList.remove('active');
            }
        });
    }

    // Hide header on scroll down, show on scroll up
    let lastScrollTop = 0;
    const header = document.querySelector('.header-simple');
    let scrollThreshold = 100; // Scroll threshold in pixels
    
    if (header) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > scrollThreshold) {
                // Scrolled past threshold
                if (scrollTop > lastScrollTop) {
                    // Scrolling down - hide header
                    header.classList.add('header-hidden');
                } else {
                    // Scrolling up - show header
                    header.classList.remove('header-hidden');
                }
            } else {
                // Near top - always show header
                header.classList.remove('header-hidden');
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, false);
    }
    </script>
    
    <!-- JS files are loaded in HeaderFull.php to avoid duplicates -->
    
    <?php
}

