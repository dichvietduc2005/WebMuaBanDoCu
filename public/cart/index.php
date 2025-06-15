<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\public\cart\index.php
require_once('../../config/config.php'); 
require_once('../../modules/cart/functions.php'); // Corrected path

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
    <title>Giỏ hàng của bạn</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../../assets/css/jumbotron-narrow.css" rel="stylesheet">
    <script src="../../assets/js/jquery-1.11.3.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/cart.css">
    
</head>
<body>
    <div class="container">
        <div class="header clearfix">
             <nav>
                <ul class="nav nav-pills pull-right">
                    <li><a href="../index.php">Trang chủ</a></li>
                    <li class="active"><a href="index.php">Giỏ hàng (<span id="header-cart-item-count"><?php echo $cartItemCount; ?></span>)</a></li>
                    <?php if ($is_guest): ?>
                        <li><a href="../user/login.php">Đăng nhập</a></li>
                        <li><a href="../user/register.php">Đăng ký</a></li>
                    <?php else: ?>
                        <li><a href="../user/order_history.php">Lịch sử đơn hàng</a></li>
                        <li><a href="../user/logout.php">Đăng xuất</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <h3 class="text-muted">Giỏ hàng</h3>
        </div>

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
        ?>

        <h2>
            Giỏ hàng của bạn (<span id="main-cart-item-count"><?php echo $cartItemCount; ?></span> sản phẩm)
            <?php if ($is_guest): ?>
                <small class="text-muted">- Khách vãng lai</small>
            <?php endif; ?>
        </h2>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info" style="margin-top: 20px;">
                Giỏ hàng của bạn hiện đang trống. <a href="../index.php" class="alert-link">Tiếp tục mua sắm</a>.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">Sản phẩm trong giỏ hàng</div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th>Giá</th>
                                            <th>Số lượng</th>
                                            <th>Thành tiền</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-items">
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr data-product-id="<?php echo $item['product_id']; ?>">
                                                <td>
                                                    <?php if (!empty($item['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                                    <?php endif; ?>
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                </td>
                                                <td class="product-price"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                                <td>
                                                    <div class="input-group" style="width: 120px;">
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-default btn-sm quantity-decrease" data-product-id="<?php echo $item['product_id']; ?>">-</button>
                                                        </span>
                                                        <input type="number" class="form-control text-center quantity-input" 
                                                               value="<?php echo $item['quantity']; ?>" 
                                                               min="1" 
                                                               data-product-id="<?php echo $item['product_id']; ?>">
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-default btn-sm quantity-increase" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="subtotal"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?> VNĐ</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-item" data-product-id="<?php echo $item['product_id']; ?>">
                                                        <span class="glyphicon glyphicon-trash"></span> Xóa
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-6">
                                    <a href="../index.php" class="btn btn-default">
                                        <span class="glyphicon glyphicon-arrow-left"></span> Tiếp tục mua sắm
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" class="btn btn-warning" id="clear-cart">
                                        <span class="glyphicon glyphicon-remove"></span> Xóa toàn bộ giỏ hàng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Tóm tắt đơn hàng</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-6">Tổng số lượng:</div>
                                <div class="col-xs-6 text-right"><strong id="total-quantity"><?php echo $cartItemCount; ?></strong></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-xs-6">Tổng tiền:</div>
                                <div class="col-xs-6 text-right">
                                    <h4 class="text-primary">
                                        <strong id="total-amount"><?php echo number_format($cartTotal, 0, ',', '.'); ?> VNĐ</strong>
                                    </h4>
                                </div>
                            </div>
                            <hr>
                            
                            <?php if ($is_guest): ?>
                                <div class="alert alert-warning">
                                    <small><strong>Lưu ý:</strong> Bạn đang mua hàng với tư cách khách vãng lai. Đơn hàng sẽ không được lưu trong lịch sử.</small>
                                </div>
                            <?php endif; ?>
                            
                            <a href="../checkout/index.php" class="btn btn-primary btn-lg btn-block">
                                <span class="glyphicon glyphicon-shopping-cart"></span> Tiến hành thanh toán
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
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
        
        // Xử lý xóa toàn bộ giỏ hàng
        $('#clear-cart').click(function() {
            if (confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')) {
                clearCart();
            }
        });
        
        function updateQuantity(productId, quantity) {
            $.ajax({
                url: '../../modules/cart/handler.php', // Path is correct
                method: 'POST',
                data: {
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Reload trang để cập nhật
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
                url: '../../modules/cart/handler.php', // Path is correct
                method: 'POST',
                data: {
                    action: 'remove',
                    product_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Reload trang để cập nhật
                    } else {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi xóa sản phẩm.');
                }
            });
        }
        
        function clearCart() {
            $.ajax({
                url: '../../modules/cart/handler.php', // Path is correct
                method: 'POST',
                data: {
                    action: 'clear'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Reload trang để cập nhật
                    } else {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi xóa giỏ hàng.');
                }
            });
        }
    });
    </script>
</body>
</html>
