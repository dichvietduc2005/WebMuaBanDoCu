<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\modules\order\functions.php

// File chứa các hàm xử lý logic liên quan đến đơn hàng

// Các hàm get_current_user_id() và isUserLoggedIn() được định nghĩa trong cart/functions.php

/**
 * Lấy danh sách đơn hàng của một người dùng
 *
 * @param PDO $pdo
 * @param int $buyer_id ID của người mua
 * @param int $limit Số lượng đơn hàng tối đa trả về (mặc định 50)
 * @param int $offset Vị trí bắt đầu (cho phân trang)
 * @return array Danh sách đơn hàng
 */
function getOrdersByUserId(PDO $pdo, $buyer_id, $limit = 50, $offset = 0) {
    // Đảm bảo limit và offset là số nguyên
    $limit = (int)$limit;
    $offset = (int)$offset;
      $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_number,
            o.total_amount,
            o.status,
            o.payment_method,
            o.payment_status,
            o.notes,
            o.created_at,
            o.updated_at,
            COUNT(oi.id) as item_count,
            (SELECT pi.image_path 
             FROM order_items oi2 
             LEFT JOIN products p ON oi2.product_id = p.id 
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
             WHERE oi2.order_id = o.id 
             LIMIT 1) as first_product_image
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.buyer_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$buyer_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy chi tiết đơn hàng bao gồm thông tin đơn hàng và các sản phẩm trong đơn hàng.
 *
 * @param PDO $pdo
 * @param int $order_id ID của đơn hàng
 * @param int|null $user_id ID của người dùng để kiểm tra quyền truy cập (tùy chọn)
 * @return array|null Trả về thông tin chi tiết đơn hàng hoặc null nếu không tìm thấy
 */
function getOrderDetails(PDO $pdo, $order_id = null, $user_id = null) {
    if (!$order_id) {
        return null;
    }

    // Lấy thông tin cơ bản của đơn hàng
    $order_query = "SELECT * FROM orders WHERE id = ?";
    $order_params = [$order_id];
    
    // Nếu có user_id, chỉ lấy đơn hàng thuộc về user đó
    if ($user_id !== null) {
        $order_query .= " AND buyer_id = ?";
        $order_params[] = $user_id;
    }
    
    $stmt = $pdo->prepare($order_query);
    $stmt->execute($order_params);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return null; // Không tìm thấy đơn hàng hoặc không có quyền truy cập
    }

    // Lấy các sản phẩm trong đơn hàng với thông tin chi tiết
    $items_stmt = $pdo->prepare("
        SELECT 
            oi.*,
            p.title as current_product_title,
            p.slug as product_slug,
            pi.image_path as product_image
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN (
            SELECT product_id, image_path, 
                   ROW_NUMBER() OVER(PARTITION BY product_id ORDER BY is_primary DESC, id ASC) as rn
            FROM product_images
        ) pi ON p.id = pi.product_id AND pi.rn = 1
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ");
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Kết hợp thông tin đơn hàng và các sản phẩm
    $order['items'] = $order_items;
    
    return $order;
}

/**
 * Tạo đơn hàng mới từ giỏ hàng
 *
 * @param PDO $pdo
 * @param int $buyer_id ID người mua
 * @param array $cart_items Danh sách sản phẩm trong giỏ hàng
 * @param string $payment_method Phương thức thanh toán (mặc định 'vnpay')
 * @param string|null $notes Ghi chú của khách hàng
 * @return array|false Trả về thông tin đơn hàng vừa tạo hoặc false nếu thất bại
 */
function createOrderFromCart(PDO $pdo, $buyer_id, $cart_items, $payment_method = 'vnpay', $notes = null) {
    try {
        // Bắt đầu transaction
        $pdo->beginTransaction();
        
        // Tính tổng tiền
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['added_price'] * $item['quantity'];
        }
        
        // Tạo mã đơn hàng unique
        $order_number = generateOrderNumber();
        
        // Thêm đơn hàng vào bảng orders
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, buyer_id, total_amount, 
                status, payment_method, payment_status, 
                notes, created_at, updated_at
            ) VALUES (?, ?, ?, 'failed', ?, 'pending', ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $order_number, $buyer_id, $total_amount,
            $payment_method, $notes
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Thêm các sản phẩm vào bảng order_items
        $item_stmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_title, 
                product_price, quantity, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($cart_items as $item) {
            // Lấy thông tin sản phẩm
            $product_stmt = $pdo->prepare("SELECT title FROM products WHERE id = ?");
            $product_stmt->execute([$item['product_id']]);
            $product_info = $product_stmt->fetch(PDO::FETCH_ASSOC);
            
            $product_title = $product_info ? $product_info['title'] : 'Sản phẩm không xác định';
            $subtotal = $item['added_price'] * $item['quantity'];
            
            $item_stmt->execute([
                $order_id, 
                $item['product_id'], 
                $product_title, 
                $item['added_price'], 
                $item['quantity'],
                $subtotal
            ]);
        }
          // Commit transaction
        $pdo->commit();
        
        // Trả về thông tin đơn hàng vừa tạo
        return [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'buyer_id' => $buyer_id,
            'total_amount' => $total_amount,
            'status' => 'failed',
            'payment_method' => $payment_method,
            'payment_status' => 'pending'
        ];
        
    } catch (Exception $e) {        // Rollback transaction nếu có lỗi
        $pdo->rollBack();
        error_log("Lỗi tạo đơn hàng: " . $e->getMessage());
        return false;
    }
}

/**
 * Cập nhật trạng thái đơn hàng
 *
 * @param PDO $pdo
 * @param int $order_id ID đơn hàng
 * @param string $status Trạng thái mới ('pending', 'confirmed', 'shipping', 'completed', 'cancelled')
 * @param int|null $user_id ID người dùng (để kiểm tra quyền nếu cần)
 * @return bool True nếu thành công
 */
function updateOrderStatus(PDO $pdo, $order_id, $status, $user_id = null) {
    $allowed_statuses = ['success', 'failed'];
    
    if (!in_array($status, $allowed_statuses)) {
        return false;
    }
    
    $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
    $params = [$status, $order_id];
    
    // Nếu có user_id, chỉ cho phép cập nhật đơn hàng của user đó
    if ($user_id !== null) {
        $query .= " AND buyer_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $pdo->prepare($query);
    return $stmt->execute($params);
}

/**
 * Cập nhật trạng thái thanh toán của đơn hàng
 *
 * @param PDO $pdo
 * @param int $order_id ID đơn hàng
 * @param string $payment_status Trạng thái thanh toán ('pending', 'paid', 'failed')
 * @return bool True nếu thành công
 */
function updateOrderPaymentStatus(PDO $pdo, $order_id, $payment_status) {
    $allowed_statuses = ['pending', 'paid', 'failed'];
    
    if (!in_array($payment_status, $allowed_statuses)) {
        return false;
    }
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$payment_status, $order_id]);
}

/**
 * Hủy đơn hàng (chỉ cho phép hủy nếu trạng thái là 'pending' hoặc 'confirmed')
 *
 * @param PDO $pdo
 * @param int $order_id ID đơn hàng
 * @param int $user_id ID người dùng
 * @param string|null $reason Lý do hủy đơn
 * @return bool True nếu thành công
 */
function cancelOrder(PDO $pdo, $order_id, $user_id, $reason = null) {
    // Kiểm tra xem đơn hàng có thể hủy không
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND buyer_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return false; // Không tìm thấy đơn hàng hoặc không có quyền
    }
    
    if ($order['status'] === 'success') {
        return false; // Không thể hủy đơn hàng đã thành công
    }
    
    // Cập nhật trạng thái thành 'failed'
    $update_stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'failed', notes = CONCAT(IFNULL(notes, ''), '\nLý do hủy: ', ?), updated_at = NOW() 
        WHERE id = ? AND buyer_id = ?
    ");
    
    return $update_stmt->execute([$reason ?? 'Khách hàng hủy đơn', $order_id, $user_id]);
}

/**
 * Lấy số lượng đơn hàng của người dùng theo trạng thái
 *
 * @param PDO $pdo
 * @param int $buyer_id ID người mua
 * @param string|null $status Trạng thái cần đếm (null = tất cả)
 * @return int Số lượng đơn hàng
 */
function getOrderCountByStatus(PDO $pdo, $buyer_id, $status = null) {
    if ($status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND status = ?");
        $stmt->execute([$buyer_id, $status]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ?");
        $stmt->execute([$buyer_id]);
    }
    
    return (int) $stmt->fetchColumn();
}

/**
 * Tìm đơn hàng theo mã đơn hàng
 *
 * @param PDO $pdo
 * @param string $order_number Mã đơn hàng
 * @param int|null $user_id ID người dùng để kiểm tra quyền
 * @return array|null Thông tin đơn hàng hoặc null nếu không tìm thấy
 */
function getOrderByOrderNumber(PDO $pdo, $order_number, $user_id = null) {
    $query = "SELECT * FROM orders WHERE order_number = ?";
    $params = [$order_number];
    
    if ($user_id !== null) {
        $query .= " AND buyer_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Tạo mã đơn hàng unique
 *
 * @return string Mã đơn hàng
 */
function generateOrderNumber() {
    return 'ORDER_' . date('YmdHis') . '_' . rand(1000, 9999);
}

// Hàm get_current_logged_in_user_id() đã được định nghĩa trong cart/functions.php

/**
 * Format hiển thị trạng thái đơn hàng
 *
 * @param string $status Trạng thái đơn hàng
 * @return array Mảng chứa text và class CSS
 */
function formatOrderStatus($status) {
    $statuses = [
        'success' => ['text' => 'Thành công', 'class' => 'badge-success'],
        'failed' => ['text' => 'Thất bại', 'class' => 'badge-danger']
    ];
    
    return $statuses[$status] ?? ['text' => 'Không xác định', 'class' => 'badge-secondary'];
}

/**
 * Format hiển thị trạng thái thanh toán
 *
 * @param string $payment_status Trạng thái thanh toán
 * @return array Mảng chứa text và class CSS
 */
function formatPaymentStatus($payment_status) {
    $statuses = [
        'pending' => ['text' => 'Chờ thanh toán', 'class' => 'badge-warning'],
        'paid' => ['text' => 'Đã thanh toán', 'class' => 'badge-success'],
        'failed' => ['text' => 'Thanh toán thất bại', 'class' => 'badge-danger']
    ];
    
    return $statuses[$payment_status] ?? ['text' => 'Không xác định', 'class' => 'badge-secondary'];
}

/**
 * Format hiển thị phương thức thanh toán
 *
 * @param string $payment_method Phương thức thanh toán
 * @return string Text hiển thị
 */
function formatPaymentMethod($payment_method) {
    $methods = [
        'cod' => 'Thanh toán khi nhận hàng (COD)',
        'vnpay' => 'Thanh toán online (VNPay)'
    ];
    
    return $methods[$payment_method] ?? 'Không xác định';
}



/**
 * Lấy CSS class cho trạng thái đơn hàng
 */
function getOrderStatusClass($status) {
    $classes = [
        'pending' => 'warning',
        'confirmed' => 'info', 
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'completed' => 'success'
    ];
    
    return $classes[$status] ?? 'secondary';
}

/**
 * Lấy text hiển thị cho trạng thái đơn hàng
 */
function getOrderStatusText($status) {
    $texts = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipped' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
        'completed' => 'Hoàn thành'
    ];
    
    return $texts[$status] ?? 'Không xác định';
}

/**
 * Lấy CSS class cho trạng thái thanh toán
 */
function getPaymentStatusClass($status) {
    $classes = [
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'info'
    ];
    
    return $classes[$status] ?? 'secondary';
}

/**
 * Lấy text hiển thị cho trạng thái thanh toán
 */
function getPaymentStatusText($status) {
    $texts = [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        'refunded' => 'Đã hoàn tiền'
    ];
    
    return $texts[$status] ?? 'Không xác định';
}
?>
