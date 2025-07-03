<?php
// Sử dụng bootstrap để tăng hiệu suất tải trang
require_once '../../../config/bootstrap.php';

// Include required components
require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: ' . BASE_URL);
    exit;
}

// Sử dụng ProductModel để lấy dữ liệu (với cache)
$productModel = new ProductModel();
$product = $productModel->getProductById($product_id);

if (!$product) {
    header('Location: ' . BASE_URL);
    exit;
}

// Lấy hình ảnh sản phẩm
$product_images = $productModel->getProductImages($product_id);

// Lấy sản phẩm liên quan (cùng danh mục)
$related_products = $productModel->getRelatedProducts($product['category_id'], $product_id);

// Đếm số sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT SUM(ci.quantity) as total_quantity
        FROM carts c 
        JOIN cart_items ci ON c.id = ci.cart_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cart_count = $result['total_quantity'] ?? 0;
}

// Các helper functions đã được đưa vào app/helpers.php
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/user_box_chat.css?v=1.2">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/product_detail.css">
    
</head>

<body>
    <?php renderHeader($pdo); ?>
    <div class="product-detail-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="TrangChu.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a
                        href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
                </li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
            </ol>
        </nav>

        <!-- Product Main Info -->
        <div class="product-main">
            <div class="row">
                <div class="col-md-6">
                    <div class="product-images">
                        <?php if (!empty($product_images)): ?>
                        <img src="/WebMuaBanDoCu/public/<?php echo htmlspecialchars($product_images[0]['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>" class="main-image" id="mainImage">

                        <?php if (count($product_images) > 1): ?>
                        <div class="image-thumbnails">
                            <?php foreach ($product_images as $index => $image): ?>
                            <img src="/WebMuaBanDoCu/public/<?php echo htmlspecialchars($image['image_path']); ?>"
                                alt="Ảnh <?php echo $index + 1; ?>"
                                class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>', this)">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="main-image d-flex align-items-center justify-content-center bg-light">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="product-info">
                        <h1><?php echo htmlspecialchars($product['title']); ?></h1>
                        <div class="price"><?php echo formatPrice($product['price']); ?></div>

                        <div class="product-meta">
                            <div class="meta-item">
                                <i class="fas fa-star text-warning"></i>
                                <span>Tình trạng: <?php echo getConditionText($product['condition_status']); ?></span>
                            </div>
                            
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span>Người bán: <?php echo htmlspecialchars($product['seller_name']); ?></span>
                            </div>
                        </div>

                        <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="quantity-selector">
                            <span>Số lượng:</span>
                            <input type="number" class="qty-input" id="quantity" value="1" min="1"
                                max="<?php echo $product['stock_quantity']; ?>">
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                            <button class="btn btn-buy-now" onclick="buyNow(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-bag"></i> Mua ngay
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="action-buttons">
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban"></i> Hết hàng
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Description -->
        <div class="product-description">
            <h3 class="mb-4">Mô tả sản phẩm</h3>
            <div class="description-content">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
        </div>

        <!-- Customer Reviews -->
        <div class="customer-reviews">
            <h3 class="reviews-header">Đánh giá của khách hàng</h3>

            <!-- Review Item Example -->
            <div class="review-item">
                <div class="reviewer-info">
                    <div class="reviewer-avatar">
                        <img src="https://via.placeholder.com/40" alt="Reviewer Avatar">
                    </div>
                    <div class="reviewer-details">
                        <div class="reviewer-name">Nguyễn Văn A</div>
                        <div class="review-date">12/10/2023</div>
                    </div>
                </div>
                <div class="review-rating">
                    <span class="star">&#9733;</span>
                    <span class="star">&#9733;</span>
                    <span class="star">&#9733;</span>
                    <span class="star">&#9733;</span>
                    <span class="star empty">&#9733;</span>
                </div>
                <div class="review-text">
                    Sản phẩm rất tốt, tôi rất hài lòng với chất lượng.
                </div>
                <div class="review-actions">
                    <div class="review-action">
                        <i class="fas fa-reply"></i> Trả lời
                    </div>
                    <div class="review-action">
                        <i class="fas fa-flag"></i> Báo cáo
                    </div>
                </div>
            </div>

            <!-- More reviews... -->

            <button class="see-all-reviews" onclick="showAllReviews()">Xem tất cả đánh giá</button>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="related-grid">
                <?php foreach ($related_products as $related): ?>
                <div class="related-item"
                    onclick="window.location.href='product_detail.php?id=<?php echo $related['id']; ?>'">
                    <?php if ($related['image_path']): ?>
                    <img src="/WebMuaBanDoCu/public/<?php echo htmlspecialchars($related['image_path']); ?>"
                        alt="<?php echo htmlspecialchars($related['title']); ?>" class="related-image">
                    <?php else: ?>
                    <div class="related-image d-flex align-items-center justify-content-center bg-light">
                        <i class="fas fa-image fa-2x text-muted"></i>
                    </div>
                    <?php endif; ?>
                    <div class="related-content">
                        <h6><?php echo htmlspecialchars($related['title']); ?></h6>
                        <div class="text-danger fw-bold"><?php echo formatPrice($related['price']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php footer(); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function changeMainImage(imagePath, thumbnail) {
    document.getElementById('mainImage').src = '/WebMuaBanDoCu/public/' + imagePath;

    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

    function changeQuantity(delta) {
        const qtyInput = document.getElementById('quantity');
        const currentQty = parseInt(qtyInput.value);
        const newQty = currentQty + delta;
        const maxQty = parseInt(qtyInput.max);

        if (newQty >= 1 && newQty <= maxQty) {
            qtyInput.value = newQty;
        }
    }

    function addToCart(productId) {
    const quantity = document.getElementById('quantity').value;

    <?php if (!isset($_SESSION['user_id'])): ?>
    showToast('warning', 'Yêu cầu đăng nhập', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
    setTimeout(() => {
        window.location.href = '/WebMuaBanDoCu/app/View/user/login.php';
    }, 2000);
    return;
    <?php endif; ?>
     const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        const currentCount = parseInt(cartCountElement.textContent) || 0;
        cartCountElement.textContent = currentCount + quantity;
    }

    // AJAX call to add to cart
    $.ajax({
        url: '../../../app/Controllers/cart/CartController.php',
        method: 'POST',
        data: {
            action: 'add',
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('success', 'Thành công', 'Đã thêm sản phẩm vào giỏ hàng!');
                updateCartCount();
            } else {
                showToast('error', 'Lỗi', response.message || 'Có lỗi xảy ra');
            }
        },
        error: function() {
            showToast('error', 'Lỗi', 'Có lỗi xảy ra khi thêm vào giỏ hàng');
        }
    });
}

    function buyNow(productId) {
    const quantity = document.getElementById('quantity').value;

    <?php if (!isset($_SESSION['user_id'])): ?>
    showToast('warning', 'Yêu cầu đăng nhập', 'Vui lòng đăng nhập để mua hàng');
    setTimeout(() => {
        window.location.href = '/WebMuaBanDoCu/app/View/user/login.php';
    }, 2000);
    return;
    <?php endif; ?>

    // Add to cart first, then redirect to checkout
    $.ajax({
        url: '../../../app/Controllers/cart/CartController.php',
        method: 'POST',
        data: {
            action: 'add',
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('success', 'Thành công', 'Đang chuyển đến trang thanh toán...');
                setTimeout(() => {
                    window.location.href = '../checkout/index.php';
                }, 1500);
            } else {
                showToast('error', 'Lỗi', response.message || 'Có lỗi xảy ra');
            }
        },
        error: function() {
            showToast('error', 'Lỗi', 'Có lỗi xảy ra khi thêm vào giỏ hàng');
        }
    });
}

    function updateCartCount() {
    const cartCountElement = document.querySelector('.cart-count');
    if (!cartCountElement) return;

    $.ajax({
        url: '../../../app/Controllers/cart/CartController.php?action=count',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                cartCountElement.textContent = data.count;
            }
        },
        error: function() {
            console.error("Failed to update cart count");
        }
    });
}

    function showAllReviews() {
        // Placeholder function for future implementation
        alert('Tính năng xem tất cả đánh giá sẽ được triển khai sau');
        console.log('Show all reviews functionality to be implemented');
    }
    </script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/product_detail.js"></script>
    <script>userId = <?php echo $_SESSION['user_id'] ?></script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"> </script>
</body>

</html>