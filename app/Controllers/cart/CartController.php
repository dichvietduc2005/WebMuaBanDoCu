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

/**
 * Exception cho lỗi tồn kho/hết hàng
 */
class StockException extends \Exception
{
    public function __construct($message = "", $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

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
        // #region agent log
        file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:49', 'message' => 'addToCart entry', 'data' => ['product_id' => $product_id, 'quantity' => $quantity], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H4')) . "\n", FILE_APPEND);
        // #endregion
        $user_id = $this->ensureUserIsLoggedIn();
        // #region agent log
        file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:52', 'message' => 'user_id obtained', 'data' => ['user_id' => $user_id, 'session_status' => session_status()], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H2')) . "\n", FILE_APPEND);
        // #endregion
        try {
            $product = $this->cartModel->findProductById($product_id);
            // #region agent log
            file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:54', 'message' => 'product found', 'data' => ['product_exists' => !empty($product), 'product_status' => $product['status'] ?? null, 'stock_quantity' => $product['stock_quantity'] ?? null, 'product_id' => $product['id'] ?? null], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H4')) . "\n", FILE_APPEND);
            // #endregion
            if (!$product)
                throw new \Exception("Sản phẩm không tồn tại.");
            if ($product['status'] != 'active')
                throw new \Exception("Sản phẩm không còn được bán.");

            $cart_id = $this->cartModel->getOrCreateCartId($user_id);
            $existing_item = $this->cartModel->findCartItem($cart_id, $product_id);

            // Calculate available quantity considering existing cart items
            $existing_quantity = $existing_item ? $existing_item['quantity'] : 0;
            $available_quantity = $product['stock_quantity'] - $existing_quantity;

            // Validate requested quantity against available stock
            if ($available_quantity < $quantity) {
                $message = "Không đủ hàng trong kho. ";
                if ($existing_quantity > 0) {
                    $message .= "Bạn đã có {$existing_quantity} sản phẩm trong giỏ. ";
                }
                $message .= "Tồn kho còn lại: {$product['stock_quantity']}. ";
                if ($available_quantity > 0) {
                    $message .= "Bạn chỉ có thể thêm tối đa {$available_quantity} sản phẩm nữa.";
                } else {
                    $message .= "Bạn không thể thêm sản phẩm này nữa.";
                }
                throw new StockException($message);
            }

            if ($existing_item) {
                $new_quantity = $existing_item['quantity'] + $quantity;
                $result = $this->cartModel->updateExistingCartItemQuantity($existing_item['id'], $new_quantity);
            } else {
                $result = $this->cartModel->addNewCartItem($cart_id, $product_id, $quantity, $product['price'], $product['condition_status']);
            }

            // Log user action after successful add
            if (function_exists('log_user_action') && $result) {
                try {
                    log_user_action($this->pdo, $user_id, 'add_to_cart', "Thêm sản phẩm vào giỏ hàng: " . ($product['title'] ?? 'ID ' . $product_id), array(
                        'product_id' => $product_id,
                        'quantity' => $quantity,
                        'product_title' => $product['title'] ?? null,
                        'price' => $product['price'] ?? null
                    ));
                } catch (Exception $e) {
                    error_log('Log add_to_cart error: ' . $e->getMessage());
                }
            }

            // #region agent log
            // @phpstan-ignore-next-line - $result is always assigned in both branches of if-else above
            $logResult = is_bool($result) ? ($result ? 'true' : 'false') : (is_scalar($result) ? $result : 'non-scalar');
            $logData = array('location' => 'CartController.php:87', 'message' => 'addToCart success', 'data' => array('result' => $logResult), 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H4');
            file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
            return $result;
        } catch (\Exception $e) {
            // #region agent log
            $logData = array('location' => 'CartController.php:89', 'message' => 'addToCart exception', 'data' => array('exception_message' => $e->getMessage(), 'exception_code' => $e->getCode(), 'exception_file' => $e->getFile(), 'exception_line' => $e->getLine()), 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H4');
            file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
            throw $e;
        }
    }

    public function getCartItems()
    {
        $user_id = $this->ensureUserIsLoggedIn();
        return $this->cartModel->getItemsByUserId($user_id);
    }

    public function getSelectedCartItems(array $product_ids)
    {
        $user_id = $this->ensureUserIsLoggedIn();
        return $this->cartModel->getItemsByUserIdAndProductIds($user_id, $product_ids);
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

    public function getDiscountedTotal()
    {
        $cartTotal = $this->getCartTotal();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $appliedCoupon = $_SESSION['applied_coupon'] ?? null;

        if (!$appliedCoupon) {
            return $cartTotal;
        }

        $discountAmount = 0;
        if ($appliedCoupon['discount_type'] === 'percent') {
            $discountAmount = ($cartTotal * $appliedCoupon['discount_value']) / 100;
        } else {
            $discountAmount = $appliedCoupon['discount_value'];
        }

        // Ensure max_discount_amount is respected (if it exists in session)
        if (isset($appliedCoupon['max_discount_amount'])) {
            $maxDiscount = (float) $appliedCoupon['max_discount_amount'];
            if ($maxDiscount > 0) {
                $discountAmount = min($discountAmount, $maxDiscount);
            }
        }

        return max(0, $cartTotal - $discountAmount);
    }

    public function updateCartItemQuantity($product_id, $quantity)
    {
        $user_id = $this->ensureUserIsLoggedIn();
        if ($quantity <= 0) {
            return $this->removeCartItem($product_id);
        }
        $product = $this->cartModel->findProductById($product_id);
        if (!$product) {
            throw new \Exception("Sản phẩm không tồn tại.");
        }

        if ($product['stock_quantity'] < $quantity) {
            $message = "Số lượng cập nhật vượt quá số lượng tồn kho. ";
            $message .= "Tồn kho hiện có: {$product['stock_quantity']}. ";
            $message .= "Bạn chỉ có thể cập nhật tối đa {$product['stock_quantity']} sản phẩm.";
            throw new StockException($message);
        }

        return $this->cartModel->updateItemQuantity($user_id, $product_id, $quantity);
    }

    public function removeCartItem($product_id)
    {
        $user_id = $this->ensureUserIsLoggedIn();
        // Log before removing
        if (function_exists('log_user_action')) {
            try {
                $product = $this->cartModel->findProductById($product_id);
                log_user_action($this->pdo, $user_id, 'remove_from_cart', "Xóa sản phẩm khỏi giỏ hàng: " . ($product['title'] ?? 'ID ' . $product_id), array(
                    'product_id' => $product_id,
                    'product_title' => $product['title'] ?? null
                ));
            } catch (Exception $e) {
                error_log('Log remove_from_cart error: ' . $e->getMessage());
            }
        }
        return $this->cartModel->removeItem($user_id, $product_id);
    }

    public function clearCart()
    {
        $user_id = $this->ensureUserIsLoggedIn();
        return $this->cartModel->clearCart($user_id);
    }

    public function validateCoupon($code)
    {
        $user_id = $this->ensureUserIsLoggedIn();
        $code = strtoupper(trim($code));

        // Use PHP's time which honors date_default_timezone_set('Asia/Ho_Chi_Minh')
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?)");
        $stmt->execute([$code, $now, $now]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            throw new \Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn.");
        }

        // --- UPDATE: Kiểm tra xem user đang checkout items nào (Selected hay All) ---
        $cartTotal = 0;
        $mode = 'ALL';
        if (isset($_SESSION['checkout_selected_ids']) && !empty($_SESSION['checkout_selected_ids'])) {
            // Trường hợp Selective Checkout
            $mode = 'SELECTED (' . $_SESSION['checkout_selected_ids'] . ')';
            $selected_ids = array_map('intval', explode(',', $_SESSION['checkout_selected_ids']));
            $selectedItems = $this->cartModel->getItemsByUserIdAndProductIds($user_id, $selected_ids);

            // Tính tổng tiền thủ công cho các items đã chọn
            $cartTotal = array_reduce($selectedItems, function ($sum, $item) {
                return $sum + ($item['quantity'] * $item['added_price']);
            }, 0);
        } else {
            // Trường hợp Checkout All (Mặc định)
            $cartTotal = $this->getCartTotal();
        }

        $logMsg = "CouponDebug: User $user_id apply code $code. Mode: $mode. CartTotal: $cartTotal. MinOrder: {$coupon['min_order_value']}\n";
        file_put_contents(__DIR__ . '/../../../debug_log.txt', $logMsg, FILE_APPEND);

        // --------------------------------------------------------------------------

        if ($cartTotal < $coupon['min_order_value']) {
            throw new \Exception("Đơn hàng phải tối thiểu " . number_format($coupon['min_order_value']) . " đ để áp dụng mã này.");
        }

        // Store in session
        $_SESSION['applied_coupon'] = [
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value'],
            'max_discount_amount' => $coupon['max_discount_amount'],
            'min_order_value' => $coupon['min_order_value'] // Store this to re-check in View if needed
        ];

        return $coupon;
    }

    public function removeCoupon()
    {
        if (session_status() == PHP_SESSION_NONE)
            session_start();
        unset($_SESSION['applied_coupon']);
        return true;
    }
}

/**
 * Xử lý các yêu cầu AJAX trực tiếp
 */
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    require_once(__DIR__ . '/../../../config/config.php');
    require_once(__DIR__ . '/../../Models/cart/CartModel.php');
    header('Content-Type: application/json');
    // #region agent log
    $logData = array('action' => $_POST['action'] ?? $_GET['action'] ?? '', 'post' => $_POST, 'session_id' => session_id(), 'user_id' => $_SESSION['user_id'] ?? null, 'session_status' => session_status(), 'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
    file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:157', 'message' => 'Request received', 'data' => $logData, 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H2,H3')) . "\n", FILE_APPEND);
    // #endregion
    $cartController = new CartController($pdo);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $response = ['success' => false, 'message' => 'Hành động không hợp lệ.'];

    try {
        switch ($action) {
            case 'add':
                $product_id = (int) ($_POST['product_id'] ?? 0);
                $quantity = (int) ($_POST['quantity'] ?? 1);
                // #region agent log
                file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:170', 'message' => 'add case - before validation', 'data' => ['product_id' => $product_id, 'quantity' => $quantity, 'product_id_valid' => $product_id > 0, 'quantity_valid' => $quantity > 0], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H3')) . "\n", FILE_APPEND);
                // #endregion
                if ($product_id > 0 && $quantity > 0) {
                    // #region agent log
                    $logData = array('location' => 'CartController.php:173', 'message' => 'calling addToCart', 'data' => array('product_id' => $product_id, 'quantity' => $quantity), 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H4');
                    file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode($logData) . "\n", FILE_APPEND);
                    // #endregion
                    $cartController->addToCart($product_id, $quantity);
                    $checkout = isset($_POST['checkout']) && $_POST['checkout'] == '1';
                    // Logging đã được xử lý trong method addToCart()
                    $response = [
                        'success' => true,
                        'message' => 'Sản phẩm đã được thêm vào giỏ hàng.',
                        'cart_count' => $cartController->getCartItemCount(),
                        'checkout' => $checkout
                    ];
                } else {
                    throw new \Exception('Thông tin sản phẩm không hợp lệ.');
                }
                break;

            case 'update':
                $product_id = (int) ($_POST['product_id'] ?? 0);
                $quantity = (int) ($_POST['quantity'] ?? 0);
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
                $product_id = (int) ($_POST['product_id'] ?? 0);
                if ($product_id > 0) {
                    // Logging đã được xử lý trong method removeCartItem()
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

            case 'apply_coupon':
                $code = $_POST['code'] ?? '';
                if (empty($code))
                    throw new \Exception("Vui lòng nhập mã giảm giá.");
                $coupon = $cartController->validateCoupon($code);
                $response = [
                    'success' => true,
                    'message' => 'Áp dụng mã giảm giá thành công!',
                    'coupon' => $coupon,
                    'cart_total' => $cartController->getCartTotal(),
                    'final_total' => $cartController->getDiscountedTotal()
                ];
                break;

            case 'remove_coupon':
                $cartController->removeCoupon();
                $response = ['success' => true, 'message' => 'Đã gỡ mã giảm giá.'];
                break;
        }
    } catch (StockException $e) {
        // #region agent log
        file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:232', 'message' => 'catch block - stock exception', 'data' => ['exception_message' => $e->getMessage(), 'exception_code' => $e->getCode(), 'exception_file' => $e->getFile(), 'exception_line' => $e->getLine()], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H1,H2,H3,H4,H5')) . "\n", FILE_APPEND);
        // #endregion
        $response = array(
            'success' => false,
            'message' => $e->getMessage(),
            'error_type' => 'stock_error'
        );
        http_response_code(422); // Unprocessable Entity - phù hợp cho lỗi validation/tồn kho
    } catch (\Exception $e) {
        // #region agent log
        file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:232', 'message' => 'catch block - exception', 'data' => ['exception_message' => $e->getMessage(), 'exception_code' => $e->getCode(), 'exception_file' => $e->getFile(), 'exception_line' => $e->getLine(), 'response' => ['success' => false, 'message' => $e->getMessage()]], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H1,H2,H3,H4,H5')) . "\n", FILE_APPEND);
        // #endregion
        $response = array('success' => false, 'message' => $e->getMessage());
        http_response_code(400); // Bad Request
    }
    // #region agent log
    file_put_contents('c:\\wamp64\\www\\WebMuaBanDoCu\\.cursor\\debug.log', json_encode(array('location' => 'CartController.php:237', 'message' => 'sending response', 'data' => ['response' => $response], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'H1,H2,H3,H4,H5')) . "\n", FILE_APPEND);
    // #endregion

    echo json_encode($response);
    exit;
}