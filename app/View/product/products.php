<?php
// Sử dụng đường dẫn tuyệt đối thay vì đường dẫn tương đối
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/WebMuaBanDoCu';
require_once $root_path . '/config/config.php';
// Include file Search.php chứa class SearchModel
require_once $root_path . '/app/Models/extra/Search.php';
// Autoloader sẽ tự động load ExtraController

// Lấy danh sách sản phẩm
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Lấy các tham số tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $search; // Hỗ trợ cả 2 tham số
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$in_stock = isset($_GET['in_stock']) ? (bool)$_GET['in_stock'] : true;

if ($keyword || $category || $condition || $min_price || $max_price) {
    // Sử dụng SearchModel::searchProducts thay vì hàm searchProducts
    $products = SearchModel::searchProducts($pdo, $keyword, $category, $condition, $min_price, $max_price, $sort_by, $in_stock, $per_page, $offset);
    
    // Sử dụng SearchModel::countSearchResults thay vì hàm tự định nghĩa
    $total_products = SearchModel::countSearchResults($pdo, $keyword, $category, $condition, $min_price, $max_price, $in_stock);
    $total_pages = ceil($total_products / $per_page);
} else {
    $where_conditions = ["p.status = 'active'"];
    $params = [];

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
    $total_pages = ceil($total_products / $per_page);

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
}

// Helper functions
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' ₫';
    }
}

if (!function_exists('getConditionText')) {
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
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Web Mua Bán Đồ Cũ</title>
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
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            height: 220px;
            width: 100%;
            object-fit: cover;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        .product-content {
            padding: 20px;
        }
        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #3a86ff;
            margin-bottom: 15px;
        }
        .product-meta {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #6c757d;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        .pagination a, .pagination .current {
            padding: 10px 15px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
        }
        .pagination .current {
            background: #3a86ff;
            color: white;
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
<body>    <div class="container">
        <a href="../../../public/TrangChu.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        
        <div class="header">
            <h1>Danh sách sản phẩm</h1>
            <?php if ($search): ?>
                <p>Kết quả tìm kiếm cho: "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
            <?php endif; ?>
            <p>Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
        </div>
        
        <div class="products-grid">
            <?php if (empty($products)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                    <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>Không tìm thấy sản phẩm</h3>
                    <p>Hãy thử tìm kiếm với từ khóa khác!</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image_path']): ?>
                            <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>"
                                 style="width: 100%; height: 220px; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 48px;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                        <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                        <div class="product-meta">
                            <div>
                                <i class="fas fa-star"></i> <?php echo getConditionText($product['condition_status']); ?>
                            </div>
                            <div>
                                <i class="fas fa-box"></i> Còn <?php echo $product['stock_quantity']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>">
                    <i class="fas fa-chevron-left"></i> Trước
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>">
                    Sau <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
