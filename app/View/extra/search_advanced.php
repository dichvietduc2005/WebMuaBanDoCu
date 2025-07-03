<?php
require_once('../../../config/config.php');
require_once('../../Models/extra/Search.php');
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
    <link rel="stylesheet" href="../../../public/assets/css/search.css">
    <link rel="stylesheet" href="../../../public/assets/css/footer.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <div class="search-results">
                        <?php foreach ($results as $product): ?>
                            <div class="product-item" onclick="window.location.href='../product/Product_detail.php?id=<?= $product['id'] ?>'">
                                <div class="product-image">
                                    <?php if (!empty($product['image_path'])): ?>
                                        <img src="/WebMuaBanDoCu/public/<?= htmlspecialchars($product['image_path']) ?>" 
                                             alt="<?= htmlspecialchars($product['title']) ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-content">
                                    <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                                    <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                    <div class="product-meta">
                                        <div class="product-price"><?= number_format($product['price']) ?> VNĐ</div>
                                        <div class="product-condition">
                                            <i class="fas fa-star"></i>
                                            <?= htmlspecialchars($product['condition_status'] ?? 'Tốt') ?>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <span class="category">
                                            <i class="fas fa-tag"></i>
                                            <?= htmlspecialchars($product['category_name'] ?? 'Khác') ?>
                                        </span>
                                        <span class="stock">
                                            <i class="fas fa-box"></i>
                                            Còn <?= $product['stock_quantity'] ?? 0 ?> sản phẩm
                                        </span>
                                    </div>
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
                        <i class="fas fa-search"></i>
                        <h3>Không tìm thấy sản phẩm nào</h3>
                        <p>Hãy thử:</p>
                        <ul>
                            <li>Kiểm tra lại chính tả</li>
                            <li>Sử dụng từ khóa khác</li>
                            <li>Thử tìm kiếm tổng quát hơn</li>
                            <li>Xóa một số bộ lọc</li>
                        </ul>
                        <a href="../../View/Home.php" class="btn-back-home">Về trang chủ</a>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php footer(); ?>
</body>
</html>
