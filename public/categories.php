<?php
require_once '../config/config.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sản phẩm - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fb;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        .category-card {
            background: white;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #3a86ff, #8338ec);
            color: white;
            text-decoration: none;
        }
        .category-card:hover .category-icon {
            color: white;
            transform: scale(1.1);
        }
        .category-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #3a86ff;
            transition: all 0.3s ease;
        }
        .category-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .category-count {
            font-size: 14px;
            opacity: 0.8;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3a86ff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="TrangChu.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        
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
</body>
</html>
