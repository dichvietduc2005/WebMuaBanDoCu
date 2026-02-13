<?php
/**
 * CartModel - Model quản lý dữ liệu giỏ hàng
 *
 * Chịu trách nhiệm cho tất cả các tương tác với cơ sở dữ liệu
 * liên quan đến giỏ hàng cho người dùng đã đăng nhập.
 *
 * @category   Models
 * @package    WebMuaBanDoCu
 * @author     Developer
 */


class CartModel
{
    /** @var PDO Đối tượng kết nối CSDL */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy hoặc tạo ID giỏ hàng cho một người dùng.
     *
     * @param int $user_id
     * @return int ID của giỏ hàng
     */
    public function getOrCreateCartId(int $user_id): int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            // CSDL cho phép user_id là NULL, nhưng logic của chúng ta yêu cầu phải có user_id
            // nên ta không chèn session_id nữa.
            $stmt = $this->pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            return (int) $this->pdo->lastInsertId();
        }

        return (int) $cart['id'];
    }

    /**
     * Lấy tất cả sản phẩm trong giỏ hàng của người dùng.
     *
     * @param int $user_id
     * @return array
     */
    public function getItemsByUserId(int $user_id): array
    {
        $sql = "
            SELECT
                ci.product_id, ci.quantity, ci.added_price,
                p.title AS product_title, p.price AS current_price, p.stock_quantity, p.status as product_status,
                pi.image_path, (ci.quantity * ci.added_price) AS subtotal,u.username as seller_name
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE c.user_id = ? 
            AND (ci.status IS NULL OR ci.status = 'active')
            AND (ci.is_hidden IS NULL OR ci.is_hidden = 0)
            ORDER BY ci.added_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy các items cụ thể trong giỏ hàng (cho chức năng checkout selected).
     */
    public function getItemsByUserIdAndProductIds(int $user_id, array $product_ids): array
    {
        if (empty($product_ids))
            return [];

        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $sql = "
            SELECT
                ci.product_id, ci.quantity, ci.added_price,
                p.title AS product_title, p.price AS current_price, p.stock_quantity, p.status as product_status,
                pi.image_path, (ci.quantity * ci.added_price) AS subtotal,u.username as seller_name
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE c.user_id = ? 
            AND ci.product_id IN ($placeholders)
            AND (ci.status IS NULL OR ci.status = 'active')
            AND (ci.is_hidden IS NULL OR ci.is_hidden = 0)
            ORDER BY ci.added_at DESC
        ";

        $params = array_merge([$user_id], $product_ids);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm sản phẩm theo ID.
     */
    public function findProductById(int $product_id)
    {
        $stmt = $this->pdo->prepare("SELECT id, title, price, status, stock_quantity, condition_status FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm một item trong giỏ hàng.
     */
    public function findCartItem(int $cart_id, int $product_id)
    {
        $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cart_id, $product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm một sản phẩm mới vào giỏ hàng.
     */
    public function addNewCartItem(int $cart_id, int $product_id, int $quantity, float $price, string $condition): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, added_price, condition_snapshot) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$cart_id, $product_id, $quantity, $price, $condition]);
    }

    /**
     * Cập nhật số lượng của một sản phẩm đã có trong giỏ.
     */
    public function updateExistingCartItemQuantity(int $cart_item_id, int $new_quantity): bool
    {
        $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        return $stmt->execute([$new_quantity, $cart_item_id]);
    }

    /**
     * Cập nhật số lượng của một sản phẩm qua user_id.
     */
    public function updateItemQuantity(int $user_id, int $product_id, int $quantity): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE cart_items ci JOIN carts c ON ci.cart_id = c.id
            SET ci.quantity = ? 
            WHERE c.user_id = ? AND ci.product_id = ?");
        return $stmt->execute([$quantity, $user_id, $product_id]);
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng.
     */
    public function removeItem(int $user_id, int $product_id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE ci FROM cart_items ci JOIN carts c ON ci.cart_id = c.id
            WHERE c.user_id = ? AND ci.product_id = ?");
        return $stmt->execute([$user_id, $product_id]);
    }

    /**
     * Xóa danh sách sản phẩm đã mua khỏi giỏ hàng.
     * @param int $user_id
     * @param array $product_ids Mảng các product_id cần xóa
     */
    public function removeBoughtItems(int $user_id, array $product_ids): bool
    {
        if (empty($product_ids)) {
            return true;
        }

        // 1. Lấy cart_id
        $cart_id = $this->getOrCreateCartId($user_id);

        // 2. Xóa các sản phẩm trong danh sách
        // Tạo chuỗi placeholder (?,?,?)
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $sql = "DELETE FROM cart_items WHERE cart_id = ? AND product_id IN ($placeholders)";

        $stmt = $this->pdo->prepare($sql);

        // Merge cart_id và list product_ids để execute
        // Merge cart_id và list product_ids để execute
        $params = array_merge([$cart_id], $product_ids);

        $result = $stmt->execute($params);

        if ($result) {
            error_log("CartModel: Removed " . $stmt->rowCount() . " bought items for user $user_id");
        } else {
            error_log("CartModel: Failed to remove bought items for user $user_id");
        }

        return $result;
    }

    /**
     * Xóa toàn bộ sản phẩm trong giỏ hàng của người dùng.
     */
    public function clearCart(int $user_id): bool
    {
        // 1. Lấy cart_id của user
        $stmt_get_cart = $this->pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt_get_cart->execute([$user_id]);
        $cart = $stmt_get_cart->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            // Không tìm thấy giỏ hàng của user -> coi như đã clear thành công
            return true;
        }

        $cart_id = $cart['id'];

        // 2. Xóa tất cả items trong giỏ
        $stmt_delete = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $result = $stmt_delete->execute([$cart_id]);

        // Log kết quả để debug
        if ($result) {
            error_log("CartModel: Extracted cart_id $cart_id for user $user_id and deleted {$stmt_delete->rowCount()} items.");
        } else {
            error_log("CartModel: Failed to delete items for cart_id $cart_id (User: $user_id)");
        }

        return $result;
    }
}