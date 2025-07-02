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

// Đường dẫn tới file cấu hình (đã bao gồm Autoloader)
require_once '../../../config/config.php';

// Autoloader sẽ tự động load CartModel và CartController khi cần 

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

// Sử dụng phương thức từ đối tượng CartController với error handling
$cartItems = [];
$cartTotal = 0;
$cartItemCount = 0;

if (!$is_guest) {
    try {
        $cartItems = $cartController->getCartItems();
        $cartTotal = $cartController->getCartTotal();
        $cartItemCount = $cartController->getCartItemCount();
    } catch (Exception $e) {
        // Log error và set default values
        error_log("Cart error for user {$user_id}: " . $e->getMessage());
        $cartItems = [];
        $cartTotal = 0;
        $cartItemCount = 0;
        $is_guest = true; // Treat as guest if error
    }
}

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
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/user_box_chat.css?v=1.2">
    <style>
    .empty-cart {
        text-align: center;
        padding: 40px 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        margin: 20px 0;
    }

    .empty-cart i {
        color: #999;
    }

    .empty-cart p {
        font-size: 18px;
        margin-top: 15px;
        color: #666;
    }

    .item-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.7);
        z-index: 10;
    }

    .item-loading-overlay i {
        color: #007bff;
        font-size: 24px;
    }
    </style>
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
                    <img src="/WebMuaBanDoCu/public/<?php echo htmlspecialchars($item['image_path']); ?>"
                        alt="Ảnh sản phẩm" class="item-image" >
                    <?php else: ?>
                    <div
                        style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border-radius:8px;">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                    <?php endif; ?>

                    <div class="item-details">
                        <h3 class="item-name"><?php echo htmlspecialchars($item['product_title'] ?? ''); ?></h3>
                    </div>

                    <div class="quantity-controls">
                        <input type="number" class="quantity-input" value="<?php echo $item['quantity'] ?? 1; ?>"
                            min="1" data-product-id="<?php echo $item['product_id']; ?>">
                        
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
                <h2 class="summary-title">Đơn Hàng</h2>

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Định nghĩa hàm showToast và showConfirmDialog trước khi load file cart.js
    function showToast(type, title, message) {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        const toastEl = document.createElement('div');
        toastEl.className =
            `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body text-white">
                    <strong>${title}</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, {
            delay: 5000
        });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastEl.remove();
        });
    }

    function createToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '1090';
            document.body.appendChild(container);
        }
        return container;
    }

    function showConfirmDialog(title, message, confirmCallback) {
        // Tạo container nếu chưa tồn tại
        let modalContainer = document.getElementById('confirm-dialog-container');
        if (!modalContainer) {
            modalContainer = document.createElement('div');
            modalContainer.id = 'confirm-dialog-container';
            document.body.appendChild(modalContainer);
        }

        // Tạo ID duy nhất cho modal
        const modalId = 'confirmModal-' + Date.now();

        // Tạo HTML cho modal
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}-label" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="${modalId}-label">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-primary confirm-btn">Xác nhận</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Thêm modal vào container
        modalContainer.innerHTML = modalHTML;

        // Lấy reference đến modal
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement);

        // Thêm sự kiện cho nút xác nhận
        const confirmBtn = modalElement.querySelector('.confirm-btn');
        confirmBtn.addEventListener('click', function() {
            modal.hide();
            if (typeof confirmCallback === 'function') {
                confirmCallback();
            }
        });

        // Xóa modal sau khi đóng để tránh tràn DOM
        modalElement.addEventListener('hidden.bs.modal', function() {
            modalElement.remove();
        });

        // Hiển thị modal
        modal.show();
    }
    </script>
    <script src="../../../public/assets/js/cart.js"></script>
    <script>userId = <?php echo $_SESSION['user_id'] ?></script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"> </script>
</body>

</html>