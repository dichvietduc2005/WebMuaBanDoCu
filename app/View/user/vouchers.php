<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/CouponModel.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'public/index.php?page=login');
    exit;
}

// Fetch active coupons
$coupons = getAllActiveCoupons($pdo);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho Voucher - HIHand Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .voucher-card {
            border: none;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .voucher-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.15);
            border-color: rgba(37, 99, 235, 0.2);
        }
        .voucher-left {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            position: relative;
            border-right: 2px dashed #bfdbfe;
        }
        .voucher-left::before, .voucher-left::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            background: #f8f9fa; /* Match body bg */
            border-radius: 50%;
            right: -10px;
        }
        .voucher-left::before { top: -10px; }
        .voucher-left::after { bottom: -10px; }
        
        .discount-badge {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }
        .voucher-code {
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: #374151;
        }
        .copy-btn {
            border-radius: 50px;
            padding: 6px 16px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>

<body class="bg-light">
    <?php include_once __DIR__ . '/../../Components/header/Header.php';
    renderHeader($pdo); ?>

    <div class="container py-5">
        <div class="d-flex align-items-center mb-4">
            <i class="fas fa-ticket-alt text-primary fa-2x me-3"></i>
            <h2 class="fw-bold mb-0">Kho Voucher của bạn</h2>
        </div>

        <?php if (empty($coupons)): ?>
            <div class="text-center py-5">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" alt="Empty"
                    style="max-width: 200px; opacity: 0.7;">
                <h5 class="mt-3 text-muted">Hiện chưa có mã giảm giá nào.</h5>
                <p class="text-muted">Hãy quay lại sau nhé!</p>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary rounded-pill px-4">Mua sắm ngay</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($coupons as $coupon): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex h-100 voucher-card">
                            <div class="voucher-left p-3">
                                <div class="discount-badge">
                                    <i
                                        class="fas <?php echo $coupon['discount_type'] == 'percent' ? 'fa-percent' : 'fa-tag'; ?> fa-lg"></i>
                                </div>
                            </div>
                            <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="voucher-code"><?php echo htmlspecialchars($coupon['code']); ?></span>
                                        <?php if ($coupon['usage_limit'] > 0): ?>
                                            <span class="badge bg-light text-secondary border">Còn:
                                                <?php echo $coupon['usage_limit'] - $coupon['used_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">
                                        Giảm
                                        <?php echo $coupon['discount_type'] == 'percent' ? number_format($coupon['discount_value']) . '%' : number_format($coupon['discount_value']) . 'đ'; ?>
                                    </h5>
                                    <p class="small text-muted mb-3" style="font-size: 0.9rem;">
                                        Đơn tối thiểu <?php echo number_format($coupon['min_order_value']); ?>đ
                                        <?php if ($coupon['max_discount_amount']): ?>
                                            <span class="d-block text-primary" style="font-size: 0.85rem; margin-top: 2px;">
                                                • Giảm tối đa <?php echo number_format($coupon['max_discount_amount']); ?>đ
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between align-items-end border-top pt-3 mt-1">
                                    <div class="d-flex flex-column">
                                        <small class="text-secondary" style="font-size: 0.8rem;">Hết hạn</small>
                                        <small class="fw-bold text-dark">
                                            <?php echo $coupon['end_date'] ? date('d/m/Y', strtotime($coupon['end_date'])) : 'Vô thời hạn'; ?>
                                        </small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary copy-btn"
                                        onclick="copyToClipboard('<?php echo $coupon['code']; ?>', this)">
                                        Copy Mã
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include_once __DIR__ . '/../../Components/footer/Footer.php';
    footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Đã copy';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                btn.style.color = 'white';

                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                    btn.style.color = '';
                }, 2000);
            });
        }
    </script>
</body>

</html>