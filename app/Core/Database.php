<?php
/**
 * Database Singleton Class
 * Quản lý kết nối database hiệu quả và bảo mật
 */
class Database 
{
    private static $instance = null;
    private $pdo = null;
    private $config;
    
    private function __construct() 
    {
        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'dbname' => $_ENV['DB_NAME'] ?? 'muabandocu', 
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        ];
        
        $this->connect();
    }
    
    /**
     * Lấy instance (Singleton pattern)
     */
    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Kết nối database với retry logic
     */
    private function connect() 
    {
        $maxRetries = 3;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=3306;dbname=%s;charset=%s",
                    $this->config['host'],
                    $this->config['dbname'], 
                    $this->config['charset']
                );
                
                $this->pdo = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    $this->config['options']
                );
                
                // Test kết nối
                $this->pdo->query('SELECT 1');
                break;
                
            } catch (PDOException $e) {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    error_log("Database connection failed after {$maxRetries} attempts: " . $e->getMessage());
                    throw new Exception("Không thể kết nối database. Vui lòng thử lại sau.");
                }
                
                // Đợi trước khi retry
                usleep(500000); // 0.5 giây
            }
        }
    }
    
    /**
     * Lấy PDO instance
     */
    public function getConnection() 
    {
        // Kiểm tra kết nối còn sống không
        try {
            $this->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            // Reconnect nếu kết nối bị mất
            $this->connect();
        }
        
        return $this->pdo;
    }
    
    /**
     * Thực hiện query với prepared statement an toàn
     */
    public function query($sql, $params = []) 
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Lỗi thực hiện truy vấn database.");
        }
    }
    
    /**
     * Bắt đầu transaction
     */
    public function beginTransaction() 
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() 
    {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() 
    {
        return $this->pdo->rollback();
    }
    
    /**
     * Lấy ID cuối cùng được insert
     */
    public function lastInsertId() 
    {
        return $this->pdo->lastInsertId();
    }
    
    // Ngăn clone và unserialize
    private function __clone() {}
    public function __wakeup() {}
} 