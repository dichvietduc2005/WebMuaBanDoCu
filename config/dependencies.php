<?php
use App\Core\Container;
use App\Core\Database;

/**
 * Container Setup - Đăng ký tất cả services
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
        // Nếu Database class có sẵn getConnection, dùng nó
        // Nếu không, trả về global $pdo (nếu có) hoặc tạo mới
        $db = $c->get('database');
        if (method_exists($db, 'getConnection')) {
            return $db->getConnection();
        }
        
        // Fallback: Use Singleton pattern directly if possible, or global
        global $pdo;
        if ($pdo) return $pdo;
        
        return null;
    });
    
    // ========== Model Services - Placeholder ==========
    // Các model này sẽ được load qua autoload hoặc require khi cần thiết, 
    // không nhất thiết phải register tất cả nếu không dùng DI triệt để.
    // Tuy nhiên, để giữ cấu trúc, có thể để trống hoặc comment out nếu Class chưa load.
    
    /*
    $container->register('productModel', function($c) {
        return new ProductModel();
    });
    */

    return $container;
}
