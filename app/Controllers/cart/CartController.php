<?php
/**
 * CartController - Quản lý chức năng giỏ hàng
 *
 * Điều phối logic nghiệp vụ cho giỏ hàng cho người dùng đã đăng nhập.
 *
 * @category   Controllers
 * @package    WebMuaBanDoCu
 * @author     Developer
 */

class CartController
{
    private $pdo;
    private $cartModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->cartModel = new CartModel($pdo);
    }

    /**
     * Đảm bảo người dùng đã đăng nhập và trả về user_id.
     * Ném Exception nếu chưa đăng nhập.
     */
    private function ensureUserIsLoggedIn(): int
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $user_id = $_SESSION["user_id"] ?? null;
        if (!$user_id) {
            throw new \Exception("Bạn cần đăng nhập để thực hiện chức năng này.");
        }
        return $user_id;
    }



    public function getCurrentUserId(): ?int
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION["user_id"] ?? null;
    }
    
    public function addToCart($product_id, $quantity = 1)
    {
        $user_id = $this->ensureUserIsLoggedIn();
        try {
            $product = $this->cartModel->findProductById($product_id);
            if (!$product) throw new \Exception("Sản phẩm không tồn tại.");
            if ($product['status'] != 'active') throw new \Exception("Sản phẩm không còn được bán.");
            if ($product['stock_quantity'] < $quantity) throw new \Exception("Không đủ hàng trong kho.");

            $cart_id = $this->cartModel->getOrCreateCartId($user_id);

            $existing_item = $this->cartModel->findCartItem($cart_id, $product_id);

            if ($existing_item) {
                $new_quantity = $existing_item['quantity'] + $quantity;
                if ($product['stock_quantity'] < $new_quantity) {
                    throw new \Exception("Số lượng trong giỏ vượt quá số lượng tồn kho.");
                }
                return $this->cartModel->updateExistingCartItemQuantity($existing_item['id'], $new_quantity);
            } else {
                return $this->cartModel->addNewCartItem($cart_id, $product_id, $quantity, $product['price'], $product['condition_status']);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getCartItems()
    {
        $user_id = $this->ensureUserIsLoggedIn();
        return $this->cartModel->getItemsByUserId($user_id);
    }

    public function getCartTotal()
    {
        $items = $this->getCartItems();
        return array_reduce($items, fn($total, $item) => $total + $item['subtotal'], 0);
    }

    public function getCartItemCount()
    {
        $items = $this->getCartItems();
        return array_reduce($items, fn($count, $item) => $count + $item['quantity'], 0);
    }

    public function updateCartItemQuantity($product_id, $quantity)
    {
        $user_id = $this->ensureUserIsLoggedIn();
        
        if ($quantity <= 0) {
            return $this->removeCartItem($product_id);
        }
        
        $product = $this->cartModel->findProductById($product_id);
        if ($product && $product['stock_quantity'] < $quantity) {
            throw new \Exception("Số lượng cập nhật vượt quá số lượng tồn kho.");
        }

        return $this->cartModel->updateItemQuantity($user_id, $product_id, $quantity);
    }

    public function removeCartItem($product_id)
    {
        $user_id = $this->ensureUserIsLoggedIn();
        return $this->cartModel->removeItem($user_id, $product_id);
    }

    public function clearCart()
    {
        $user_id = $this->ensureUserIsLoggedIn();
        return $this->cartModel->clearCart($user_id);
    }
}

/**
 * Xử lý các yêu cầu AJAX trực tiếp
 */
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    require_once(__DIR__ . '/../../../config/config.php');
    require_once(__DIR__ . '/../../Models/cart/CartModel.php');
    
    header('Content-Type: application/json');
    $cartController = new CartController($pdo);
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $response = ['success' => false, 'message' => 'Hành động không hợp lệ.'];

    try {
        switch ($action) {
            case 'add':
                $product_id = (int)($_POST['product_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 1);
                if ($product_id > 0 && $quantity > 0) {
                    $cartController->addToCart($product_id, $quantity);
                    $response = [
                        'success' => true,
                        'message' => 'Sản phẩm đã được thêm vào giỏ hàng.',
                        'cart_count' => $cartController->getCartItemCount()
                    ];
                } else {
                    throw new \Exception('Thông tin sản phẩm không hợp lệ.');
                }
                break;

            case 'update':
                $product_id = (int)($_POST['product_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 0);
                if ($product_id > 0) {
                    $cartController->updateCartItemQuantity($product_id, $quantity);
                    $response = [
                        'success' => true,
                        'message' => 'Giỏ hàng đã được cập nhật.',
                        'cart_count' => $cartController->getCartItemCount(),
                        'total' => $cartController->getCartTotal()
                    ];
                } else {
                    throw new \Exception('ID sản phẩm không hợp lệ.');
                }
                break;

            case 'remove':
                $product_id = (int)($_POST['product_id'] ?? 0);
                if ($product_id > 0) {
                    $cartController->removeCartItem($product_id);
                    $response = [
                        'success' => true,
                        'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng.',
                        'cart_count' => $cartController->getCartItemCount(),
                        'total' => $cartController->getCartTotal()
                    ];
                } else {
                    throw new \Exception('ID sản phẩm không hợp lệ.');
                }
                break;

            case 'clear':
                $cartController->clearCart();
                $response = ['success' => true, 'message' => 'Giỏ hàng đã được xóa sạch.'];
                break;

            case 'count':
                $response = [
                    'success' => true,
                    'count' => $cartController->getCartItemCount()
                ];
                break;
        }
    } catch (\Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
        http_response_code(400); // Bad Request
    }

    echo json_encode($response);
    exit;
}
