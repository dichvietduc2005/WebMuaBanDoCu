<?php
// Sử dụng đường dẫn tuyệt đối thay vì tương đối để tránh lỗi khi được gọi từ router
$config_path = __DIR__ . '/../../../config/config.php';
require_once($config_path);
// Autoloader sẽ tự động load Auth class

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'public/index.php');
    exit();
}

$auth = new Auth($pdo);
$success_message = '';
$error_message = '';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation cơ bản
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error_message = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Mật khẩu xác nhận không khớp.';
    } else {
        // Sử dụng Auth class để đăng ký
        $result = $auth->register($username, $email, $password, $full_name, $phone);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Web Mua Bán Đồ Cũ</title>
    <link href="<?php echo BASE_URL; ?>public/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Mobile Responsive CSS for Auth Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-auth-pages.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .register-header {
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h2>Đăng ký tài khoản</h2>
                <p class="text-muted">Web Mua Bán Đồ Cũ</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                    <br><a href="<?php echo BASE_URL; ?>public/index.php?page=login">Đăng nhập ngay</a>
                </div>
            <?php endif; ?>
              <form method="POST">
                <div class="form-group mb-3">
                    <label for="username">Tên đăng nhập: <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="full_name">Họ và tên: <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="full_name" 
                           name="full_name" 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="email">Email: <span class="text-danger">*</span></label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="phone">Số điện thoại:</label>
                    <input type="text" 
                           class="form-control" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group mb-3">
                    <label for="password">Mật khẩu: <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           required>
                    <small class="form-text text-muted">Ít nhất 6 ký tự</small>
                </div>
                
                <div class="form-group mb-3">
                    <label for="confirm_password">Xác nhận mật khẩu: <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
            </form>
              <div class="text-center mt-3">
                <p>Đã có tài khoản? <a href="<?php echo BASE_URL; ?>public/index.php?page=login">Đăng nhập</a></p>
                <p><a href="<?php echo BASE_URL; ?>public/index.php">Về trang chủ</a></p>
            </div>
        </div>
    </div>
</body>
</html>
