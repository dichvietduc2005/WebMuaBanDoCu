<?php
require_once('../../../config/config.php');
require_once('../../Models/extra/Search.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

$query = $_GET['q'] ?? '';
$results = [];

if (!empty($query)) {
    $results = SearchModel::searchProducts($pdo, $query);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm - Web Mua Bán Đồ Cũ</title>
    <link rel="stylesheet" href="../../../public/assets/css/search.css">
    <link rel="stylesheet" href="../../../public/assets/css/footer.css">
    <!-- Mobile Responsive CSS for Search Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-search-pages.css">
</head>
<body>
    <?php renderHeader($pdo); ?>
    <div class="container">
        <div class="search-header">
            <h1>Kết quả tìm kiếm</h1>
            <?php if (!empty($query)): ?>
                <p class="search-query">Kết quả cho: <strong>"<?= htmlspecialchars($query) ?>"</strong> (<?= count($results) ?> sản phẩm)</p>
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
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Không tìm thấy sản phẩm nào</h3>
                    <p>Hãy thử tìm kiếm với từ khóa khác hoặc</p>
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
    
    
    value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php footer(); ?>
</body>
</html>
