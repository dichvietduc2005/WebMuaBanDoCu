<?php
// modules/user/search_functions.php
// Hàm tìm kiếm sản phẩm cho user

require_once(__DIR__ . '/../../Models/extra/Search.php');




/**
 * Tìm kiếm sản phẩm nâng cao
 * 
 * @param PDO $pdo
 * @param string $keyword Từ khóa tìm kiếm
 * @param int $category_id ID danh mục (0 = tất cả)
 * @param string $condition Tình trạng sản phẩm
 * @param int $min_price Giá tối thiểu
 * @param int $max_price Giá tối đa
 * @param string $sort_by Sắp xếp theo (newest, oldest, price_asc, price_desc, popular)
 * @param bool $in_stock Chỉ hiện sản phẩm còn hàng
 * @param int $limit Số lượng kết quả mỗi trang
 * @param int $offset Vị trí bắt đầu
 * @return array
 */
function searchProducts(PDO $pdo, $keyword = '', $category_id = 0, $condition = '', $min_price = 0, $max_price = 0, $sort_by = 'newest', $in_stock = true, $limit = 20, $offset = 0) {
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    
    // Tìm kiếm theo từ khóa trong title, description và category name
    if (!empty($keyword)) {
        $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $keyword_param = "%$keyword%";
        $params[] = $keyword_param;
        $params[] = $keyword_param;
        $params[] = $keyword_param;
    }
    
    // Lọc theo danh mục
    if ($category_id > 0) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category_id;
    }
    
    // Lọc theo tình trạng
    if (!empty($condition)) {
        $where_conditions[] = "p.condition_status = ?";
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
    
    // Chỉ hiện sản phẩm còn hàng
    if ($in_stock) {
        $where_conditions[] = "p.stock_quantity > 0";
    }
    
    // Xây dựng ORDER BY
    $order_clause = "";
    switch ($sort_by) {
        case 'oldest':
            $order_clause = "ORDER BY p.created_at ASC";
            break;
        case 'price_asc':
            $order_clause = "ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $order_clause = "ORDER BY p.price DESC";
            break;
        case 'popular':
            $order_clause = "ORDER BY p.featured DESC, p.created_at DESC";
            break;
        case 'newest':
        default:
            $order_clause = "ORDER BY p.created_at DESC";
            break;
    }
      $where_sql = implode(' AND ', $where_conditions);
    
    $sql = "SELECT p.*, pi.image_path, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $where_sql
            $order_clause
            LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Đếm tổng số sản phẩm theo điều kiện tìm kiếm
 */
function countSearchResults(PDO $pdo, $keyword = '', $category_id = 0, $condition = '', $min_price = 0, $max_price = 0, $in_stock = true) {
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    
    if (!empty($keyword)) {
        $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $keyword_param = "%$keyword%";
        $params[] = $keyword_param;
        $params[] = $keyword_param;
        $params[] = $keyword_param;
    }
    
    if ($category_id > 0) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category_id;
    }
    
    if (!empty($condition)) {
        $where_conditions[] = "p.condition_status = ?";
        $params[] = $condition;
    }
    
    if ($min_price > 0) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $min_price;
    }
    if ($max_price > 0) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $max_price;
    }
    
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
function getSearchSuggestions(PDO $pdo, $keyword, $limit = 10) {
    if (strlen($keyword) < 2) {
        return [];
    }
    
    $sql = "SELECT DISTINCT p.title
            FROM products p
            WHERE p.status = 'active' AND p.stock_quantity > 0 AND p.title LIKE ?
            ORDER BY p.title ASC
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$keyword%", $limit]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
