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
        $page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        if ($page < 1)
            $page = 1;

        $per_page = 12;
        $offset = ($page - 1) * $per_page;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $search;
        $category = isset($_GET['category']) ? (int) $_GET['category'] : (isset($_GET['seller']) ? 0 : 0);
        $condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
        $min_price = isset($_GET['min_price']) ? (int) $_GET['min_price'] : 0;
        $max_price = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 0;
        $sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
        $in_stock = isset($_GET['in_stock']) ? (bool) $_GET['in_stock'] : true;

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
            function formatPrice($price)
            {
                return number_format($price, 0, ',', '.') . ' ₫';
            }
        }

        if (!function_exists('getConditionText')) {
            function getConditionText($condition)
            {
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
    <!-- Unified Product Card Styles (Home & Search) -->
    <link rel="stylesheet"
        href="<?php echo BASE_URL; ?>public/assets/css/unified-product-cards.css?v=<?php echo time(); ?>">
    <!-- Home Page Improvements - Hero & Product Cards -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/home-improvements.css">
    <!-- Mobile Responsive CSS for Product Pages (Loaded last) -->
    <link rel="stylesheet"
        href="<?php echo BASE_URL; ?>public/assets/css/mobile-product-pages.css?v=<?php echo time(); ?>">
    <style>
        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--gradient-accent, linear-gradient(135deg, #ec4899, #4f46e5));
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Ensure image doesn't overlap badges */
        .product-image {
            position: relative;
        }

        /* Support for second badge (Modern style) */
        .product-badge.badge-new {
            background: linear-gradient(135deg, #22c55e, #10b981);
            top: 40px;
            /* Offset if both exist */
        }
    </style>
</head>

<body class="bg-[#f0f2f5] font-sans text-gray-800">
    <?php renderHeader($pdo); ?>

    <div class="container mx-auto px-4 py-4 max-w-7xl">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="flex list-none p-0 text-sm text-gray-500">
                <li class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=home"
                        class="hover:text-blue-600 transition-colors"><i class="fas fa-home mr-1"></i> Trang chủ</a>
                    <span class="mx-2 text-gray-300">›</span>
                </li>
                <?php if ($category): ?>
                    <li class="flex items-center">
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=products"
                            class="hover:text-blue-600 transition-colors">Sản phẩm</a>
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



        <div class="section-header"
            style="margin-top: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 30px;">
            <div class="text-left">
                <h1 class="section-title">
                    <?php if ($category): ?>
                        Danh sách sản phẩm trong ngành
                    <?php else: ?>
                        Tất cả danh mục sản phẩm
                    <?php endif; ?>
                </h1>
                <?php if ($search): ?>
                    <p class="text-gray-600 mt-2">Kết quả tìm kiếm cho:
                        "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
                <?php endif; ?>
                <p class="text-gray-500 mt-1">Tìm thấy <span
                        class="font-semibold text-blue-600"><?php echo $total_products; ?></span> sản phẩm</p>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="w-full flex flex-col items-center justify-center text-center py-24 px-4 text-gray-500">
                <i class="fas fa-box-open text-7xl mb-6 opacity-40"></i>
                <h3 class="text-2xl font-semibold mb-3">Không tìm thấy sản phẩm</h3>
                <p class="text-gray-400">Hãy thử tìm kiếm với từ khóa khác!</p>
            </div>
        <?php else: ?>
            <div class="products-grid mb-8">
                <?php foreach ($products as $product): ?>
                    <div class="product-card"
                        onclick="window.location.href='?page=product_detail&id=<?php echo $product['id']; ?>'">
                        <div class="product-image">
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo BASE_URL . 'public/' . htmlspecialchars($product['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($product['title']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Badges -->
                            <?php if ($product['featured']): ?>
                                <span class="product-badge">Nổi bật</span>
                            <?php endif; ?>
                            <?php if ($product['condition_status'] === 'new' && !$product['featured']): ?>
                                <span class="product-badge badge-new">Mới</span>
                            <?php elseif ($product['condition_status'] === 'new'): ?>
                                <span class="product-badge badge-new" style="top: 40px;">Mới</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-content">
                            <div class="product-specs">
                                <?php if (!empty($product['category_name'])): ?>
                                    <span class="spec-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <?php endif; ?>
                                <span class="spec-tag"><?php echo getConditionText($product['condition_status']); ?></span>
                            </div>

                            <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>

                            <div class="product-price-section">
                                <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                    <span
                                        class="discount-percent">-<?php echo round((1 - $product['price'] / $product['original_price']) * 100); ?>%</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-meta"
                                style="display: flex; justify-content: space-between; font-size: 11px; color: #9ca3af; margin-bottom: 8px;">
                                <span class="location"><i
                                        class="fas fa-location-dot me-1"></i><?php echo htmlspecialchars($product['location'] ?? 'Toàn quốc'); ?></span>
                                <span class="time"><i
                                        class="far fa-clock me-1"></i><?php echo isset($product['created_at']) ? date('d/m/Y', strtotime($product['created_at'])) : 'Vừa xong'; ?></span>
                            </div>

                            <div class="product-footer">
                                <div class="product-rating">
                                    <span class="stars">
                                        <i class="fas fa-star"></i>
                                        <?php echo isset($product['rating']) && $product['rating'] > 0 ? number_format($product['rating'], 1) : '5.0'; ?>
                                    </span>
                                    <span class="separator">•</span>
                                    <span class="sales">Đã bán
                                        <?php echo isset($product['sales_count']) && $product['sales_count'] > 0 ? number_format($product['sales_count']) : rand(0, 50); ?></span>
                                </div>

                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <button type="button" class="btn-add-cart-footer"
                                        onclick="event.stopPropagation(); addToCartFromList(<?php echo $product['id']; ?>)"
                                        title="Thêm vào giỏ">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination flex justify-center gap-2 mt-8 mb-12">
            <?php if ($page > 1): ?>
                <a href="?page=products&p=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                    class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            ?>

            <?php if ($start > 1): ?>
                <a href="?page=products&p=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                    class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">1</a>
                <?php if ($start > 2): ?>
                    <span class="px-2 py-2 text-gray-500">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span
                        class="px-4 py-2 bg-blue-600 border border-blue-600 rounded-lg text-white font-medium cursor-default shadow-md"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=products&p=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?>
                    <span class="px-2 py-2 text-gray-500">...</span>
                <?php endif; ?>
                <a href="?page=products&p=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                    class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline"><?php echo $total_pages; ?></a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=products&p=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                    class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors no-underline">
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
                const response = await fetch('<?php echo BASE_URL; ?>app/Controllers/cart/CartController.php', {
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