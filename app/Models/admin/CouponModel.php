<?php
require_once __DIR__ . '/../../../config/config.php';

function getAllCoupons($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function createCoupon($pdo, $data) {
    try {
        $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_order_value, start_date, end_date, usage_limit, status) 
                VALUES (:code, :discount_type, :discount_value, :min_order_value, :start_date, :end_date, :usage_limit, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':code' => $data['code'],
            ':discount_type' => $data['discount_type'],
            ':discount_value' => $data['discount_value'],
            ':min_order_value' => $data['min_order_value'] ?? 0,
            ':start_date' => $data['start_date'] ?: null,
            ':end_date' => $data['end_date'] ?: null,
            ':usage_limit' => $data['usage_limit'] ?? 0,
            ':status' => $data['status'] ?? 1
        ]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function deleteCoupon($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

function toggleCouponStatus($pdo, $id) {
    try {
        $stmt = $pdo->prepare("UPDATE coupons SET status = NOT status WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}
