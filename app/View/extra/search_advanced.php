<?php
require_once('../../../config/config.php');
require_once('../../Models/extra/Search.php');
require_once(__DIR__ . '/../../helpers.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

// Lấy parameters
$query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$condition = $_GET['condition'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));

// Pagination
$per_page = 12;
$offset = ($page - 1) * $per_page;

$results = [];
$total_count = 0;

if (!empty($query)) {
    $results = SearchModel::searchProducts(
        $pdo, 
        $query, 
        (int)$category, 
        $condition, 
        (int)$min_price, 
        (int)$max_price, 
        $sort, 
        true, 
        $per_page, 
        $offset
    );
    
    $total_count = SearchModel::countSearchResults(
        $pdo, 
        $query, 
        (int)$category, 
        $condition, 
        (int)$min_price, 
        (int)$max_price, 
        true
    );
}

$total_pages = ceil($total_count / $per_page);

// Lấy danh sách categories cho filter
$stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm<?= !empty($query) ? ' - ' . htmlspecialchars($query) : '' ?> - HIHand Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Unified Product Card Styles for Search -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/unified-product-cards.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/search.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/footer.css">
    <!-- Mobile Responsive CSS for Search Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-search-pages.css">
</head>
<body>
    <?php renderHeader($pdo); ?>
    
    <div class="search-container">
        <!-- Search Filters -->
        <div class="search-filters">
            <form method="GET" class="filters-form">
                <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
                
                <div class="filters-row">
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label for="category">Danh mục:</label>
                        <select name="category" id="category" class="form-select">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Condition Filter -->
                    <div class="filter-group">
                        <label for="condition">Tình trạng:</label>
                        <select name="condition" id="condition" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="new" <?= $condition == 'new' ? 'selected' : '' ?>>Mới</option>
                            <option value="like_new" <?= $condition == 'like_new' ? 'selected' : '' ?>>Như mới</option>
                            <option value="good" <?= $condition == 'good' ? 'selected' : '' ?>>Tốt</option>
                            <option value="fair" <?= $condition == 'fair' ? 'selected' : '' ?>>Khá tốt</option>
                            <option value="poor" <?= $condition == 'poor' ? 'selected' : '' ?>>Cần sửa chữa</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <label>Khoảng giá:</label>
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="Từ" value="<?= htmlspecialchars($min_price) ?>" class="form-control">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Đến" value="<?= htmlspecialchars($max_price) ?>" class="form-control">
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="filter-group">
                        <label for="sort">Sắp xếp:</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                            <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                            <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                            <option value="popular" <?= $sort == 'popular' ? 'selected' : '' ?>>Phổ biến</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        <a href="?q=<?= urlencode($query) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <div class="container">
            <div class="search-header">
                <h1>Kết quả tìm kiếm</h1>
                <?php if (!empty($query)): ?>
                    <p class="search-query">
                        Kết quả cho: <strong>"<?= htmlspecialchars($query) ?>"</strong>
                        <?php if ($total_count > 0): ?>
                            (<?= number_format($total_count) ?> sản phẩm)
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($query)): ?>
                <?php if (count($results) > 0): ?>
                    <div class="products-grid">
                        <?php foreach ($results as $product): ?>
                            <div class="product-card"
                                onclick="window.location.href='../product/Product_detail.php?id=<?= $product['id'] ?>'">
                                <div class="product-image">
                                    <!-- Cart icon on right -->
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <button type="button" class="cart-icon-btn" onclick="event.stopPropagation(); addToCart(event, <?= $product['id'] ?>)" title="Thêm vào giỏ">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($product['image_path'])): ?>
                                        <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product['image_path']); ?>" 
                                             alt="<?= htmlspecialchars($product['title']) ?>">
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
                                    
                                    <!-- Product Title (Blue) -->
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
                                        <button type="button" class="btn-quick-add" onclick="addToCart(event, <?= $product['id'] ?>)">
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
                            <nav aria-label="Search results pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                                <i class="fas fa-chevron-left"></i> Trước
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    ?>
                                    
                                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                Sau <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-inbox"></i>
                        <h3>Không có sản phẩm này</h3>
                        <p>Sản phẩm bạn tìm kiếm không tồn tại hoặc đã hết hàng</p>
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=home" class="btn-back-home">Về trang chủ</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-query">
                    <i class="fas fa-search"></i>
                    <h3>Vui lòng nhập từ khóa tìm kiếm</h3>
                    <p>Sử dụng thanh tìm kiếm phía trên để tìm sản phẩm bạn cần</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>
    <?php footer(); ?>
</body>
</html>
