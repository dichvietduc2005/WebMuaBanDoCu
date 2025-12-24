<?php
// app/View/product/categories.php
// View chỉ hiển thị dữ liệu được truyền từ FrontendProductController
// Các biến: $categories, $category_icons

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

// Default icons if not provided
if (!isset($category_icons)) {
    $category_icons = [
        'dien-thoai' => 'fas fa-mobile-alt',
        'laptop' => 'fas fa-laptop', 
        'may-tinh-bang' => 'fas fa-tablet-alt',
        'may-anh' => 'fas fa-camera',
        'phu-kien' => 'fas fa-headphones',
        'dong-ho' => 'fas fa-clock',
        'tivi' => 'fas fa-tv', 
        'tu-lanh' => 'fas fa-snowflake',
        'may-giat' => 'fas fa-tshirt',
        'xe-co' => 'fas fa-motorcycle'
    ];
}

require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Danh mục sản phẩm - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Load Tailwind (via admin-style.css) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/admin-style.css">
    <link rel="stylesheet" href="../../../public/assets/css/index.css">
    <!-- Mobile Responsive CSS for Product Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-product-pages.css">
</head>
<body class="bg-gray-50 font-sans text-gray-800">  
    <?php renderHeader($pdo); ?>
    
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Danh mục sản phẩm</h1>
            <p class="text-gray-600">Khám phá các danh mục sản phẩm đa dạng</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <?php foreach ($categories as $category): 
                $icon = $category_icons[$category['slug']] ?? 'fas fa-cube';
            ?>
            <a href="category.php?slug=<?php echo $category['slug']; ?>" 
               class="group block bg-white rounded-xl p-8 text-center shadow-sm hover:translate-y-[-4px] hover:shadow-lg hover:bg-gradient-to-br hover:from-blue-500 hover:to-indigo-600 transition-all duration-300 cursor-pointer no-underline">
                
                <i class="<?php echo $icon; ?> text-5xl mb-5 text-blue-500 transition-transform duration-300 group-hover:text-white group-hover:scale-110"></i>
                
                <div class="text-lg font-semibold mb-2 text-gray-800 group-hover:text-white transition-colors">
                    <?php echo htmlspecialchars($category['name']); ?>
                </div>
                
                <div class="text-sm text-gray-500 opacity-90 group-hover:text-blue-100 transition-colors">
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
