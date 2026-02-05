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
            $baseUrl = defined('BASE_URL') ? BASE_URL : '/';
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

    private static $forcedPageType = null;

    /**
     * Ép kiểu page type thủ công (dùng trong controller nếu muốn ghi đè logic tự động)
     */
    public static function setPageType($type)
    {
        self::$forcedPageType = $type;
    }

    /**
     * Auto-detect page type từ nhiều nguồn tin cậy
     * 
     * @param string|null $path Đường dẫn kiểm tra (nếu null sẽ tự detect)
     * @return string|null Page type hoặc null nếu không detect được
     */
    public static function detectPageType($path = null)
    {
        // 0. Ưu tiên giá trị được ép kiểu thủ công
        if (self::$forcedPageType !== null) {
            return self::$forcedPageType;
        }

        if ($path === null) {
            // 1. Ưu tiên $_GET['page'] (do RewriteRule truyền vào public/index.php)
            $pageParam = $_GET['page'] ?? '';
            
            // 2. Tiếp theo là REQUEST_URI (URL thực tế người dùng gõ)
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            
            // 3. Cuối cùng là SCRIPT_NAME (file php thực tế đang chạy)
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

            // Kết hợp các nguồn để tìm kiếm pattern
            $testPaths = [$pageParam, $requestUri, $scriptName];
        } else {
            $testPaths = [$path];
        }

        foreach ($testPaths as $testPath) {
            if (empty($testPath)) continue;
            
            $testPath = str_replace('\\', '/', $testPath);

            // Product pages
            if (strpos($testPath, '/product/') !== false || 
                strpos($testPath, 'product_detail') !== false ||
                strpos($testPath, 'Product_detail') !== false ||
                strpos($testPath, 'Product.php') !== false ||
                strpos($testPath, 'sell.php') !== false ||
                strpos($testPath, 'category') !== false ||
                strpos($testPath, 'categories') !== false ||
                strpos($testPath, 'products') !== false) {
                return 'product';
            }

            // Order pages
            if (strpos($testPath, '/order/') !== false ||
                strpos($testPath, 'order_history') !== false ||
                strpos($testPath, 'order_details') !== false) {
                return 'order';
            }

            // Checkout page
            if (strpos($testPath, '/checkout/') !== false ||
                strpos($testPath, 'checkout') !== false) {
                return 'checkout';
            }

            // Auth pages
            if (strpos($testPath, '/user/login') !== false ||
                strpos($testPath, '/user/register') !== false ||
                strpos($testPath, 'login') !== false ||
                strpos($testPath, 'register') !== false ||
                strpos($testPath, 'forgot_password') !== false ||
                strpos($testPath, 'reset_password') !== false) {
                return 'auth';
            }

            // Profile page
            if (strpos($testPath, 'ProfileUserView') !== false ||
                strpos($testPath, '/user/profile') !== false ||
                strpos($testPath, 'profile') !== false) {
                return 'profile';
            }

            // Search pages
            if (strpos($testPath, '/extra/search') !== false ||
                strpos($testPath, 'search_advanced') !== false ||
                strpos($testPath, 'search.php') !== false ||
                strpos($testPath, 'search') !== false) {
                return 'search';
            }

            // Notification page
            if (strpos($testPath, '/extra/notifications') !== false ||
                strpos($testPath, 'notifications.php') !== false ||
                strpos($testPath, 'notification') !== false) {
                return 'notification';
            }
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

