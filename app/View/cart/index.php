<?php
/**
 * View hiển thị giỏ hàng cho trang web WebMuaBanDoCu
 * 
 * File này hiển thị các sản phẩm trong giỏ hàng của người dùng,
 * cho phép người dùng cập nhật số lượng, xóa sản phẩm và thanh toán
 * 
 * @package WebMuaBanDoCu
 * @author  Developer
 */

// Đường dẫn tới file cấu hình
require_once '../../../config/config.php';

// Import CartModel (phải được import trước CartController)
require_once(__DIR__ . '/../../Models/cart/CartModel.php');

// Import CartController
require_once(__DIR__ . '/../../Controllers/cart/CartController.php'); 

// Import các thành phần UI
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

// Kiểm tra kết nối CSDL
if (!isset($pdo)) {
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Khởi tạo CartController
$cartController = new CartController($pdo);

// Lấy thông tin user hiện tại
$user_id = $cartController->getCurrentUserId();
$is_guest = !$user_id;

// Sử dụng phương thức từ đối tượng CartController
$cartItems = $cartController->getCartItems();
$cartTotal = $cartController->getCartTotal();
$cartItemCount = $cartController->getCartItemCount();

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
                            <?php
                                    $price = $item['current_price'] ?? $item['added_price'] ?? 0;
                                    echo number_format($price, 0, ',', '.') . ' VNĐ';
                                    ?>
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
                        <?php
                                $total = ($item['current_price'] ?? $item['added_price'] ?? 0) * ($item['quantity'] ?? 1);
                                echo number_format($total, 0, ',', '.') . ' VNĐ';
                                ?> </div>

                    <button type="button" class="remove-btn remove-item"
                        data-product-id="<?php echo $item['product_id']; ?>">
                        <i class="fas fa-trash"></i>    
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="order-summary">
                <h2 class="summary-title">Order Summary</h2>

                <div class="summary-row">
                    <span>Tạm tính</span>
                    <span><?php echo number_format(($cartTotal ?? 0), 0, ',', '.'); ?> VNĐ</span>
                </div>

                <div class="summary-row">
                    <span>Shipping</span>
                     <span>Miễn Phí</span>
                </div>

        

                <div class="summary-row total">
                    <span>Tổng tiền</span>
                        <span id="total-amount"><?php echo number_format(($cartTotal ?? 0), 0, ',', '.'); ?> VNĐ</span>
                </div>

                <?php if ($is_guest): ?>
                <div
                    style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 10px; margin: 15px 0; font-size: 14px;">
                    Vui lòng <a href="../auth/login.php">đăng nhập</a> để tiếp tục thanh toán.
                </div>
                <?php endif; ?>

                <button type="button" class="checkout-btn" onclick="window.location.href='../checkout/index.php'"
                    <?php echo $is_guest ? ' disabled' : ''; ?>>
                    Thanh Toán
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php footer(); ?>
    <!-- Script JavaScript xử lý tính năng giỏ hàng -->
    <script src="../../../public/assets/js/cart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>