<?php
/**
 * Configuration file - Cải thiện bảo mật và hiệu suất
 */

// Timezone và session
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables từ .env file nếu có
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Load Core classes
require_once(__DIR__ . '/../app/Core/Autoloader.php');
require_once(__DIR__ . '/../app/Core/Database.php');

// Đăng ký autoloader
Autoloader::register();

// Database connection sử dụng Singleton pattern  
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
    die("Kết nối database thất bại. Vui lòng thử lại sau.");
}

// VNPAY Configuration - Sử dụng environment variables để bảo mật
$vnp_TmnCode = $_ENV['VNPAY_TMN_CODE'] ?? "X4DCQ1UX";
$vnp_HashSecret = $_ENV['VNPAY_HASH_SECRET'] ?? "MPI8C42IYO31NDYYLS2HN2KYD0XBYIFH";
$vnp_Url = $_ENV['VNPAY_URL'] ?? "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = $_ENV['VNPAY_RETURN_URL'] ?? "http://localhost/WebMuaBanDoCu/app/Controllers/payment/return.php";
$vnp_apiUrl = $_ENV['VNPAY_API_URL'] ?? "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";

// Expire time cho payment
$startTime = date("YmdHis");
$expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

// Application constants
define('BASE_URL', $_ENV['BASE_URL'] ?? '/WebMuaBanDoCu/');
define('ASSETS_URL', BASE_URL . 'public/assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');
define('UPLOAD_URL', BASE_URL . 'public/uploads/');

// Path constants
define('APP_PATH', __DIR__ . '/../app/');
define('CONTROLLERS_PATH', APP_PATH . 'Controllers/');
define('MODELS_PATH', APP_PATH . 'Models/');  
define('VIEWS_PATH', APP_PATH . 'Views/');
define('COMPONENTS_PATH', APP_PATH . 'Components/');

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour

// Application settings
define('ITEMS_PER_PAGE', 12);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Error reporting (chỉ trong development)
if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Helper function để lấy config value an toàn
 */
function getConfig($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

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
