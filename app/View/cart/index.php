<?php
/**
 * View hiển thị giỏ hàng - Amazon Style
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
        /* Hiệu ứng Zoom ảnh */
        .product-img-container {
            overflow: hidden;
            border-radius: 8px;
            display: block;
        }
        .product-img-container img {
            transition: transform 0.3s ease;
            transform-origin: center center;
        }
        .product-img-container:hover img {
            transform: scale(1.15);
            cursor: pointer;
        }
        /* Sidebar styling */
        .order-summary-container .fs-4 {
            font-size: 1.5rem !important;
        }
    </style>
</head>
<body class="amazon-bg">
    <?php renderHeader($pdo); ?>

    <div class="container-fluid py-4 shopping-cart-container" style="max-width: 1500px;">
        
        <?php if (empty($cartItems)): ?>
            <div class="cart-container bg-white p-5 rounded text-center shadow-sm">
                <h2 class="mb-3">Giỏ hàng của bạn đang trống</h2>
                <p><a href="../TrangChu.php" class="amazon-link">Tiếp tục mua sắm</a></p>
            </div>
        <?php else: ?>
            
            <div class="row">
                <div class="col-lg-9 col-md-12 mb-4">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <div class="d-flex justify-content-between align-items-end border-bottom pb-2 mb-3">
                            <h2 class="h3 fw-normal mb-0">Giỏ hàng</h2>
                            
                        </div>

                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item py-3 border-bottom" data-product-id="<?= $item['product_id'] ?>">
                                <div class="row">
                                    <div class="col-md-2 col-3 text-center">
                                        <?php if (!empty($item['image_path'])): ?>
                                            <a href="#" class="product-img-container">
                                                <img src="/WebMuaBanDoCu/public/<?= htmlspecialchars($item['image_path']) ?>" 
                                                     class="img-fluid" 
                                                     alt="<?= htmlspecialchars($item['product_title']) ?>"
                                                     style="max-height: 150px; object-fit: contain;">
                                            </a>
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 100px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-8 col-9">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5 class="fs-5 mb-1">
                                                    <a href="#" class="amazon-link-dark text-decoration-none fw-bold">
                                                        <?= htmlspecialchars($item['product_title']) ?>
                                                    </a>
                                                </h5>
                                                
                                                
                                                <?php if ($item['stock_quantity'] > 0): ?>
    <?php if ($item['stock_quantity'] <= 5): ?>
        <div class="text-danger small mb-1">
            Chỉ còn <?= $item['stock_quantity'] ?> sản phẩm - Đặt hàng ngay.
        </div>
    <?php else: ?>
        <div class="text-success small mb-1">
            Còn hàng (Kho: <?= $item['stock_quantity'] ?>)
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="text-danger small mb-1 fw-bold">Tạm thời hết hàng</div>
<?php endif; ?>
                                                <div class="text-secondary small mb-2">Người bán: <?php echo htmlspecialchars($item['seller_name']); ?> </div>
                                                
                                                <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                                                    <div class="amazon-qty-select d-flex align-items-center bg-light border rounded px-2 py-1 shadow-sm">
                                                        <span class="small me-2">SL:</span>
                                                        <span class="quantity-control quantity-decrease px-1" style="cursor:pointer;">-</span>
                                                        <input type="text" 
                                                               class="qty-input quantity-input border-0 bg-transparent text-center mx-1" 
                                                               style="width: 30px; outline: none;" 
                                                               value="<?= $item['quantity'] ?>" 
                                                               readonly>
                                                        <span class="quantity-control quantity-increase px-1" style="cursor:pointer;">+</span>
                                                    </div>
                                                    
                                                    <div class="vr mx-2 text-secondary"></div>
                                                    
                                                    <button class="btn btn-link amazon-link p-0 text-decoration-none small btn-remove remove-item" 
                                                            data-product-id="<?= $item['product_id'] ?>">
                                                        Xóa
                                                    </button>
                                                    
                                                    <div class="vr mx-2 text-secondary"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-md-none fw-bold fs-5">
    <span class="text-secondary fs-6 fw-normal">Giá:</span> 
    <?= formatPrice($item['current_price']) ?>
</div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 d-none d-md-block text-end">
                                        <span class="fw-bold fs-5"><?= formatPrice($item['current_price']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    
                    <div class="bg-white p-3 rounded shadow-sm mb-3 order-summary-container border">
                        <?php if ($cartTotal >= 5000000): ?>
                            <div class="mb-3 text-success small">
                                <i class="fas fa-check-circle"></i> Đơn hàng đủ điều kiện <strong>Miễn phí vận chuyển</strong>.
                            </div>
                        <?php endif; ?>

                        <?php 
                        $discountAmount = $cartController->getDiscountAmount();
                        $finalTotal = $cartController->getFinalTotal();
                        $appliedCoupon = $_SESSION['applied_coupon'] ?? null;
                        ?>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span class="fw-bold" id="cart-subtotal"><?= formatPrice($cartTotal) ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 text-success" id="discount-row" style="<?= $discountAmount > 0 ? '' : 'display:none;' ?>">
                            <span>Giảm giá:</span>
                            <span class="fw-bold">-<span id="discount-amount"><?= formatPrice($discountAmount) ?></span></span>
                        </div>

                        <div class="fs-5 mb-3 d-flex justify-content-between align-items-center border-top pt-2">
                            <span>Tổng cộng:</span>
                            <span class="fw-bold text-danger fs-4" id="cart-final-total"><?= formatPrice($finalTotal) ?></span>
                        </div>

                        <?php if ($is_guest): ?>
                             <a href="../user/login.php" class="btn btn-warning w-100 shadow-sm rounded-3 py-2 border border-warning">
                                Đăng nhập để thanh toán
                            </a>
                        <?php else: ?>
                            <a href="../checkout/index.php" class="btn btn-amazon-primary w-100 shadow-sm rounded-3 py-2 fw-bold">
                                Tiến hành thanh toán (<?= $cartItemCount ?> món)        
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white p-3 rounded shadow-sm mb-3 border">
                        <h6 class="fw-bold mb-3" style="font-size: 16px;">Mã giảm giá / Quà tặng</h6>
                        
                        <div class="input-group mb-2" id="coupon-input-group" style="<?= $appliedCoupon ? 'display:none;' : '' ?>">
                            <input type="text" id="coupon-code-input" class="form-control" placeholder="Nhập mã tại đây" aria-label="Mã giảm giá">
                            <button class="btn btn-outline-secondary" type="button" id="apply-coupon-btn">Áp dụng</button>
                        </div>

                        <div id="applied-coupon-info" class="alert alert-success d-flex justify-content-between align-items-center p-2 mb-0" style="<?= $appliedCoupon ? '' : 'display:none;' ?>">
                            <span class="small">
                                <i class="fas fa-tag me-1"></i> Mã: <strong id="applied-code-text"><?= htmlspecialchars($appliedCoupon['code'] ?? '') ?></strong>
                            </span>
                            <button type="button" class="btn-close btn-sm" id="remove-coupon-btn" aria-label="Close"></button>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded shadow-sm border">
                        <h6 class="fw-bold mb-3" style="font-size: 16px;">Có thể bạn thích</h6>
                        
                        <div class="d-flex gap-2 mb-3 border-bottom pb-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-secondary" style="width: 60px; height: 60px; min-width: 60px;">
                                <i class="fas fa-image"></i>
                            </div>
                            
                            <div class="flex-grow-1">
                                <a href="#" class="amazon-link-dark text-decoration-none small fw-bold d-block mb-1" style="line-height: 1.2;">
                                    Tai nghe Bluetooth Sony chống ồn...
                                </a>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-danger fw-bold small">1.200.000₫</span>
                                    <button class="btn btn-sm btn-warning rounded-pill px-2 py-0" style="font-size: 11px; height: 24px;">
                                        Thêm
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-3 border-bottom pb-3">
                             <div class="bg-light rounded d-flex align-items-center justify-content-center text-secondary" style="width: 60px; height: 60px; min-width: 60px;">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <a href="#" class="amazon-link-dark text-decoration-none small fw-bold d-block mb-1" style="line-height: 1.2;">
                                    Áo thun nam Polo Coolmate...
                                </a>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-danger fw-bold small">299.000₫</span>
                                    <button class="btn btn-sm btn-warning rounded-pill px-2 py-0" style="font-size: 11px; height: 24px;">
                                        Thêm
                                    </button>
                                </div>
                            </div>
                        </div>

                         <div class="text-end">
                             <a href="#" class="amazon-link small">Xem thêm gợi ý <i class="fas fa-angle-double-right"></i></a>
                         </div>

                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php include_once __DIR__ . '/../../Components/dialog/DeleteConfirmModal.php'; ?>
    
    <?php footer(); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WebMuaBanDoCu/public/assets/js/cart.js"></script>
</body>
</html>                                                 