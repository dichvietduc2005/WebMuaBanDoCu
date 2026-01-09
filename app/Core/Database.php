<?php
namespace App\Core;

/**
 * Database class - Quản lý kết nối database với cache
 * Class này sử dụng Singleton pattern để đảm bảo chỉ có một kết nối database trong toàn ứng dụng
 * Được bổ sung cơ chế caching để tăng hiệu suất
 */
class Database 
{
    // Instance của class
    private static $instance = null;
    
    // PDO connection
    private $connection;
    
    // Cache cho query results
    private static $queryCache = [];
    private $cachingEnabled = true;
    private $cacheLifetime = 300; // 5 phút
    private $cachePrefix = 'db_cache_';
    
    /**
     * Khởi tạo kết nối PDO
     */
    private function __construct() 
    {
        try {
            // Database config
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'muabandocu';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            // DSN
            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            
            // PDO Options
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true // Sử dụng kết nối persistent để tăng hiệu suất
            ];
            
            // Tạo connection
            $this->connection = new \PDO($dsn, $username, $password, $options);
            
        } catch (\PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new \Exception('Kết nối database thất bại: ' . $e->getMessage());
        }
    }
    
    /**
     * Singleton pattern - Lấy instance
     */
    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Lấy connection
     */
    public function getConnection() 
    {
        return $this->connection;
    }
    
    /**
     * Thực hiện prepared statement với caching
     */
    public function query($query, $params = [], $useCache = true) 
    {
        if ($useCache && $this->cachingEnabled) {
            $cacheKey = $this->generateCacheKey($query, $params);
            
            // Kiểm tra cache
            $cachedResult = $this->getFromCache($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
        
        // Thực thi query
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Lưu vào cache
        if ($useCache && $this->cachingEnabled) {
            $this->saveToCache($cacheKey, $result);
        }
        
        return $result;
    }
    
    /**
     * Thực hiện query và chỉ trả về một dòng kết quả
     */
    public function queryOne($query, $params = [], $useCache = true) 
    {
        if ($useCache && $this->cachingEnabled) {
            $cacheKey = $this->generateCacheKey($query, $params, '_one');
            
            // Kiểm tra cache
            $cachedResult = $this->getFromCache($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
        
        // Thực thi query
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Lưu vào cache
        if ($useCache && $this->cachingEnabled) {
            $this->saveToCache($cacheKey, $result);
        }
        
        return $result;
    }
    
    /**
     * Thực hiện query trả về giá trị đơn
     */
    public function queryValue($query, $params = [], $useCache = true) 
    {
        if ($useCache && $this->cachingEnabled) {
            $cacheKey = $this->generateCacheKey($query, $params, '_value');
            
            // Kiểm tra cache
            $cachedResult = $this->getFromCache($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
        
        // Thực thi query
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchColumn();
        
        // Lưu vào cache
        if ($useCache && $this->cachingEnabled) {
            $this->saveToCache($cacheKey, $result);
        }
        
        return $result;
    }
    
    /**
     * Thực hiện update/insert/delete (không cache)
     */
    public function execute($query, $params = []) 
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Lấy last insert ID
     */
    public function lastInsertId() 
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Bắt đầu transaction
     */
    public function beginTransaction() 
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() 
    {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() 
    {
        return $this->connection->rollBack();
    }
    
    /**
     * Xóa cache cho query cụ thể
     */
    public function invalidateCache($query, $params = []) 
    {
        $cacheKey = $this->generateCacheKey($query, $params);
        unset(self::$queryCache[$cacheKey]);
    }
    
    /**
     * Xóa toàn bộ cache
     */
    public function clearCache() 
    {
        self::$queryCache = [];
    }
    
    /**
     * Bật/tắt caching
     */
    public function enableCaching($enabled = true) 
    {
        $this->cachingEnabled = $enabled;
        return $this;
    }
    
    /**
     * Thiết lập thời gian cache
     */
    public function setCacheLifetime($seconds) 
    {
        $this->cacheLifetime = $seconds;
        return $this;
    }
    
    /**
     * Tạo cache key từ query và params
     */
    private function generateCacheKey($query, $params, $suffix = '') 
    {
        return $this->cachePrefix . md5($query . json_encode($params)) . $suffix;
    }
    
    /**
     * Lấy data từ cache
     */
    private function getFromCache($key) 
    {
        if (isset(self::$queryCache[$key])) {
            $cacheData = self::$queryCache[$key];
            
            // Kiểm tra xem cache có hết hạn chưa
            if ($cacheData['expires'] > time()) {
                return $cacheData['data'];
            }
            
            // Cache đã hết hạn, xóa đi
            unset(self::$queryCache[$key]);
        }
        
        return null;
    }
    
    /**
     * Lưu data vào cache
     */
    private function saveToCache($key, $data) 
    {
        self::$queryCache[$key] = [
            'data' => $data,
            'expires' => time() + $this->cacheLifetime
        ];
    }
} 