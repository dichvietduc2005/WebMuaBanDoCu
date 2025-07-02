<?php
/**
 * Autoloader class để tự động load các class
 * Giúp giảm thiểu số lượng require_once và cải thiện hiệu suất
 */
class Autoloader 
{
    private static $registered = false;
    private static $classMap = [];
    
    /**
     * Đăng ký autoloader
     */
    public static function register() 
    {
        if (self::$registered) {
            return;
        }
        
        spl_autoload_register([__CLASS__, 'load']);
        self::$registered = true;
        
        // Map các class thường dùng để tăng tốc độ
        self::$classMap = [
            // Models
            'Auth' => __DIR__ . '/../Models/user/Auth.php',
            'UserModel' => __DIR__ . '/../Models/user/UserModel.php',
            'CartModel' => __DIR__ . '/../Models/cart/CartModel.php',
            'ProductModel' => __DIR__ . '/../Models/product/ProductModel.php',
            'ProductUserModel' => __DIR__ . '/../Models/product/ProductUserModel.php',
            'CategoryModel' => __DIR__ . '/../Models/product/CategoryModel.php',
            'OrderModel' => __DIR__ . '/../Models/order/OrderModel.php',
            'CancelOrder' => __DIR__ . '/../Models/order/CancelOrder.php',
            'ReOrder' => __DIR__ . '/../Models/order/ReOrder.php',
            'AdminModel' => __DIR__ . '/../Models/admin/AdminModel.php',
            'SellModel' => __DIR__ . '/../Models/sell/SellModel.php',
            'ExtraModel' => __DIR__ . '/../Models/extra/ExtraModel.php',
            'Search' => __DIR__ . '/../Models/extra/Search.php',
            
            // Controllers
            'UserController' => __DIR__ . '/../Controllers/user/UserController.php',
            'CartController' => __DIR__ . '/../Controllers/cart/CartController.php',
            'ProductController' => __DIR__ . '/../Controllers/product/ProductController.php',
            'ProductUserController' => __DIR__ . '/../Controllers/product/ProductUserController.php',
            'OrderController' => __DIR__ . '/../Controllers/order/OrderController.php',
            'AdminController' => __DIR__ . '/../Controllers/admin/AdminController.php',
            'SellController' => __DIR__ . '/../Controllers/sell/SellController.php',
            'ExtraController' => __DIR__ . '/../Controllers/extra/ExtraController.php',
            
            // Components
            'Header' => __DIR__ . '/../Components/header/Header.php',
            'Footer' => __DIR__ . '/../Components/footer/Footer.php',
            'Sidebar' => __DIR__ . '/../Components/sidebar/Sidebar.php',
            
            // Helpers
            'ValidationHelper' => __DIR__ . '/../Helpers/ValidationHelper.php',
            'SecurityHelper' => __DIR__ . '/../Helpers/SecurityHelper.php',
            
            // Core classes
            'Database' => __DIR__ . '/Database.php',
        ];
    }
    
    /**
     * Load class theo tên
     */
    public static function load($className) 
    {
        // Kiểm tra trong class map trước
        if (isset(self::$classMap[$className])) {
            require_once self::$classMap[$className];
            return true;
        }
        
        // Tìm kiếm theo convention
        $paths = [
            // Core
            __DIR__ . '/',
            
            // Controllers và Models
            __DIR__ . '/../Controllers/',
            __DIR__ . '/../Models/',
            
            // Subdirectories của Controllers
            __DIR__ . '/../Controllers/user/',
            __DIR__ . '/../Controllers/cart/',
            __DIR__ . '/../Controllers/product/',
            __DIR__ . '/../Controllers/order/',
            __DIR__ . '/../Controllers/admin/',
            __DIR__ . '/../Controllers/sell/',
            __DIR__ . '/../Controllers/payment/',
            __DIR__ . '/../Controllers/extra/',
            
            // Subdirectories của Models
            __DIR__ . '/../Models/user/',
            __DIR__ . '/../Models/cart/',
            __DIR__ . '/../Models/product/',
            __DIR__ . '/../Models/order/',
            __DIR__ . '/../Models/admin/',
            __DIR__ . '/../Models/sell/',
            __DIR__ . '/../Models/extra/',
            
            // Components
            __DIR__ . '/../Components/',
            __DIR__ . '/../Components/header/',
            __DIR__ . '/../Components/footer/',
            __DIR__ . '/../Components/sidebar/',
            
            // Helpers
            __DIR__ . '/../Helpers/',
        ];
        
        foreach ($paths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                // Thêm vào classMap để lần sau load nhanh hơn
                self::addClassMap($className, $file);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Thêm class vào map
     */
    public static function addClassMap($className, $filePath) 
    {
        self::$classMap[$className] = $filePath;
    }
    
    /**
     * Lấy toàn bộ class map
     */
    public static function getClassMap()
    {
        return self::$classMap;
    }
    
    /**
     * Preload các class thường xuyên sử dụng
     */
    public static function preloadCommonClasses()
    {
        $commonClasses = [
            'Database',
            'UserModel',
            'ProductModel',
            'CartModel'
        ];
        
        foreach ($commonClasses as $className) {
            if (isset(self::$classMap[$className])) {
                require_once self::$classMap[$className];
            }
        }
    }
} 