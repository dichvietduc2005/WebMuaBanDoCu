<?php
// app/View/product/categories.php
// View hiển thị tất cả danh mục sản phẩm

// Load config for BASE_URL and $pdo
require_once __DIR__ . '/../../../config/config.php';
global $pdo;

// Auto-fetch categories if not provided (Direct Access)
if (!isset($categories)) {
    try {
        $stmt = $pdo->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            WHERE c.status = 'active' 
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $categories = [];
    }
}

// Category icons with colors
$category_styles = [
    'am-nhac-nhac-cu' => ['icon' => 'fas fa-music', 'color' => '#e91e63', 'bg' => '#fce4ec'],
    'dien-thoai-may-tinh-bang' => ['icon' => 'fas fa-mobile-alt', 'color' => '#2196f3', 'bg' => '#e3f2fd'],
    'laptop-may-tinh' => ['icon' => 'fas fa-laptop', 'color' => '#673ab7', 'bg' => '#ede7f6'],
    'thoi-trang-phu-kien' => ['icon' => 'fas fa-tshirt', 'color' => '#ff9800', 'bg' => '#fff3e0'],
    'do-gia-dung-noi-that' => ['icon' => 'fas fa-couch', 'color' => '#795548', 'bg' => '#efebe9'],
    'xe-co-phuong-tien' => ['icon' => 'fas fa-motorcycle', 'color' => '#f44336', 'bg' => '#ffebee'],
    'sach-van-phong-pham' => ['icon' => 'fas fa-book', 'color' => '#4caf50', 'bg' => '#e8f5e9'],
    'the-thao-giai-tri' => ['icon' => 'fas fa-futbol', 'color' => '#00bcd4', 'bg' => '#e0f7fa'],
    'dien-may-cong-nghe' => ['icon' => 'fas fa-tv', 'color' => '#607d8b', 'bg' => '#eceff1'],
    'me-va-be' => ['icon' => 'fas fa-baby', 'color' => '#ff4081', 'bg' => '#fce4ec'],
    'suc-khoe-lam-dep' => ['icon' => 'fas fa-heart', 'color' => '#e91e63', 'bg' => '#fce4ec'],
    'thu-cung-phu-kien' => ['icon' => 'fas fa-paw', 'color' => '#8bc34a', 'bg' => '#f1f8e9'],
    'am-thuc' => ['icon' => 'fas fa-utensils', 'color' => '#ff5722', 'bg' => '#fbe9e7'],
    'default' => ['icon' => 'fas fa-cube', 'color' => '#9e9e9e', 'bg' => '#fafafa']
];

require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sản phẩm - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-responsive-enhanced.css">
    
    <?php
    // Render frontend theme styles
    require_once __DIR__ . '/../../Components/frontend/FrontendThemeRenderer.php';
    $frontendTheme = new FrontendThemeRenderer();
    $frontendTheme->renderThemeStyles();
    ?>
    
    <style>
        .categories-page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 16px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .page-header p {
            color: #6b7280;
            font-size: 15px;
        }
        
        .categories-main-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .category-item {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px 16px;
            text-align: center;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        
        .category-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            border-color: transparent;
        }
        
        .category-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        
        .category-item:hover .category-icon-wrapper {
            transform: scale(1.1);
        }
        
        .category-icon-wrapper i {
            font-size: 28px;
        }
        
        .category-info h3 {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px 0;
            line-height: 1.4;
        }
        
        .category-info span {
            font-size: 13px;
            color: #6b7280;
        }
        
        @media (max-width: 768px) {
            .categories-main-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .category-item {
                padding: 16px 12px;
            }
            
            .category-icon-wrapper {
                width: 48px;
                height: 48px;
                border-radius: 12px;
            }
            
            .category-icon-wrapper i {
                font-size: 22px;
            }
            
            .category-info h3 {
                font-size: 13px;
            }
            
            .category-info span {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <?php renderHeader($pdo); ?>
    
    <div class="categories-page-container">
        <div class="page-header">
            <h1><i class="fas fa-th-large" style="color: var(--primary, #2563eb); margin-right: 10px;"></i>Danh mục sản phẩm</h1>
            <p>Khám phá <?php echo count($categories); ?> danh mục sản phẩm đa dạng</p>
        </div>
        
        <div class="categories-main-grid">
            <?php foreach ($categories as $category): 
                $style = $category_styles[$category['slug']] ?? $category_styles['default'];
            ?>
            <a href="category.php?slug=<?php echo $category['slug']; ?>" class="category-item">
                <div class="category-icon-wrapper" style="background: <?php echo $style['bg']; ?>;">
                    <i class="<?php echo $style['icon']; ?>" style="color: <?php echo $style['color']; ?>;"></i>
                </div>
                <div class="category-info">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <span><?php echo $category['product_count']; ?> sản phẩm</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php footer(); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
