<?php
require_once '../config/config.php';

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: TrangChu.php');
    exit;
}

// Lấy thông tin chi tiết sản phẩm
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug,
           u.username as seller_name, u.email as seller_email
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE p.id = ? AND p.status = 'active'
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: TrangChu.php');
    exit;
}

// Lấy tất cả hình ảnh của sản phẩm
$images_stmt = $pdo->prepare("
    SELECT * FROM product_images 
    WHERE product_id = ? 
    ORDER BY is_primary DESC, id ASC
");
$images_stmt->execute([$product_id]);
$product_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy sản phẩm liên quan (cùng danh mục)
$related_stmt = $pdo->prepare("
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' AND p.stock_quantity > 0
    ORDER BY p.created_at DESC 
    LIMIT 4
");
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

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

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

function getConditionText($condition) {
    $conditions = [
        'new' => 'Mới',
        'like_new' => 'Như mới', 
        'good' => 'Tốt',
        'fair' => 'Khá tốt',
        'poor' => 'Cần sửa chữa'
    ];
    return $conditions[$condition] ?? $condition;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f5f7fb; }
        .product-detail-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .product-main { background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .product-images { position: relative; }
        .main-image { width: 100%; height: 400px; object-fit: cover; border-radius: 12px; border: 1px solid #eee; }
        .image-thumbnails { display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; }
        .thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent; }
        .thumbnail.active { border-color: #007bff; }
        .product-info h1 { font-size: 28px; font-weight: 600; margin-bottom: 15px; }
        .price { font-size: 32px; font-weight: 700; color: #ee4d2d; margin-bottom: 20px; }
        .product-meta { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px; }
        .meta-item { display: flex; align-items: center; gap: 8px; color: #666; }
        .quantity-selector { display: flex; align-items: center; gap: 10px; margin-bottom: 25px; }
        .qty-btn { width: 40px; height: 40px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 6px; }
        .qty-input { width: 80px; height: 40px; text-align: center; border: 1px solid #ddd; border-radius: 6px; }
        .action-buttons { display: flex; gap: 15px; margin-bottom: 30px; }
        .btn-add-cart { background: #ffb449; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; }
        .btn-buy-now { background: #ee4d2d; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; }
        .product-description { background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .related-products { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .related-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .related-item { border: 1px solid #eee; border-radius: 8px; overflow: hidden; transition: transform 0.2s; }
        .related-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .related-image { width: 100%; height: 200px; object-fit: cover; }
        .related-content { padding: 15px; }
        .stock-status { padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 500; }
        .in-stock { background: #d4edda; color: #155724; }
        .out-stock { background: #f8d7da; color: #721c24; }
        .breadcrumb-custom { background: white; padding: 15px 30px; border-radius: 12px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="product-detail-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="TrangChu.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
            </ol>
        </nav>

        <!-- Product Main Info -->
        <div class="product-main">
            <div class="row">
                <div class="col-md-6">
                    <div class="product-images">
                        <?php if (!empty($product_images)): ?>
                            <img src="../<?php echo htmlspecialchars($product_images[0]['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 class="main-image" id="mainImage">
                            
                            <?php if (count($product_images) > 1): ?>
                            <div class="image-thumbnails">
                                <?php foreach ($product_images as $index => $image): ?>
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
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
                                <i class="fas fa-box"></i>
                                <span>Còn lại: <?php echo $product['stock_quantity']; ?> sản phẩm</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span>Người bán: <?php echo htmlspecialchars($product['seller_name']); ?></span>
                            </div>
                        </div>

                        <div class="stock-status <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-stock'; ?> d-inline-block mb-3">
                            <?php echo $product['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>
                        </div>

                        <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="quantity-selector">
                            <span>Số lượng:</span>
                            <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" class="qty-input" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                            <button class="btn btn-buy-now">
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

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="related-grid">
                <?php foreach ($related_products as $related): ?>
                <div class="related-item" onclick="window.location.href='product_detail.php?id=<?php echo $related['id']; ?>'">
                    <?php if ($related['image_path']): ?>
                        <img src="../<?php echo htmlspecialchars($related['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($related['title']); ?>" 
                             class="related-image">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeMainImage(imagePath, thumbnail) {
            document.getElementById('mainImage').src = '../' + imagePath;
            
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
            alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
            window.location.href = 'user/login.php';
            return;
            <?php endif; ?>

            // AJAX call to add to cart
            $.post('../modules/cart/handler.php', {
                action: 'add',
                product_id: productId,
                quantity: quantity
            }, function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    alert('Đã thêm sản phẩm vào giỏ hàng!');
                    // Update cart count if needed
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            }).fail(function() {
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
            });
        }
    </script>
</body>
</html>
