<?php
// Logout functionality - Clear user session and redirect
require_once('../../Models/user/Auth.php');

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

if ($was_logged_in) {
    // Sử dụng Auth class để đăng xuất
    $auth = new Auth($pdo);
    $result = $auth->logout();
    
    // Khởi tạo session mới để có thể hiển thị thông báo
    session_start();
    if ($result['success']) {
        $_SESSION['logout_message'] = $result['message'];
        $_SESSION['logout_toast'] = "Bạn đã đăng xuất thành công. Hẹn gặp lại!";
    }
}

// Xác định trang redirect
$redirect_url = '../../../public/index.php'; // Mặc định về trang chủ

// Kiểm tra tham số redirect từ URL (để có thể quay về trang trước đó)
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $redirect_param = $_GET['redirect'];
    
    // Validate redirect URL để tránh open redirect attack
    $allowed_redirects = [
        '../../../public/index.php',
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
