<?php
require_once('../../config/config.php');

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

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
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password, full_name, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'];
                    
                    // Log đăng nhập thành công
                    error_log("User login success: {$user['email']} (ID: {$user['id']})");

                    // Ưu tiên chuyển hướng về trang đã lưu trong session, sau đó là GET param, cuối cùng là trang chủ
                    $redirect_url = '../index.php'; // Mặc định là trang chủ
                    if (isset($_SESSION['login_redirect_url'])) {
                        $redirect_url = $_SESSION['login_redirect_url'];
                        unset($_SESSION['login_redirect_url']); // Xóa session sau khi sử dụng
                    } elseif (isset($_GET['redirect'])) {
                        // Cần kiểm tra tính hợp lệ của URL từ GET để tránh Open Redirect Vulnerability
                        // Ví dụ đơn giản: chỉ cho phép các URL tương đối trong trang web
                        if (filter_var($_GET['redirect'], FILTER_VALIDATE_URL) === FALSE || parse_url($_GET['redirect'], PHP_URL_HOST) === null) {
                             // Nếu là URL tương đối (không có host) hoặc URL không hợp lệ, cần xử lý cẩn thận
                             // Ví dụ: đảm bảo nó bắt đầu bằng '../' hoặc là một đường dẫn an toàn đã biết
                             // Hiện tại, để đơn giản, nếu có vẻ là URL tương đối, ta sẽ dùng nó
                             // Cần có logic kiểm tra kỹ hơn trong thực tế
                            $redirect_url = $_GET['redirect'];
                        }
                        // Nếu là URL tuyệt đối và không phải domain của bạn, không nên redirect
                        // else if (parse_url($_GET['redirect'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
                        //    $redirect_url = '../index.php'; // Hoặc trang lỗi
                        // }
                    }
                    
                    header('Location: ' . $redirect_url);
                    exit();
                } else {
                    $error_message = 'Tài khoản của bạn đã bị vô hiệu hóa.';
                }
            } else {
                $error_message = 'Email hoặc mật khẩu không đúng.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error_message = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
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
    <title>Đăng nhập - Web Mua Bán Đồ Cũ</title>    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
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
                
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
            
            <div class="text-center mt-3">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                <p><a href="index.php">Về trang chủ</a></p>
            </div>
        </div>
    </div>
</body>
</html>
