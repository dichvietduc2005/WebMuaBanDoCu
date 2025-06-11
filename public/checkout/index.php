<?php
require_once("../../config/config.php");
require_once('../../modules/cart/functions.php');

// Lấy user_id hiện tại (nếu đã đăng nhập)
$user_id = get_current_logged_in_user_id();
$cartItems = getCartContents($pdo, $user_id);
$cartTotal = getCartTotal($pdo, $user_id);
$cartItemCount = getCartItemCount($pdo, $user_id);

// Kiểm tra giỏ hàng có trống không
if (empty($cartItems)) {
    header('Location: cart_view.php');
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
    <title>Thanh toán - Web Mua Bán Đồ Cũ</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet"/>
    <link href="../assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="../assets/jquery-1.11.3.min.js"></script>
    <style>
        .checkout-summary { 
            background: #f9f9f9; 
            padding: 20px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
        .form-section { 
            background: #fff; 
            padding: 20px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
        .order-item { 
            border-bottom: 1px solid #eee; 
            padding: 10px 0; 
        }
        .order-item:last-child { 
            border-bottom: none; 
        }
        .product-image-checkout { 
            width: 60px; 
            height: 60px; 
            object-fit: cover; 
            margin-right: 10px; 
        }
        .header .nav-pills > li > a { font-size: 14px; }
        .total-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="cart_view.php">Giỏ hàng (<?php echo $cartItemCount; ?>)</a></li>
                    <li><a href="payment_history.php">Lịch sử GD</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">Thanh toán</h3>
        </div>

        <div class="row">
            <div class="col-md-8">
                <h2>Thông tin thanh toán</h2>
                
                <!-- Form thông tin khách hàng -->
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
                                        <option value="TP.HCM">TP. Hồ Chí Minh</option>
                                        <option value="Hà Nội">Hà Nội</option>
                                        <option value="Đà Nẵng">Đà Nẵng</option>
                                        <option value="Cần Thơ">Cần Thơ</option>
                                        <option value="Hải Phòng">Hải Phòng</option>
                                        <option value="Khác">Khác</option>
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

                        <!-- Hidden fields for VNPay -->
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
                        <input type="hidden" name="txt_inv_taxcode" value="">
                        <input type="hidden" name="cbo_inv_type" value="I">
                        <input type="hidden" name="redirect" value="1">
                </div>
            </div>

            <div class="col-md-4">
                <h3>Đơn hàng của bạn</h3>
                
                <!-- Tóm tắt đơn hàng -->
                <div class="checkout-summary">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <div class="row">
                            <div class="col-xs-3">
                                <img src="<?php echo htmlspecialchars(!empty($item['image']) && file_exists('../' . $item['image']) ? '../' . $item['image'] : '../assets/default_product_image.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image-checkout img-thumbnail">
                            </div>
                            <div class="col-xs-9">
                                <div class="product-name"><strong><?php echo htmlspecialchars($item['name']); ?></strong></div>
                                <div class="product-details">
                                    Số lượng: <?php echo $item['quantity']; ?> × <?php echo number_format($item['added_price'], 0, ',', '.'); ?> VNĐ
                                </div>
                                <div class="product-total text-right">
                                    <strong><?php echo number_format($item['added_price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="total-summary">
                        <div class="row">
                            <div class="col-xs-6"><strong>Tổng cộng:</strong></div>
                            <div class="col-xs-6 text-right"><strong style="color: #d9534f; font-size: 18px;"><?php echo number_format($cartTotal, 0, ',', '.'); ?> VNĐ</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
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
                </div>

                <button type="submit" class="btn btn-success btn-lg btn-block" style="margin-top: 20px;">
                    <span class="glyphicon glyphicon-credit-card"></span>
                    Thanh toán ngay
                </button>
                    </form>
            </div>
        </div>

        <footer class="footer" style="margin-top: 50px;">
            <p>&copy; Web Mua Ban Do Cu <?php echo date('Y')?></p>
        </footer>
    </div>

    <script>
    $(document).ready(function() {
        // Copy billing info to invoice fields when form submits
        $('#checkout-form').on('submit', function() {
            $('input[name="txt_inv_mobile"]').val($('input[name="txt_billing_mobile"]').val());
            $('input[name="txt_inv_email"]').val($('input[name="txt_billing_email"]').val());
            $('input[name="txt_inv_customer"]').val($('input[name="txt_billing_fullname"]').val());
        });

        // Form validation
        $('#checkout-form').on('submit', function(e) {
            var isValid = true;
            var errorMessages = [];

            // Validate required fields
            $('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val().trim()) {
                    isValid = false;
                    $(this).addClass('has-error');
                    errorMessages.push('Vui lòng điền đầy đủ thông tin bắt buộc');
                } else {
                    $(this).removeClass('has-error');
                }
            });

            // Validate email
            var email = $('#txt_billing_email').val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                isValid = false;
                $('#txt_billing_email').addClass('has-error');
                errorMessages.push('Email không hợp lệ');
            }

            // Validate phone
            var phone = $('#txt_billing_mobile').val();
            var phoneRegex = /^[0-9]{10,11}$/;
            if (phone && !phoneRegex.test(phone)) {
                isValid = false;
                $('#txt_billing_mobile').addClass('has-error');
                errorMessages.push('Số điện thoại không hợp lệ');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Lỗi: ' + errorMessages.join(', '));
                return false;
            }

            // Show loading
            $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="glyphicon glyphicon-refresh"></span> Đang xử lý...');
        });
    });
    </script>
</body>
</html>
