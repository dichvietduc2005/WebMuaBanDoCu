<?php
require_once("../config/config.php"); // For $pdo and session_start()
require_once('../modules/cart/functions.php'); // For cart functions

// Lấy user_id hiện tại (nếu đã đăng nhập)
$user_id = get_current_logged_in_user_id();
$cartItemCount = getCartItemCount($pdo, $user_id);

// Lấy danh sách sản phẩm từ database
$products = [];
try {
    // Lấy sản phẩm và hình ảnh đầu tiên của mỗi sản phẩm
    $stmt = $pdo->query("
        SELECT p.*, pi.image_path 
        FROM products p
        LEFT JOIN (
            SELECT product_id, image_path, ROW_NUMBER() OVER(PARTITION BY product_id ORDER BY is_primary DESC, id ASC) as rn
            FROM product_images
        ) pi ON p.id = pi.product_id AND pi.rn = 1
        WHERE p.status = 'active' AND p.stock_quantity > 0
        ORDER BY p.created_at DESC
        LIMIT 20 
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Trang chủ - Mua bán đồ cũ</title>
    <!-- Bootstrap core CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- Custom styles for this template -->
    <link href="../assets/css/jumbotron-narrow.css" rel="stylesheet">  
    <script src="../assets/js/jquery-1.11.3.min.js"></script>
    <style>
        .product-card { 
            margin-bottom: 20px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            padding: 15px; 
            height: 400px;
            display: flex;
            flex-direction: column;
        }
        .product-card img { 
            width: 100%; 
            height: 200px; 
            object-fit: cover; 
            margin-bottom: 10px; 
        }
        .product-info {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            flex: 1;
        }
        .product-price { 
            font-weight: bold; 
            color: #d9534f; 
            font-size: 18px;
            margin-bottom: 10px;
        }
        .product-condition {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .header .nav-pills > li > a { font-size: 14px; }
        .add-to-cart-form {
            margin-top: auto;
        }

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
                    <li class="active"><a href="index.php">Trang chủ</a></li>
                    <li><a href="cart/index.php">Giỏ hàng (<span id="header-cart-count"><?php echo $cartItemCount; ?></span>)</a></li>
                    <li><a href="payment/history.php">Lịch sử GD</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">Web Mua Bán Đồ Cũ</h3>
        </div>

        <div class="jumbotron">
            <h1>Chào mừng!</h1>
            <p class="lead">Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng.</p>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h2>Sản phẩm mới nhất</h2>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars(!empty($product['image_path']) && file_exists('../' . $product['image_path']) ? '../' . $product['image_path'] : '../assets/images/default_product_image.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>">
                            <div class="product-info">
                                <div class="product-title"><?php echo htmlspecialchars($product['title']); ?></div>
                                <div class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</div>
                                <div class="product-condition">
                                    Tình trạng: 
                                    <?php
                                    $conditions = [
                                        'new' => 'Mới',
                                        'like_new' => 'Như mới', 
                                        'good' => 'Tốt',
                                        'fair' => 'Khá',
                                        'poor' => 'Cũ'
                                    ];
                                    echo $conditions[$product['condition_status']] ?? $product['condition_status'];
                                    ?>
                                    | Còn lại: <?php echo $product['stock_quantity']; ?> sản phẩm
                                </div>
                                <!-- Form để thêm vào giỏ hàng -->
                                <form action="cart/handler.php" method="GET" class="add-to-cart-form form-add-to-cart">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="quantity_<?php echo $product['id']; ?>" style="font-size: 12px;">Số lượng:</label>
                                        <input type="number" 
                                               id="quantity_<?php echo $product['id']; ?>"
                                               name="quantity" 
                                               value="1" 
                                               min="1" 
                                               max="<?php echo $product['stock_quantity']; ?>"
                                               style="width: 60px; text-align:center;" 
                                               class="form-control form-control-sm">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Thêm vào giỏ</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-12">
                    <div class="alert alert-info">
                        <strong>Chưa có sản phẩm nào!</strong> Vui lòng thêm dữ liệu mẫu bằng cách chạy file SQL trong thư mục data.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer" style="margin-top: 30px;">
            <p>&copy; Web Mua Ban Do Cu <?php echo date('Y')?></p>
        </footer>

    </div> <!-- /container -->

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

        // Script để xử lý thêm vào giỏ hàng bằng AJAX
        $('.form-add-to-cart').on('submit', function(e) {
            e.preventDefault(); // Ngăn chặn submit form truyền thống
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.text();
            
            // Disable button và hiển thị loading
            submitBtn.prop('disabled', true).text('Đang thêm...');
            
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Hiển thị toast thành công
                        showToast(response.message, 'success');
                        // Cập nhật số lượng trên icon giỏ hàng ở header
                        $('#header-cart-count').text(response.item_count);
                        
                        // Reset button
                        submitBtn.prop('disabled', false).text(originalText);
                    } else {
                        // Hiển thị toast lỗi
                        showToast(response.message, 'error');
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    showToast('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.', 'error');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    });
    </script>

</body>
</html>
