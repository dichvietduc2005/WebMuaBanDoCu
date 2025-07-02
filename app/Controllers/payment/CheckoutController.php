<?php
class CheckoutController {
    private $pdo;
    
    public function __construct($pdo = null) {
        if ($pdo === null) {
            global $pdo;
        }
        $this->pdo = $pdo;
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
