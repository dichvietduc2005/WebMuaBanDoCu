<?php
function renderHeader($user = null) {
    // Nếu không truyền user, lấy từ session
    if (!$user && isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }
      // Lấy số lượng giỏ hàng
    $cartCount = 0;
    if (isset($_SESSION['user']['id'])) {
        global $pdo;
        if ($pdo) {
            // Sử dụng function helper đã có
            require_once __DIR__ . '/../../helpers.php';
            $cartCount = getCartItemCount($pdo, $_SESSION['user']['id']);
        }
    }
    ?>
    <header class="bg-white shadow-sm sticky-top">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light py-3">                <div class="container-fluid">
                    <a class="navbar-brand fw-bold text-primary" href="/WebMuaBanDoCu/app/router.php?controller=extra&action=home">
                        <i class="bi bi-recycle me-1"></i> MuaBánĐồCũ
                    </a>
                    
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="navbarMain">                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="/WebMuaBanDoCu/app/router.php?controller=product&action=index">Sản phẩm</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/WebMuaBanDoCu/app/router.php?controller=extra&action=categories">Danh mục</a>
                            </li>
                            <?php if ($user): ?>
                            <li class="nav-item">
                                <a class="nav-link text-success fw-bold" href="/WebMuaBanDoCu/app/router.php?controller=sell&action=index">
                                    <i class="bi bi-plus-circle"></i> Đăng bán
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/WebMuaBanDoCu/app/router.php?controller=extra&action=help">Hướng dẫn</a>
                            </li>
                        </ul>
                        
                        <form class="d-flex me-2" action="/WebMuaBanDoCu/app/router.php" method="GET">
                            <input type="hidden" name="controller" value="extra">
                            <input type="hidden" name="action" value="search">
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" placeholder="Tìm sản phẩm...">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>                        <div class="d-flex align-items-center">
                            <a href="/WebMuaBanDoCu/app/router.php?controller=cart&action=index" class="btn btn-light position-relative me-2">
                                <i class="bi bi-cart"></i>
                                <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $cartCount; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                            
                            <?php if ($user): ?>
                                <div class="dropdown">
                                    <a href="#" class="d-block text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                        <img src="<?= $user['avatar'] ?? '/assets/images/default-avatar.jpg' ?>" 
                                             alt="User" width="32" height="32" class="rounded-circle me-1">
                                        <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/router.php?controller=user&action=profile">Tài khoản</a></li>
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/router.php?controller=user&action=manageProducts">Sản phẩm của tôi</a></li>
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/router.php?controller=order&action=index">Đơn hàng</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/WebMuaBanDoCu/app/router.php?controller=user&action=logout">Đăng xuất</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <a href="/WebMuaBanDoCu/app/router.php?controller=user&action=login" class="btn btn-outline-primary me-2">Đăng nhập</a>
                                <a href="/WebMuaBanDoCu/app/router.php?controller=user&action=register" class="btn btn-primary">Đăng ký</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    <?php
}