<?php
// app/View/product/products.php
// View chỉ hiển thị dữ liệu được truyền từ FrontendProductController
// Các biến: $products, $total_products, $total_pages, $page, $search, $category, etc.

// Always load config to ensure $pdo is available
require_once __DIR__ . '/../../../config/config.php';
global $pdo;

require_once __DIR__ . '/../../Components/header/Header.php';
require_once __DIR__ . '/../../Components/footer/Footer.php';

// If this page is accessed directly (not through controller), fetch products
if (!isset($products)) {
    require_once __DIR__ . '/../../Core/Autoloader.php';
    require_once __DIR__ . '/../../Controllers/product/FrontendProductController.php';
    
    // Load controller
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    try {
        $controller = new FrontendProductController();
        // Set up variables via reflection (indirect call)
        ob_start();
        
        // Manually execute controller logic here instead of calling index()
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page < 1) $page = 1;
        
        $per_page = 12;
        $offset = ($page - 1) * $per_page;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $search;
        $category = isset($_GET['category']) ? (int)$_GET['category'] : (isset($_GET['seller']) ? 0 : 0);
        $condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
        $min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
        $max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
        $sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
        $in_stock = isset($_GET['in_stock']) ? (bool)$_GET['in_stock'] : true;

        $products = [];
        $total_products = 0;

        // Get products
        $where_conditions = ["p.status = 'active'"];
        $params = [];

        if ($category) {
            $where_conditions[] = "p.category_id = ?";
            $params[] = $category;
        }

        $where_sql = implode(' AND ', $where_conditions);

        // Count total
        $count_sql = "SELECT COUNT(*) FROM products p WHERE $where_sql";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_products = $count_stmt->fetchColumn();

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

        $total_pages = ceil($total_products / $per_page);
        $category_name = '';
        
        // Helper functions for View
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
        
        ob_end_clean();
    } catch (Exception $e) {
        // Set default values on error
        $products = [];
        $total_products = 0;
        $total_pages = 1;
        $page = 1;
        $search = '';
        $category = 0;
        $category_name = '';
    }
} else {
    // Initialize default values to prevent undefined variable warnings
    $products = $products ?? [];
    $total_products = $total_products ?? 0;
    $total_pages = $total_pages ?? 1;
    $page = $page ?? 1;
    $search = $search ?? '';
    $category = $category ?? 0;
    $category_name = $category_name ?? '';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Load Tailwind (via admin-style.css) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/admin-style.css">
    <!-- Mobile Responsive CSS for Product Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-product-pages.css">
    <style>
        /* Custom overrides suitable for this page that Tailwind arbitrary values make too verbose */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-[#f5f5f5] font-sans text-gray-800">
    <?php renderHeader($pdo); ?>
    
    <div class="container mx-auto px-6 py-6 max-w-7xl">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="flex list-none p-0 text-sm text-gray-500">
                <li class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=home" class="hover:text-blue-600 transition-colors"><i class="fas fa-home mr-1"></i> Trang chủ</a>
                    <span class="mx-2 text-gray-300">›</span>
                </li>
                <?php if ($category): ?>
                    <li class="flex items-center">
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=products" class="hover:text-blue-600 transition-colors">Sản phẩm</a>
                        <span class="mx-2 text-gray-300">›</span>
                    </li>
                    <li class="text-blue-600 font-semibold"><?php echo htmlspecialchars($category_name ?? ''); ?></li>
                <?php else: ?>
                    <li class="text-blue-600 font-semibold">Tất cả sản phẩm</li>
                <?php endif; ?>
            </ol>
        </nav>
        
        <?php if ($category): ?>
        <?php
        // Lấy tên danh mục
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$category]);
        $category_name = $stmt->fetchColumn();
        ?>
        <?php endif; ?>
        
        <?php if ($category): ?>
            <!-- Category Hero Banner -->
            <div class="category-banner bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 mb-8 text-white relative overflow-hidden shadow-lg border border-blue-500/20">
                <div class="relative z-10">
                    <h2 class="text-4xl font-extrabold mb-3 tracking-tight"><?php echo htmlspecialchars($category_name); ?></h2>
                    <p class="text-blue-100 text-lg opacity-90 max-w-lg leading-relaxed">Khám phá hàng ngàn sản phẩm <?php echo mb_strtolower(htmlspecialchars($category_name)); ?> chất lượng, giá rẻ đang được đăng bán hôm nay.</p>
                </div>
                <!-- Decorative Shapes -->
                <div class="absolute top-[-20%] right-[-5%] w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-[-30%] left-[-5%] w-48 h-48 bg-blue-300/20 rounded-full blur-2xl"></div>
            </div>
        <?php endif; ?>
        
        <div class="text-left mb-8 pt-4 border-b border-gray-200 pb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <?php if ($category): ?>
                    Danh sách sản phẩm trong ngành
                <?php else: ?>
                    Tất cả danh mục sản phẩm
                <?php endif; ?>
            </h1>
            <?php if ($search): ?>
                <p class="text-gray-600">Kết quả tìm kiếm cho: "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
            <?php endif; ?>
            <p class="text-gray-500 mt-1">Tìm thấy <span class="font-semibold text-blue-600"><?php echo $total_products; ?></span> sản phẩm</p>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="w-full flex flex-col items-center justify-center text-center py-24 px-4 text-gray-500">
                <i class="fas fa-box-open text-7xl mb-6 opacity-40"></i>
                <h3 class="text-2xl font-semibold mb-3">Không tìm thấy sản phẩm</h3>
                <p class="text-gray-400">Hãy thử tìm kiếm với từ khóa khác!</p>
            </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
                <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-[0_2px_15px_rgba(0,0,0,0.05)] hover:translate-y-[-6px] hover:shadow-[0_10px_30px_rgba(0,0,0,0.1)] transition-all duration-300 cursor-pointer group flex flex-col h-full ring-1 ring-gray-100" 
                     onclick="window.location.href='Product_detail.php?id=<?php echo $product['id']; ?>'">
                    <div class="relative h-[220px] w-full bg-gray-100 overflow-hidden">
                        <?php if ($product['image_path']): ?>
                            <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product['image_path']); ?>"
                                class="w-full h-full object-contain p-4 transition-transform duration-300 group-hover:scale-105"
                                alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-image text-5xl"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Badges/Tags can go here -->
                        <?php if ($product['condition_status'] === 'new'): ?>
                            <span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">Mới</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-5 flex flex-col flex-grow">
                        <!-- Category name if available -->
                        <?php if (!empty($product['category_name'])): ?>
                            <div class="text-xs text-blue-500 font-medium mb-1 uppercase tracking-wide">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h3>
                        
                        <div class="mt-auto">
                            <div class="text-xl font-bold text-blue-600 mb-3">
                                <?php echo formatPrice($product['price']); ?>
                            </div>
                            
                            <div class="flex justify-between items-center text-sm text-gray-500 mb-4 border-t border-gray-100 pt-3">
                                <span>
                                    <i class="fas fa-tag mr-1 text-gray-400"></i> <?php echo getConditionText($product['condition_status']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-box mr-1 text-gray-400"></i> SL: <?php echo $product['stock_quantity']; ?>
                                </span>
                            </div>
                            
                            <?php if ($product['stock_quantity'] > 0): ?>
                            <button class="add-to-cart-btn w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm hover:shadow active:scale-95" 
                                    onclick="event.stopPropagation(); addToCartFromList(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                            <?php else: ?>
                            <button class="w-full bg-gray-300 text-gray-500 font-medium py-2 px-4 rounded-lg cursor-not-allowed flex items-center justify-center gap-2" disabled>
                                <i class="fas fa-ban"></i> Hết hàng
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination flex justify-center gap-2 mt-8 mb-12">
            <?php if ($page > 1): ?>
                <a href="?page=products&p=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php 
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
            ?>
            
            <?php if ($start > 1): ?>
                <a href="?page=products&p=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">1</a>
                <?php if ($start > 2): ?>
                    <span class="px-2 py-2 text-gray-500">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="px-4 py-2 bg-blue-600 border border-blue-600 rounded-lg text-white font-medium cursor-default shadow-md"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=products&p=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?>
                    <span class="px-2 py-2 text-gray-500">...</span>
                <?php endif; ?>
                <a href="?page=products&p=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline"><?php echo $total_pages; ?></a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=products&p=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">
                    <i class="fas fa-chevron-right"></i>
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
