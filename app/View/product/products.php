<?php
require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/products.css">
</head>
<body>    
    <?php renderHeader($pdo); ?>
<div class="container">
        <a href="../TrangChu.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        
        <?php if ($category): ?>
        <?php
        // Lấy tên danh mục
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$category]);
        $category_name = $stmt->fetchColumn();
        ?>
        <nav aria-label="breadcrumb" style="margin-bottom: 20px;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../TrangChu.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="categories.php">Danh mục</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($category_name); ?></li>
            </ol>
        </nav>
        <?php endif; ?>
        
        <div class="header">
            <h1>
                <?php if ($category): ?>
                    <?php echo htmlspecialchars($category_name); ?>
                <?php else: ?>
                    Danh sách sản phẩm
                <?php endif; ?>
            </h1>
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
                <div class="product-card" onclick="window.location.href='Product_detail.php?id=<?php echo $product['id']; ?>'" style="cursor: pointer;">
                    <div class="product-image">
                    <?php if ($product['image_path']): ?>
                                    <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($product['title']); ?>"
                                        style="width: 100%; height: 220px; object-fit: contain;">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 220px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        <i class="fas fa-image" style="font-size: 48px;"></i>
                                    </div>
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
                        <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="product-actions">
                            <button class="btn btn-primary btn-sm add-to-cart-btn" 
                                    onclick="event.stopPropagation(); addToCartFromList(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </div>
                        <?php endif; ?>
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
    <?php footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toast notification function
        function showToast(type, title, message) {
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            const toastEl = document.createElement('div');
            toastEl.className = `toast show align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.style.minWidth = '300px';
            
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toastContainer.appendChild(toastEl);

            setTimeout(() => {
                toastEl.classList.remove('show');
                setTimeout(() => toastEl.remove(), 300);
            }, 3000);
        }

        // Add to cart function
        async function addToCartFromList(productId) {
            const button = event.target.closest('.add-to-cart-btn');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
            button.disabled = true;

            try {
                const response = await fetch('/WebMuaBanDoCu/app/Controllers/cart/CartController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=add&product_id=${productId}&quantity=1`
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'Thành công!', 'Đã thêm sản phẩm vào giỏ hàng');
                    
                    // Update cart count in header
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(element => {
                        const currentCount = parseInt(element.textContent) || 0;
                        const newCount = currentCount + 1;
                        if (newCount <= 9) {
                            element.textContent = newCount;
                        } else {
                            element.textContent = '9+';
                        }
                        element.style.display = 'flex';
                    });
                } else {
                    showToast('error', 'Lỗi!', data.message || 'Không thể thêm sản phẩm vào giỏ hàng');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'Lỗi!', 'Không thể kết nối đến máy chủ');
            } finally {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
    </script>
</body>
</html>
