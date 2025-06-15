<?php
// Logout functionality - Clear user session and redirect

// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if config file exists before requiring it
$config_path = '../../config/config.php';
if (file_exists($config_path)) {
    try {
        require_once($config_path);
    } catch (Exception $e) {
        error_log("Error loading config during logout: " . $e->getMessage());
    }
} else {
    // If config not found, continue with basic logout
    error_log("Config file not found during logout: " . $config_path);
}

// Kiểm tra xem có phiên đăng nhập không
$was_logged_in = isset($_SESSION['user_id']);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Thực hiện đăng xuất an toàn
if ($was_logged_in) {
    // Log the logout action
    error_log("User logout: " . ($user_name ? $user_name : 'Unknown') . " (ID: " . $_SESSION['user_id'] . ")");
    
    // Xóa tất cả session variables
    $_SESSION = array();
    
    // Xóa session cookie nếu có
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Hủy session hoàn toàn
    session_destroy();
    
    // Khởi tạo session mới để có thể hiển thị thông báo
    session_start();
    $_SESSION['logout_message'] = 'Đăng xuất thành công!';
}

// Xác định trang redirect
$redirect_url = '../index.php'; // Mặc định về trang chủ

// Kiểm tra tham số redirect từ URL (để có thể quay về trang trước đó)
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $redirect_param = $_GET['redirect'];
    
    // Validate redirect URL để tránh open redirect attack
    $allowed_redirects = [
        '../index.php',
        '../cart/index.php',
        '../checkout/index.php',
        'login.php',
        'register.php'
    ];
    
    if (in_array($redirect_param, $allowed_redirects)) {
        $redirect_url = $redirect_param;
    }
}

// Thêm cache control headers để tránh cache trang logout
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Redirect với thông báo
header('Location: ' . $redirect_url . '?logout=success');
exit();
?>
