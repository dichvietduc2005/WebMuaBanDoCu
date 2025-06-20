<?php
/**
 * Helper Functions for the Application
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

/**
 * Format price with VND currency
 */
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}

/**
 * Get condition text
 */
if (!function_exists('getConditionText')) {
    function getConditionText($condition) {
        switch ($condition) {
            case 'new':
                return 'Mới';
            case 'like_new':
                return 'Như mới';
            case 'good':
                return 'Tốt';
            case 'fair':
                return 'Khá';
            case 'poor':
                return 'Cũ';
            default:
                return 'Không xác định';
        }
    }
}

/**
 * Get status text
 */
if (!function_exists('getStatusText')) {
    function getStatusText($status) {
        switch ($status) {
            case 'active':
                return 'Đang bán';
            case 'sold':
                return 'Đã bán';
            case 'inactive':
                return 'Tạm ngưng';
            default:
                return 'Không xác định';
        }
    }
}

/**
 * Get status badge class
 */
if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        switch ($status) {
            case 'active':
                return 'badge-success';
            case 'sold':
                return 'badge-secondary';
            case 'inactive':
                return 'badge-warning';
            default:
                return 'badge-light';
        }
    }
}

/**
 * Cart helper functions
 */
if (!function_exists('getCartItems')) {
    function getCartItems($pdo, $user_id = null) {
        try {
            if (!$user_id) {
                // Nếu không có user_id truyền vào, thử lấy từ session
                $user_id = get_current_user_id();
            }

            if (!$user_id) {
                return []; // Guest không có giỏ hàng
            }

            $sql = "
                SELECT
                    ci.id as cart_item_id,
                    ci.product_id,
                    ci.quantity,
                    ci.added_price,
                    ci.condition_snapshot,
                    p.title AS product_name,
                    p.price AS current_price,
                    p.stock_quantity,
                    p.status as product_status,
                    pi.image_path,
                    (ci.quantity * ci.added_price) AS subtotal
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE c.user_id = ?
                ORDER BY ci.added_at DESC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("getCartItems Error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('calculateCartTotal')) {
    function calculateCartTotal($cartItems) {
        $total = 0;
        foreach ($cartItems as $item) {
            // Use added_price (price when added to cart) for calculation
            $price = isset($item['added_price']) ? $item['added_price'] : $item['current_price'];
            $total += $price * $item['quantity'];
        }
        return $total;
    }
}

if (!function_exists('getCartTotal')) {
    function getCartTotal($pdo, $user_id = null) {
        try {
            if (!$user_id) {
                $user_id = get_current_user_id();
            }
            
            if (!$user_id) {
                return 0; // Guest không có giỏ hàng
            }
            
            $stmt = $pdo->prepare("
                SELECT SUM(ci.added_price * ci.quantity) as total
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("getCartTotal Error: " . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('getCartItemCount')) {
    function getCartItemCount($pdo, $user_id = null) {
        try {
            if (!$user_id) {
                $user_id = get_current_user_id();
            }
            
            if (!$user_id) {
                return 0; // Guest không có giỏ hàng
            }
            
            $stmt = $pdo->prepare("
                SELECT SUM(ci.quantity) as total 
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id 
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("getCartItemCount Error: " . $e->getMessage());
            return 0;
        }
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
 * Clear cart after successful payment
 */
if (!function_exists('clearCart')) {
    function clearCart($pdo, $user_id = null) {
        try {
            if ($user_id) {
                // Clear cart for logged-in user
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } else {
                // For guest users, we would need session-based cart
                // Currently this system seems to require login, so just return
                return true;
            }
            return true;
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
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
                $sql .= ", transaction_id = ?";
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
