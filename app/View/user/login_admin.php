<?php
// Sử dụng đường dẫn tuyệt đối thay vì tương đối để tránh lỗi khi được gọi từ router
$config_path = __DIR__ . '/../../../config/config.php';
require_once($config_path);
// Autoloader sẽ tự động load Auth class

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: /WebMuaBanDoCu/public/index.php');
    exit();
}

$auth = new Auth($pdo);
$error_message = '';
$success_message = '';

// Hiển thị thông báo logout thành công
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success_message = 'Đăng xuất thành công!';
}

if (isset($_SESSION['logout_message'])) {
    $success_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (!empty($email) && !empty($password)) {
        $result = $auth->login($email, $password, $remember_me);
        
        if ($result['success']) {
            // Ưu tiên chuyển hướng về trang đã lưu trong session, sau đó là GET param, cuối cùng là trang chủ
            $redirect_url = '/WebMuaBanDoCu/public/index.php'; // Mặc định là trang chủ
            if (isset($_SESSION['login_redirect_url'])) {
                $redirect_url = $_SESSION['login_redirect_url'];
                unset($_SESSION['login_redirect_url']); // Xóa session sau khi sử dụng
            } elseif (isset($_GET['redirect'])) {
                // Kiểm tra tính hợp lệ của URL từ GET để tránh Open Redirect Vulnerability
                if (filter_var($_GET['redirect'], FILTER_VALIDATE_URL) === FALSE || parse_url($_GET['redirect'], PHP_URL_HOST) === null) {
                    $redirect_url = $_GET['redirect'];
                }
            }
            
            header('Location: ' . $redirect_url);
            exit();
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = 'Vui lòng nhập đầy đủ thông tin.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Web Mua Bán Đồ Cũ</title>    <link href="../../../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            text-align: center;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>Đăng nhập</h2>
                <p class="text-muted">Web Mua Bán Đồ Cũ</p>
            </div>
              <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group mb-3">
                    <label for="email">Email:</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required>
                </div>
                  <div class="form-group mb-3">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           required>
                </div>
                
                <div class="form-group mb-3">
                    <input type="checkbox" 
                           id="remember_me" 
                           name="remember_me" 
                           class="form-check-input">
                    <label for="remember_me" class="form-check-label">Ghi nhớ đăng nhập</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
              <div class="text-center mt-3">
                <p class="mb-2">
                    <a href="/WebMuaBanDoCu/app/View/user/forgot_password.php" class="text-decoration-none">
                        <i class="fas fa-key me-1"></i>
                        Quên mật khẩu?
                    </a>
                </p>
                <p>Chưa có tài khoản? <a href="/WebMuaBanDoCu/public/index.php?page=register">Đăng ký ngay</a></p>
                <p><a href="/WebMuaBanDoCu/public/index.php">Về trang chủ</a></p>
            </div>
        </div>
    </div>
</body>
</html>
