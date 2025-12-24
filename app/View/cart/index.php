<?php
/**
 * View hiển thị giỏ hàng
 */
require_once '../../../config/config.php';
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

if (!isset($pdo)) {
    die("Lỗi kết nối cơ sở dữ liệu.");
}

$cartController = new CartController($pdo);
$user_id = $cartController->getCurrentUserId();
$is_guest = !$user_id;

// Lấy dữ liệu giỏ hàng
$cartItems = [];
$cartTotal = 0;
$cartItemCount = 0;

if (!$is_guest) {
    try {
        $cartItems = $cartController->getCartItems();
        $cartTotal = $cartController->getCartTotal();
        $cartItemCount = $cartController->getCartItemCount();
    } catch (Exception $e) {
        error_log("Cart error: " . $e->getMessage());
        $is_guest = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/WebMuaBanDoCu/public/assets/css/cart.css" rel="stylesheet">
    <style>
        .cart-empty {
            text-align: center;
            padding: 60px 40px;
            background: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .cart-item {
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }
        .cart-item:hover {
            border-left-color: #0d6efd;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .quantity-display {
            background: white;
            border: 1px solid #dee2e6;
            display: inline-block;
            min-width: 40px;
            text-align: center;
            padding: 6px;
            border-radius: 4px;
        }
        .btn-remove:hover {
            color: #dc3545 !important;
            transform: scale(1.1);
        }
        .product-price {
            font-size: 1.1rem;
            color: #dc3545;
            font-weight: 600;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .cart-empty {
                padding: 40px 20px;
            }
            
            .cart-empty .fa-4x {
                font-size: 3rem;
            }
            
            .cart-item {
                padding: 15px !important;
                margin-bottom: 15px !important;
            }
            
            .cart-item .row {
                flex-direction: column;
                gap: 10px;
            }
            
            .cart-item .col-md-2 {
                align-self: center;
            }
            
            .cart-item .col-md-5 {
                text-align: center;
            }
            
            .cart-item .col-md-2:nth-child(3) {
                align-self: center;
            }
            
            .cart-item .col-md-3 {
                text-align: center;
            }
            
            .cart-item img {
                max-width: 100px;
                height: 100px;
                object-fit: cover;
            }
            
            .product-price {
                font-size: 1.2rem;
                margin: 8px 0;
            }
            
            .quantity-display {
                font-size: 1.1rem;
                padding: 8px 12px;
                min-width: 50px;
            }
            
            .quantity-btn {
                width: 35px;
                height: 35px;
            }
            
            .btn-remove {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
            
            .order-summary-container {
                margin-top: 20px;
            }
            
            .checkout-button-container .btn {
                font-size: 1.1rem;
            }
            
            /* Sidebar responsive */
            .col-lg-4 .bg-white {
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: stretch !important;
                gap: 15px;
            }
            
            .d-flex.justify-content-between.align-items-center h1 {
                text-align: center;
                font-size: 1.5rem;
            }
            
            .cart-empty {
                padding: 30px 15px;
            }
            
            .cart-empty h3 {
                font-size: 1.3rem;
            }
            
            .cart-item {
                padding: 12px !important;
            }
            
            .cart-item h5 {
                font-size: 1.1rem;
            }
            
            .cart-item img {
                max-width: 80px;
                height: 80px;
            }
            
            .product-price {
                font-size: 1.1rem;
            }
            
            .order-summary-container {
                padding: 20px !important;
            }
            
            .order-summary-container h4 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php renderHeader($pdo); ?>

    <div class="container py-4">
        <div class="shopping-cart-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                    Giỏ hàng của bạn
                    <span class="badge bg-primary ms-2"><?= $cartItemCount ?></span>
                </h1>
                <a href="../index.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>
                    <span class="d-none d-sm-inline">Tiếp tục mua sắm</span>
                    <span class="d-sm-none">Tiếp tục</span>
                </a>
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart fa-4x mb-4 text-muted"></i>
                    <h3 class="mb-3">Giỏ hàng đang trống</h3>
                    <p class="text-muted mb-4">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                    <a href="../index.php" class="btn btn-primary px-4">
                        <i class="fas fa-store me-2"></i>Mua sắm ngay
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <!-- Danh sách sản phẩm (Cột trái) -->
                    <div class="col-lg-8 col-12">
                        <div class="bg-white rounded-3 shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 fw-bold">
                                    <i class="fas fa-list text-primary me-2"></i>
                                    Sản phẩm trong giỏ hàng (<?= $cartItemCount ?>)
                                </h5>
                            
                            </div>
                            
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-item p-3 mb-3 rounded-2">
                                    <!-- <div class="row align-items-center "> -->
                                        <!-- Hình ảnh -->
                                        <div class="col-md-2 col-12 text-center text-md-start">
                                            <?php if (!empty($item['image_path'])): ?>
                                                <img src="/WebMuaBanDoCu/public/<?= htmlspecialchars($item['image_path']) ?>" 
                                                     class="img-thumbnail rounded-2" 
                                                     alt="<?= htmlspecialchars($item['product_title']) ?>"
                                                     style="max-width: 100px; height: 100px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="img-thumbnail rounded-2 d-flex align-items-center justify-content-center" style="height: 100px; width: 100px; margin: 0 auto;">
                                                    <i class="fas fa-image fa-lg text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Thông tin sản phẩm -->
                                        <div class="col-md-3 col-10 text-center text-md-start">
                                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($item['product_title']) ?></h5>
                                            <small class="text-muted d-block mb-2">Mã SP: <?= $item['product_id'] ?></small>
                                            <div class="product-price">
                                                <?= formatPrice($item['current_price']) ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Số lượng -->
                                        <div class="col-md-2 col-12 text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <span class="quantity-display">
                                                    <?= $item['quantity'] ?>
                                                </span>
                                               
                                            </div>
                                        </div>
                                        
                                        <!-- nút xóa -->
                                        <div class="col-md-3 col-12 text-center text-md-end">
                                            <div class="d-flex justify-content-end">
                                                <button class="btn btn-sm btn-link text-danger btn-remove remove-item" 
                                                        title="Xóa" 
                                                        data-product-id="<?= $item['product_id'] ?>">
                                                    <i class="fas fa-trash-alt me-1"></i>
                                                    <span class="d-sm-none">Xóa</span>
                                                </button>
                                            </div>
                                        </div>
                                    <!-- </div> -->
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Sidebar bên phải -->
                    <div class="col-lg-4 col-12">
                        <!-- Tổng hợp đơn hàng -->
                        <div class="order-summary-container bg-white rounded-3 shadow-sm p-4 mb-4">
                            <h4 class="h5 mb-3 fw-bold">
                                <i class="fas fa-receipt text-primary me-2"></i>Tóm tắt đơn hàng
                            </h4>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính:</span>
                                    <span><?= formatPrice($cartTotal) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span class="text-success">Miễn phí</span>
                                </div>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Tổng cộng:</span>
                                    <span class="text-primary"><?= formatPrice($cartTotal) ?></span>
                                </div>
                            </div>
                            
                            <div class="checkout-button-container mt-4">
                                <?php if ($is_guest): ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        Vui lòng <a href="../user/login.php" class="alert-link fw-bold">đăng nhập</a> để thanh toán
                                    </div>
                                <?php else: ?>
                                    <a href="../checkout/index.php" class="btn btn-primary w-100 py-3 fw-bold">
                                        <i class="fas fa-credit-card me-2"></i>THANH TOÁN NGAY
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Voucher/Mã giảm giá -->
                        <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                            <h5 class="h6 mb-3 fw-bold">
                                <i class="fas fa-tags text-success me-2"></i>Mã giảm giá
                            </h5>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Nhập mã giảm giá" id="couponCode">
                                <button class="btn btn-outline-success" type="button" id="applyCoupon">
                                    <i class="fas fa-check me-1"></i>Áp dụng
                                </button>
                            </div>
                            <small class="text-muted">Nhập mã để được giảm giá đặc biệt</small>
                        </div>

                        <!-- Thông tin vận chuyển -->
                        <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                            <h5 class="h6 mb-3 fw-bold">
                                <i class="fas fa-truck text-info me-2"></i>Thông tin vận chuyển
                            </h5>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span class="small">Miễn phí vận chuyển toàn quốc</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <span class="small">Giao hàng trong 2-3 ngày</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                <span class="small">Bảo hành chính hãng</span>
                            </div>
                        </div>

                        <!-- Hỗ trợ khách hàng -->
                        <div class="bg-white rounded-3 shadow-sm p-4">
                            <h5 class="h6 mb-3 fw-bold">
                                <i class="fas fa-headset text-danger me-2"></i>Hỗ trợ khách hàng
                            </h5>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-phone text-success me-2"></i>
                                <span class="small">Hotline: 1900-xxxx</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <span class="small">Email: support@example.com</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fab fa-facebook-messenger text-info me-2"></i>
                                <span class="small">Chat trực tuyến 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php footer(); ?>
    <script src="/WebMuaBanDoCu/public/assets/js/main.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WebMuaBanDoCu/public/assets/js/cart.js"></script>
    <script>
        let userId = <?php echo $_SESSION['user_id'] ?>
    </script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"></script>
</body>
</html>