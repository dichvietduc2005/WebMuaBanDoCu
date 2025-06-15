<?php
// filepath: c:\wamp64\www\Web_MuaBanDoCu\modules\cart\functions_new.php

// Hàm lấy session ID cho guest user
function getGuestSessionId() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['guest_cart_id'])) {
        $_SESSION['guest_cart_id'] = 'guest_' . uniqid() . '_' . time();
    }
    
    return $_SESSION['guest_cart_id'];
}

// Hàm lấy user ID hiện tại (logged in user hoặc null cho guest)
function get_current_user_id() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION["user_id"] ?? null;
}

// Hàm kiểm tra user đã đăng nhập chưa
function isUserLoggedIn() {
    return get_current_user_id() !== null;
}

// Hàm thêm sản phẩm vào giỏ (chỉ cho phép người dùng đã đăng nhập)
function addToCart($pdo, $product_id, $quantity = 1, $user_id = null) {
    try {
        // Kiểm tra đăng nhập trước khi thêm vào giỏ
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // CHẶN GUEST - Chỉ cho phép người dùng đã đăng nhập thêm sản phẩm vào giỏ
        if (!$user_id) {
            throw new Exception("Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng");
        }
        
        // Kiểm tra sản phẩm tồn tại
        $stmt = $pdo->prepare("SELECT id, title, price, user_id, status, stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new Exception("Product not found");
        }
        
        // Kiểm tra trạng thái sản phẩm
        if ($product['status'] != 'active') {
            throw new Exception("Product is not active");
        }
        
        // Kiểm tra tồn kho
        if ($product['stock_quantity'] < $quantity) {
            throw new Exception("Not enough stock");        }
        
        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa - chỉ kiểm tra cho logged-in user
        $stmt = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Cập nhật số lượng
            $new_quantity = $existing["quantity"] + $quantity;
            
            // Kiểm tra tổng số lượng không vượt quá tồn kho
            if ($new_quantity > $product['stock_quantity']) {
                throw new Exception("Total quantity exceeds stock");
            }            
            $stmt = $pdo->prepare("UPDATE carts SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$new_quantity, $existing["id"]]);
        } else {
            // Thêm mới - chỉ cho logged-in user
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $result = $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("addToCart Error: " . $e->getMessage());
        throw $e;
    }
}

// Hàm lấy items trong giỏ hàng
function getCartItems($pdo, $user_id = null) {
    try {
        if (!$user_id) {
            // Nếu không có user_id truyền vào, thử lấy từ session
            $user_id = get_current_user_id();
        }

        $sql = "";
        $params = [];

        if ($user_id) { // Người dùng đã đăng nhập
            $sql = "
                SELECT
                    c.product_id,
                    c.quantity,
                    p.title AS product_name,
                    p.price AS price,  -- Lấy giá hiện tại từ bảng products
                    (c.quantity * p.price) AS subtotal
                FROM carts c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?
                ORDER BY c.created_at DESC -- Giả sử bảng 'carts' có cột created_at khi item được thêm
            ";
            $params = [$user_id];
        } else { 
            $guest_session_id = getGuestSessionId();
            if (!$guest_session_id) {
                return []; // Không có session ID cho guest, không có giỏ hàng
            }
            $sql = "
                SELECT
                    c.product_id,
                    c.quantity,
                    p.title AS product_name,
                    p.price AS price, -- Lấy giá hiện tại từ bảng products
                    (c.quantity * p.price) AS subtotal
                FROM carts c
                JOIN products p ON c.product_id = p.id
                WHERE c.session_id = ?
                ORDER BY c.created_at DESC -- Giả sử bảng 'carts' có cột created_at khi item được thêm
            ";
            $params = [$guest_session_id];
        }

        if (empty($sql)) {
            return []; // Không có điều kiện nào được đáp ứng
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("getCartItems Error: " . $e->getMessage());
        return [];
    }
}

// Hàm tính tổng tiền giỏ hàng
function getCartTotal($pdo, $user_id = null) {
    $items = getCartItems($pdo, $user_id); // Sử dụng hàm getCartItems đã được cập nhật
    $total = 0;
    foreach ($items as $item) {
        // Nên sử dụng giá hiện tại của sản phẩm (price) để tính tổng cho đơn hàng
        // added_price chỉ để tham khảo hoặc nếu bạn có logic giá cố định khi thêm vào giỏ
        $total += ($item['price'] * $item['quantity']); 
    }
    return $total;
}


// Hàm đếm số lượng items trong giỏ
function getCartItemCount($pdo, $user_id = null) {
    try {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $is_guest = !$user_id;
        
        if ($is_guest) {
            $guest_session_id = getGuestSessionId();
            $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM carts WHERE session_id = ?");
            $stmt->execute([$guest_session_id]);
        } else {
            $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM carts WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    } catch (Exception $e) {
        error_log("getCartItemCount Error: " . $e->getMessage());
        return 0;
    }
}

// Hàm cập nhật số lượng sản phẩm trong giỏ
function updateCartItemQuantity($pdo, $product_id, $quantity, $user_id = null) {
    try {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $is_guest = !$user_id;
        
        if ($quantity <= 0) {
            // Xóa sản phẩm nếu quantity <= 0
            return removeCartItem($pdo, $product_id, $user_id);
        }
        
        if ($is_guest) {
            $guest_session_id = getGuestSessionId();
            $stmt = $pdo->prepare("UPDATE carts SET quantity = ?, updated_at = NOW() WHERE session_id = ? AND product_id = ?");
            $result = $stmt->execute([$quantity, $guest_session_id, $product_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE carts SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$quantity, $user_id, $product_id]);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("updateCartItemQuantity Error: " . $e->getMessage());
        return false;
    }
}

// Hàm xóa sản phẩm khỏi giỏ
function removeCartItem($pdo, $product_id, $user_id = null) {
    try {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $is_guest = !$user_id;
        
        if ($is_guest) {
            $guest_session_id = getGuestSessionId();
            $stmt = $pdo->prepare("DELETE FROM carts WHERE session_id = ? AND product_id = ?");
            $result = $stmt->execute([$guest_session_id, $product_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$user_id, $product_id]);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("removeCartItem Error: " . $e->getMessage());
        return false;
    }
}

// Hàm xóa sạch giỏ hàng
function clearCart($pdo, $user_id = null) {
    try {
        if ($user_id) {
            $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            error_log("Cleared cart for user_id: $user_id");
            return true;
        } else { // Guest user
            $guest_session_id = getGuestSessionId(); // Hàm này bạn đã có
            if ($guest_session_id) {
                $stmt = $pdo->prepare("DELETE FROM carts WHERE session_id = ?");
                $stmt->execute([$guest_session_id]);
                unset($_SESSION['guest_cart_id']); // Quan trọng: Xóa session ID của giỏ hàng guest
                error_log("Cleared cart for guest session: $guest_session_id");
                return true;
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("clearCart Error: " . $e->getMessage());
        return false;
    }
}

// Backward compatibility
function get_current_logged_in_user_id() {
    return get_current_user_id();
}
?>
