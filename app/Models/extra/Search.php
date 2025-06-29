<?php

class SearchModel
{
    public static function searchProducts($pdo, $keyword = '', $category_id = 0, $condition = '', $min_price = 0, $max_price = 0, $sort_by = 'newest', $in_stock = true, $limit = 20, $offset = 0)
    {
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
}