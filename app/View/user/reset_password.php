<?php
// Trang reset mật khẩu với token
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
$token = $_GET['token'] ?? '';
$user_info = null;

// Kiểm tra token hợp lệ
if (empty($token)) {
    $error_message = 'Token không hợp lệ hoặc đã hết hạn.';
} else {
    $validation = $controller->validateToken($token);
    if (!$validation['success']) {
        $error_message = $validation['message'];
    } else {
        $user_info = $validation;
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user_info) {
    $data = $_POST;
    $data['token'] = $token;
    
    $result = $controller->resetPassword($data);
    
    if ($result['success']) {
        $success_message = $result['message'];
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
    <title>Đặt lại mật khẩu - Cửa Hàng Đồ Cũ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Mobile Responsive CSS for Auth Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-auth-pages.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .reset-password-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }
        
        .reset-password-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .reset-password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-password-header i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .reset-password-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .reset-password-header p {
            color: #666;
            margin-bottom: 0;
        }
        
        .user-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .user-info strong {
            color: #667eea;
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
        
        .password-strength {
            margin-top: 10px;
            font-size: 12px;
        }
        
        .password-strength .requirement {
            color: #dc3545;
            transition: color 0.3s ease;
        }
        
        .password-strength .requirement.valid {
            color: #28a745;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            cursor: pointer;
            z-index: 10;
        }
        
        @media (max-width: 576px) {
            .reset-password-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-header">
            <i class="fas fa-shield-alt"></i>
            <h2>Đặt lại mật khẩu</h2>
            <p>Nhập mật khẩu mới cho tài khoản của bạn</p>
        </div>
        
        <?php if ($user_info): ?>
            <div class="user-info">
                <i class="fas fa-user-circle me-2"></i>
                Đặt lại mật khẩu cho: <strong><?php echo htmlspecialchars($user_info['user_name']); ?></strong>
                <br>
                <small class="text-muted"><?php echo htmlspecialchars($user_info['email']); ?></small>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <div class="back-to-login">
                <a href="/WebMuaBanDoCu/app/View/user/login.php">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Đăng nhập ngay
                </a>
            </div>
        <?php elseif ($error_message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <div class="back-to-login">
                <a href="/WebMuaBanDoCu/app/View/user/forgot_password.php">
                    <i class="fas fa-redo me-2"></i>
                    Yêu cầu reset mật khẩu mới
                </a>
            </div>
        <?php elseif ($user_info): ?>
        <form method="POST" id="resetPasswordForm">
            <div class="mb-3">
                <label for="new_password" class="form-label">Mật khẩu mới</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           class="form-control" 
                           id="new_password" 
                           name="new_password" 
                           placeholder="Nhập mật khẩu mới"
                           required>
                    <i class="fas fa-eye password-toggle" id="toggleNewPassword"></i>
                </div>
                <div class="password-strength">
                    <div class="requirement" id="length">
                        <i class="fas fa-times me-1"></i>
                        Ít nhất 6 ký tự
                    </div>
                    <div class="requirement" id="uppercase">
                        <i class="fas fa-times me-1"></i>
                        Có chữ hoa
                    </div>
                    <div class="requirement" id="lowercase">
                        <i class="fas fa-times me-1"></i>
                        Có chữ thường
                    </div>
                    <div class="requirement" id="number">
                        <i class="fas fa-times me-1"></i>
                        Có số
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Nhập lại mật khẩu mới"
                           required>
                    <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                <i class="fas fa-shield-alt me-2"></i>
                Đặt lại mật khẩu
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
        // Password visibility toggle
        document.getElementById('toggleNewPassword')?.addEventListener('click', function() {
            const password = document.getElementById('new_password');
            const icon = this;
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
            const password = document.getElementById('confirm_password');
            const icon = this;
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password strength validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        
        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };
            
            // Update UI
            Object.keys(requirements).forEach(key => {
                const element = document.getElementById(key);
                if (element) {
                    const icon = element.querySelector('i');
                    if (requirements[key]) {
                        element.classList.add('valid');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-check');
                    } else {
                        element.classList.remove('valid');
                        icon.classList.remove('fa-check');
                        icon.classList.add('fa-times');
                    }
                }
            });
            
            return Object.values(requirements).every(req => req);
        }
        
        function validateForm() {
            const password = newPasswordInput?.value || '';
            const confirmPassword = confirmPasswordInput?.value || '';
            
            const isPasswordStrong = checkPasswordStrength(password);
            const isPasswordMatch = password === confirmPassword;
            
            if (submitBtn) {
                submitBtn.disabled = !(isPasswordStrong && isPasswordMatch && password.length > 0);
            }
            
            // Update confirm password field styling
            if (confirmPasswordInput) {
                if (confirmPassword && !isPasswordMatch) {
                    confirmPasswordInput.classList.add('is-invalid');
                } else {
                    confirmPasswordInput.classList.remove('is-invalid');
                }
            }
        }
        
        newPasswordInput?.addEventListener('input', validateForm);
        confirmPasswordInput?.addEventListener('input', validateForm);
        
        // Form submission
        document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
            const password = newPasswordInput?.value || '';
            const confirmPassword = confirmPasswordInput?.value || '';
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp.');
                return;
            }
            
            if (!checkPasswordStrength(password)) {
                e.preventDefault();
                alert('Mật khẩu không đủ mạnh. Vui lòng kiểm tra các yêu cầu.');
                return;
            }
        });
    </script>
</body>
</html> 