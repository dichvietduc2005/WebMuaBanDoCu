<?php
/**
 * ViewHelper - Template Helper Functions
 * Tập hợp các hàm hỗ trợ để dùng trong Views
 * Bỏ logic phức tạp ra khỏi templates
 */

class ViewHelper
{
    /**
     * Get condition text in Vietnamese
     */
    public static function getConditionText($status)
    {
        $conditions = [
            'new' => 'Mới',
            'like_new' => 'Như mới',
            'good' => 'Tốt',
            'fair' => 'Khá tốt',
            'poor' => 'Cần sửa'
        ];
        return $conditions[$status] ?? 'Tốt';
    }
    
    /**
     * Format price in Vietnamese format
     */
    public static function formatPrice($price)
    {
        return number_format((int)$price) . ' VNĐ';
    }
    
    /**
     * Get product badge (featured, sale, etc)
     */
    public static function getProductBadge($product)
    {
        if ($product['featured'] ?? false) {
            return '<span class="product-badge">Nổi bật</span>';
        }
        return '';
    }
    
    /**
     * Truncate text with ellipsis
     */
    public static function truncate($text, $length = 100)
    {
        $stripped = strip_tags($text);
        $stripped = preg_replace('/\s+/', ' ', $stripped);
        return mb_strimwidth($stripped, 0, $length, '...');
    }
    
    /**
     * Get rating display
     */
    public static function getRatingDisplay($product)
    {
        $rating = isset($product['rating']) ? number_format($product['rating'], 1) : '5.0';
        $sales = isset($product['sales_count']) ? number_format($product['sales_count']) : rand(10, 500);
        
        return '<div class="product-rating">
                    <span class="stars"><i class="fas fa-star"></i> ' . $rating . '</span>
                    <span class="separator">•</span>
                    <span class="sales">Đã bán ' . $sales . '</span>
                </div>';
    }
    
    /**
     * Get discount percentage badge
     */
    public static function getDiscountBadge($price, $originalPrice)
    {
        if ($originalPrice > $price) {
            $discount = round((1 - $price/$originalPrice) * 100);
            return '<span class="discount-percent">-' . $discount . '%</span>';
        }
        return '';
    }
    
    /**
     * Get stock status text
     */
    public static function getStockStatus($quantity)
    {
        if ($quantity <= 0) {
            return '<span style="color: #ef4444; font-weight: 600;">Hết hàng</span>';
        }
        if ($quantity < 5) {
            return '<span style="color: #f59e0b; font-weight: 600;">Còn ' . $quantity . ' sản phẩm</span>';
        }
        return '<span style="color: #10b981; font-weight: 600;">Còn hàng</span>';
    }
    
    /**
     * Get category icon
     */
    public static function getCategoryIcon($slug)
    {
        $icons = [
            'dien-thoai-may-tinh-bang' => 'fas fa-mobile-alt',
            'laptop-may-tinh' => 'fas fa-laptop',
            'thoi-trang-phu-kien' => 'fas fa-tshirt',
            'do-gia-dung-noi-that' => 'fas fa-home',
            'xe-co-phuong-tien' => 'fas fa-motorcycle',
            'sach-van-phong-pham' => 'fas fa-book',
            'the-thao-giai-tri' => 'fas fa-gamepad',
            'dien-may-cong-nghe' => 'fas fa-tv',
            'me-va-be' => 'fas fa-baby'
        ];
        return $icons[$slug] ?? 'fas fa-cube';
    }
    
    /**
     * Check if product is in stock
     */
    public static function isInStock($product)
    {
        return (int)($product['stock_quantity'] ?? 0) > 0;
    }
    
    /**
     * Get safe URL parameter
     */
    public static function safeUrl($url)
    {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Get safe HTML text
     */
    public static function safeText($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

