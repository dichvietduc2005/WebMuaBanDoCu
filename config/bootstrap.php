<?php
/**
 * Bootstrap file - Điểm khởi động tối ưu cho ứng dụng
 * File này được thiết kế để cải thiện hiệu suất bằng cách:
 * 1. Sử dụng autoloading
 * 2. Preload các class thường dùng
 * 3. Thiết lập output buffering cho hiệu suất tốt hơn
 * 4. Cấu hình caching cho PHP
 */

// Bắt đầu output buffering - cải thiện hiệu suất bằng cách gửi trang web một lần
ob_start();

// Định nghĩa các đường dẫn cơ bản
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Hiển thị lỗi (trong quá trình phát triển)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoload (PSR-4)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load config chỉ khi file tồn tại
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    die("Không tìm thấy file config.php. Vui lòng kiểm tra lại cấu hình.");
}

// Thiết lập các tùy chọn PHP cho hiệu suất tốt hơn
ini_set('memory_limit', '128M');

// Cấu hình opcache nếu có sẵn
if (function_exists('opcache_get_status')) {
    // OPcache đang được kích hoạt
    // Có thể thêm cấu hình bổ sung ở đây
}

// Kiểm tra sự tồn tại của database (nếu chưa tồn tại trong config)
if (!isset($pdo)) {
    // Thông tin kết nối database
    try {
        $db_host = $_ENV['DB_HOST'] ?? 'localhost';
        $db_name = $_ENV['DB_NAME'] ?? 'muabandocu';
        $db_user = $_ENV['DB_USER'] ?? 'root';
        $db_pass = $_ENV['DB_PASS'] ?? '';
        $db_charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        // Tạo DSN
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
        
        // Tạo PDO object với error mode hiển thị đầy đủ thông tin
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
    } catch (PDOException $e) {
        // Ghi log và hiển thị thông báo lỗi chi tiết
        error_log("Database connection error in bootstrap: " . $e->getMessage());
        
        // Hiển thị thông báo lỗi thân thiện
        show_error("Không thể kết nối đến cơ sở dữ liệu. Chi tiết lỗi: " . $e->getMessage());
    }
}

// Đường dẫn tương đối từ thư mục public
function getRelativePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = '/WebMuaBanDoCu/'; // Điều chỉnh nếu cần
    
    if (strpos($scriptName, '/public/') !== false) {
        return '..';
    } else {
        // Tính toán đường dẫn tương đối dựa trên vị trí hiện tại
        $currentPath = str_replace($basePath, '', dirname($scriptName));
        $levels = substr_count($currentPath, '/');
        return str_repeat('../', $levels);
    }
}

/**
 * Helper function để debug nhanh
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

// Helper function hiển thị lỗi thân thiện
function show_error($message, $code = 500) {
    http_response_code($code);
    echo '<div style="text-align: center; margin: 50px auto; max-width: 800px; border: 1px solid #ddd; padding: 20px; border-radius: 5px;">';
    echo '<h1 style="color: #d9534f;">Lỗi ' . $code . '</h1>';
    echo '<p>' . $message . '</p>';
    echo '<p><a href="' . ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . '/WebMuaBanDoCu/" style="color: #337ab7;">Về trang chủ</a></p>';
    echo '</div>';
    exit;
}

// Load core classes first (before creating aliases)
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Container.php';

// Use namespaced classes
use App\Core\Container;
use App\Core\Database;

// Backward compatibility: Create aliases for old class names (without namespace)
// This allows existing code to continue working without immediate refactoring
if (!class_exists('Database', false)) {
    class_alias('App\Core\Database', 'Database');
}
if (!class_exists('Container', false)) {
    class_alias('App\Core\Container', 'Container');
}

// Helper function for UrlHelper (backward compatibility)
if (!function_exists('url')) {
    function url(string $type, ...$args) {
        switch ($type) {
            case 'css':
                return \App\Core\UrlHelper::css(...$args);
            case 'js':
                return \App\Core\UrlHelper::js(...$args);
            case 'asset':
                return \App\Core\UrlHelper::asset(...$args);
            case 'route':
                return \App\Core\UrlHelper::route(...$args);
            default:
                return \App\Core\UrlHelper::to($type);
        }
    }
}

// Auto-load core classes để tách View Logic (backward compatibility)
// Các classes này sẽ được autoload bởi Composer nếu có namespace
require_once __DIR__ . '/../app/Core/UrlHelper.php';
require_once __DIR__ . '/../app/Core/ViewRenderer.php';
require_once __DIR__ . '/../app/Core/ViewHelper.php';
require_once __DIR__ . '/../app/Core/LayoutManager.php';
require_once __DIR__ . '/../app/Core/AssetManager.php';



// Setup Dependency Injection Container
require_once __DIR__ . '/dependencies.php';
$container = setupContainer();

// Expose container globally (trích xuất PDO từ container)
// Này là compatibility layer - giữ code cũ vẫn hoạt động
if (!isset($pdo)) {
    $pdo = $container->get('pdo');
}
