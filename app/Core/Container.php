<?php
/**
 * Service Container - Dependency Injection Container
 * Quản lý tất cả dependencies của ứng dụng
 * 
 * Thay thế global variables bằng explicit dependency injection
 * 
 * Usage:
 *   $container = Container::getInstance();
 *   $pdo = $container->get('pdo');
 *   $renderer = $container->get('viewRenderer');
 */

class Container
{
    private static $instance = null;
    private $services = [];
    private $singletons = [];
    
    /**
     * Singleton - Lấy instance duy nhất của Container
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Đăng ký service (singleton)
     * 
     * @param string $name - Tên service
     * @param callable|object $definition - Definition hoặc Object
     */
    public function register($name, $definition)
    {
        $this->services[$name] = $definition;
        return $this;
    }
    
    /**
     * Lấy service từ container
     * Nếu là callable (factory), gọi nó để tạo instance
     * Nếu là object, trả về trực tiếp (singleton)
     */
    public function get($name)
    {
        if (!isset($this->services[$name])) {
            throw new Exception("Service '{$name}' not found in container");
        }
        
        // Nếu đã có singleton, trả về nó
        if (isset($this->singletons[$name])) {
            return $this->singletons[$name];
        }
        
        $definition = $this->services[$name];
        
        // Nếu là callable (function/closure), gọi nó để tạo instance
        if (is_callable($definition)) {
            $instance = call_user_func($definition, $this);
        } else {
            // Nếu là object, trả về trực tiếp
            $instance = $definition;
        }
        
        // Lưu singleton
        $this->singletons[$name] = $instance;
        return $instance;
    }
    
    /**
     * Kiểm tra service có tồn tại không
     */
    public function has($name)
    {
        return isset($this->services[$name]);
    }
    
    /**
     * Xóa service (dùng trong test)
     */
    public function remove($name)
    {
        unset($this->services[$name]);
        unset($this->singletons[$name]);
    }
    
    /**
     * Clear tất cả services (dùng trong test)
     */
    public function clear()
    {
        $this->services = [];
        $this->singletons = [];
    }
}

/**
 * Container Setup - Đăng ký tất cả services
 * Gọi function này trong bootstrap.php
 */
function setupContainer()
{
    $container = Container::getInstance();
    
    // ========== Database Service ==========
    $container->register('database', function($c) {
        return Database::getInstance();
    });
    
    // PDO Connection
    $container->register('pdo', function($c) {
        $db = $c->get('database');
        return $db->getConnection();
    });
    
    // ========== Model Services ==========
    // Product Models
    $container->register('productModel', function($c) {
        return new ProductModel();
    });
    
    $container->register('categoryModel', function($c) {
        return new CategoryModel();
    });
    
    $container->register('productUserModel', function($c) {
        return new ProductUserModel();
    });
    
    // User Models
    $container->register('userModel', function($c) {
        return new UserModel();
    });
    
    $container->register('authModel', function($c) {
        return new Auth();
    });
    
    // Order Models
    $container->register('orderModel', function($c) {
        return new OrderModel();
    });
    
    // Cart Models
    $container->register('cartModel', function($c) {
        return new CartModel();
    });
    
    // Admin Models
    $container->register('themeModel', function($c) {
        return new ThemeModel();
    });
    
    $container->register('notificationModel', function($c) {
        return new NotificationModel();
    });
    
    // ========== Theme Service ==========
    $container->register('frontendTheme', function($c) {
        return new FrontendThemeRenderer();
    });
    
    // ========== View Services ==========
    $container->register('viewRenderer', function($c) {
        return new ViewRenderer();
    });
    
    $container->register('viewHelper', function($c) {
        return new ViewHelper();
    });
    
    // ========== Controller Services ==========
    $container->register('homeController', function($c) {
        return new HomeController();
    });
    
    $container->register('productController', function($c) {
        return new ProductController();
    });
    
    $container->register('userController', function($c) {
        return new UserController();
    });
    
    $container->register('cartController', function($c) {
        return new CartController();
    });
    
    $container->register('orderController', function($c) {
        return new OrderController();
    });
    
    // ========== Application Config ==========
    $container->register('config', function($c) {
        return [
            'app_name' => 'HIHand Shop',
            'base_url' => BASE_URL,
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_name' => $_ENV['DB_NAME'] ?? 'muabandocu',
            'debug' => true,
        ];
    });
    
    return $container;
}

/**
 * Helper function - Tính năng riêng
 * Nếu không muốn dùng $container->get() everywhere, có thể dùng:
 * $pdo = service('pdo');
 * $productModel = service('productModel');
 */
function service($name)
{
    return Container::getInstance()->get($name);
}

