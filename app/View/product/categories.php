<?php
require_once '../../../config/config.php';
require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';
// Lấy danh sách danh mục với số lượng sản phẩm
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
    GROUP BY c.id 
    ORDER BY c.name
");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$category_icons = [
    'dien-thoai-may-tinh-bang' => 'fas fa-mobile-alt',
    'laptop-may-tinh' => 'fas fa-laptop',
    'thoi-trang-phu-kien' => 'fas fa-tshirt',
    'do-gia-dung-noi-that' => 'fas fa-home',
    'xe-co-phuong-tien' => 'fas fa-motorcycle',
    'sach-van-phong-pham' => 'fas fa-book',
    'the-thao-giai-tri' => 'fas fa-gamepad',
    'dien-may-cong-nghe' => 'fas fa-tv',
    'me-va-be' => 'fas fa-baby'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Danh mục sản phẩm - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/index.css">
    <link rel="stylesheet" href="../../../public/assets/css/Categories.css">
   </head>
<body>  
    <?php renderHeader($pdo); ?>
<div class="container">
        <a href="../TrangChu.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        
        <div class="header">
            <h1>Danh mục sản phẩm</h1>
            <p>Khám phá các danh mục sản phẩm đa dạng</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): 
                $icon = $category_icons[$category['slug']] ?? 'fas fa-cube';
            ?>
            <a href="category.php?slug=<?php echo $category['slug']; ?>" class="category-card">
                <i class="<?php echo $icon; ?> category-icon"></i>
                <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                <div class="category-count">
                    <?php echo $category['product_count']; ?> sản phẩm
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
