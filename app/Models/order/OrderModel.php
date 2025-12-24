<?php
/**
 * OrderModel - Handle order related database operations
 */

class OrderModel 
{
    private $db;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get recent orders for a user
     */
    public function getRecentOrders($userId, $limit = 6) 
    {
        $sql = "
            SELECT o.*, COUNT(oi.id) as item_count
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.buyer_id = ? 
            GROUP BY o.id
            ORDER BY o.created_at DESC 
            LIMIT ?
        ";
        
        return $this->db->query($sql, [$userId, $limit]);
    }
    
    /**
     * Count items in cart for a user
     */
    public function getCartItemCount($userId)
    {
        $sql = "
            SELECT SUM(ci.quantity) as total_quantity
            FROM carts c 
            JOIN cart_items ci ON c.id = ci.cart_id 
            WHERE c.user_id = ?
        ";
        
        $result = $this->db->queryOne($sql, [$userId]);
        return $result['total_quantity'] ?? 0;
    }

    /**
     * Count unread notifications
     */
    public function countUnreadNotifications($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $result = $this->db->queryOne($sql, [$userId]);
        return (int)($result['count'] ?? 0);
    }
}
