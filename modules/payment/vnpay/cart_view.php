<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\vnpay_php\cart_view.php
require_once('../modules/config.php'); // For $pdo, session_start(), etc.
require_once('cart_functions.php');   // For database-driven cart functions

// Lấy user_id hiện tại (nếu đã đăng nhập)
$user_id = get_current_logged_in_user_id();
// Biến $pdo đã được khởi tạo trong config.php

if (!isset($pdo)) {
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}

$cartItems = getCartContents($pdo, $user_id);
$cartTotal = getCartTotal($pdo, $user_id);
$cartItemCount = getCartItemCount($pdo, $user_id);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet"/>
    <link href="../assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="../assets/jquery-1.11.3.min.js"></script>    <style>
        .product-image-sm { max-width: 80px; max-height: 80px; margin-right: 15px; object-fit: cover; }
        .quantity-input { width: 70px; text-align: center; }
        .cart-summary { margin-top: 20px; padding: 20px; background-color: #f9f9f9; border: 1px solid #eee; border-radius: 5px; }
        .table > tbody > tr > td { vertical-align: middle; }
        .header .nav-pills > li > a { font-size: 14px; }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            display: none;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            min-width: 300px;
            max-width: 350px;
            overflow: hidden;
            border-left: 4px solid #5cb85c;
            animation: slideIn 0.3s ease-out;
        }
        .toast.error {
            border-left-color: #d9534f;
        }
        .toast-header {
            background: #f8f9fa;
            padding: 8px 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: bold;
        }
        .toast-body {
            padding: 12px 15px;
            font-size: 14px;
            color: #333;
        }
        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toast-close:hover {
            color: #666;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        .toast.hiding {
            animation: slideOut 0.3s ease-in;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
             <nav>
                <ul class="nav nav-pills pull-right">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li class="active"><a href="cart_view.php">Giỏ hàng (<span id="header-cart-item-count"><?php echo $cartItemCount; ?></span>)</a></li>
                    <li><a href="payment_history.php">Lịch sử GD</a></li>
                    <!-- Thêm link đăng nhập/đăng ký nếu cần -->
                </ul>
            </nav>
            <h3 class="text-muted">Giỏ hàng</h3>
        </div>

        <h2>Giỏ hàng của bạn (<span id="main-cart-item-count"><?php echo $cartItemCount; ?></span> sản phẩm)</h2>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info" style="margin-top: 20px;">
                Giỏ hàng của bạn hiện đang trống. <a href="index.php" class="alert-link">Tiếp tục mua sắm</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 10%;">Ảnh</th>
                            <th style="width: 30%;">Tên sản phẩm</th>
                            <th style="width: 15%;" class="text-right">Đơn giá</th>
                            <th style="width: 15%;" class="text-center">Số lượng</th>
                            <th style="width: 20%;" class="text-right">Thành tiền</th>
                            <th style="width: 10%;" class="text-center">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr id="cart-item-row-<?php echo $item['product_id']; ?>">
                            <td>
                                <img src="<?php echo htmlspecialchars(!empty($item['image']) && file_exists('../' . $item['image']) ? '../' . $item['image'] : '../assets/default_product_image.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image-sm img-thumbnail">
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="text-right"><?php echo number_format($item['added_price'], 0, ',', '.'); ?> VNĐ</td>
                            <td class="text-center">
                                <input type="number" 
                                       name="quantities[<?php echo $item['product_id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       class="form-control quantity-input update-quantity-btn" 
                                       data-product-id="<?php echo $item['product_id']; ?>">
                            </td>
                            <td class="text-right item-total-price" id="item-total-<?php echo $item['product_id']; ?>">
                                <?php echo number_format($item['added_price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm remove-item-btn" data-product-id="<?php echo $item['product_id']; ?>">
                                    &times;
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row cart-actions-summary" style="margin-top:20px;">
                <div class="col-md-6 col-sm-12">
                    <a href="index.php" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Tiếp tục mua sắm</a>
                    <a href="cart_handler.php?action=clear" class="btn btn-warning clear-cart-btn" style="margin-left:10px;">
                        <span class="glyphicon glyphicon-trash"></span> Xóa hết giỏ hàng
                    </a>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="cart-summary text-right">
                        <h3>Tổng cộng: <strong id="cart-grand-total" style="color: #d9534f;"><?php echo number_format($cartTotal, 0, ',', '.'); ?> VNĐ</strong></h3>
                        <a href="checkout.php" class="btn btn-success btn-lg btn-block" style="margin-top:15px;">
                            Tiến hành thanh toán <span class="glyphicon glyphicon-chevron-right"></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>        <footer class="footer" style="margin-top: 50px;">
            <p>&copy; Web Mua Ban Do Cu <?php echo date('Y')?></p>
        </footer>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

<script>
$(document).ready(function() {
    // Hàm tạo và hiển thị toast notification
    function showToast(message, type = 'success') {
        var toastId = 'toast-' + Date.now();
        var iconHtml = type === 'success' ? '✓' : '✗';
        var title = type === 'success' ? 'Thành công' : 'Lỗi';
        
        var toastHtml = `
            <div class="toast ${type === 'error' ? 'error' : ''}" id="${toastId}">
                <div class="toast-header">
                    <span>${iconHtml} ${title}</span>
                    <button type="button" class="toast-close" onclick="closeToast('${toastId}')">&times;</button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        $('#toast-container').prepend(toastHtml);
        $('#' + toastId).fadeIn(300);
        
        // Tự động ẩn sau 4 giây
        setTimeout(function() {
            closeToast(toastId);
        }, 4000);
    }
    
    // Hàm đóng toast
    window.closeToast = function(toastId) {
        var toast = $('#' + toastId);
        toast.addClass('hiding');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }

    function updateCartView(itemCount, grandTotal) {
        $('#header-cart-item-count').text(itemCount);
        $('#main-cart-item-count').text(itemCount);
        if (grandTotal !== undefined) {
            $('#cart-grand-total').text(new Intl.NumberFormat('vi-VN').format(grandTotal) + ' VNĐ');
        }

        if (itemCount === 0) {
            $('.table-responsive').replaceWith('<div class="alert alert-info" style="margin-top: 20px;">Giỏ hàng của bạn hiện đang trống. <a href="index.php" class="alert-link">Tiếp tục mua sắm</a>.</div>');
            $('.cart-actions-summary').remove(); // Xóa cả phần tổng tiền và nút
        }
    }

    function handleCartAction(productId, quantity, actionType) {
        $.ajax({
            url: 'cart_handler.php',
            type: 'POST',
            data: {
                action: actionType,
                product_id: productId,
                quantity: quantity // Chỉ dùng cho 'update' và 'add' (mặc dù add thường dùng GET từ trang sản phẩm)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateCartView(response.item_count, response.total);
                    
                    if (actionType === 'remove') {
                        $('#cart-item-row-' + productId).remove();
                        showToast('Đã xóa sản phẩm khỏi giỏ hàng');
                    } else if (actionType === 'update') {
                        // Cập nhật thành tiền của item
                        var itemRow = $('#cart-item-row-' + productId);
                        var pricePerItemText = itemRow.find('td:nth-child(3)').text();
                        var pricePerItem = parseFloat(pricePerItemText.replace(/[^0-9]/g, ''));
                        if (!isNaN(pricePerItem)) {
                             itemRow.find('.item-total-price').text(new Intl.NumberFormat('vi-VN').format(pricePerItem * quantity) + ' VNĐ');
                        } else {
                            // Nếu không lấy được giá từ ô, có thể reload để đảm bảo chính xác
                            // Hoặc dựa vào response.total để cập nhật tổng, item_count đã cập nhật ở updateCartView
                            // location.reload(); 
                        }
                        showToast('Đã cập nhật số lượng sản phẩm');
                    }
                    // Không cần reload nếu thành công, trừ khi có lỗi logic cập nhật giao diện phức tạp
                } else {
                    showToast(response.message, 'error');
                    if(actionType === 'update'){
                        // Có thể reload để input số lượng quay về giá trị cũ nếu update lỗi
                        location.reload(); 
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showToast('Có lỗi xảy ra khi thực hiện thao tác với giỏ hàng. Vui lòng thử lại.', 'error');
                location.reload();
            }
        });
    }

    $('.update-quantity-btn').on('change', function() {
        var productId = $(this).data('product-id');
        var quantity = parseInt($(this).val());
        if (quantity < 1) {
            quantity = 1;
            $(this).val(1); // Reset input về 1 nếu người dùng nhập số nhỏ hơn
        }
        handleCartAction(productId, quantity, 'update');
    });

    $('.remove-item-btn').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            handleCartAction(productId, 0, 'remove'); // quantity không quan trọng khi remove
        }
    });
    
    $('.clear-cart-btn').on('click', function(e){
        e.preventDefault();
        if(confirm('Bạn có chắc muốn xóa toàn bộ sản phẩm trong giỏ hàng?')) {
            // Đối với clear, chúng ta có thể dùng AJAX hoặc để nó redirect như hiện tại
            // Để dùng AJAX:
            // handleCartAction(null, null, 'clear');
            // Hoặc giữ nguyên redirect:
            window.location.href = $(this).attr('href');
        }
    });
});
</script>
</body>
</html>
