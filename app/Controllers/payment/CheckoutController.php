<?php
class CheckoutController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }      // Hiển thị trang checkout
    public function index() {
        // Require user helpers
        require_once __DIR__ . '/../../Models/user/Auth.php';
        require_once __DIR__ . '/../../Models/cart/CartModel.php';
        require_once __DIR__ . '/../../helpers.php';
        
        // Lấy user_id hiện tại - REQUIRE LOGIN for checkout
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            $_SESSION['error_message'] = 'Bạn cần đăng nhập để thanh toán.';
            header('Location: /WebMuaBanDoCu/app/Views/auth/login.php');
            exit;
        }        
        // Lấy thông tin giỏ hàng
        $cartItems = $this->getCartItems($user_id);
        $cartTotal = $this->getCartTotal($user_id);
        $cartItemCount = $this->getCartItemCount($user_id);
        
        // Thêm thông tin hình ảnh sản phẩm vào cart items
        $cartItems = $this->addProductImages($cartItems);
        
        // Debug log cho checkout
        error_log("=== CHECKOUT DEBUG ===");
        error_log("User ID: " . $user_id);
        error_log("Cart Items: " . print_r($cartItems, true));
        error_log("Cart Total: " . $cartTotal);
        error_log("Cart Item Count: " . $cartItemCount);
          // Kiểm tra giỏ hàng có trống không
        if (empty($cartItems)) {
            $_SESSION['error_message'] = 'Giỏ hàng của bạn đang trống!';
            header('Location: /WebMuaBanDoCu/app/Views/cart/index.php');
            exit;
        }
        
        // Lấy thông tin user nếu đã đăng nhập
        $userInfo = null;
        if ($user_id) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Hiển thị view checkout
        require_once __DIR__ . '/../../Views/checkout/index.php';
    }    // Xử lý thanh toán
    public function processPayment() {
        // Debug logging
        error_log("=== PROCESS PAYMENT DEBUG ===");
        error_log("Current URL: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'NOT SET'));
        error_log("HTTP_HOST: " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'NOT SET'));
        error_log("REQUEST_METHOD: " . (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'NOT SET'));
        error_log("POST data: " . print_r($_POST, true));
        error_log("Session user: " . print_r($_SESSION['user'] ?? 'NOT LOGGED IN', true));
        
        // Require login for payment
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            $_SESSION['error_message'] = 'Bạn cần đăng nhập để thanh toán.';
            header('Location: /WebMuaBanDoCu/app/Views/auth/login.php');
            exit;
        }        
        // Validate thông tin form
        $billingInfo = $this->validateBillingInfo($_POST);
        error_log("Billing info validation: " . print_r($billingInfo, true));
          if (!$billingInfo['valid']) {
            $_SESSION['error_message'] = $billingInfo['message'];
            header('Location: /WebMuaBanDoCu/app/Views/checkout/index.php');
            exit;
        }
        
        $cartItems = $this->getCartItems($user_id);
        $cartTotal = $this->getCartTotal($user_id);
          if (empty($cartItems)) {
            $_SESSION['error_message'] = 'Giỏ hàng của bạn đang trống!';
            header('Location: /WebMuaBanDoCu/app/Views/cart/index.php');
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Tạo đơn hàng
            $orderId = $this->createOrder($user_id, $cartItems, $cartTotal, $billingInfo['data']);
            
            if (!$orderId) {
                throw new Exception('Không thể tạo đơn hàng');
            }
            
            // Cập nhật tồn kho
            $this->updateProductStock($cartItems);
            
            $this->pdo->commit();
            
            // Chuyển hướng đến payment gateway
            $this->redirectToPaymentGateway($orderId, $cartTotal, $billingInfo['data']);
            
        } catch (Exception $e) {
            $this->pdo->rollback();            error_log("Checkout error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Có lỗi xảy ra khi xử lý đơn hàng. Vui lòng thử lại.';
            header('Location: /WebMuaBanDoCu/app/Views/checkout/index.php');
            exit;
        }
    }
    
    // Trang thành công sau thanh toán
    public function success() {
        $orderId = $_GET['order_id'] ?? null;
        $paymentStatus = $_GET['status'] ?? 'pending';
          if (!$orderId) {
            header('Location: /WebMuaBanDoCu/app/router.php?controller=home&action=index');
            exit;
        }
        
        // Lấy thông tin đơn hàng
        $order = $this->getOrderById($orderId);
          if (!$order) {
            $_SESSION['error_message'] = 'Không tìm thấy đơn hàng!';
            header('Location: /WebMuaBanDoCu/app/router.php?controller=home&action=index');
            exit;
        }
        
        // Hiển thị view thành công
        require_once __DIR__ . '/../../Views/checkout/success.php';
    }
    
    // === PRIVATE HELPER METHODS ===
    
    private function getCurrentUserId() {
        return $_SESSION['user']['id'] ?? null;
    }
    
    private function getCartItems($user_id) {
        if (!$user_id) {
            // Guest cart - sử dụng session_id
            $session_id = session_id();
            $stmt = $this->pdo->prepare("
                SELECT ci.*, p.title as product_name, p.price as current_price,
                       (ci.added_price * ci.quantity) as subtotal
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                JOIN products p ON ci.product_id = p.id
                WHERE c.session_id = ? AND c.user_id IS NULL
            ");
            $stmt->execute([$session_id]);
        } else {
            // Logged in user cart
            $stmt = $this->pdo->prepare("
                SELECT ci.*, p.title as product_name, p.price as current_price,
                       (ci.added_price * ci.quantity) as subtotal
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                JOIN products p ON ci.product_id = p.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getCartTotal($user_id) {
        if (!$user_id) {
            // Guest cart
            $session_id = session_id();
            $stmt = $this->pdo->prepare("
                SELECT SUM(ci.added_price * ci.quantity) as total
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.session_id = ? AND c.user_id IS NULL
            ");
            $stmt->execute([$session_id]);
        } else {
            // Logged in user cart
            $stmt = $this->pdo->prepare("
                SELECT SUM(ci.added_price * ci.quantity) as total
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function getCartItemCount($user_id) {
        if (!$user_id) {
            // Guest cart
            $session_id = session_id();
            $stmt = $this->pdo->prepare("
                SELECT SUM(ci.quantity) as count
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.session_id = ? AND c.user_id IS NULL
            ");
            $stmt->execute([$session_id]);
        } else {
            // Logged in user cart
            $stmt = $this->pdo->prepare("
                SELECT SUM(ci.quantity) as count
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    private function validateBillingInfo($postData) {
        $errors = [];
        
        // Validate required fields
        $requiredFields = [
            'txt_billing_fullname' => 'Họ và tên',
            'txt_billing_email' => 'Email',
            'txt_billing_mobile' => 'Số điện thoại',
            'txt_bill_city' => 'Thành phố',
            'txt_inv_addr1' => 'Địa chỉ'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (empty(trim($postData[$field] ?? ''))) {
                $errors[] = "$label là bắt buộc";
            }
        }
        
        // Validate email
        if (!empty($postData['txt_billing_email']) && !filter_var($postData['txt_billing_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        
        // Validate phone
        $phone = $postData['txt_billing_mobile'] ?? '';
        if (!empty($phone) && !preg_match('/^(0|\+84)[0-9]{9}$/', $phone)) {
            $errors[] = 'Số điện thoại không hợp lệ';
        }
        
        if (!empty($errors)) {
            return [
                'valid' => false,
                'message' => implode(', ', $errors)
            ];
        }
        
        return [
            'valid' => true,
            'data' => [
                'fullname' => trim($postData['txt_billing_fullname']),
                'email' => trim($postData['txt_billing_email']),
                'mobile' => trim($postData['txt_billing_mobile']),
                'city' => trim($postData['txt_bill_city']),
                'address' => trim($postData['txt_inv_addr1']),
                'notes' => trim($postData['order_notes'] ?? '')
            ]
        ];
    }
      private function createOrder($user_id, $cartItems, $total, $billingInfo) {
        $orderNumber = 'ORDER_' . date('YmdHis') . '_' . rand(1000, 9999);
        
        // Tạo ghi chú đơn hàng với thông tin billing
        $orderNotes = "Thanh toán đơn hàng từ Web Mua Bán Đồ Cũ";
        $orderNotes .= "\nNgười nhận: " . $billingInfo['fullname'];
        $orderNotes .= "\nEmail: " . $billingInfo['email'];
        $orderNotes .= "\nSĐT: " . $billingInfo['mobile'];
        $orderNotes .= "\nĐịa chỉ: " . $billingInfo['address'] . ", " . $billingInfo['city'];
        if (!empty($billingInfo['notes'])) {
            $orderNotes .= "\nGhi chú: " . $billingInfo['notes'];
        }
        
        // Tạo đơn hàng chính
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (
                order_number, buyer_id, total_amount, status, payment_status, notes, created_at
            ) VALUES (?, ?, ?, 'pending', 'pending', ?, NOW())
        ");
        
        $stmt->execute([
            $orderNumber,
            $user_id,
            $total,
            $orderNotes
        ]);
        
        $orderId = $this->pdo->lastInsertId();
        
        // Tạo order items
        $orderItemStmt = $this->pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_title, product_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($cartItems as $item) {
            $subtotal = $item['added_price'] * $item['quantity'];
            $orderItemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['product_name'],
                $item['added_price'],
                $item['quantity'],
                $subtotal
            ]);
        }
        
        return $orderId;
    }
    
    private function updateProductStock($cartItems) {
        $updateStockStmt = $this->pdo->prepare("
            UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?
        ");
        
        foreach ($cartItems as $item) {
            $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
        }
    }
      private function redirectToPaymentGateway($orderId, $total, $billingInfo) {
        // Prepare data cho VNPay  
        $paymentData = [
            'order_id' => 'ORDER_' . date('YmdHis') . '_' . rand(1000, 9999),
            'order_type' => 'billpayment',
            'amount' => $total,
            'order_desc' => 'Thanh toan don hang tu Web Mua Ban Do Cu',
            'bank_code' => '',
            'language' => 'vn',
            'txtexpire' => '',
            'txt_billing_fullname' => $billingInfo['fullname'],
            'txt_billing_email' => $billingInfo['email'],
            'txt_billing_mobile' => $billingInfo['mobile'],
            'txt_bill_city' => $billingInfo['city'],
            'txt_inv_addr1' => $billingInfo['address'],
            'txt_bill_country' => 'VN',
            'txt_bill_state' => '',
            'redirect' => '1',
            'order_notes' => $billingInfo['notes'] ?? ''
        ];
        
        // Backup current $_POST
        $originalPost = $_POST;
        
        // Set $_POST with payment data
        $_POST = $paymentData;
        
        // Include VNPay payment file
        require_once __DIR__ . '/create_payment.php';
        
        // Restore original $_POST (chỉ để an toàn, thực tế không cần vì sẽ exit)
        $_POST = $originalPost;
        exit;
    }
    
    private function getOrderById($orderId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function clearCart($user_id) {
        if (!$user_id) {
            // Guest cart
            $session_id = session_id();
            $stmt = $this->pdo->prepare("
                DELETE ci FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.session_id = ? AND c.user_id IS NULL
            ");
            $stmt->execute([$session_id]);
        } else {
            // Logged in user cart
            $stmt = $this->pdo->prepare("
                DELETE ci FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
        }
    }
    
    private function addProductImages($cartItems) {
        foreach ($cartItems as &$item) {
            $stmt = $this->pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC LIMIT 1");
            $stmt->execute([$item['product_id']]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            $item['image_path'] = $image ? $image['image_path'] : '';
        }
        return $cartItems;
    }
}
?>
