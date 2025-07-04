<?php

require_once(__DIR__ . '/../../../config/config.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';


// Kiểm tra đăng nhập
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $_SESSION['error_message'] = 'Bạn cần đăng nhập để thanh toán.';
    header('Location: ../auth/login.php');
    exit;
}

// Lấy thông tin giỏ hàng
$cartController = new CartController($pdo);
$cartItems = $cartController->getCartItems();
$cartTotal = $cartController->getCartTotal();

// Kiểm tra giỏ hàng có sản phẩm không
if (empty($cartItems)) {
    $_SESSION['error_message'] = 'Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi thanh toán.';
    header('Location: ../cart/index.php');
    exit;
}

// Lấy thông tin user từ database
$stmt = $pdo->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="../../../public/assets/css/checkout.css" rel="stylesheet">
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php renderHeader($pdo); ?>

    <div class="container mt-5">
        <form action="../../Controllers/payment/create_payment.php" method="POST" id="checkout-form">
            <input type="hidden" name="redirect" value="true">
            <input type="hidden" name="order_id" value="<?php echo 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid()); ?>">
            <input type="hidden" name="amount" value="<?php echo $cartTotal; ?>">
            <input type="hidden" name="order_desc" value="Thanh toan don hang">
            <input type="hidden" name="language" value="vn">
            <input type="hidden" name="order_type" value="billpayment">
            <input type="hidden" name="bank_code" value="">
            <input type="hidden" name="txtexpire" value="">
            <input type="hidden" name="txt_bill_country" value="VN">
            <input type="hidden" name="txt_bill_state" value="">
            
            <div class="row">
                <!-- Cột thông tin khách hàng -->
                <div class="col-md-7">
                    <h2>Thông tin thanh toán</h2>
                    <div class="mb-3">
                        <label for="txt_billing_fullname" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="txt_billing_fullname" name="txt_billing_fullname" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="txt_billing_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="txt_billing_email" name="txt_billing_email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="txt_billing_mobile" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="txt_billing_mobile" name="txt_billing_mobile" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="txt_inv_addr1" class="form-label">Địa chỉ nhận hàng</label>
                        <textarea class="form-control" id="txt_inv_addr1" name="txt_inv_addr1" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="txt_bill_city" class="form-label">Thành phố</label>
                        <input type="text" class="form-control" id="txt_bill_city" name="txt_bill_city" value="" required>
                    </div>
                    <div class="mb-3">
                        <label for="order_notes" class="form-label">Ghi chú đơn hàng (tùy chọn)</label>
                        <textarea class="form-control" id="order_notes" name="order_notes" rows="3"></textarea>
                    </div>
                </div>

                <!-- Cột tóm tắt đơn hàng -->
                <div class="col-md-5">
                    <div class="order-summary">
                        <h2>Đơn hàng của bạn</h2>
                        <ul class="list-group mb-3">
                            <?php foreach ($cartItems as $item): ?>
                                <li class="list-group-item d-flex justify-content-between lh-sm">
                                    <div>
                                        <h6 class="my-0"><?php echo htmlspecialchars($item['product_title']); ?></h6>
                                        <small class="text-muted">Số lượng: <?php echo htmlspecialchars($item['quantity']); ?></small>
                                    </div>
                                    <span class="text-muted"><?php echo number_format($item['subtotal']); ?> VNĐ</span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Tổng cộng (VNĐ)</span>
                                <strong><?php echo number_format($cartTotal); ?> VNĐ</strong>
                            </li>
                        </ul>

                        <h4>Phương thức thanh toán</h4>
                        <div class="my-3">
                            <div class="form-check">
                                <input id="vnpay" name="payment_method" type="radio" class="form-check-input" value="vnpay" checked required>
                                <label class="form-check-label" for="vnpay">Thanh toán qua VNPAY</label>
                            </div>
                            <div class="form-check">
                                <input id="cod" name="payment_method" type="radio" class="form-check-input" value="cod" required disabled>
                                <label class="form-check-label" for="cod">Thanh toán khi nhận hàng (COD) - Tạm khóa</label>
                            </div>
                        </div>
                        <hr class="my-4">
                        <button class="w-100 btn btn-primary btn-lg" type="submit">Thanh toán qua VNPAY</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Toast Container -->
    <div class="toast-container">
        <!-- Toast notifications will appear here -->
    </div>

    <?php footer(); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="../../../public/assets/js/checkout.js"></script>
    <script>
        let userId = <?php echo $_SESSION['user_id'] ?>
    </script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"></script>
</body>
</html>