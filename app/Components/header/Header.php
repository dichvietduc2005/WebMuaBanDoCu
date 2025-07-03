<?php
require_once __DIR__ . '/../../../config/config.php';

function renderHeader($pdo, $categories = [], $cart_count = 0, $unread_notifications = 0)
{
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <h1 class="mb-0 fw-bold text-gradient d-none d-sm-inline" style="font-size: clamp(20px, 5vw, 28px); color: #2563eb;">HIHand Shop</h1>
            <h1 class="mb-0 fw-bold text-gradient d-sm-none" style="font-size: clamp(16px, 4vw, 22px); color: #2563eb;">HIHand</h1>
        </a>

        <!-- Mobile Icons -->
        <div class="d-flex align-items-center d-lg-none ms-auto me-1 gap-1">
            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Notifications -->
            <a href="/WebMuaBanDoCu/app/View/extra/notifications.php" 
               class="btn btn-link text-dark position-relative rounded-circle d-flex align-items-center justify-content-center notifications-bell"
               title="Thông báo" style="width: 44px; height: 44px; padding: 0;">
                <i class="fas fa-bell" style="font-size: clamp(18px, 4.5vw, 22px); color: #374151;"></i>
                <?php if ($unread_notifications > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                      style="font-size: clamp(10px, 2.5vw, 12px); padding: 0.25em 0.5em; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                    <?php echo min($unread_notifications, 99); ?><?php echo $unread_notifications > 99 ? '+' : ''; ?>
                </span>
                <?php endif; ?>
            </a>

            <!-- Cart -->
            <a href="/WebMuaBanDoCu/app/View/cart/index.php" 
               class="btn btn-link text-dark position-relative rounded-circle d-flex align-items-center justify-content-center" 
               title="Giỏ hàng" style="width: 44px; height: 44px; padding: 0;">
                <i class="fas fa-shopping-cart" style="font-size: clamp(18px, 4.5vw, 22px); color: #374151;"></i>
                <?php if ($cart_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" 
                      style="font-size: clamp(10px, 2.5vw, 12px); padding: 0.25em 0.5em; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                    <?php echo min($cart_count, 99); ?><?php echo $cart_count > 99 ? '+' : ''; ?>
                </span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarMain">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center w-100 gap-2">
                <!-- Categories Dropdown -->
                <div class="dropdown me-lg-3 mb-2 mb-lg-0 w-100 w-lg-auto">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center w-100 w-lg-auto" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" style="font-size: min(16px, 4vw); height: 42px;">
                        <i class="fas fa-bars me-2" style="font-size: min(16px, 4vw);"></i>
                        <span>Danh mục</span>
                    </button>
                    <ul class="dropdown-menu" style="max-height: 50vh; overflow-y: auto;">
                        <?php foreach ($categories as $category): ?>
                        <li><a class="dropdown-item" style="font-size: min(16px, 4vw);"
                                href="../product/category.php?slug=<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Search Form - Takes available space on desktop, full width on mobile -->
                <form id="search-form2" class="w-100 mb-2 mb-lg-0 me-lg-4" method="GET" 
                    action="/WebMuaBanDoCu/app/View/extra/search.php">
                    <div class="input-group" style="height: 42px;">
                        <input type="text" class="form-control" id="search-input" name="q"
                            placeholder="Tìm kiếm sản phẩm" style="font-size: min(16px, 4vw); border-radius: 8px 0 0 8px;"
                            value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button class="btn btn-primary" type="submit" style="border-radius: 0 8px 8px 0;">
                            <i class="fas fa-search" style="font-size: min(16px, 4vw);"></i>
                        </button>
                    </div>
                </form>

                <!-- Right side actions - Stack vertically on mobile -->
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2 ms-lg-auto w-100 w-lg-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Desktop Icons - hidden on mobile -->
                        <div class="d-none d-lg-flex align-items-center gap-2">
                            <!-- Notifications -->
                            <a href="/WebMuaBanDoCu/app/View/extra/notifications.php" class="btn btn-link text-dark p-1 position-relative notifications-bell"
                                title="Thông báo">
                                <i class="fas fa-bell" style="font-size: 20px;"></i>
                                <?php if ($unread_notifications > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 12px; padding: 0.2em 0.4em;">
                                    <?php echo min($unread_notifications, 99); ?><?php echo $unread_notifications > 99 ? '+' : ''; ?>
                                </span>
                                <?php endif; ?>
                            </a>

                            <!-- Messages -->
                            <button class="btn btn-link text-dark p-1" title="Tin nhắn" id="button-chat" onclick="toggleChat()">
                                <i class="fas fa-comment" style="font-size: 20px;"></i>
                            </button>

                            <!-- Cart -->
                            <a href="/WebMuaBanDoCu/app/View/cart/index.php" class="btn btn-link text-dark p-1 position-relative" title="Giỏ hàng">
                                <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                                <?php if ($cart_count > 0): ?>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" style="font-size: 12px; padding: 0.2em 0.4em;">
                                    <?php echo min($cart_count, 9); ?><?php echo $cart_count > 9 ? '+' : ''; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <!-- Mobile Messages -->
                        <div class="d-lg-none w-100">
                            <button class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center shadow-sm" 
                                    title="Tin nhắn" id="button-chat-mobile" onclick="toggleChat()" 
                                    style="height: 48px; font-size: clamp(14px, 3.5vw, 16px); border-radius: 12px; margin-bottom: 12px; border: 2px solid #3b82f6; transition: all 0.3s ease;">
                                <i class="fas fa-comment me-2" style="font-size: clamp(16px, 4vw, 18px);"></i>
                                <span class="fw-semibold">Tin nhắn</span>
                            </button>
                        </div>
                        <?php require_once __DIR__ . '/../../View/user/ChatView.php'; ?>

                        <!-- Account Dropdowns - Full width on mobile -->
                        <div class="dropdown mb-2 mb-lg-0 w-100 w-lg-auto">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center justify-content-center w-100" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" style="height: 42px; font-size: min(16px, 4vw); border-radius: 8px;">
                                <i class="fas fa-user-circle me-2" style="font-size: min(18px, 4.5vw);"></i>
                                <span>Tài khoản</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: min(16px, 4vw); max-height: 50vh; overflow-y: auto;">
                                <li><h6 class="dropdown-header">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h6></li>
                                <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/product/Product.php"><i class="fas fa-box me-2"></i>Tin đăng của tôi</a></li>
                                <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/order/order_history.php"><i class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                                <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/user/ProfileUserView.php"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
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
                            <a href="/WebMuaBanDoCu/app/View/user/login.php"
                               class="btn btn-primary border-0 d-flex align-items-center justify-content-center fw-semibold w-100 shadow-sm"
                               style="height: 48px; padding: 0 20px; font-size: clamp(14px, 3.5vw, 16px); line-height: 1; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); transition: all 0.3s ease;">
                                <i class="fas fa-sign-in-alt me-2" style="font-size: clamp(16px, 4vw, 18px);"></i>Đăng nhập
                            </a>
                            <a href="/WebMuaBanDoCu/app/View/user/register.php"
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

<!-- Notifications Popup JS -->
<script src="/WebMuaBanDoCu/public/assets/js/notifications.js"></script>

<style>
/* Enhanced Mobile Responsive Styles */
:root {
    --primary-blue: #3b82f6;
    --primary-dark: #2563eb;
    --success-green: #10b981;
    --warning-orange: #f59e0b;
    --text-gray: #374151;
    --shadow-light: 0 2px 8px rgba(0,0,0,0.1);
    --shadow-medium: 0 4px 12px rgba(0,0,0,0.15);
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
    .btn, .dropdown-toggle {
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
    
    .dropdown-item {
        border-radius: 8px;
        padding: 12px 16px;
        font-size: clamp(15px, 3.8vw, 17px);
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background-color: rgba(59, 130, 246, 0.1);
        transform: translateX(4px);
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

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
<?php
}