<?php
/**
 * MobileCSSLoader - Component để tự động load CSS responsive phù hợp
 * Tối ưu code bằng cách tập trung logic load CSS vào một nơi
 */

class MobileCSSLoader
{
    /**
     * CSS files mapping theo page type
     */
    private static $cssMap = [
        'product' => 'mobile-product-pages.css',
        'order' => 'mobile-order-pages.css',
        'checkout' => 'mobile-checkout-page.css',
        'auth' => 'mobile-auth-pages.css',
        'profile' => 'mobile-profile-page.css',
        'search' => 'mobile-search-pages.css',
        'notification' => 'mobile-notifications-page.css',
    ];

    /**
     * Load mobile CSS cho page type cụ thể
     * 
     * @param string $pageType Loại trang: 'product', 'order', 'checkout', 'auth', 'profile', 'search', 'notification'
     * @param string|null $baseUrl Base URL của website (nếu null sẽ dùng BASE_URL constant)
     * @return void Output CSS link tag
     */
    public static function loadMobileCSS($pageType, $baseUrl = null)
    {
        if ($baseUrl === null) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : '/WebMuaBanDoCu/';
        }

        // Kiểm tra page type hợp lệ
        if (!isset(self::$cssMap[$pageType])) {
            error_log("MobileCSSLoader: Invalid page type '$pageType'");
            return;
        }

        $cssFile = self::$cssMap[$pageType];
        $cssPath = $baseUrl . 'public/assets/css/' . $cssFile;

        // Kiểm tra file tồn tại
        $fullPath = realpath(__DIR__ . '/../../../public/assets/css/' . $cssFile);
        if (!$fullPath || !file_exists($fullPath)) {
            error_log("MobileCSSLoader: CSS file not found: $cssFile");
            return;
        }

        // Output CSS link tag
        echo '    <!-- Mobile Responsive CSS for ' . htmlspecialchars($pageType) . ' pages -->' . "\n";
        echo '    <link rel="stylesheet" href="' . htmlspecialchars($cssPath) . '">' . "\n";
    }

    /**
     * Load multiple CSS files cho nhiều page types
     * 
     * @param array $pageTypes Mảng các page types
     * @param string|null $baseUrl Base URL
     * @return void
     */
    public static function loadMultipleMobileCSS($pageTypes, $baseUrl = null)
    {
        foreach ($pageTypes as $pageType) {
            self::loadMobileCSS($pageType, $baseUrl);
        }
    }

    /**
     * Auto-detect page type từ file path hoặc script name
     * 
     * @param string|null $scriptPath Đường dẫn script (nếu null sẽ dùng $_SERVER['SCRIPT_NAME'])
     * @return string|null Page type hoặc null nếu không detect được
     */
    public static function detectPageType($scriptPath = null)
    {
        if ($scriptPath === null) {
            $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
        }

        // Normalize path
        $scriptPath = str_replace('\\', '/', $scriptPath);

        // Product pages
        if (strpos($scriptPath, '/product/') !== false || 
            strpos($scriptPath, 'Product_detail') !== false ||
            strpos($scriptPath, 'Product.php') !== false ||
            strpos($scriptPath, 'sell.php') !== false ||
            strpos($scriptPath, 'category.php') !== false ||
            strpos($scriptPath, 'categories.php') !== false ||
            strpos($scriptPath, 'products.php') !== false) {
            return 'product';
        }

        // Order pages
        if (strpos($scriptPath, '/order/') !== false ||
            strpos($scriptPath, 'order_history') !== false ||
            strpos($scriptPath, 'order_details') !== false) {
            return 'order';
        }

        // Checkout page
        if (strpos($scriptPath, '/checkout/') !== false ||
            strpos($scriptPath, 'checkout') !== false) {
            return 'checkout';
        }

        // Auth pages
        if (strpos($scriptPath, '/user/login') !== false ||
            strpos($scriptPath, '/user/register') !== false ||
            strpos($scriptPath, 'forgot_password') !== false ||
            strpos($scriptPath, 'reset_password') !== false) {
            return 'auth';
        }

        // Profile page
        if (strpos($scriptPath, 'ProfileUserView') !== false ||
            strpos($scriptPath, '/user/profile') !== false) {
            return 'profile';
        }

        // Search pages
        if (strpos($scriptPath, '/extra/search') !== false ||
            strpos($scriptPath, 'search_advanced') !== false ||
            strpos($scriptPath, 'search.php') !== false) {
            return 'search';
        }

        // Notification page
        if (strpos($scriptPath, '/extra/notifications') !== false ||
            strpos($scriptPath, 'notifications.php') !== false) {
            return 'notification';
        }

        return null;
    }

    /**
     * Auto-load CSS dựa trên script path hiện tại
     * 
     * @param string|null $baseUrl Base URL
     * @return void
     */
    public static function autoLoad($baseUrl = null)
    {
        $pageType = self::detectPageType();
        if ($pageType) {
            self::loadMobileCSS($pageType, $baseUrl);
        }
    }
}

