<?php
function getPendingProducts($pdo) {
    $stmt = $pdo->query("
        SELECT p.*, u.username, pi.image_path
        FROM products p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.status = 'pending'
        ORDER BY p.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateProductStatus($pdo, $id, $status) {
    $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
    }

    function deleteProduct($pdo, $product_id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id=?" );
        return $stmt->execute([$product_id]);
    }

    /**
     * Lấy danh sách sản phẩm với bộ lọc linh hoạt cho trang quản lý sản phẩm
     */
    function getAllProducts($pdo, array $filters = []) {
        $sql = "
            SELECT p.*, u.username, pi.image_path
            FROM products p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['condition'])) {
            // Map giá trị tiếng Việt từ form sang giá trị ENUM trong database
            $conditionMap = [
                'Mới' => 'new',
                'Đã qua sử dụng' => ['like_new', 'good', 'fair'], // Có thể là một trong các giá trị này
                'Kém' => 'poor'
            ];
            $dbCondition = $conditionMap[$filters['condition']] ?? $filters['condition'];
            
            // Nếu là mảng, dùng IN clause
            if (is_array($dbCondition)) {
                $placeholders = [];
                foreach ($dbCondition as $idx => $val) {
                    $key = ':condition_' . $idx;
                    $placeholders[] = $key;
                    $params[$key] = $val;
                }
                $sql .= " AND p.condition_status IN (" . implode(',', $placeholders) . ")";
            } else {
                $sql .= " AND p.condition_status = :condition";
                $params[':condition'] = $dbCondition;
            }
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (p.title LIKE :kw1 OR u.username LIKE :kw2)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[':kw1'] = $keyword;
            $params[':kw2'] = $keyword;
        }

        $sql .= " ORDER BY p.featured DESC, p.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>