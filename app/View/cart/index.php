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

// Lấy sản phẩm gợi ý (Real products)
require_once __DIR__ . '/../../Models/product/ProductModel.php';
$productModel = new ProductModel();
$suggestions = $productModel->getProducts(4); // Lấy 4 sản phẩm mới nhất/nổi bật

// Xử lý mã giảm giá từ session
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discountAmount = 0;
if ($appliedCoupon) {
    if ($appliedCoupon['discount_type'] === 'percent') {
        $discountAmount = ($cartTotal * $appliedCoupon['discount_value']) / 100;
    } else {
        $discountAmount = $appliedCoupon['discount_value'];
    }
}
$finalTotal = max(0, $cartTotal - $discountAmount);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/assets/css/cart.css" rel="stylesheet">
    
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
                <p><a href="<?php echo BASE_URL; ?>public/index.php?page=home" class="amazon-link">Tiếp tục mua sắm</a></p>
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
                                                <img src="<?php echo BASE_URL; ?>public/<?= htmlspecialchars($item['image_path']) ?>" 
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

                        <div class="fs-6 mb-1 d-flex justify-content-between">
                             <span class="text-muted">Tạm tính:</span>
                             <span><?= formatPrice($cartTotal) ?></span>
                         </div>
                         <?php if ($discountAmount > 0): ?>
                         <div class="fs-6 mb-2 d-flex justify-content-between text-danger">
                             <span>Giảm giá:</span>
                             <span>- <?= formatPrice($discountAmount) ?></span>
                         </div>
                         <?php endif; ?>
                         <div class="fs-5 mb-3 d-flex align-items-center gap-2 border-top pt-2">
                            <span>Tổng cộng:</span>
                            <span class="fw-bold text-danger fs-4"><?= formatPrice($finalTotal) ?></span>
                        </div>

                        <?php if ($is_guest): ?>
                             <a href="<?php echo BASE_URL; ?>public/index.php?page=login" class="btn btn-warning w-100 shadow-sm rounded-3 py-2 border border-warning">
                                Đăng nhập để thanh toán
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>app/View/checkout/index.php" class="btn btn-amazon-primary w-100 shadow-sm rounded-3 py-2 fw-bold">
                                Tiến hành thanh toán (<?= $cartItemCount ?> món)        
                            </a>
                        <?php endif; ?>
                    </div>


                    <div class="bg-white p-3 rounded shadow-sm border">
                        <h6 class="fw-bold mb-3" style="font-size: 16px;">Có thể bạn thích</h6>
                        
                        <?php if (!empty($suggestions)): ?>
                            <?php foreach ($suggestions as $suggestion): ?>
                                <div class="d-flex gap-2 mb-3 border-bottom pb-3">
                                    <div class="product-img-container rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 60px; height: 60px; min-width: 60px;">
                                        <?php if (!empty($suggestion['image_path'])): ?>
                                            <img src="<?php echo BASE_URL; ?>public/<?= htmlspecialchars($suggestion['image_path']) ?>" 
                                                 class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;"
                                                 onerror="this.src='https://placehold.co/100x100?text=No+Image'">
                                        <?php else: ?>
                                            <i class="fas fa-image text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <a href="<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?= $suggestion['id'] ?>" class="amazon-link-dark text-decoration-none small fw-bold d-block mb-1 text-truncate" style="line-height: 1.2; max-width: 160px;">
                                            <?= htmlspecialchars($suggestion['title']) ?>
                                        </a>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="text-danger fw-bold small"><?= number_format($suggestion['price']) ?>₫</span>
                                            <button class="btn btn-sm btn-warning rounded-pill px-2 py-0 add-to-cart-btn" 
                                                    style="font-size: 11px; height: 24px;"
                                                    onclick="addToCart(<?= $suggestion['id'] ?>)">
                                                Thêm
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">Chưa có gợi ý phù hợp.</p>
                        <?php endif; ?>

                         <div class="text-end">
                             <a href="<?php echo BASE_URL; ?>app/View/product/Product.php" class="amazon-link small">Xem thêm gợi ý <i class="fas fa-angle-double-right"></i></a>
                         </div>

                         <script>
                         function addToCart(productId) {
                             fetch('<?php echo BASE_URL; ?>app/Controllers/cart/CartController.php', {
                                 method: 'POST',
                                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                 body: `action=add&product_id=${productId}&quantity=1`
                             })
                             .then(res => res.json())
                             .then(data => {
                                 if (data.success) {
                                     location.reload();
                                 } else {
                                     alert(data.message);
                                 }
                             });
                         }
                         </script>

                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php include_once __DIR__ . '/../../Components/dialog/DeleteConfirmModal.php'; ?>
    
    <?php footer(); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/cart.js"></script>
</body>
</html>                                                 