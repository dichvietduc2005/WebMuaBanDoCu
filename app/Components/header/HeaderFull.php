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

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top d-none d-lg-block">
        <div class="container-fluid px-2 px-sm-3 px-lg-4">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center me-auto me-lg-4" href="<?php echo BASE_URL; ?>public/index.php?page=home">
                <i class="fas fa-recycle text-primary me-2" style="font-size: clamp(24px, 6vw, 32px);"></i>
                <h1 class="mb-0 fw-bold text-gradient d-none d-sm-inline"
                    style="font-size: clamp(20px, 5vw, 28px); color: #2563eb;">HIHand Shop</h1>
                <h1 class="mb-0 fw-bold text-gradient d-sm-none" style="font-size: clamp(16px, 4vw, 22px); color: #2563eb;">
                    HIHand</h1>
            </a>

            <!-- Main Content -->
            <div class="d-flex flex-row align-items-center w-100 gap-3" style="justify-content: space-between;">
                <!-- Left: Categories Button -->
                <div style="display: flex; gap: 12px; align-items: center;">
                        <!-- Categories Button -->
                        <div class="dropdown">
                            <button
                                class="btn d-flex align-items-center categories-btn"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                style="height: 50px; padding: 0 14px; border-radius: 10px; border: 2px solid #e5e7eb; background: white; color: #4f46e5; font-weight: 600; font-size: 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); white-space: nowrap; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);">
                                <i class="fas fa-list" style="font-size: 16px; margin-right: 6px;"></i>
                                <span>Danh mục</span>
                            </button>
                            <ul class="dropdown-menu categories-dropdown-menu"
                                style="min-width: 280px; max-width: 350px; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 12px 40px rgba(0,0,0,0.12); padding: 0; margin-top: 6px; animation: dropdownSlide 0.25s cubic-bezier(0.4, 0, 0.2, 1) forwards;">
                                
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
                                        <?php foreach ($categories as $index => $category): ?>
                                            <li style="animation: slideIn 0.3s ease forwards; animation-delay: <?php echo $index * 0.03; ?>s;">
                                                <a href="<?php echo BASE_URL; ?>app/View/product/category.php?slug=<?php echo htmlspecialchars($category['slug'] ?? ''); ?>"
                                                   style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; font-size: 14px; color: #4b5563; font-weight: 500; text-decoration: none; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); border-left: 3px solid transparent;"
                                                   onmouseover="this.style.backgroundColor='#f9f8ff'; this.style.color='#4f46e5'; this.style.borderLeftColor='#4f46e5';"
                                                   onmouseout="this.style.backgroundColor='white'; this.style.color='#4b5563'; this.style.borderLeftColor='transparent';">
                                                    <span><?php echo htmlspecialchars($category['name'] ?? 'Unnamed'); ?></span>
                                                    <div style="width: 18px; height: 18px; border: 2px solid #e5e7eb; border-radius: 50%; transition: all 0.2s ease;" 
                                                         onmouseover="this.style.borderColor='#4f46e5';"
                                                         onmouseout="this.style.borderColor='#e5e7eb';"></div>
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
                
                <!-- Center: Search Form (Fixed Width) -->
                <div style="flex: 0 0 auto; width: 500px; max-width: 100%; position: relative;">
                    <style>
                            @keyframes dropdownSlide {
                                from {
                                    opacity: 0;
                                    transform: translateY(-8px);
                                }
                                to {
                                    opacity: 1;
                                    transform: translateY(0);
                                }
                            }
                            
                            @keyframes slideIn {
                                from {
                                    opacity: 0;
                                    transform: scale(0.95);
                                }
                                to {
                                    opacity: 1;
                                    transform: scale(1);
                                }
                            }
                            
                            .categories-dropdown-menu::-webkit-scrollbar {
                                width: 6px;
                            }
                            
                            .categories-dropdown-menu::-webkit-scrollbar-track {
                                background: transparent;
                            }
                            
                            .categories-dropdown-menu::-webkit-scrollbar-thumb {
                                background: #d4c5f9;
                                border-radius: 3px;
                            }
                            
                            .categories-dropdown-menu::-webkit-scrollbar-thumb:hover {
                                background: #c4b5e9;
                            }
                            
                            .categories-btn:hover {
                                border-color: #4f46e5;
                                background: #f9f8ff;
                                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
                            }
                            
                            .categories-btn[aria-expanded="true"] {
                                border-color: #4f46e5;
                                background: #f9f8ff;
                                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
                            }
                        </style>

                    <!-- Search Form - Simple -->
                    <form id="search-form2" style="width: 100%; display: flex; position: relative;" method="GET"
                        action="<?php echo BASE_URL; ?>app/View/extra/search_advanced.php" onsubmit="return validateSearchForm(event);">
                        <input type="text" id="search-input" name="q"
                            placeholder="Tìm sản phẩm..."
                            style="flex: 1; height: 50px; font-size: 15px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0 16px; background: white; color: #1f2937; outline: none;"
                            value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                            autocomplete="off">
                        <button type="submit" style="height: 50px; width: 50px; margin-left: 8px; border: none; border-radius: 8px; background: #e5e7eb; color: #6b7280; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; padding: 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    
                    <script>
                    function validateSearchForm(event) {
                        const input = document.getElementById('search-input').value.trim();
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
                            class="btn btn-warning d-flex align-items-center justify-content-center fw-bold shadow"
                            title="Đăng tin bán hàng"
                            style="height: 50px; min-width: 136px; padding: 0 20px; font-size: 15px; line-height: 1; gap: 8px; border-radius: 12px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border: none; transition: all 0.3s ease;">
                            <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
                            <span style="line-height: 1; font-weight: 600;">Đăng Tin</span>
                        </a>
                    <?php else: ?>
                        <!-- Guest user buttons -->
                        <div class="d-flex gap-3">
                            <a href="<?php echo BASE_URL; ?>public/index.php?page=login"
                                class="btn btn-primary border-0 d-flex align-items-center justify-content-center fw-semibold shadow-sm"
                                style="height: 48px; padding: 0 20px; font-size: 15px; line-height: 1; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); transition: all 0.3s ease;">
                                <i class="fas fa-sign-in-alt me-2" style="font-size: 18px;"></i>Đăng nhập
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/index.php?page=register"
                                class="btn btn-success border-0 d-flex align-items-center justify-content-center fw-semibold shadow-sm"
                                style="height: 48px; padding: 0 20px; font-size: 15px; line-height: 1; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); transition: all 0.3s ease;">
                                <i class="fas fa-user-plus me-2" style="font-size: 18px;"></i>Đăng ký
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Notifications Popup JS -->
    <script src="<?php echo BASE_URL; ?>public/assets/js/notifications.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/search-autocomplete.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/cart-count-realtime.js"></script>
    
    <!-- Global userId variable for chat system -->
    <script>
        window.userId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
        let userId = window.userId;
    </script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/components/header.js"></script>
    
    <?php
}

