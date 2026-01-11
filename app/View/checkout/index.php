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
    <title>Thanh toán</title>
    <link href="../../../public/assets/css/checkout.css" rel="stylesheet">
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Mobile Responsive CSS for Checkout Page -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-checkout-page.css">
</head>
<body>
    <?php renderHeader($pdo); ?>

    <div class="container mt-5 mb-5">
        <!-- Test Instructions Accordion -->
        <div class="accordion mb-4" id="testInstructions">
            <div class="accordion-item shadow-sm border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-info-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstructions">
                        <i class="fas fa-vial me-2"></i> <strong>Hướng dẫn sử dụng thẻ test (Dành cho nhà phát triển)</strong>
                    </button>
                </h2>
                <div id="collapseInstructions" class="accordion-collapse collapse" data-bs-parent="#testInstructions">
                    <div class="accordion-body bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li><b>Số thẻ:</b> <code class="bg-dark text-warning p-1 rounded">9704 1985 2619 1432 198</code></li>
                                    <li><b>Tên chủ thẻ:</b> <code>NGUYEN VAN A</code></li>
                                    <li><b>Ngày phát hành:</b> <code>07/15</code></li>
                                </ul>
                            </div>
                            <div class="col-md-6 border-start">
                                <p class="mb-0 small text-muted">
                                    <i class="fas fa-info-circle"></i> Sử dụng thông tin này tại cổng VNPay Sandbox.<br>
                                    Mã OTP có thể nhập bất kỳ (ví dụ: <code>123456</code>).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
            <!-- Hidden field to store full address combined from dropdowns -->
            <input type="hidden" id="txt_inv_addr1" name="txt_inv_addr1" value="">
            <input type="hidden" id="txt_bill_city" name="txt_bill_city" value="">
            
            <div class="row g-4">
                <!-- Cột thông tin khách hàng (8/12) -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h4 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2 text-primary"></i>Thông tin thanh toán</h4>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <div class="mb-4">
                                <label for="txt_billing_fullname" class="form-label fw-semibold">Họ và tên người nhận</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0" id="txt_billing_fullname" name="txt_billing_fullname" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Nhập đầy đủ họ tên" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="txt_billing_email" class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control bg-light border-start-0" id="txt_billing_email" name="txt_billing_email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="vidu@email.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="txt_billing_mobile" class="form-label fw-semibold">Số điện thoại</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                                        <input type="tel" class="form-control bg-light border-start-0" id="txt_billing_mobile" name="txt_billing_mobile" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Nhập số điện thoại" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="province" class="form-label fw-semibold">Tỉnh/Thành phố</label>
                                    <select class="form-select bg-light" id="province" required>
                                        <option value="">Chọn Tỉnh/Thành</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ward" class="form-label fw-semibold">Phường/Xã/Thị trấn</label>
                                    <select class="form-select bg-light" id="ward" required disabled>
                                        <option value="">Chọn Phường/Xã</option>
                                    </select>
                                </div>
                                <!-- District removed for API v2 (Direct Province -> Ward) -->
                                <select id="district" class="d-none" disabled><option value="">-</option></select>
                            </div>

                            <div class="mb-4">
                                <label for="specific_address" class="form-label fw-semibold">Địa chỉ cụ thể (Số nhà, tên đường...)</label>
                                <input type="text" class="form-control bg-light" id="specific_address" name="specific_address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="Ví dụ: 123 Đường ABC..." required>
                            </div>

                            <div class="mb-4">
                                <label for="order_notes" class="form-label fw-semibold">Ghi chú đơn hàng (tùy chọn)</label>
                                <textarea class="form-control bg-light" id="order_notes" name="order_notes" rows="2" placeholder="Lưu ý cho người bán hoặc người giao hàng..."></textarea>
                            </div>

                            <div class="form-check custom-checkbox mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="save_info" name="save_info">
                                <label class="form-check-label text-muted small" for="save_info">
                                    Lưu thông tin này cho lần thanh toán sau
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột tóm tắt đơn hàng (4/12) -->
                <div class="col-lg-4">
                    <div class="order-summary-card shadow-sm border-0 rounded-4">
                        <div class="p-4 border-bottom">
                            <h4 class="mb-0 fw-bold">Đơn hàng của bạn</h4>
                        </div>
                        
                        <div class="summary-items p-4" style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0 position-relative">
                                        <?php 
                                            // Dữ liệu image_path trong DB đã bao gồm 'uploads/products/'
                                            $imagePath = !empty($item['image_path']) ? BASE_URL . 'public/' . $item['image_path'] : 'https://placehold.co/100x100?text=No+Image';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                             class="rounded-3 border" width="60" height="60" style="object-fit: cover;" 
                                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                             onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary border border-light">
                                            <?php echo htmlspecialchars($item['quantity']); ?>
                                        </span>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-0 text-truncate" style="max-width: 150px; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($item['product_title']); ?>
                                        </h6>
                                        <small class="text-muted fw-bold"><?php echo number_format($item['added_price']); ?> đ</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-semibold" style="font-size: 0.9rem;"><?php echo number_format($item['subtotal']); ?> đ</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="p-4 bg-light-subtle rounded-bottom-4">
                            <!-- Coupon area -->
                            <div class="input-group input-group-sm mb-4">
                                <input type="text" class="form-control" placeholder="Nhập mã ưu đãi" value="<?php echo $appliedCoupon['code'] ?? ''; ?>">
                                <button class="btn btn-outline-primary" type="button">Áp dụng</button>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính</span>
                                <span class="fw-medium"><?php echo number_format($cartTotal); ?> đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phí vận chuyển</span>
                                <span class="text-success fw-bold">Miễn phí</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                <span class="text-muted">Giảm giá</span>
                                <span class="text-danger fw-medium">- <?php echo number_format($discountAmount); ?> đ</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="h5 mb-0 fw-bold">Tổng tiền</span>
                                <span class="h4 mb-0 fw-bold text-danger"><?php echo number_format($finalTotal); ?> đ</span>
                            </div>

                            <h5 class="fw-bold mb-3">Phương thức thanh toán</h5>
                            <div class="payment-methods mb-4">
                                <div class="form-check payment-option p-3 border rounded-3 mb-2 active">
                                    <input id="vnpay" name="payment_method" type="radio" class="form-check-input" value="vnpay" checked required>
                                    <label class="form-check-label d-flex align-items-center justify-content-between w-100 cursor-pointer" for="vnpay">
                                        <div class="me-2">
                                            <span class="fw-semibold">Thanh toán qua VNPAY</span>
                                            <p class="mb-0 small text-muted">Hỗ trợ ATM, Thẻ Quốc tế, VNPAY-QR</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 bg-white p-1 rounded-2 shadow-sm">
                                            <img src="https://sandbox.vnpayment.vn/paymentv2/Images/brands/logo-vnpay.png" height="18" alt="VNPAY">
                                            <div class="vr mx-1" style="height: 15px;"></div>
                                            <i class="fab fa-cc-visa text-primary fa-lg"></i>
                                            <i class="fab fa-cc-mastercard text-danger fa-lg"></i>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check payment-option p-3 border rounded-3 mb-2 opacity-50">
                                    <input id="cod" name="payment_method" type="radio" class="form-check-input" value="cod" disabled>
                                    <label class="form-check-label" for="cod">
                                        <span class="fw-semibold">Thanh toán khi nhận hàng (COD)</span>
                                        <p class="mb-0 small text-muted">Hiện đang tạm khóa bảo trì</p>
                                    </label>
                                </div>
                            </div>

                            <button class="w-100 btn btn-primary btn-lg fw-bold rounded-3 shadow-lg py-3 btn-checkout-action" type="submit">
                                <i class="fas fa-lock me-2"></i>THANH TOÁN QUA VNPAY
                            </button>
                            <p class="text-center mt-3 mb-0 small text-muted">
                                <i class="fas fa-shield-alt me-1"></i> Giao dịch được bảo mật bởi VNPay
                            </p>
                        </div>
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
    <script src="../../../public/assets/js/checkout_address.js"></script>
    <script src="../../../public/assets/js/checkout.js"></script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"></script>
</body>
</html>