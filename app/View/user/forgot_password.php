<?php
// Trang yêu cầu reset mật khẩu
$config_path = __DIR__ . '/../../../config/config.php';
require_once($config_path);
require_once(__DIR__ . '/../../Controllers/user/PasswordResetController.php');

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: /WebMuaBanDoCu/app/View/Home.php');
    exit();
}

$controller = new PasswordResetController($pdo);
$success_message = '';
$error_message = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = $controller->requestReset($_POST);
    
    if ($result['success']) {
        $success_message = $result['message'];
        // Trong môi trường development, hiển thị link reset
        if (isset($result['token'])) {
            $reset_link = BASE_URL . "app/View/user/reset_password.php?token=" . $result['token'];
            $success_message .= "<br><br><strong>Link reset mật khẩu (chỉ dành cho test):</strong><br>";
            $success_message .= "<a href='$reset_link' class='btn btn-sm btn-outline-primary mt-2'>Đặt lại mật khẩu</a>";
        }
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Cửa Hàng Đồ Cũ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .forgot-password-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }
        
        .forgot-password-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .forgot-password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .forgot-password-header i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .forgot-password-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .forgot-password-header p {
            color: #666;
            margin-bottom: 0;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .success-message {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa8a8 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: #764ba2;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 10;
        }
        
        .input-group .form-control {
            padding-left: 45px;
        }
        
        @media (max-width: 576px) {
            .forgot-password-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="forgot-password-header">
            <i class="fas fa-key"></i>
            <h2>Quên mật khẩu?</h2>
            <p>Nhập email của bạn để nhận hướng dẫn đặt lại mật khẩu</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success_message): ?>
        <form method="POST" id="forgotPasswordForm">
            <div class="mb-3">
                <label for="email" class="form-label">Email đăng ký</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="Nhập email của bạn"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-paper-plane me-2"></i>
                Gửi yêu cầu đặt lại mật khẩu
            </button>
        </form>
        <?php endif; ?>
        
        <div class="back-to-login">
            <a href="/WebMuaBanDoCu/app/View/user/login.php">
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại đăng nhập
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('forgotPasswordForm')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                e.preventDefault();
                alert('Vui lòng nhập email.');
                return;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Email không hợp lệ.');
                return;
            }
        });
    </script>
</body>
</html> 