<?php
// modules/user/search_functions.php
// Hàm tìm kiếm sản phẩm cho user

require_once(__DIR__ . '/../../Models/extra/Search.php');
require_once __DIR__ . '/../../Core/Database.php';

class ExtraController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Tìm kiếm sản phẩm với nhiều điều kiện
     */
    function searchProducts(PDO $pdo, $keyword = '', $category_id = 0, $condition = '', $min_price = 0, $max_price = 0, $sort_by = 'newest', $in_stock = true, $limit = 20, $offset = 0) {
        $where_conditions = ["p.status = 'active'"];
        $params = [];
        
        // Tìm kiếm theo từ khóa
        if (!empty($keyword)) {
            $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // Lọc theo danh mục
        if ($category_id > 0) {
            $where_conditions[] = "p.category_id = ?";
            $params[] = $category_id;
        }
        
        // Lọc theo tình trạng
        if (!empty($condition)) {
            $where_conditions[] = "p.condition = ?";
            $params[] = $condition;
        }
        
        // Lọc theo giá
        if ($min_price > 0) {
            $where_conditions[] = "p.price >= ?";
            $params[] = $min_price;
        }
        
        if ($max_price > 0) {
            $where_conditions[] = "p.price <= ?";
            $params[] = $max_price;
        }
        
        // Lọc sản phẩm còn hàng
        if ($in_stock) {
            $where_conditions[] = "p.stock_quantity > 0";
        }
        
        $where_sql = implode(' AND ', $where_conditions);
        
        // Sắp xếp
        $order_by = "p.created_at DESC"; // Default
        switch ($sort_by) {
            case 'price_asc':
                $order_by = "p.price ASC";
                break;
            case 'price_desc':
                $order_by = "p.price DESC";
                break;
            case 'name_asc':
                $order_by = "p.title ASC";
                break;
            case 'name_desc':
                $order_by = "p.title DESC";
                break;
            case 'oldest':
                $order_by = "p.created_at ASC";
                break;
        }
        
        $sql = "SELECT p.*, c.name as category_name, u.username as seller_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE $where_sql
                ORDER BY $order_by
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm số lượng kết quả tìm kiếm
     */
    function countSearchResults(PDO $pdo, $keyword = '', $category_id = 0, $condition = '', $min_price = 0, $max_price = 0, $in_stock = true) {
        $where_conditions = ["p.status = 'active'"];
        $params = [];
        
        // Tìm kiếm theo từ khóa
        if (!empty($keyword)) {
            $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // Lọc theo danh mục
        if ($category_id > 0) {
            $where_conditions[] = "p.category_id = ?";
            $params[] = $category_id;
        }
        
        // Lọc theo tình trạng
        if (!empty($condition)) {
            $where_conditions[] = "p.condition = ?";
            $params[] = $condition;
        }
        
        // Lọc theo giá
        if ($min_price > 0) {
            $where_conditions[] = "p.price >= ?";
            $params[] = $min_price;
        }
        
        if ($max_price > 0) {
            $where_conditions[] = "p.price <= ?";
            $params[] = $max_price;
        }
        
        // Lọc sản phẩm còn hàng
        if ($in_stock) {
            $where_conditions[] = "p.stock_quantity > 0";
        }
        
        $where_sql = implode(' AND ', $where_conditions);
        
        $sql = "SELECT COUNT(*) FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $where_sql";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Lấy gợi ý tìm kiếm (autocomplete)
     */
    // Trong file xử lý dữ liệu (ví dụ SearchModel hoặc ExtraController)
    public function getSearchSuggestions($pdo, $keyword, $limit = 10) {
        // Câu lệnh SQL lấy tiêu đề và ảnh đại diện của sản phẩm
        $sql = "SELECT p.id, p.title, pi.image_path 
                FROM products p 
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE p.title LIKE ? AND p.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT $limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$keyword%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log để kiểm tra
        error_log("Search results: " . json_encode($results));
        
        return $results;
    }

    /**
     * Lấy các từ khóa phổ biến
     */
    function getPopularKeywords(PDO $pdo, $limit = 20) {
        $sql = "SELECT p.title, COUNT(*) as count
                FROM products p
                WHERE p.status = 'active' AND p.stock_quantity > 0
                GROUP BY p.title
                ORDER BY count DESC, p.title ASC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

    
}

// Utility functions
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

if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        $badges = [
            'pending' => 'status-pending',
            'success' => 'status-confirmed',
            'failed' => 'status-cancelled'
        ];
        return $badges[$status] ?? 'status-pending';
    }
}

if (!function_exists('getStatusText')) {
    function getStatusText($status) {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'success' => 'Thành công',
            'failed' => 'Đã hủy'
        ];
        return $statuses[$status] ?? $status;
    }
}


?>
