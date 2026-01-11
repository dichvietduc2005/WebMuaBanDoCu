<?php
// app/View/user/vouchers.php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/CouponModel.php';
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

// Check login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'public/index.php?page=login');
    exit;
}

$activeCoupons = getAllActiveCoupons($pdo);
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
            border: 2px dashed #ddd;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .voucher-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .voucher-left {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            width: 120px;
            border-right: 2px dashed #fff;
        }

        .voucher-code {
            font-family: monospace;
            font-weight: bold;
            font-size: 1.1rem;
            letter-spacing: 1px;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .copy-btn {
            cursor: pointer;
        }

        .copy-btn:active {
            transform: scale(0.95);
        }
    </style>
</head>

<body class="bg-light">
    <?php renderHeader($pdo); ?>

    <div class="container py-5" style="min-height: 60vh;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-ticket-alt text-primary fa-2x me-3"></i>
                    <h2 class="fw-bold mb-0">Kho Voucher của tôi</h2>
                </div>

                <?php if (empty($activeCoupons)): ?>
                    <div class="text-center py-5 bg-white rounded shadow-sm">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="Empty"
                            style="width: 100px; opacity: 0.5;">
                        <p class="mt-3 text-muted">Hiện tại chưa có mã giảm giá nào khả dụng.</p>
                        <a href="../TrangChu.php" class="btn btn-outline-primary mt-2">về trang chủ</a>
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($activeCoupons as $coupon): ?>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="d-flex h-100 voucher-card rounded bg-white">
                                        <div class="voucher-left p-2 text-center rounded-start">
                                            <i class="fas fa-gift fa-2x mb-2"></i>
                                            <span class="small fw-bold">HIHand</span>
                                        </div>
                                        <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h5 class="fw-bold text-primary mb-1">
                                                        Giảm
                                                        <?php echo $coupon['discount_type'] == 'percent' ? floatval($coupon['discount_value']) . '%' : number_format($coupon['discount_value']) . 'đ'; ?>
                                                    </h5>
                                                    <?php if ($coupon['discount_type'] == 'percent' && !empty($coupon['max_discount_amount']) && $coupon['max_discount_amount'] > 0): ?>
                                                        <span class="badge bg-info text-dark" style="font-size: 0.75rem;">Tối đa:
                                                            <?= number_format($coupon['max_discount_amount']) ?>đ</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-muted small mb-2">
                                                    Đơn tối thiểu: <?php echo number_format($coupon['min_order_value']); ?>đ
                                                </p>
                                                <?php if ($coupon['end_date']): ?>
                                                    <p class="text-danger x-small mb-0" style="font-size: 0.8rem;">
                                                        <i class="far fa-clock me-1"></i>HSD:
                                                        <?php echo date('d/m/Y', strtotime($coupon['end_date'])); ?>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="text-success x-small mb-0" style="font-size: 0.8rem;">
                                                        <i class="far fa-check-circle me-1"></i>Vô thời hạn
                                                    </p>
                                                <?php endif; ?>
                                            </div>

                                            <div
                                                class="d-flex align-items-center justify-content-between mt-3 bg-light p-2 rounded">
                                                <span class="text-secondary small">Mã:</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="fw-bold text-dark code-text"><?php echo htmlspecialchars($coupon['code']); ?></span>
                                                    <button class="btn btn-sm btn-outline-primary copy-btn py-0 px-2"
                                                        title="Sao chép"
                                                        onclick="copyToClipboard('<?php echo $coupon['code']; ?>')">
                                                        <i class="far fa-copy"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                // Show toast or alert
                const toast = document.createElement('div');
                toast.className = 'position-fixed bottom-0 end-0 p-3';
                toast.style.zIndex = '1111';
                toast.innerHTML = `
                    <div class="toast show align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                Đã sao chép mã: <strong>${text}</strong>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2000);
            }, function (err) {
                console.error('Không thể sao chép: ', err);
            });
        }
    </script>
</body>

</html>