<?php

/**
 * Đọc log hành vi người dùng từ bảng user_logs
 */
function getUserActionLogs(PDO $pdo, array $filters = []): array
{
    $sql = "SELECT l.*, u.username, u.email, u.full_name
            FROM user_logs l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE 1=1";

    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND l.user_id = :user_id";
        $params[':user_id'] = (int)$filters['user_id'];
    }

    if (!empty($filters['action'])) {
        $sql .= " AND l.action = :action";
        $params[':action'] = $filters['action'];
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

/**
 * Lấy danh sách các action types để filter
 */
function getUserActionTypes(PDO $pdo): array
{
    try {
        // Kiểm tra xem bảng có tồn tại không
        $stmt = $pdo->query("SELECT DISTINCT action FROM user_logs ORDER BY action");
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $result ?: [];
    } catch (PDOException $e) {
        // Nếu bảng chưa tồn tại, trả về mảng rỗng
        error_log('getUserActionTypes error: ' . $e->getMessage());
        return [];
    }
}

