<?php
/**
 * FrontendProductController - Handle product listing for frontend
 */

require_once __DIR__ . '/../../Models/product/ProductModel.php';
require_once __DIR__ . '/../../Models/product/CategoryModel.php';
require_once __DIR__ . '/../../Models/extra/Search.php';

class FrontendProductController
{
    private $pdo;
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        // Get pagination parameters
        // Ensure page is at least 1 to prevent negative offset
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page < 1) $page = 1;
        
        $per_page = 12;
        $offset = ($page - 1) * $per_page;

        // Get filter parameters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $search;
        $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
        $min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
        $max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
        $sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
        $in_stock = isset($_GET['in_stock']) ? (bool)$_GET['in_stock'] : true;

        $products = [];
        $total_products = 0;

        // Logic to fetch products
        if ($keyword || $category || $condition || $min_price || $max_price) {
            // Log search action
            if (function_exists('log_user_action')) {
                $userId = $_SESSION['user_id'] ?? null;
                log_user_action($this->pdo, $userId, 'search', "Tìm kiếm sản phẩm: " . ($keyword ?: 'Lọc theo danh mục/category'), [
                    'keyword' => $keyword,
                    'category_id' => $category,
                    'condition' => $condition,
                    'min_price' => $min_price,
                    'max_price' => $max_price,
                    'sort_by' => $sort_by
                ]);
            }
            
            // Use SearchModel
            $products = SearchModel::searchProducts($this->pdo, $keyword, $category, $condition, $min_price, $max_price, $sort_by, $in_stock, $per_page, $offset);
            $total_products = SearchModel::countSearchResults($this->pdo, $keyword, $category, $condition, $min_price, $max_price, $in_stock);
        } else {
            // Log view action
            if (function_exists('log_user_action')) {
                $userId = $_SESSION['user_id'] ?? null;
                log_user_action($this->pdo, $userId, 'view_products', "Xem danh sách sản phẩm", [
                    'category_id' => $category,
                    'page' => $page
                ]);
            }

            // Default listing (Active products)
            // Can use ProductModel here or existing logic from view which constructed a query manually
            // Reusing logic from view for consistency but moved here
            
            $where_conditions = ["p.status = 'active'"];
            $params = [];

            if ($category) {
                $where_conditions[] = "p.category_id = ?";
                $params[] = $category;
            }

            $where_sql = implode(' AND ', $where_conditions);

            // Count total
            $count_sql = "SELECT COUNT(*) FROM products p WHERE $where_sql";
            $count_stmt = $this->pdo->prepare($count_sql);
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

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $total_pages = ceil($total_products / $per_page);

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

        // Pass variables to view
        require __DIR__ . '/../../View/product/products.php';
    }

    public function categories()
    {
        // Get list of categories with product count
        $stmt = $this->pdo->prepare("
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

        require __DIR__ . '/../../View/product/categories.php';
    }
}
