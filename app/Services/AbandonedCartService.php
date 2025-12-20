<?php
// app/Services/AbandonedCartService.php

class AbandonedCartService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function checkAbandonedCarts($thresholdMinutes = 1440) {
        // Tìm các giỏ hàng có item, chưa thanh toán
        // Sử dụng MAX(ci.added_at) để xác định thời gian cập nhật cuối cùng
        
        $sql = "
            SELECT c.user_id, c.id as cart_id, u.username, u.email, COUNT(ci.id) as item_count, MAX(ci.added_at) as last_activity
            FROM carts c
            JOIN users u ON c.user_id = u.id
            JOIN cart_items ci ON c.id = ci.cart_id
            GROUP BY c.id, c.user_id, u.username, u.email
            HAVING last_activity < DATE_SUB(NOW(), INTERVAL ? MINUTE)
            AND last_activity > DATE_SUB(NOW(), INTERVAL 30 DAY) -- Check trong 30 ngày
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$thresholdMinutes]);
        $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($carts as $cart) {
            // Nếu là test (threshold < 60 phút) thì bỏ qua check duplicate 7 ngày
            $ignoreDuplicate = $thresholdMinutes < 60;
            
            if ($this->shouldSendNotification($cart['user_id'], $ignoreDuplicate)) {
                $this->queueNotification($cart);
                $count++;
            }
        }

        return $count;
    }

    private function shouldSendNotification($userId, $ignoreDuplicate = false) {
        if ($ignoreDuplicate) return true;

        // Check if already queued or sent recently
        $sql = "
            SELECT COUNT(*) 
            FROM notification_queue 
            WHERE user_id = ? 
            AND template_code = 'cart_abandoned' 
            AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() == 0;
    }

    private function queueNotification($cart) {
        require_once __DIR__ . '/../Models/admin/NotificationQueueModel.php';
        
        $data = [
            'cart_id' => $cart['cart_id'],
            'count' => $cart['item_count'],
            'username' => $cart['username']
        ];

        addToQueue($this->pdo, $cart['user_id'], 'cart_abandoned', $data, date('Y-m-d H:i:s'));
    }
}
