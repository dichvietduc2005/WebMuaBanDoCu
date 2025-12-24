<?php
require_once __DIR__ . '/../../config/config.php';

// Chỉ cho phép admin truy cập khu vực này
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

// Xác định trang hiện tại qua tham số ?page=
$page = $_GET['page'] ?? 'dashboard';

// Ánh xạ tên page -> file view tương ứng
$routes = [
    'dashboard' => APP_PATH . '/View/admin/DashboardView.php',
    'users' => APP_PATH . '/View/admin/QuanLyTaiKhoanView.php',
    'products_pending' => APP_PATH . '/View/admin/products.php',
    'products' => APP_PATH . '/View/admin/manage_products.php',
    'messages' => APP_PATH . '/View/admin/DanhSachBoxChatView.php',
    'payments' => APP_PATH . '/View/admin/PaymentsView.php',
    'admin_logs' => APP_PATH . '/View/admin/AdminLogsView.php',
    'user_logs' => APP_PATH . '/View/admin/UserLogsView.php',
    'user_behavior' => APP_PATH . '/View/admin/UserBehaviorDashboardView.php',
    'coupons' => APP_PATH . '/View/admin/coupons.php',
    'notifications' => APP_PATH . '/View/admin/notifications.php',
    'theme_customization' => APP_PATH . '/View/admin/ThemeCustomizationView.php',
];

// Nếu không tồn tại route, đưa về dashboard (hoặc có thể hiển thị 404 riêng)
if (!array_key_exists($page, $routes)) {
    $page = 'dashboard';
}

$viewFile = $routes[$page];

if (!file_exists($viewFile)) {
    http_response_code(404);
    echo '<h1>404 - Trang quản trị không tồn tại</h1>';
    exit;
}

// Biến $currentAdminPage có thể dùng trong layout (highlight menu, breadcrumb, ...)
$currentAdminPage = $page;

require $viewFile;
