<?php
/**
 * Helper functions - Các hàm tiện ích dùng chung
 */

// Include other helper files
require_once(__DIR__ . '/Controllers/auth_helper.php');

/**
 * Get current user ID from session
 */
if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION["user_id"] ?? null;
    }
}

if (!function_exists('formatPrice')) {
    /**
     * Định dạng giá tiền
     */
    function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}

if (!function_exists('getConditionText')) {
    /**
     * Chuyển mã trạng thái sản phẩm thành text
     */
    function getConditionText($condition) {
        $conditions = [
            'new' => 'Mới',
            'like_new' => 'Như mới',
            'good' => 'Tốt',
            'fair' => 'Khá',
            'poor' => 'Cũ'
        ];
        return $conditions[$condition] ?? 'Không xác định';
    }
}

if (!function_exists('getStatusText')) {
    /**
     * Chuyển mã trạng thái đơn hàng thành text
     */
    function getStatusText($status) {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy',
            'success' => 'Thành công'
        ];
        return $statuses[$status] ?? 'Không xác định';
    }
}

if (!function_exists('getStatusBadge')) {
    /**
     * Chuyển mã trạng thái đơn hàng thành class CSS của badge
     * Updated: Mapping to status-* classes with high contrast colors
     */
    function getStatusBadge($status) {
        $badges = [
            'pending' => 'status-pending',
            'confirmed' => 'status-confirmed',
            'shipping' => 'status-delivered',
            'delivered' => 'status-delivered',
            'cancelled' => 'status-cancelled',
            'success' => 'status-delivered',
            'paid' => 'status-paid',
            'unpaid' => 'status-unpaid',
            'pending_payment' => 'status-pending-payment',
            'failed' => 'status-failed',
            'payment-failed' => 'status-payment-failed'
        ];
        return $badges[$status] ?? 'status-pending';
    }
}

/**
 * VNPAY helper functions
 */
if (!function_exists('log_vnpay_debug_data')) {
    function log_vnpay_debug_data($context, $data) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'context' => $context,
            'data' => $data
        ];
        error_log("VNPAY DEBUG [$context]: " . json_encode($log_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}

if (!function_exists('get_vnpay_config_for_logging')) {
    function get_vnpay_config_for_logging() {
        global $vnp_TmnCode, $vnp_Url, $vnp_Returnurl;
        return [
            'vnp_TmnCode' => $vnp_TmnCode ?? 'NOT_SET',
            'vnp_Url' => $vnp_Url ?? 'NOT_SET', 
            'vnp_Returnurl' => $vnp_Returnurl ?? 'NOT_SET'
        ];
    }
}

/**
 * Update order status by order number
 */
if (!function_exists('updateOrderStatusByNumber')) {
    function updateOrderStatusByNumber($pdo, $order_number, $order_status, $payment_status, $transaction_no = null) {
        try {
            $sql = "UPDATE orders SET status = ?, payment_status = ?, updated_at = NOW()";
            $params = [$order_status, $payment_status];
            
            if ($transaction_no) {
                $sql .= ", vnpay_transaction_id = ?";
                $params[] = $transaction_no;
            }
            
            $sql .= " WHERE order_number = ?";
            $params[] = $order_number;
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result && $stmt->rowCount() > 0) {
                error_log("Successfully updated order status for order_number: $order_number to $order_status/$payment_status");
                return true;
            } else {
                error_log("No rows affected when updating order status for order_number: $order_number");
                return false;
            }
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
}
