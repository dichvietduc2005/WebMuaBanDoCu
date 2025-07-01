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
            'Auth' => __DIR__ . '/../Models/user/Auth.php',
            'CartModel' => __DIR__ . '/../Models/cart/CartModel.php',
            'CartController' => __DIR__ . '/../Controllers/cart/CartController.php',
            'ProductModel' => __DIR__ . '/../Models/product/ProductModel.php',
            'ProductController' => __DIR__ . '/../Controllers/product/ProductController.php',
            'UserModel' => __DIR__ . '/../Models/user/UserModel.php',
            'UserController' => __DIR__ . '/../Controllers/user/UserController.php',
            'OrderController' => __DIR__ . '/../Controllers/order/OrderController.php',
            'CategoryModel' => __DIR__ . '/../Models/product/CategoryModel.php',
            'ExtraController' => __DIR__ . '/../Controllers/extra/ExtraController.php',
            'Search' => __DIR__ . '/../Models/extra/Search.php',
            'AdminController' => __DIR__ . '/../Controllers/admin/AdminController.php',
            'AdminModel' => __DIR__ . '/../Models/admin/AdminModel.php',
            'SellController' => __DIR__ . '/../Controllers/sell/SellController.php',
            'SellModel' => __DIR__ . '/../Models/sell/SellModel.php',
            'ValidationHelper' => __DIR__ . '/../Helpers/ValidationHelper.php',
            'SecurityHelper' => __DIR__ . '/../Helpers/SecurityHelper.php',
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
            return;
        }
        
        // Tìm kiếm theo convention
        $paths = [
            __DIR__ . '/../Controllers/',
            __DIR__ . '/../Models/',
            __DIR__ . '/../Models/user/',
            __DIR__ . '/../Models/cart/',
            __DIR__ . '/../Models/product/',
            __DIR__ . '/../Models/order/',
            __DIR__ . '/../Controllers/user/',
            __DIR__ . '/../Controllers/cart/',
            __DIR__ . '/../Controllers/product/',
            __DIR__ . '/../Controllers/order/',
            __DIR__ . '/../Controllers/admin/',
        ];
        
        foreach ($paths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
    
    /**
     * Thêm class vào map
     */
    public static function addClassMap($className, $filePath) 
    {
        self::$classMap[$className] = $filePath;
    }
} 