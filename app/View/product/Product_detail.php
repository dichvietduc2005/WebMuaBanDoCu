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

// Log user action: view product
if (function_exists('log_user_action')) {
    $userId = $_SESSION['user_id'] ?? null;
    log_user_action($pdo, $userId, 'view_product', "Xem chi tiết sản phẩm: " . htmlspecialchars($product['title']), [
        'product_id' => $product_id,
        'product_title' => $product['title'],
        'category_id' => $product['category_id'],
        'price' => $product['price'],
        'seller_id' => $product['user_id'] ?? null
    ]);
}

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
    
    <!-- Meta tags để tránh adblock -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/product_detail.css">
    <!-- Mobile Responsive CSS for Product Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-product-pages.css">
    <!-- Shopee Style Product Detail -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/product-detail-shopee.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/review-modal.css">
    
    <!-- Thêm style inline để đảm bảo review system hiển thị -->
    <style>
        /* Đảm bảo review system không bị adblock chặn */
        .customer-reviews {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
        }
        
        .review-form {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .reviews-container {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .toast-container {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        
        /* Fallback cho trường hợp CSS bị chặn */
        .adblock-fallback {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    </style>
</head>

<body>
    <?php renderHeader($pdo); ?>
    <div class="product-detail-container">
        <!-- Breadcrumb
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="categories.php">Danh mục</a></li>
                <li class="breadcrumb-item"><a
                        href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
                </li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
            </ol>
        </nav> -->

        <!-- Product Main Info -->
        <div class="product-main">
            <div class="row">
                <div class="col-md-6">
                    <div class="product-images">
                        <!-- Back Button
                        <a href="javascript:history.back()" class="product-back-button">
                            <i class="fas fa-arrow-left"></i>
                        </a> -->
                        
                        <!-- Image Pagination -->
                        <?php if (!empty($product_images) && count($product_images) > 1): ?>
                        <div class="image-pagination">
                            <span id="currentImageIndex">1</span> / <span id="totalImages"><?php echo count($product_images); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product_images)): ?>
                        <div class="main-image-wrapper">
                            <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product_images[0]['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($product['title']); ?>" class="main-image" id="mainImage">
                        </div>
                        <?php if (count($product_images) > 1): ?>
                        <div class="product-variations">
                            <div class="variations-label"><?php echo count($product_images); ?> phân loại có sẵn</div>
                            <div class="image-thumbnails">
                                <?php foreach ($product_images as $index => $image): ?>
                                <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($image['image_path']); ?>"
                                    alt="Ảnh <?php echo $index + 1; ?>"
                                    class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>"
                                    onclick="changeMainImage('<?php echo BASE_URL . 'public/' . htmlspecialchars($image['image_path']); ?>', this, <?php echo $index + 1; ?>)">
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="main-image-wrapper d-flex align-items-center justify-content-center bg-light">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="product-info-section">
                        <div class="product-info">
                            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
                            
                            <div class="price">
                                <?php echo formatPrice($product['price']); ?>
                            </div>

                            <div class="product-meta">
                                <div class="meta-item">
                                    <i class="fas fa-star text-warning"></i>
                                    <span>Tình trạng: <?php echo getConditionText($product['condition_status']); ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <i class="fas fa-box"></i>
                                    <span>Còn lại: <?php echo $product['stock_quantity']; ?> sản phẩm</span>
                                </div>
                            </div>

                            <!-- Shipping Info -->
                            <div class="shipping-info">
                                <div class="shipping-info-item">
                                    <i class="fas fa-truck"></i>
                                    <div>
                                        <div>Nhận hàng trong 2-3 ngày</div>
                                        <div class="shipping-free">Phí ship 0₫</div>
                                    </div>
                                </div>
                                <div class="shipping-voucher">
                                    <i class="fas fa-gift"></i> Tặng Voucher 15.000₫ nếu đơn giao sau thời gian trên.
                                </div>
                            </div>

                            <!-- Shopee Guarantee -->
                            <div class="shopee-guarantee">
                                <i class="fas fa-shield-alt"></i>
                                <span>HIHand Xử Lý - Trả hàng miễn phí 15 ngày - Chính hãng</span>
                            </div>

                            <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="quantity-selector">
                                <span>Số lượng:</span>
                                <div class="qty-controls">
                                    <button class="qty-btn" onclick="decreaseQty()">-</button>
                                    <input type="number" class="qty-input" id="quantity" value="1" min="1"
                                        max="<?php echo $product['stock_quantity']; ?>" readonly>
                                    <button class="qty-btn" onclick="increaseQty()">+</button>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                                </button>
                                <button class="btn-buy-now" onclick="buyNow(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-shopping-bag"></i> Mua ngay
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="action-buttons">
                                <button class="btn-add-cart" disabled style="opacity: 0.5;">
                                    <i class="fas fa-ban"></i> Hết hàng
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Store Info Section -->
                    <div class="store-info-section">
                        <div class="store-header">
                            <div class="store-avatar">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="store-details">
                                <div class="store-name"><?php echo htmlspecialchars($product['seller_name'] ?? 'Người bán'); ?></div>
                                <div class="store-status online">
                                    <i class="fas fa-circle"></i> Online
                                </div>
                                <div class="store-stats">
                                    <div class="store-stat-item">
                                        <span class="store-stat-value"><?php echo $product['seller_products_count'] ?? 0; ?></span>
                                        <span>Sản phẩm</span>
                                    </div>
                                    <div class="store-stat-item">
                                        <span class="store-stat-value">4.8</span>
                                        <span>Đánh giá</span>
                                    </div>
                                    <div class="store-stat-item">
                                        <span class="store-stat-value">100%</span>
                                        <span>Phản hồi</span>
                                    </div>
                                </div>
                            </div>
                            <button class="view-shop-btn" onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/shop.php?seller=<?php echo $product['user_id']; ?>'">
                                Xem Shop
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    
        <!-- Customer Reviews -->
        <div class="customer-reviews">
            <h3 class="reviews-header mb-4">Đánh giá <?php echo htmlspecialchars($product['title']); ?></h3>

            <!-- Reviews Container -->
            <div class="reviews-container" id="reviewsContainer">
                <?php
                // Build Review Stats
                $stats = $productModel->getReviewStats($product_id);
                $total_reviews = $stats['total_reviews'] ?: 0;
                $avg_rating = round($stats['average_rating'] ?: 0, 1);
                
                // Helper to calculate percentage width
                function getWidth($count, $total) {
                    if ($total == 0) return 0;
                    return ($count / $total) * 100;
                }
                ?>
                
                <?php if ($total_reviews > 0): ?>
                <div class="rating-summary-box mb-4 p-4 border rounded bg-white">
                    <div class="row align-items-center">
                        <!-- Left: Big Score -->
                        <div class="col-md-5 text-center border-end">
                            <div class="d-flex justify-content-center align-items-end mb-2">
                                <span class="display-3 fw-bold text-warning me-2" style="line-height:1;"><?php echo $avg_rating; ?></span>
                                <div class="text-warning fs-3" style="line-height:1.4;">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <div class="text-muted mb-1"><?php echo $total_reviews; ?> đánh giá</div>
                        </div>
                        
                        <!-- Right: Progress Bars -->
                        <div class="col-md-7 ps-md-4">
                            <?php for($star=5; $star>=1; $star--): 
                                $key = "star_" . $star;
                                $count = $stats[$key] ?? 0;
                                $percent = getWidth($count, $total_reviews);
                            ?>
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2 small fw-bold text-dark" style="width:10px;"><?php echo $star; ?></span>
                                <i class="fas fa-star text-warning me-2 small"></i>
                                <div class="progress flex-grow-1" style="height: 6px; background-color: #f1f1f1;">
                                    <!-- Changed to Blue (#2f80ed) to match reference image -->
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percent; ?>%; background-color: #2f80ed;"></div>
                                </div>
                                <span class="ms-3 small text-muted text-end" style="width: 35px;"><?php echo $percent > 0 ? round($percent).'%' : '0%'; ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Write Review Button (TGDĐ Style) -->
                <div class="text-center mb-4">
                    <button class="btn btn-write-review btn-lg w-100 p-3" data-bs-toggle="modal" data-bs-target="#reviewModal" style="max-width: 400px; border-radius: 8px;">
                        Viết đánh giá
                    </button>
                </div>
                <?php endif; ?>

                <div id="reviewsList">
                <?php
                $stmt = $pdo->prepare('SELECT * FROM review_products WHERE product_id = ? ORDER BY sent_at DESC');
                $stmt->execute([$product_id]);

                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($reviews) {
                    foreach ($reviews as $review) {
                        $review_username = $review['username'];
                        $review_sent_at = $review['sent_at'];
                        $review_content = $review['content'];
                        $is_recommended = isset($review['is_recommended']) && $review['is_recommended'] == 1;

                        echo '<div class="review-item border-bottom pb-4 mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <div class="reviewer-info d-flex align-items-center">
                                    <div class="reviewer-avatar fw-bold bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:28px; height:28px; font-size:14px;">
                                        ' . strtoupper(substr($review_username, 0, 1)) . '
                                    </div>
                                    <div class="reviewer-name fw-bold text-dark">' . htmlspecialchars($review_username) . '</div>
                                </div>
                                ' . ($is_recommended ? '<div class="text-success small fw-medium"><i class="fas fa-check-circle me-1"></i>Sẽ giới thiệu</div>' : '') . '
                            </div>
                            
                            <div class="review-rating-row mb-3 d-flex align-items-center">
                                <div class="text-warning small me-3">
                                    ' . str_repeat('<i class="fas fa-star"></i>', (int)($review['rating'] ?? 5)) . '
                                    ' . str_repeat('<i class="far fa-star"></i>', 5 - (int)($review['rating'] ?? 5)) . '
                                </div>
                                <div class="verified-badge text-success small">
                                    <i class="fas fa-check-circle me-1"></i>Đã mua tại WebMuaBanDoCu
                                </div>
                            </div>

                            <div class="review-text text-dark mb-3" style="font-size: 15px; line-height: 1.5;">' . nl2br(htmlspecialchars($review_content)) . '</div>
                            
                            <div class="review-footer d-flex justify-content-between align-items-center">
                                <div class="review-actions small">
                                    <a href="#" class="text-secondary text-decoration-none me-4"><i class="far fa-thumbs-up me-1"></i> Hữu ích (0)</a>
                                    <a href="#" class="text-secondary text-decoration-none"><i class="far fa-comment-dots me-1"></i> Thảo luận</a>
                                </div>
                                <div class="review-date small text-muted">' . date('d/m/Y', strtotime($review_sent_at)) . '</div>
                             </div>
                        </div>';
                    }
                } else {
                    echo '<div class="no-reviews text-center py-5">
                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này</p>
                    </div>';
                }
                ?>
            </div>
            
            <!-- Review Form Trigger & Modal -->
            <?php if (isset($_SESSION['user_id'])): 
                // Check if user reviewed
                $checkUserReview = $pdo->prepare('SELECT COUNT(*) FROM review_products WHERE user_id = ? AND product_id = ?');
                $checkUserReview->execute([$_SESSION['user_id'], $product_id]);
                $hasUserReviewed = $checkUserReview->fetchColumn() > 0;
            ?>
                <div class="reviews-footer text-center mt-4">
                    <?php if (!$hasUserReviewed): ?>
                    <button class="btn btn-write-review" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="fas fa-pencil-alt me-2"></i>Viết đánh giá
                    </button>
                    <?php else: ?>
                    <div class="already-reviewed">
                        <i class="fas fa-check-circle text-success"></i>
                        <p>Bạn đã đánh giá sản phẩm này rồi</p>
                        <small>Cảm ơn bạn đã chia sẻ ý kiến!</small>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Review Modal -->
                <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content modal-review-content">
                            <div class="modal-header modal-review-header">
                                <h5 class="modal-title">Đánh giá sản phẩm</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body modal-review-body">
                                <!-- Product Info -->
                                <div class="review-product-info">
                                    <?php if (!empty($product_images)): ?>
                                        <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product_images[0]['image_path']); ?>" alt="Product" class="review-product-img">
                                    <?php else: ?>
                                        <div class="review-product-img d-inline-flex align-items-center justify-content-center bg-light">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h6 class="review-product-name"><?php echo htmlspecialchars($product['title']); ?></h6>
                                </div>

                                <!-- Star Rating -->
                                <div class="review-star-rating">
                                    <div class="review-stars-group">
                                        <i class="fas fa-star active" data-rating="1" data-label="Rất tệ"></i>
                                        <i class="fas fa-star active" data-rating="2" data-label="Tệ"></i>
                                        <i class="fas fa-star active" data-rating="3" data-label="Tạm ổn"></i>
                                        <i class="fas fa-star active" data-rating="4" data-label="Tốt"></i>
                                        <i class="fas fa-star active" data-rating="5" data-label="Rất tốt"></i>
                                    </div>
                                    <div class="review-rating-label" id="ratingLabel">Rất tốt</div>
                                    <input type="hidden" id="reviewRating" value="5">
                                </div>

                                <!-- Comment -->
                                <div class="review-textarea-wrapper">
                                    <textarea class="review-textarea" id="contentReview" placeholder="Mời bạn chia sẻ thêm cảm nhận..."></textarea>
                                </div>

                                <!-- Extra Inputs (Optional for now, but UI present) -->
                                <div class="review-options">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="recommendCheck" checked>
                                        <label class="form-check-label" for="recommendCheck">
                                            Tôi sẽ giới thiệu sản phẩm cho bạn bè, người thân
                                        </label>
                                    </div>
                                    <!-- Placeholder for Image Upload -->
                                    <div class="upload-photo-btn" onclick="document.getElementById('reviewImages').click()">
                                        <i class="fas fa-camera me-1"></i> Gửi ảnh thực tế (tối đa 3 ảnh)
                                        <input type="file" id="reviewImages" hidden multiple accept="image/*">
                                    </div>
                                </div>

                                <div class="review-inputs-row">
                                    <input type="text" class="review-input" placeholder="Họ tên (bắt buộc)" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" readonly>
                                    <input type="text" class="review-input" placeholder="Số điện thoại (bắt buộc)">
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="policyCheck" checked>
                                    <label class="form-check-label small text-muted" for="policyCheck">
                                        Tôi đồng ý với Quy định đánh giá
                                    </label>
                                </div>

                                <button type="button" class="btn-submit-review" id="sendButton">
                                    Gửi đánh giá
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
            <div class="reviews-footer text-center">
                <div class="login-to-review">
                    <p>Bạn cần <a href="/WebMuaBanDoCu/app/View/user/login.php" class="text-primary fw-bold">đăng nhập</a> để đánh giá sản phẩm</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="related-grid">
                <?php foreach ($related_products as $related): ?>
                <div class="related-item"
                    onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $related['id']; ?>'">
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
    function changeMainImage(imagePath, thumbnail, index) {
        document.getElementById('mainImage').src = imagePath;

        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        if (thumbnail) {
            thumbnail.classList.add('active');
        }

        // Update pagination
        if (index && document.getElementById('currentImageIndex')) {
            document.getElementById('currentImageIndex').textContent = index;
        }
    }

    function increaseQty() {
        const qtyInput = document.getElementById('quantity');
        const currentQty = parseInt(qtyInput.value);
        const maxQty = parseInt(qtyInput.max);
        if (currentQty < maxQty) {
            qtyInput.value = currentQty + 1;
        }
    }

    function decreaseQty() {
        const qtyInput = document.getElementById('quantity');
        const currentQty = parseInt(qtyInput.value);
        if (currentQty > 1) {
            qtyInput.value = currentQty - 1;
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
                
                // Trigger cart updated event for real-time system
                document.dispatchEvent(new CustomEvent('cartItemAdded'));
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

    function buyNow(productId) {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        
        <?php if (!isset($_SESSION['user_id'])): ?>
        showToast('warning', 'Yêu cầu đăng nhập', 'Vui lòng đăng nhập để mua sản phẩm');
        setTimeout(() => {
            window.location.href = '<?php echo BASE_URL; ?>app/View/user/login.php?redirect=' + encodeURIComponent(window.location.href);
        }, 1500);
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
                if (response && response.success) {
                    // Redirect to checkout
                    window.location.href = '<?php echo BASE_URL; ?>app/View/checkout/index.php';
                } else {
                    showToast('error', 'Lỗi', response?.message || 'Có lỗi xảy ra');
                }
            },
            error: function() {
                showToast('error', 'Lỗi', 'Không thể kết nối đến server');
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
    <script>userId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?></script>
    <?php require_once __DIR__ . '/../user/ChatView.php'; ?>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"> </script>
    
    <!-- Footer -->
    <?php footer(); ?>

</body>

</html>