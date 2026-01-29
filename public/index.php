<?php
/**
 * File index.php chính của website - Router
 */

// Chuyển hướng đến trang chủ
// Chuyển hướng đến trang chủ
require_once __DIR__ . '/../config/bootstrap.php';

// Lấy tham số page từ URL

// Lấy tham số page từ URL
$page = $_GET['page'] ?? 'home';

// Router - xử lý các trang khác nhau
switch ($page) {
    case 'notification_api':
        // Xử lý API thông báo
        require_once __DIR__ . '/../app/Controllers/extra/NotificationAPI.php';
        $api = new NotificationAPI();
        $api->handleRequest();
        break;

    case 'login':
        require_once __DIR__ . '/../app/View/user/login.php';
        break;

    case 'register':
        require_once __DIR__ . '/../app/View/user/register.php';
        break;

    case 'logout':
        $userController = new UserController();
        $result = $userController->logout();
        if (isset($result['success']) && $result['success']) {
            $_SESSION['logout_message'] = $result['message'];
        }
        header('Location: login');
        exit();
        break;

    case 'product_detail':
        require_once __DIR__ . '/../app/View/product/Product_detail.php';
        break;

    case 'payment_success':
        // Trang thanh toán thành công
        require_once __DIR__ . '/../app/View/payment/success.php';
        break;

    case 'products':
        require_once __DIR__ . '/../app/Controllers/product/FrontendProductController.php';
        $controller = new FrontendProductController();
        $controller->index();
        break;

    case 'categories':
        require_once __DIR__ . '/../app/Controllers/product/FrontendProductController.php';
        $controller = new FrontendProductController();
        $controller->categories();
        break;

    case 'home':
    default:
        // Trang chủ mặc định
        require_once __DIR__ . '/../app/Controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
}
?>