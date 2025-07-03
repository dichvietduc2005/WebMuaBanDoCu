<?php
/**
 * Auth Helper Functions
 * Sử dụng trong các controller và view để kiểm tra authentication
 */

require_once(__DIR__ . '/../Models/user/Auth.php');

/**
 * Khởi tạo Auth instance
 */
if (!function_exists('getAuth')) {
    function getAuth($pdo = null) {
        global $pdo;
        return new Auth($pdo);
    }
}

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function isLoggedIn() {
    global $pdo;
    $auth = new Auth($pdo);
    return $auth->isLoggedIn();
}

/**
 * Lấy thông tin user hiện tại
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    $auth = new Auth($pdo);
    return $auth->getCurrentUser();
}

/**
 * Yêu cầu đăng nhập - redirect đến login nếu chưa đăng nhập
 */
function requireLogin($redirect_url = null) {
    if (!isLoggedIn()) {
        // Lưu URL hiện tại để redirect lại sau khi login
        if ($redirect_url) {
            $_SESSION['login_redirect_url'] = $redirect_url;
        } else {
            $_SESSION['login_redirect_url'] = $_SERVER['REQUEST_URI'];
        }
        
        header('Location: /WebMuaBanDoCu/public/index.php?page=login');
        exit();
    }
    
    return $_SESSION;
}

/**
 * Yêu cầu quyền admin
 */
function requireAdmin() {
    $user = requireLogin();
    
    // Kiểm tra role admin (cần thêm trường role vào bảng users)
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData || $userData['role'] !== 'admin') {
            header('HTTP/1.0 403 Forbidden');
            die('Bạn không có quyền truy cập trang này.');
        }
    } catch (PDOException $e) {
        error_log("Check admin role error: " . $e->getMessage());
        header('HTTP/1.0 500 Internal Server Error');
        die('Có lỗi xảy ra.');
    }
    
    return $user;
}

/**
 * Tạo CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Kiểm tra CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Tạo CSRF input hidden field
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Escape output để tránh XSS
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect an toàn
 */
function safeRedirect($url, $allowed_domains = []) {
    // Chỉ cho phép redirect trong cùng domain hoặc các domain được phép
    $parsed = parse_url($url);
    
    if (isset($parsed['host'])) {
        $current_host = $_SERVER['HTTP_HOST'];
        if ($parsed['host'] !== $current_host && !in_array($parsed['host'], $allowed_domains)) {
            $url = '/WebMuaBanDoCu/public/TrangChu.php'; // Default fallback
        }
    }
    
    header('Location: ' . $url);
    exit();
}

/**
 * Tạo flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Lấy và xóa flash messages
 */
function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Hiển thị flash messages HTML
 */
function displayFlashMessages() {
    $messages = getFlashMessages();
    if (empty($messages)) return '';
    
    $html = '';
    foreach ($messages as $message) {
        $alertClass = $message['type'] === 'error' ? 'alert-danger' : 'alert-' . $message['type'];
        $html .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        $html .= e($message['message']);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
    }
    
    return $html;
}
?>
