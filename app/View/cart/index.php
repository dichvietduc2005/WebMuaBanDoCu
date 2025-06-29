<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\public\cart\index.php
require_once '../../../config/config.php';
require_once(__DIR__ . '/../..//../app/Controllers/cart/CartController.php'); // For cart-related functions
// Lấy user_id hiện tại (có thể null cho guest users)
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

$user_id = get_current_user_id();
$is_guest = !$user_id;

if (!isset($pdo)) {
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}

$cartItems = getCartItems($pdo, $user_id);
$cartTotal = getCartTotal($pdo, $user_id);
$cartItemCount = getCartItemCount($pdo, $user_id);

// Debug log cho cart
error_log("=== CART DEBUG ===");
error_log("User ID: " . ($user_id ? $user_id : 'NULL (guest)'));
error_log("Is Guest: " . ($is_guest ? 'YES' : 'NO'));
error_log("Cart Items: " . print_r($cartItems, true));
error_log("Cart Total: " . $cartTotal);
error_log("Cart Item Count: " . $cartItemCount);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn</title>
    <link href="../../../public/assets/css/cart.css" rel="stylesheet">
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body>
    <?php
    renderHeader($pdo);
    ?>
    <div class="container">

        <div class="shopping-cart-container">
            <h1 class="cart-header">Shopping Cart</h1>

            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <h3>Giỏ hàng của bạn hiện đang trống</h3>
                    <p>Hãy thêm một số sản phẩm vào giỏ hàng của bạn để tiếp tục.</p>
                    <a href="../TrangChu.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>
                <div class="cart-items-container">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="../../<?php echo htmlspecialchars($item['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>" class="item-image">
                            <?php else: ?>
                                <div class="item-image"
                                    style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>

                            <div class="item-details">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></h3>
                                <p class="item-price">
                                    $<?php echo number_format(($item['current_price'] ?? $item['added_price'] ?? 0) / 1000, 2); ?>
                                </p>
                            </div>

                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn quantity-decrease"
                                    data-product-id="<?php echo $item['product_id']; ?>">−</button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity'] ?? 1; ?>"
                                    min="1" data-product-id="<?php echo $item['product_id']; ?>">
                                <button type="button" class="quantity-btn quantity-increase"
                                    data-product-id="<?php echo $item['product_id']; ?>">+</button>
                            </div>

                            <div class="item-total">
                                $<?php echo number_format(($item['subtotal'] ?? 0) / 1000, 2); ?>
                            </div>

                            <button type="button" class="remove-btn remove-item"
                                data-product-id="<?php echo $item['product_id']; ?>">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <h2 class="summary-title">Order Summary</h2>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format(($cartTotal ?? 0) / 1000, 2); ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>

                    <div class="summary-row">
                        <span>Taxes</span>
                        <span>Calculated at checkout</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="total-amount">$<?php echo number_format(($cartTotal ?? 0) / 1000, 2); ?></span>
                    </div>

                    <?php if ($is_guest): ?>
                        <div
                            style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 10px; margin: 15px 0; font-size: 14px;">
                        </div>
                    <?php endif; ?>

                    <button type="button" class="checkout-btn" onclick="window.location.href='../checkout/index.php'">
                        Checkout
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php footer(); ?>
    <script src="../../../public/assets/js/cart.js"></script>
</body>

</html>