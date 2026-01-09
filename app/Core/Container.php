<?php
namespace App\Core;

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
            throw new \Exception("Service '{$name}' not found in container");
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


