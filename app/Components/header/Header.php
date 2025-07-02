<?php
require_once __DIR__ . '/../../../config/config.php';

function renderHeader($pdo, $categories = [], $cart_count = 0, $unread_notifications = 0)
{
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
        echo '<div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Lỗi!</strong> ' . htmlspecialchars($_SESSION['checkout_error_message']) .
            '</div>';
        unset($_SESSION['checkout_error_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Thông báo!</strong> ' . htmlspecialchars($_SESSION['error_message']) .
            '</div>';
        unset($_SESSION['error_message']);
    }
    ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Mobile Toggler -->
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Logo (visible on all screens) -->
        <a class="navbar-brand d-flex align-items-center me-lg-4 me-2" href="/WebMuaBanDoCu/app/View/Home.php"> 
            <i class="fas fa-recycle text-primary me-2" style="font-size: 28px;"></i>
            <h1 class="mb-0 fw-bold text-gradient d-none d-sm-inline" style="font-size: 24px;">HIHand Shop</h1>
        </a>

        <!-- Main Content -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center w-100">
                <!-- Categories Dropdown -->
                <div class="dropdown me-lg-3 mb-2 mb-lg-0">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-bars me-2"></i>
                        <span>Danh mục</span>
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $category): ?>
                        <li><a class="dropdown-item"
                                href="../product/category.php?slug=<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Search Form - Takes available space on desktop, full width on mobile -->
                <form id="search-form2" class="w-100 mb-2 mb-lg-0 me-lg-4" method="GET" 
                    action="/WebMuaBanDoCu/app/View/extra/search.php">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="search-input" name="q"
                            placeholder="Tìm kiếm sản phẩm"
                            value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Right side actions - Stack vertically on mobile -->
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2 gap-lg-3 ms-lg-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Notifications -->
                        <a href="/WebMuaBanDoCu/app/View/extra/notifications.php" class="btn btn-link text-dark p-2 position-relative"
                            title="Thông báo">
                            <i class="fas fa-bell" style="font-size: 20px;"></i>
                            <?php if ($unread_notifications > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $unread_notifications; ?>
                            </span>
                            <?php endif; ?>
                        </a>

                        <!-- Messages -->
                        <button class="btn btn-link text-dark p-2" title="Tin nhắn">
                            <i class="fas fa-comment" style="font-size: 20px;"></i>
                        </button>
                        
                        <!-- Cart -->
                        <a href="/WebMuaBanDoCu/app/View/cart/index.php" class="btn btn-link text-dark p-2 position-relative" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                            <?php if ($cart_count > 0): ?>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                <?php echo $cart_count; ?>
                            </span>
                            <?php endif; ?>
                        </a>

                        <!-- Account Dropdowns - Combined for mobile -->
                        <div class="dropdown mb-2 mb-lg-0">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i>
                                <span class="d-none d-lg-inline">Tài khoản</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h6></li>
                                <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/product/Product.php"><i class="fas fa-box me-2"></i>Tin đăng của tôi</a></li>
                                <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/order/order_history.php"><i class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/View/user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>
                        
                        <!-- Post Ad Button -->
                        <a href="/WebMuaBanDoCu/app/View/product/sell.php"
                           class="btn btn-warning d-flex align-items-center justify-content-center fw-bold mb-2 mb-lg-0"
                           title="Đăng tin bán hàng"
                           style="height: 42px; min-width: 136px; padding: 0 16px; font-size: 16px; line-height: 1; gap: 6px;">
                            <i class="fas fa-plus" style="font-size: 16px;"></i>
                            <span style="line-height: 1;">Đăng Tin</span>
                        </a>
                    <?php else: ?>
<!-- Guest user buttons -->
<div class="d-flex flex-column flex-lg-row gap-2 w-100">
    <a href="user/login.php"
       class="btn btn-light border d-flex align-items-center fw-bold"
       style="height: 42px; min-width: 120px; padding: 0 16px; font-size: 16px; line-height: 1; border-radius: 8px;">
        <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
    </a>
    <a href="user/register.php"
       class="btn btn-primary border d-flex align-items-center fw-bold"
       style="height: 42px; min-width: 120px; padding: 0 16px; font-size: 16px; line-height: 1; border-radius: 8px;">
        <i class="fas fa-user-plus me-2"></i>Đăng ký
    </a>

</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>
<?php
}