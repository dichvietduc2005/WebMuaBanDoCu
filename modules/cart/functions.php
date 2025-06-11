<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\vnpay_php\cart_functions.php

// Đảm bảo session đã được khởi động (thường trong config.php)
// session_start() đã được gọi trong config.php, không cần gọi lại ở đây nếu config.php được include trước.
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

/**
 * Lấy hoặc tạo cart_id cho người dùng hiện tại (đã đăng nhập hoặc khách).
 *
 * @param PDO $pdo Đối tượng PDO kết nối CSDL.
 * @param int|null $user_id ID của người dùng nếu đã đăng nhập.
 * @return int|false Trả về cart_id nếu thành công, ngược lại false.
 */
function get_or_create_cart_id(PDO $pdo, $user_id = null) {
    $cart_id = false;

    if ($user_id !== null) {
        // Người dùng đã đăng nhập
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_id = $stmt->fetchColumn();

        if (!$cart_id) {
            // Tạo giỏ hàng mới cho người dùng
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
            if ($stmt->execute([$user_id])) {
                $cart_id = $pdo->lastInsertId();
            }
        }
    } else {
        // Khách (guest)
        $session_id = session_id();
        if (empty($session_id)) { // Đảm bảo session_id tồn tại
            // Nếu không có session_id, có thể là lỗi cấu hình session hoặc session chưa được start đúng cách.
            // Hoặc có thể tạo một session_id tạm thời nếu cần, nhưng tốt nhất là đảm bảo session hoạt động.
            error_log("Session ID is empty in get_or_create_cart_id. Ensure session_start() is called.");
            return false;
        }
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $cart_id = $stmt->fetchColumn();

        if (!$cart_id) {
            // Tạo giỏ hàng mới cho khách
            $stmt = $pdo->prepare("INSERT INTO carts (session_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
            if ($stmt->execute([$session_id])) {
                $cart_id = $pdo->lastInsertId();
            }
        }
    }
    // Cập nhật thời gian updated_at cho giỏ hàng mỗi khi có tương tác
    if ($cart_id) {
        $updateStmt = $pdo->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$cart_id]);
    }
    return $cart_id;
}

/**
 * Thêm sản phẩm vào giỏ hàng hoặc cập nhật số lượng.
 *
 * @param PDO $pdo
 * @param int $product_id
 * @param int $quantity
 * @param int|null $user_id
 * @return bool True nếu thành công, false nếu thất bại.
 */
function addToCart(PDO $pdo, $product_id, $quantity, $user_id = null) {
    if ($quantity <= 0) return false; // Số lượng phải lớn hơn 0

    $cart_id = get_or_create_cart_id($pdo, $user_id);
    if (!$cart_id) {
        return false;
    }

    // Lấy thông tin sản phẩm (giá, tình trạng)
    $stmt_product = $pdo->prepare("SELECT price, condition_status FROM products WHERE id = ? AND status = 'active' AND stock_quantity >= ?");
    $stmt_product->execute([$product_id, $quantity]); // Kiểm tra cả stock_quantity cơ bản
    $product_info = $stmt_product->fetch(PDO::FETCH_ASSOC);

    if (!$product_info) {
        // Sản phẩm không tồn tại, không hoạt động hoặc không đủ hàng
        return false;
    }
    $added_price = $product_info['price'];
    $condition_snapshot = $product_info['condition_status'];

    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $stmt_check = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt_check->execute([$cart_id, $product_id]);
    $existing_item = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($existing_item) {
        // Cập nhật số lượng
        $new_quantity = $existing_item['quantity'] + $quantity;
        // Kiểm tra lại stock với số lượng mới
        $stmt_stock_check = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt_stock_check->execute([$product_id]);
        $stock = $stmt_stock_check->fetchColumn();
        if ($stock < $new_quantity) {
            // Nếu vượt quá stock, có thể thông báo lỗi hoặc chỉ cho phép thêm tối đa
            // Hiện tại, trả về false
            return false; 
        }

        $stmt_update = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW(), added_price = ?, condition_snapshot = ? WHERE id = ?");
        return $stmt_update->execute([$new_quantity, $added_price, $condition_snapshot, $existing_item['id']]);
    } else {
        // Thêm sản phẩm mới vào giỏ
        $stmt_insert = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, added_price, condition_snapshot, added_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        return $stmt_insert->execute([$cart_id, $product_id, $quantity, $added_price, $condition_snapshot]);
    }
}

/**
 * Lấy toàn bộ nội dung giỏ hàng.
 *
 * @param PDO $pdo
 * @param int|null $user_id
 * @return array Danh sách các sản phẩm trong giỏ.
 */
function getCartContents(PDO $pdo, $user_id = null) {
    $cart_id = get_or_create_cart_id($pdo, $user_id);
    if (!$cart_id) {
        return [];
    }

    $stmt = $pdo->prepare("
        SELECT 
            ci.id as cart_item_id, 
            ci.product_id, 
            ci.quantity, 
            ci.added_price,
            p.title as name, 
            p.slug,
            pi.image_path as image
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN (
            SELECT product_id, image_path, ROW_NUMBER() OVER(PARTITION BY product_id ORDER BY is_primary DESC, id ASC) as rn
            FROM product_images
        ) pi ON p.id = pi.product_id AND pi.rn = 1
        WHERE ci.cart_id = ?
        ORDER BY ci.added_at DESC
    ");
    $stmt->execute([$cart_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Cập nhật số lượng của một sản phẩm trong giỏ hàng.
 *
 * @param PDO $pdo
 * @param int $product_id
 * @param int $quantity
 * @param int|null $user_id
 * @return bool True nếu thành công.
 */
function updateCartQuantity(PDO $pdo, $product_id, $quantity, $user_id = null) {
    $cart_id = get_or_create_cart_id($pdo, $user_id);
    if (!$cart_id) {
        return false;
    }

    if ($quantity <= 0) {
        return removeFromCart($pdo, $product_id, $user_id);
    }

    // Kiểm tra stock trước khi cập nhật
    $stmt_stock_check = $pdo->prepare("SELECT stock_quantity, price, condition_status FROM products WHERE id = ?");
    $stmt_stock_check->execute([$product_id]);
    $product_info = $stmt_stock_check->fetch(PDO::FETCH_ASSOC);

    if (!$product_info || $product_info['stock_quantity'] < $quantity) {
        // Sản phẩm không tồn tại hoặc không đủ hàng
        return false;
    }
    
    $added_price = $product_info['price']; // Lấy giá hiện tại khi cập nhật
    $condition_snapshot = $product_info['condition_status'];

    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, added_price = ?, condition_snapshot = ?, updated_at = NOW() WHERE cart_id = ? AND product_id = ?");
    return $stmt->execute([$quantity, $added_price, $condition_snapshot, $cart_id, $product_id]);
}

/**
 * Xóa một sản phẩm khỏi giỏ hàng.
 *
 * @param PDO $pdo
 * @param int $product_id
 * @param int|null $user_id
 * @return bool True nếu thành công.
 */
function removeFromCart(PDO $pdo, $product_id, $user_id = null) {
    $cart_id = get_or_create_cart_id($pdo, $user_id);
    if (!$cart_id) {
        return false;
    }
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
    return $stmt->execute([$cart_id, $product_id]);
}

/**
 * Tính tổng giá trị giỏ hàng.
 *
 * @param PDO $pdo
 * @param int|null $user_id
 * @return float Tổng giá trị.
 */
function getCartTotal(PDO $pdo, $user_id = null) {
    $cart_id = get_or_create_cart_id($pdo, $user_id);
    if (!$cart_id) {
        return 0.0;
    }

    $stmt = $pdo->prepare("SELECT SUM(quantity * added_price) as total FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cart_id]);
    $total = $stmt->fetchColumn();
    return $total ? (float)$total : 0.0;
}

/**
 * Đếm tổng số lượng các mặt hàng trong giỏ.
 *
 * @param PDO $pdo
 * @param int|null $user_id
 * @return int Tổng số lượng.
 */
function getCartItemCount(PDO $pdo, $user_id = null) {
    $cart_id = get_or_create_cart_id($pdo, $user_id);
    if (!$cart_id) {
        return 0;
    }

    $stmt = $pdo->prepare("SELECT SUM(quantity) as item_count FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cart_id]);
    $count = $stmt->fetchColumn();
    return $count ? (int)$count : 0;
}

/**
 * Xóa toàn bộ sản phẩm khỏi giỏ hàng.
 *
 * @param PDO $pdo
 * @param int|null $user_id
 * @return bool True nếu thành công.
 */
function clearCart(PDO $pdo, $user_id = null) {
    $cart_id = get_or_create_cart_id($pdo, $user_id);
    if (!$cart_id) {
        // Nếu không có cart_id (ví dụ session không hoạt động), coi như đã "clear"
        return true; 
    }
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $success = $stmt->execute([$cart_id]);
    
    // Cân nhắc: Có nên xóa luôn cart trong bảng `carts` nếu là guest và giỏ hàng trống?
    if ($success && $user_id === null) {
        $stmt_delete_cart = $pdo->prepare("DELETE FROM carts WHERE id = ?");
        $stmt_delete_cart->execute([$cart_id]);
    }
    return $success;
}

/**
 * Lấy user_id hiện tại nếu đã đăng nhập.
 * Hàm này nên được đặt ở một nơi quản lý user session chung hơn.
 * Tạm thời để đây để các hàm cart có thể gọi.
 * @return int|null
 */
function get_current_logged_in_user_id() {
    // Giả sử user_id được lưu trong session khi đăng nhập
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }
    return null;
}

?>
