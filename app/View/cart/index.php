<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\public\cart\index.php
require_once '../../../config/config.php';
require_once(__DIR__ . '/../..//../app/Controllers/cart/CartController.php'); // For cart-related functions
// Lấy user_id hiện tại (có thể null cho guest users)
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
    <title>Giỏ hàng của bạn</title>    <link href="../../../public/assets/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../../../public/assets/css/jumbotron-narrow.css" rel="stylesheet">
    <script src="../../../public/assets/js/jquery-1.11.3.min.js"></script>
    <link rel="stylesheet" href="../../../public/assets/css/cart.css">
    
</head>
<body>    <div class="container">
        <!-- Simple navigation bar -->
        <nav style="background: white; padding: 15px 0; border-bottom: 1px solid #eee; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="../../../public/TrangChu.php" style="text-decoration: none; color: #666; font-size: 14px;">← Tiếp tục mua sắm</a>
                <div style="display: flex; gap: 20px; align-items: center;">
                    <?php if ($is_guest): ?>
                        <a href="../user/login.php" style="text-decoration: none; color: #666; font-size: 14px;">Đăng nhập</a>
                        <a href="../user/register.php" style="text-decoration: none; color: #666; font-size: 14px;">Đăng ký</a>
                    <?php else: ?>
                        <a href="../user/order_history.php" style="text-decoration: none; color: #666; font-size: 14px;">Lịch sử đơn hàng</a>
                        <a href="../user/logout.php" style="text-decoration: none; color: #666; font-size: 14px;">Đăng xuất</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <?php
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
        ?>        <div class="shopping-cart-container">
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
                                     alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>" 
                                     class="item-image">
                            <?php else: ?>
                                <div class="item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="item-details">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></h3>
                                <p class="item-price">$<?php echo number_format(($item['current_price'] ?? $item['added_price'] ?? 0) / 1000, 2); ?></p>
                            </div>
                            
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn quantity-decrease" data-product-id="<?php echo $item['product_id']; ?>">−</button>
                                <input type="number" class="quantity-input" 
                                       value="<?php echo $item['quantity'] ?? 1; ?>" 
                                       min="1" 
                                       data-product-id="<?php echo $item['product_id']; ?>">
                                <button type="button" class="quantity-btn quantity-increase" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                            </div>
                            
                            <div class="item-total">
                                $<?php echo number_format(($item['subtotal'] ?? 0) / 1000, 2); ?>
                            </div>
                            
                            <button type="button" class="remove-btn remove-item" data-product-id="<?php echo $item['product_id']; ?>">×</button>
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
                        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 10px; margin: 15px 0; font-size: 14px;">
                            <strong>Lưu ý:</strong> Bạn đang mua hàng với tư cách khách vãng lai.
                        </div>
                    <?php endif; ?>
                    
                    <button type="button" class="checkout-btn" onclick="window.location.href='../checkout/index.php'">
                        Checkout
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>    <script>
    $(document).ready(function() {
        // Xử lý tăng giảm số lượng
        $('.quantity-increase').click(function() {
            var productId = $(this).data('product-id');
            var input = $('.quantity-input[data-product-id="' + productId + '"]');
            var newQuantity = parseInt(input.val()) + 1;
            updateQuantity(productId, newQuantity);
        });
        
        $('.quantity-decrease').click(function() {
            var productId = $(this).data('product-id');
            var input = $('.quantity-input[data-product-id="' + productId + '"]');
            var newQuantity = parseInt(input.val()) - 1;
            if (newQuantity > 0) {
                updateQuantity(productId, newQuantity);
            }
        });
        
        $('.quantity-input').change(function() {
            var productId = $(this).data('product-id');
            var newQuantity = parseInt($(this).val());
            if (newQuantity > 0) {
                updateQuantity(productId, newQuantity);
            }
        });
        
        // Xử lý xóa sản phẩm
        $('.remove-item').click(function() {
            var productId = $(this).data('product-id');
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeItem(productId);
            }
        });
        
        function updateQuantity(productId, quantity) {
            $.ajax({
                url: '../../modules/cart/handler.php',
                method: 'POST',
                data: {
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi cập nhật giỏ hàng.');
                }
            });
        }
        
        function removeItem(productId) {
            $.ajax({
                url: '../../modules/cart/handler.php',
                method: 'POST',
                data: {
                    action: 'remove',
                    product_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi xóa sản phẩm.');
                }
            });
        }
    });
    </script>
</body>
</html>
