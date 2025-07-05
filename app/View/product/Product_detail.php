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
                        <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product_images[0]['image_path']); ?>"
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
                        <div class="description"><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></div>
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
                            <button class="btn btn-add-cart " onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                            <!-- <button class="btn btn-buy-now" onclick="buyNow(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-bag"></i> Mua ngay
                            </button> -->
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
            <div class="reviews-container" id="reviewsContainer">
                <?php
                $stmt = $pdo->prepare('SELECT * FROM review_products WHERE product_id = ? ORDER BY sent_at ASC');
                $stmt->execute([$product_id]);

                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($reviews) {
                    foreach ($reviews as $review) {
                        $review_username = $review['username'];
                        $review_sent_at = $review['sent_at'];
                        $review_content = $review['content'];

                        $text_html = '<div class="review-item">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="reviewer-details">
                                <div class="reviewer-name">' . htmlspecialchars($review_username) . '</div>
                                <div class="review-date">' . htmlspecialchars($review_sent_at) . '</div>
                            </div>
                        </div>
                        <div class="review-text">' . htmlspecialchars($review_content) . '</div>
                        </div>';

                        echo $text_html;
                    }
                }
                ?>

                <!-- <div class="review-item">
                    <div class="reviewer-info">
                        <div class="reviewer-avatar">
                            <img src="https://via.placeholder.com/40" alt="Reviewer Avatar">
                        </div>
                        <div class="reviewer-details">
                            <div class="reviewer-name">Nguyễn Văn A</div>
                            <div class="review-date">12/10/2023</div>
                        </div>
                    </div>
                    <div class="review-text">
                        Sản phẩm rất tốt, tôi rất hài lòng với chất lượng.
                    </div>
                </div> -->
            </div>
            <div class="reviews-footer">
                <input type="text" name="" id="contentReview" placeholder="Thêm đánh giá sản phẩm">
                <button id="sendButton">Đăng</button>
            </div>
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
                        <div class="text-muted small">Tình trạng: <?php echo getConditionText($related['condition_status']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php footer(); ?>
    <script>let product_id = <?php echo $product_id ?></script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_review_system.js"></script>
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
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    <?php if (!isset($_SESSION['user_id'])): ?>
    showToast('warning', 'Yêu cầu đăng nhập', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
    setTimeout(() => {
        window.location.href = '/WebMuaBanDoCu/app/View/user/login.php';
    }, 2000);
    return;
    <?php endif; ?>

    // Optimistic UI update
    const cartCountElements = document.querySelectorAll('.cart-count');
    if (cartCountElements.length > 0) {
        cartCountElements.forEach(element => {
            const currentCount = parseInt(element.textContent) || 0;
            element.textContent = currentCount + quantity;
            
            if (currentCount === 0) {
                element.style.display = 'flex';
            }
        });
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
            console.log('Response:', response); // Debug log
            if (response && response.success) {
                // Hiển thị thông báo thành công
                showToast('success', 'Thành công', response.message || 'Đã thêm sản phẩm vào giỏ hàng');
                
                // Cập nhật lại số lượng chính xác từ server
                if (response.cart_count !== undefined) {
                    cartCountElements.forEach(element => {
                        element.textContent = response.cart_count;
                        element.style.display = response.cart_count > 0 ? 'flex' : 'none';
                    });
                }
            } else {
                showToast('error', 'Lỗi', response?.message || 'Có lỗi xảy ra');
                // Rollback optimistic update
                cartCountElements.forEach(element => {
                    const currentCount = parseInt(element.textContent) || 0;
                    element.textContent = Math.max(0, currentCount - quantity);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error); // Debug log
            showToast('error', 'Lỗi', 'Không thể kết nối đến server');
            // Rollback optimistic update
            cartCountElements.forEach(element => {
                const currentCount = parseInt(element.textContent) || 0;
                element.textContent = Math.max(0, currentCount - quantity);
            });
        }
    });
}

    function updateCartCount() {
    const cartCountElements = document.querySelectorAll('.cart-count');
    if (!cartCountElements.length) return;

    $.ajax({
        url: '../../../app/Controllers/cart/CartController.php?action=count',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                cartCountElements.forEach(element => {
                    element.textContent = data.count;
                    // Ẩn badge nếu count = 0
                    if (data.count == 0) {
                        element.style.display = 'none';
                    } else {
                        element.style.display = 'flex';
                    }
                });
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