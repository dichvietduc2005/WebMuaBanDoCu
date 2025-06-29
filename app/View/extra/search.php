<?php
require_once('../../../config/config.php');
require_once('../../Controllers/extra/ExtraController.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

$query = $_GET['q'] ?? '';
$results = [];

if (!empty($query)) {
    $results = searchProducts($pdo, $query);
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
</head>
<body>
    <?php renderHeader($pdo); ?>
    <div class="container">
        <h1>Kết quả tìm kiếm</h1>
        
        <?php if (!empty($query)): ?>
            <p>Kết quả cho: <strong><?= htmlspecialchars($query) ?></strong></p>
            
            <?php if (count($results) > 0): ?>
                <div class="search-results">
                    <?php foreach ($results as $product): ?>
                        <div class="product-item">
                            <h3><?= htmlspecialchars($product['title']) ?></h3>
                            <p><?= htmlspecialchars($product['description']) ?></p>
                            <p class="price"><?= number_format($product['price']) ?> VNĐ</p>
                        </div>
                    <?php endforeach; ?>
                    
                </div>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm nào.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Vui lòng nhập từ khóa tìm kiếm.</p>
        <?php endif; ?>
        
        <a href="../../../public/TrangChu.php">Về trang chủ</a>
    </div>
    
    
    value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
    <?php footer(); ?>
</body>
</html>
