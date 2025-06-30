<?php
require_once '../../../config/config.php';
require_once(__DIR__ . '/../../Controllers/cart/CartController.php');

$user_id = get_current_user_id();
$is_guest = !$user_id;

if (!isset($pdo)) {
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}

$cartItems = getCartItems($pdo, $user_id);
$cartTotal = getCartTotal($pdo, $user_id);
$cartItemCount = getCartItemCount($pdo, $user_id);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn - Web Mua Bán Đồ Cũ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/checkout.css">
    <link rel="stylesheet" href="../../../public/assets/css/footer.css">
</head>

<body>
    <?php
    include_once __DIR__ . '/../../Components/header/Header.php';
    renderHeader($pdo);
    ?>

    <!-- Cart Content -->
    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Giỏ hàng của bạn</h1>
            <a href="../TrangChu.php" class="continue-shopping">
                <i class="fas fa-arrow-left me-1"></i> Tiếp tục mua sắm
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="cart-items-container">
                    <?php if (empty($cartItems)): ?>
                    <div class="empty-cart">
                        <span class="empty-cart-icon"><i class="fas fa-box-open"></i></span>
                        <h3>Giỏ hàng của bạn hiện đang trống</h3>
                        <p>Hãy thêm một số sản phẩm vào giỏ hàng của bạn để tiếp tục.</p>
                        <a href="../TrangChu.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                    </div>
                    <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="item-image-container">
                            <?php if (!empty($item['image_path'])): ?>
                            <img src="../../<?php echo htmlspecialchars($item['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($item['product_name'] ?? 'Sản phẩm'); ?>" class="item-image">
                            <?php else: ?>
                            <div class="item-image"
                                style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; width: 100%; height: 100%;">
                                <span>No Image</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="item-details">
                            <h3 class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></h3>

                            <div class="item-price">
                                <span class="price-label">Giá:</span>
                                <span><?php echo number_format($item['current_price'] ?? $item['added_price'] ?? 0, 0, ',', '.') . ' VNĐ'; ?></span>
                            </div>

                            <div class="item-meta">
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['category_name'] ?? ''); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location'] ?? ''); ?></span>
                            </div>

                            <div class="quantity-controls">
                                <button class="quantity-btn quantity-decrease" data-product-id="<?php echo $item['product_id']; ?>">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity'] ?? 1; ?>" min="1" data-product-id="<?php echo $item['product_id']; ?>">
                                <button class="quantity-btn quantity-increase" data-product-id="<?php echo $item['product_id']; ?>">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="item-total-container">
                            <div class="item-total-label">Thành tiền</div>
                            <div class="item-total"><?php echo number_format($item['subtotal'] ?? 0, 0, ',', '.') . ' VNĐ'; ?></div>
                        </div>

                        <button class="remove-btn" data-product-id="<?php echo $item['product_id']; ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="order-summary">
                    <h2 class="summary-title">Tóm tắt đơn hàng</h2>

                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span class="summary-value"><?php echo number_format($cartTotal, 0, ',', '.') . ' VNĐ'; ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Phí vận chuyển</span>
                        <span class="summary-value">Miễn phí</span>
                    </div>

                    <div class="summary-row">
                        <span>Giảm giá</span>
                        <span class="summary-value">0 VNĐ</span>
                    </div>

                    <div class="summary-row total">
                        <span>Tổng cộng</span>
                        <span class="summary-total-value"><?php echo number_format($cartTotal, 0, ',', '.') . ' VNĐ'; ?></span>
                    </div>

                    <button class="checkout-btn">
                        <i class="fas fa-lock me-1"></i> Thanh toán ngay
                    </button>

                    <div class="mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="termsCheck" checked>
                            <label class="form-check-label small" for="termsCheck">
                                Tôi đồng ý với <a href="#" class="text-primary">Điều khoản & Điều kiện</a>
                                và <a href="#" class="text-primary">Chính sách bảo mật</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container">
        <!-- Toast notifications will appear here -->
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="../../../public/assets/js/checkout.js"></script>
<?php
    include_once __DIR__ . '/../../Components/footer/Footer.php';
    Footer();
    
?>
</body>

</html>