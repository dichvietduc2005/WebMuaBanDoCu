<?php

/**
 * Đọc log thao tác admin từ bảng admin_action_logs
 */
function getAdminActionLogs(PDO $pdo, array $filters = []): array
{
    $sql = "SELECT l.*, u.username AS admin_username
            FROM admin_action_logs l
            LEFT JOIN users u ON l.admin_id = u.id
            WHERE 1=1";

    $params = [];

    if (!empty($filters['admin_id'])) {
        $sql .= " AND l.admin_id = :admin_id";
        $params[':admin_id'] = (int)$filters['admin_id'];
    }

    if (!empty($filters['action'])) {
        $sql .= " AND l.action = :action";
        $params[':action'] = $filters['action'];
    }

    if (!empty($filters['product_id'])) {
        $sql .= " AND l.product_id = :product_id";
        $params[':product_id'] = (int)$filters['product_id'];
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND l.created_at >= :from_date";
        $params[':from_date'] = $filters['from_date'];
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND l.created_at <= :to_date";
        $params[':to_date'] = $filters['to_date'];
    }

    $sql .= " ORDER BY l.created_at DESC LIMIT 500";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


