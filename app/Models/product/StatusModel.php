<?php
/**
 * StatusModel - lấy các giá trị tình trạng sản phẩm (condition_status) từ DB
 */
if (!function_exists('fetchConditionStatuses')) {
    /**
     * Trả về mảng các giá trị enum condition_status trong bảng products
     * @param PDO $pdo
     * @return array
     */
    function fetchConditionStatuses(PDO $pdo): array {
        $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'condition_status'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return [];
        $type = $row['COLUMN_TYPE']; // vd: enum('new','like_new','good','fair','poor')
        if (preg_match("/^enum\((.*)\)$/", $type, $matches)) {
            $values = str_getcsv($matches[1], ',', "'");
            return $values;
        }
        return [];
    }
} 