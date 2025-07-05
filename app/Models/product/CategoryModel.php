<?php
/**
 * CategoryModel - chứa các hàm thao tác với bảng categories
 */

if (!function_exists('fetchAllCategories')) {
    /**
     * Lấy toàn bộ danh mục sắp xếp theo tên
     * @param PDO $pdo
     * @return array
     */
    function fetchAllCategories(PDO $pdo): array {
        $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }
}
