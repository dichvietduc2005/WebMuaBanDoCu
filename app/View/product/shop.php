<?php
/**
 * shop.php - Shopee-style shop page
 * Uses unified product cards from Home page
 * Displays store info + product listings
 */

require_once __DIR__ . '/../../../config/config.php';
global $pdo;

require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';

// Get seller info from URL or default to seller 1
$seller_id = isset($_GET['seller']) ? (int)$_GET['seller'] : 1;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;

$per_page = 20;
$offset = ($page - 1) * $per_page;

// Initialize variables
$products = [];
$total_products = 0;
$seller_info = null;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Helper functions
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}

if (!function_exists('getConditionText')) {
    function getConditionText($condition) {
        $conditions = [
            'new' => 'Mới',
            'like_new' => 'Như mới',
            'good' => 'Tốt',
            'fair' => 'Khá',
            'poor' => 'Cũ'
        ];
        return $conditions[$condition] ?? 'Không xác định';
    }
}

try {
    // Get seller/store info
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.avatar, 
               COUNT(p.id) as product_count,
               COALESCE(AVG(p.rating), 0) as avg_rating
        FROM users u
        LEFT JOIN products p ON u.id = p.seller_id AND p.status = 'active'
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$seller_id]);
    $seller_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller_info) {
        throw new Exception('Cửa hàng không tồn tại');
    }

    // Get products from this seller
    $where_conditions = ["p.seller_id = ?", "p.status = 'active'"];
    $params = [$seller_id];

    if ($search) {
        $where_conditions[] = "p.title LIKE ?";
        $params[] = "%$search%";
    }

    if ($category) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category;
    }

    $where_sql = implode(' AND ', $where_conditions);

    // Count total products
    $count_sql = "SELECT COUNT(*) FROM products p WHERE $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();

    // Get products
    $sql = "
        SELECT p.*, pi.image_path, c.name as category_name 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_sql
        ORDER BY p.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_pages = ceil($total_products / $per_page);

} catch (Exception $e) {
    $seller_info = null;
    $products = [];
    $total_products = 0;
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seller_info ? htmlspecialchars($seller_info['username']) . ' - ' : ''; ?>Cửa Hàng - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/unified-product-cards.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/shopee-style.css">
    <style>
        body {
            background: var(--bg-light);
        }
    </style>
</head>
<body>
    <?php renderHeader($pdo); ?>

    <!-- Store Header -->
    <div class="store-header">
        <?php if ($seller_info): ?>
        <div class="store-info">
            <div class="store-avatar <?php echo empty($seller_info['avatar']) ? 'no-image' : ''; ?>">
                <?php if (!empty($seller_info['avatar'])): ?>
                    <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($seller_info['avatar']); ?>" alt="Store">
                <?php else: ?>
                    <i class="fas fa-store"></i>
                <?php endif; ?>
            </div>

            <div class="store-details">
                <h1 class="store-name"><?php echo htmlspecialchars($seller_info['username']); ?></h1>
                
                <div class="store-stats">
                    <div class="store-stat">
                        <div class="store-stat-value"><?php echo number_format($seller_info['product_count']); ?></div>
                        <div class="store-stat-label">Sản phẩm</div>
                    </div>
                    <div class="store-stat">
                        <div class="store-stat-value"><?php echo number_format($seller_info['avg_rating'], 1); ?></div>
                        <div class="store-stat-label">Đánh giá</div>
                    </div>
                    <div class="store-stat">
                        <div class="store-stat-value">98%</div>
                        <div class="store-stat-label">Phản hồi tích cực</div>
                    </div>
                </div>

                <div class="store-status">
                    <span class="badge"></span>
                    Online 3 phút trước
                </div>
            </div>

            <button class="follow-btn">
                <i class="fas fa-heart"></i> Theo dõi
            </button>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Cửa hàng không tồn tại
        </div>
        <?php endif; ?>
    </div>

    <!-- Shop Navigation -->
    <?php if ($seller_info): ?>
    <div class="shop-nav">
        <a href="?seller=<?php echo $seller_id; ?>" class="shop-nav-item active">
            <i class="fas fa-home"></i> Trang chủ
        </a>
        <a href="?seller=<?php echo $seller_id; ?>&tab=products" class="shop-nav-item">
            <i class="fas fa-box"></i> Tất cả sản phẩm
        </a>
        <a href="?seller=<?php echo $seller_id; ?>&tab=reviews" class="shop-nav-item">
            <i class="fas fa-star"></i> Đánh giá
        </a>
    </div>

    <!-- Search Bar -->
    <div class="shop-search">
        <input type="text" id="search-input" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
        <button onclick="searchProducts()">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
    </div>

    <!-- Products Section -->
    <div class="shop-products">
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card"
                        onclick="window.location.href='<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo $product['id']; ?>'">
                        <div class="product-image">
                            <!-- Cart icon on right -->
                            <?php if ($product['stock_quantity'] > 0): ?>
                            <button type="button" class="cart-icon-btn" onclick="event.stopPropagation(); addToCart(event, <?php echo $product['id']; ?>)" title="Thêm vào giỏ">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                            <?php endif; ?>
                            
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <!-- Spec Tags (Category + Condition) -->
                            <div class="product-specs">
                                <?php if (!empty($product['category_name'])): ?>
                                    <span class="spec-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <?php endif; ?>
                                <span class="spec-tag"><?php echo getConditionText($product['condition_status']); ?></span>
                            </div>
                            
                            <!-- Product Title -->
                            <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                            
                            <!-- Price Section -->
                            <div class="product-price-section">
                                <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                    <span class="discount-percent">-<?php echo round((1 - $product['price']/$product['original_price']) * 100); ?>%</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Rating & Sales -->
                            <div class="product-rating">
                                <span class="stars">
                                    <i class="fas fa-star"></i>
                                    <?php echo isset($product['rating']) ? number_format($product['rating'], 1) : '5.0'; ?>
                                </span>
                                <span class="separator">•</span>
                                <span class="sales">Đã bán <?php echo isset($product['sales_count']) ? number_format($product['sales_count']) : rand(10, 500); ?></span>
                            </div>
                        </div>
                        
                        <!-- Quick Add Button (appears on hover) -->
                        <div class="product-hover-action" onclick="event.stopPropagation();">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <button type="button" class="btn-quick-add" onclick="addToCart(event, <?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                </button>
                            <?php else: ?>
                                <button class="btn-quick-add disabled" disabled>
                                    <i class="fas fa-ban"></i> Hết hàng
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <?php if ($page > 1): ?>
                    <a href="?seller=<?php echo $seller_id; ?>&p=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item">
                        <i class="fas fa-chevron-left"></i> Đầu
                    </a>
                    <a href="?seller=<?php echo $seller_id; ?>&p=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-item disabled">
                        <i class="fas fa-chevron-left"></i> Đầu
                    </span>
                    <span class="pagination-item disabled">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                ?>

                <?php if ($start > 1): ?>
                    <a href="?seller=<?php echo $seller_id; ?>&p=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item">1</a>
                    <?php if ($start > 2): ?>
                        <span class="pagination-item disabled">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-item active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?seller=<?php echo $seller_id; ?>&p=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?>
                        <span class="pagination-item disabled">...</span>
                    <?php endif; ?>
                    <a href="?seller=<?php echo $seller_id; ?>&p=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item">
                        <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?seller=<?php echo $seller_id; ?>&p=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="?seller=<?php echo $seller_id; ?>&p=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item">
                        Cuối <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-item disabled">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                    <span class="pagination-item disabled">
                        Cuối <i class="fas fa-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>Không có sản phẩm</h3>
                <p>Cửa hàng chưa có sản phẩm nào</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>

    <script>
        function searchProducts() {
            const searchInput = document.getElementById('search-input').value;
            const seller = <?php echo $seller_id; ?>;
            
            if (searchInput.trim()) {
                window.location.href = `?seller=${seller}&search=${encodeURIComponent(searchInput)}`;
            }
        }

        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
    </script>
</body>
</html>

