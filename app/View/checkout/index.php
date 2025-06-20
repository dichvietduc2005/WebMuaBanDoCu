<?php
require_once '../../../config/config.php';
require_once(__DIR__ . '/../..//../app/Controllers/cart/CartController.php'); // For cart-related functions
// Lấy user_id hiện tại (có thể null cho guest users)
$user_id = get_current_user_id();
$is_guest = !$user_id;
$cartItems = getCartItems($pdo, $user_id);
$cartTotal = getCartTotal($pdo, $user_id);
$cartItemCount = getCartItemCount($pdo, $user_id);

// Debug log cho checkout
error_log("=== CHECKOUT DEBUG ===");
error_log("User ID: " . ($user_id ? $user_id : 'NULL (guest)'));
error_log("Is Guest: " . ($is_guest ? 'YES' : 'NO'));
error_log("Cart Items: " . print_r($cartItems, true));
error_log("Cart Total: " . $cartTotal);
error_log("Cart Item Count: " . $cartItemCount);

// Kiểm tra giỏ hàng có trống không
if (empty($cartItems)) {
    error_log("CHECKOUT: Cart is empty, redirecting to cart page");
    header('Location: ../cart/index.php'); // Updated path
    exit;
}

// Lấy thông tin user nếu đã đăng nhập
$userInfo = null;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Web Mua Bán Đồ Cũ</title>    <link href="../../../public/assets/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../../../public/assets/css/jumbotron-narrow.css" rel="stylesheet">
    <script src="../../../public/assets/js/jquery-1.11.3.min.js"></script>
    <link rel="stylesheet" href="../../../public/assets/css/checkout.css">
    
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li><a href="../TrangChu.php">Trang chủ</a></li>
                    <li><a href="../cart/index.php">Giỏ hàng (<?php echo $cartItemCount; ?>)</a></li>
                    <li><a href="../user/order_history.php">Lịch sử GD</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">Thanh toán</h3>
        </div>

        <div class="row">
            <div class="col-md-8">
                <h2>Thông tin thanh toán</h2>
                  <div class="form-section">
                    <h4>Thông tin người nhận</h4>
                    <form id="checkout-form" action="../../modules/payment/vnpay/create_payment.php" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="txt_billing_fullname">Họ và tên *</label>
                                    <input type="text" class="form-control" id="txt_billing_fullname" name="txt_billing_fullname" 
                                           value="<?php echo htmlspecialchars($userInfo['full_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="txt_billing_email">Email *</label>
                                    <input type="email" class="form-control" id="txt_billing_email" name="txt_billing_email" 
                                           value="<?php echo htmlspecialchars($userInfo['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="txt_billing_mobile">Số điện thoại *</label>
                                    <input type="tel" class="form-control" id="txt_billing_mobile" name="txt_billing_mobile" 
                                           value="<?php echo htmlspecialchars($userInfo['phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="txt_bill_city">Thành phố *</label>
                                    <select class="form-control" id="txt_bill_city" name="txt_bill_city" required>
                                        <option value="">Chọn thành phố</option>
                                        <option value="TP.HCM" <?php echo (isset($userInfo['city']) && $userInfo['city'] == 'TP.HCM') ? 'selected' : ''; ?>>TP. Hồ Chí Minh</option>
                                        <option value="Hà Nội" <?php echo (isset($userInfo['city']) && $userInfo['city'] == 'Hà Nội') ? 'selected' : ''; ?>>Hà Nội</option>
                                        <option value="Đà Nẵng" <?php echo (isset($userInfo['city']) && $userInfo['city'] == 'Đà Nẵng') ? 'selected' : ''; ?>>Đà Nẵng</option>
                                        <option value="Cần Thơ" <?php echo (isset($userInfo['city']) && $userInfo['city'] == 'Cần Thơ') ? 'selected' : ''; ?>>Cần Thơ</option>
                                        <option value="Hải Phòng" <?php echo (isset($userInfo['city']) && $userInfo['city'] == 'Hải Phòng') ? 'selected' : ''; ?>>Hải Phòng</option>
                                        <option value="Khác" <?php echo (isset($userInfo['city']) && !in_array($userInfo['city'], ['TP.HCM', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng'])) ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="txt_inv_addr1">Địa chỉ *</label>
                            <textarea class="form-control" id="txt_inv_addr1" name="txt_inv_addr1" rows="3" 
                                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện" required><?php echo htmlspecialchars($userInfo['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="order_notes">Ghi chú đơn hàng</label>
                            <textarea class="form-control" id="order_notes" name="order_notes" rows="3" 
                                      placeholder="Ghi chú về đơn hàng của bạn (tùy chọn)"></textarea>
                        </div>

                        <input type="hidden" name="order_id" value="<?php echo 'ORDER_' . date('YmdHis') . '_' . rand(1000, 9999); ?>">
                        <input type="hidden" name="order_type" value="billpayment">
                        <input type="hidden" name="amount" value="<?php echo $cartTotal; ?>">
                        <input type="hidden" name="order_desc" value="Thanh toan don hang tu Web Mua Ban Do Cu">
                        <input type="hidden" name="bank_code" value="">
                        <input type="hidden" name="language" value="vn">
                        <input type="hidden" name="txtexpire" value="">
                        <input type="hidden" name="txt_bill_country" value="VN">
                        <input type="hidden" name="txt_bill_state" value="">
                        <input type="hidden" name="txt_inv_mobile" value="">
                        <input type="hidden" name="txt_inv_email" value="">
                        <input type="hidden" name="txt_inv_customer" value="">
                        <input type="hidden" name="txt_inv_company" value="">
                        <input type="hidden" name="txt_inv_taxcode" value="">                        <input type="hidden" name="cbo_inv_type" value="I">
                        <input type="hidden" name="redirect" value="1">
                </div>
            </div>

            <div class="col-md-4">
                <h3>Đơn hàng của bạn</h3>
                
                <div class="checkout-summary">                    <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <div class="row">                            <div class="col-xs-3">
                                <?php
                                // Lấy hình ảnh từ bảng product_images
                                $stmt_img = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC LIMIT 1");
                                $stmt_img->execute([$item['product_id']]);
                                $image = $stmt_img->fetch(PDO::FETCH_ASSOC);
                                $image_path = $image ? $image['image_path'] : '';
                                ?>
                                <img src="<?php echo htmlspecialchars(!empty($image_path) && file_exists('../../' . $image_path) ? '../../' . $image_path : '../../assets/images/default_product_image.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image-checkout img-thumbnail">
                            </div>
                            <div class="col-xs-9">
                                <div class="product-name"><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></div>                                <div class="product-details">
                                    Số lượng: <?php echo $item['quantity']; ?> × <?php echo number_format($item['current_price'] ?? $item['added_price'] ?? 0, 0, ',', '.'); ?> VNĐ
                                </div>
                                <div class="product-total text-right">
                                    <strong><?php echo number_format($item['subtotal'] ?? 0, 0, ',', '.'); ?> VNĐ</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="total-summary">
                        <div class="row">
                            <div class="col-xs-6"><strong>Tổng cộng:</strong></div>
                            <div class="col-xs-6 text-right"><strong style="color: #d9534f; font-size: 18px;"><?php echo number_format($cartTotal ?? 0, 0, ',', '.'); ?> VNĐ</strong></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4>Phương thức thanh toán</h4>
                    <div class="radio">
                        <label>
                            <input type="radio" name="payment_method" value="vnpay" checked>
                            <img src="https://sandbox.vnpayment.vn/paymentv2/images/brands/logo.png" alt="VNPay" style="height: 30px; margin-left: 10px;">
                            Thanh toán qua VNPay
                        </label>
                    </div>
                    <div class="text-muted" style="margin-top: 10px; font-size: 12px;">
                        Bạn sẽ được chuyển đến trang thanh toán an toàn của VNPay
                    </div>
                </div>                <button type="submit" class="btn btn-success btn-lg btn-block" style="margin-top: 20px;">
                    <span class="glyphicon glyphicon-credit-card"></span>
                    Thanh toán ngay
                </button>
                </form>
            </div>
        </div>

        <footer class="footer" style="margin-top: 50px;">
            <p>&copy; Web Mua Ban Do Cu <?php echo date('Y')?></p>
        </footer>
    </div>    <script>
    $(document).ready(function() {
        console.log('=== CHECKOUT JAVASCRIPT LOADED ===');
        console.log('jQuery version:', $.fn.jquery);
        console.log('Form exists:', $('#checkout-form').length);
        console.log('Submit button exists:', $('button[type="submit"]').length);
        
        $('#checkout-form').on('submit', function() {
            console.log('=== FORM SUBMIT EVENT - Step 1 ===');
            $('input[name="txt_inv_mobile"]').val($('input[name="txt_billing_mobile"]').val());
            $('input[name="txt_inv_email"]').val($('input[name="txt_billing_email"]').val());
            $('input[name="txt_inv_customer"]').val($('input[name="txt_billing_fullname"]').val());
            console.log('=== FORM SUBMIT EVENT - Step 2 ===');
        });

        $('#checkout-form').on('submit', function(e) {
            console.log('=== FORM VALIDATION EVENT ===');
            var isValid = true;
            var errorMessages = [];
            var firstErrorField = null;

            $('input[required], select[required], textarea[required]').each(function() {
                $(this).removeClass('has-error'); // Reset error class
                var fieldValue = $(this).val().trim();
                var fieldName = $(this).attr('name') || $(this).attr('id');
                console.log('Checking field:', fieldName, 'Value:', fieldValue);
                
                if (!fieldValue) {
                    isValid = false;
                    $(this).addClass('has-error');
                    if (!firstErrorField) firstErrorField = $(this);
                    console.log('Field validation failed:', fieldName);
                }
            });
            
            if (!isValid && errorMessages.indexOf('Vui lòng điền đầy đủ thông tin bắt buộc.') === -1) {
                errorMessages.push('Vui lòng điền đầy đủ thông tin bắt buộc.');
            }

            var email = $('#txt_billing_email').val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                isValid = false;
                $('#txt_billing_email').addClass('has-error');
                if (!firstErrorField) firstErrorField = $('#txt_billing_email');
                if (errorMessages.indexOf('Email không hợp lệ.') === -1) {
                    errorMessages.push('Email không hợp lệ.');
                }
                console.log('Email validation failed:', email);
            }

            var phone = $('#txt_billing_mobile').val();
            var phoneRegex = /^(0|\+84)[0-9]{9}$/; // Updated regex for Vietnamese phone numbers
            if (phone && !phoneRegex.test(phone)) {
                isValid = false;
                $('#txt_billing_mobile').addClass('has-error');
                if (!firstErrorField) firstErrorField = $('#txt_billing_mobile');
                if (errorMessages.indexOf('Số điện thoại không hợp lệ. Phải có 10 chữ số bắt đầu bằng 0 hoặc +84.') === -1) {
                    errorMessages.push('Số điện thoại không hợp lệ. Phải có 10 chữ số bắt đầu bằng 0 hoặc +84.');
                }
                console.log('Phone validation failed:', phone);
            }

            console.log('Validation result:', isValid, 'Errors:', errorMessages);

            if (!isValid) {
                e.preventDefault();
                alert('Lỗi:\n- ' + errorMessages.join('\n- '));
                if (firstErrorField) {
                    firstErrorField.focus();
                }
                return false;
            }            console.log('=== FORM VALIDATION PASSED, SUBMITTING ===');
            $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Đang xử lý...');
        });
        
        // Test button click
        $('button[type="submit"]').on('click', function(e) {
            console.log('=== SUBMIT BUTTON CLICKED ===');
            console.log('Button type:', $(this).attr('type'));
            console.log('Form action:', $('#checkout-form').attr('action'));
        });
    });
    // Add animation for loading icon
    $("<style type='text/css'> .glyphicon-refresh-animate { -animation: spin .7s infinite linear; -webkit-animation: spin2 .7s infinite linear; } @-webkit-keyframes spin2 { from { -webkit-transform: rotate(0deg);} to { -webkit-transform: rotate(360deg);}} @keyframes spin { from { transform: scale(1) rotate(0deg);} to { transform: scale(1) rotate(360deg);}} </style>").appendTo("head");
    </script>
</body>
</html>