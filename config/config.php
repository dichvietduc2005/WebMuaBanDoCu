<?php
/**
 * Configuration file - Cải thiện bảo mật và hiệu suất
 */

// Đặt timezone và khởi tạo session
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình session để tăng tính ổn định
if (session_status() == PHP_SESSION_NONE) {
    // Cấu hình session trước khi khởi tạo
    ini_set('session.cookie_lifetime', 86400); // 24 giờ
    ini_set('session.gc_maxlifetime', 86400); // 24 giờ
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    
    // Thiết lập cookie parameters
    session_set_cookie_params([
        'lifetime' => 86400, // 24 giờ
        'path' => '/',
        'domain' => '',
        'secure' => false, // Đặt true nếu dùng HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
    
    // Regenerate session ID định kỳ để tăng bảo mật
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 phút
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Hằng số cho đường dẫn (nếu chưa được định nghĩa trong bootstrap.php)
if (!defined('BASE_PATH')) define('BASE_PATH', realpath(__DIR__ . '/..'));
if (!defined('APP_PATH')) define('APP_PATH', BASE_PATH . '/app');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', BASE_PATH . '/public');

// Thử load env variables nếu có file .env
$env_file = BASE_PATH . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Khởi tạo Autoloader nếu file tồn tại
$autoloader_file = APP_PATH . '/Core/Autoloader.php';
if (file_exists($autoloader_file)) {
    require_once $autoloader_file;
    
    // Đăng ký autoloader nếu class tồn tại
    if (class_exists('Autoloader')) {
        Autoloader::register();
    }
    
    // Load helpers.php nếu tồn tại
    $helpers_file = APP_PATH . '/helpers.php';
    if (file_exists($helpers_file)) {
        require_once $helpers_file;
    }
}

// Database connection
try {
    // Thông tin kết nối database
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
    
    // Tạo object Database nếu class tồn tại
    if (class_exists('Database')) {
        $db = Database::getInstance();
    }
    
} catch (PDOException $e) {
    // Ghi log và hiển thị thông báo lỗi chi tiết hơn
    error_log("Database connection error: " . $e->getMessage());
    
    // Hiển thị thông báo lỗi chi tiết để debug
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">';
    echo '<h2>Lỗi kết nối cơ sở dữ liệu</h2>';
    echo '<p><strong>Chi tiết lỗi:</strong> ' . $e->getMessage() . '</p>';
    echo '<p><strong>Host:</strong> ' . $db_host . '</p>';
    echo '<p><strong>Database:</strong> ' . $db_name . '</p>';
    echo '<p><strong>User:</strong> ' . $db_user . '</p>';
    echo '</div>';
    die();
}

// Application constants
define('BASE_URL', $_ENV['BASE_URL'] ?? '/WebMuaBanDoCu/');
define('ASSETS_URL', BASE_URL . 'public/assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');
define('UPLOAD_URL', BASE_URL . 'public/uploads/');

// Các constants khác
define('ITEMS_PER_PAGE', 12);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);  // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Error reporting (hiển thị đầy đủ lỗi để debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Helper function để lấy config value an toàn
 */
function getConfig($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// VNPAY Configuration - Sử dụng environment variables để bảo mật
$vnp_TmnCode = $_ENV['VNPAY_TMN_CODE'] ?? "5HOTQ8NB";
$vnp_HashSecret = $_ENV['VNPAY_HASH_SECRET'] ?? "JUHW3LNEC8O3JNJNN6AGRQKF81Y94DXZ";
$vnp_Url = $_ENV['VNPAY_URL'] ?? "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = $_ENV['VNPAY_RETURN_URL'] ?? "http://localhost/WebMuaBanDoCu/app/Controllers/payment/return.php";
$vnp_apiUrl = $_ENV['VNPAY_API_URL'] ?? "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";

// Expire time cho payment
$startTime = date("YmdHis");
$expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

// Path constants
define('CONTROLLERS_PATH', APP_PATH . '/Controllers/');
define('MODELS_PATH', APP_PATH . '/Models/');  
define('VIEWS_PATH', APP_PATH . '/Views/');
define('COMPONENTS_PATH', APP_PATH . '/Components/');

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour

/**
 * Validate CSRF token
 */
function validateCSRF($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && 
           hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Generate CSRF token
 */  
function generateCSRF() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}
