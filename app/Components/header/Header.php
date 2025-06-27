<?php
require_once __DIR__ . '/../../../config/config.php';

function renderHeader($pdo,$categories = [], $cart_count = 0, $unread_notifications = 0)
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
    // Display checkout error messages if any
    if (isset($_SESSION['checkout_error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Lỗi!</strong> ' . htmlspecialchars($_SESSION['checkout_error_message']) .
            '</div>';
        unset($_SESSION['checkout_error_message']);
    }
    // Display general error messages if any (e.g., from login attempts)
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Thông báo!</strong> ' . htmlspecialchars($_SESSION['error_message']) .
            '</div>';
        unset($_SESSION['error_message']);
    }
    ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center w-100">
                <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center me-4" href="TrangChu.php">
                    <i class="fas fa-recycle text-primary me-2" style="font-size: 28px;"></i>
                    <h1 class="mb-0 fw-bold text-gradient" style="font-size: 24px;">Mua Bán đồ cũ</h1>
                </a>

                <!-- Categories Dropdown -->
                <div class="dropdown me-3">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-bars me-2"></i>
                        <span>Danh mục</span>
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $category): ?>
                            <li><a class="dropdown-item"
                                    href="product/category.php?slug=<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Search Form - Expanded -->
                <form id="search-form" class="flex-grow-1 me-4" method="GET" action="extra/search.php"
                    style="max-width: 500px;">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="search-input" name="keyword"
                            placeholder="Tìm kiếm sản phẩm"
                            value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Right side actions -->
                <div class="d-flex align-items-center gap-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Notifications -->
                        <a href="extra/notifications.php" class="btn btn-link text-dark p-2 position-relative"
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
                        <a href="cart/index.php" class="btn btn-link text-dark p-2 position-relative" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                            <?php if ($cart_count > 0): ?>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>

                        <!-- Account Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i>
                                <span>Quản lý tin</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="product/Product.php"><i class="fas fa-box me-2"></i>Tin đăng
                                        của tôi</a></li>
                                <li><a class="dropdown-item" href="order/order_history.php"><i
                                            class="fas fa-history me-2"></i>Lịch sử mua hàng</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng
                                        xuất</a></li>
                            </ul>
                        </div>

                        <!-- Account Info Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i>
                                <span>Tài khoản</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng
                                        xuất</a></li>
                            </ul>
                        </div>
                        <!-- Post Ad Button -->
                        <a href="product/sell.php" class="btn btn-warning fw-bold px-4 py-2">
                            <i class="fas fa-plus me-2"></i>ĐĂNG TIN
                        </a>
                    <?php else: ?>
                        <!-- Guest user buttons -->
                        <a href="user/login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </a>
                        <a href="user/register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký
                        </a>
                        <a href="product/sell.php" class="btn btn-warning fw-bold px-4">
                            <i class="fas fa-plus me-2"></i>ĐĂNG TIN
                        </a>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <?php
}