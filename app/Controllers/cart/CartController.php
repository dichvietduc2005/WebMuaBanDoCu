<?php
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
    try {        // Kiểm tra đăng nhập trước khi thêm vào giỏ
        if (!$user_id) {
            $user_id = $_SESSION['user_id'] ?? null;
        }
        
        // CHẶN GUEST - Chỉ cho phép người dùng đã đăng nhập thêm sản phẩm vào giỏ
        if (!$user_id) {
            throw new Exception("Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng");
        }
        
        // Kiểm tra sản phẩm tồn tại
        $stmt = $pdo->prepare("SELECT id, title, price, user_id, status, stock_quantity, condition_status FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new Exception("Sản phẩm không tồn tại");
        }
        
        // Kiểm tra trạng thái sản phẩm
        if ($product['status'] != 'active') {
            throw new Exception("Sản phẩm không còn bán");
        }
        
        // Kiểm tra tồn kho
        if ($product['stock_quantity'] < $quantity) {
            throw new Exception("Không đủ hàng trong kho");
        }
        
        // Tìm hoặc tạo cart cho user
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cart) {
            // Tạo cart mới cho user
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
        } else {
            $cart_id = $cart['id'];
        }
        
        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cart_id, $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Cập nhật số lượng
            $new_quantity = $existing["quantity"] + $quantity;
            
            // Kiểm tra tổng số lượng không vượt quá tồn kho
            if ($new_quantity > $product['stock_quantity']) {
                throw new Exception("Tổng số lượng vượt quá tồn kho có sẵn");
            }
            
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$new_quantity, $existing["id"]]);
        } else {
            // Thêm mới vào cart_items
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, added_price, condition_snapshot, added_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $result = $stmt->execute([$cart_id, $product_id, $quantity, $product['price'], $product['condition_status']]);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("addToCart Error: " . $e->getMessage());
        throw $e;
    }
}

// Hàm lấy items trong giỏ hàng
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
        ";        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("getCartItems Error: " . $e->getMessage());
        return [];
    }
}
}

// Hàm tính tổng tiền giỏ hàng
if (!function_exists('getCartTotal')) {
    function getCartTotal($pdo, $user_id = null) {
        $items = getCartItems($pdo, $user_id);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['subtotal']; // Sử dụng subtotal đã tính trong getCartItems
        }
        return $total;
    }
}

// Hàm đếm số lượng items trong giỏ
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

// Hàm cập nhật số lượng sản phẩm trong giỏ
function updateCartItemQuantity($pdo, $product_id, $quantity, $user_id = null) {
    try {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            throw new Exception("Bạn cần đăng nhập để cập nhật giỏ hàng");
        }
        
        if ($quantity <= 0) {
            // Xóa sản phẩm nếu quantity <= 0
            return removeCartItem($pdo, $product_id, $user_id);
        }
        
        $stmt = $pdo->prepare("
            UPDATE cart_items ci
            JOIN carts c ON ci.cart_id = c.id 
            SET ci.quantity = ?, ci.updated_at = NOW() 
            WHERE c.user_id = ? AND ci.product_id = ?
        ");
        $result = $stmt->execute([$quantity, $user_id, $product_id]);
        
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
        
        if (!$user_id) {
            throw new Exception("Bạn cần đăng nhập để xóa sản phẩm khỏi giỏ hàng");
        }
        
        $stmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id 
            WHERE c.user_id = ? AND ci.product_id = ?
        ");
        $result = $stmt->execute([$user_id, $product_id]);
        
        return $result;
    } catch (Exception $e) {
        error_log("removeCartItem Error: " . $e->getMessage());
        return false;
    }
}

// Hàm xóa sạch giỏ hàng
function clearCart($pdo, $user_id = null) {
    try {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            throw new Exception("Bạn cần đăng nhập để xóa giỏ hàng");
        }
        
        $stmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        error_log("Cleared cart for user_id: $user_id");
        return true;    } catch (Exception $e) {
        error_log("clearCart Error: " . $e->getMessage());
        return false;
    }
}

// Backward compatibility
function get_current_logged_in_user_id() {
    return get_current_user_id();
}
?>
