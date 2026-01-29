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
    <title>Đăng ký - Trao Đổi Đồ Cũ</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/assets/images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-hover: #0b5ed7;
            --text-color: #1a1a1a;
            --text-secondary: #666;
            --border-color: #e5e7eb;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 0.75rem;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }

        .register-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 1100px;
            height: auto;
            max-height: 95vh;
            display: flex;
            position: relative;
        }

        /* Banner Section */
        .register-banner {
            flex: 0.9;
            background-image: url('<?php echo BASE_URL; ?>public/assets/images/login_bg.png');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
            color: #1a1a1a;
        }

        .register-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0) 60%);
        }

        .banner-content {
            position: relative;
            z-index: 10;
        }

        .banner-title {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.5rem;
            color: #0d6efd;
        }

        .banner-text {
            font-size: 1rem;
            color: #4b5563;
        }

        /* Form Section */
        .register-form-wrapper {
            flex: 1.2;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .brand-logo {
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .brand-logo svg {
            width: 28px;
            height: 28px;
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.25rem;
            font-size: 1.5rem;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Inputs */
        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .required-mark {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 0.95rem;
            color: var(--text-color);
            background-color: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: 0;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--text-color);
        }

        .btn-primary-custom {
            display: inline-block;
            width: 100%;
            padding: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            background-color: var(--primary-color);
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.4);
            margin-top: 0.5rem;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .login-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .login-text a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .alert-custom {
            border-radius: var(--radius);
            border: none;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
        }

        /* Google & Divider Styles */
        .divider {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            color: #9ca3af;
            font-size: 0.8rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid var(--border-color);
        }

        .divider span {
            padding: 0 0.75rem;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-color);
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            gap: 10px;
        }

        .btn-google:hover {
            background-color: #f9fafb;
            border-color: #d1d5db;
        }

        @media (max-width: 992px) {
            body {
                overflow: auto;
                height: auto;
                min-height: 100vh;
            }

            .register-container {
                max-width: 500px;
                height: auto;
                flex-direction: column;
                max-height: none;
            }

            .register-banner {
                display: none;
            }

            .register-form-wrapper {
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <!-- Banner -->
        <div class="register-banner">
            <div class="banner-content">
                <div class="banner-title">Tham gia cộng đồng<br>ngay hôm nay</div>
                <div class="banner-text">Tạo tài khoản miễn phí để đăng bán và mua sắm thảo thích.</div>
            </div>
        </div>

        <!-- Form -->
        <div class="register-form-wrapper">
            <a href="<?php echo BASE_URL; ?>" class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 11V7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7V11M5 9H19L20 21H4L5 9Z"
                        stroke="#0d6efd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>WebMuaBanDoCu</span>
            </a>

            <div class="form-header">
                <h2>Đăng ký tài khoản</h2>
                <p>Nhập thông tin cá nhân của bạn bên dưới.</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-custom alert-dismissible fade show">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-custom alert-dismissible fade show">
                    <?php echo htmlspecialchars($success_message); ?>
                    <div class="mt-2">
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=login" class="fw-bold text-success">Đăng nhập
                            ngay</a>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="username">Tên đăng nhập <span
                                    class="required-mark">*</span></label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="full_name">Họ và tên <span
                                    class="required-mark">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="email">Email <span class="required-mark">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="phone">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="password">Mật khẩu <span
                                    class="required-mark">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" id="password" name="password" required
                                    style="padding-right: 40px;">
                                <button type="button" class="toggle-password" data-target="#password">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-eye">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <small class="text-secondary" style="font-size: 0.75rem;">Tối thiểu 6 ký tự</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Nhập lại mật khẩu <span
                                    class="required-mark">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required style="padding-right: 40px;">
                                <button type="button" class="toggle-password" data-target="#confirm_password">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-eye">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary-custom">Đăng ký tài khoản</button>

                <div class="divider">
                    <span>hoặc</span>
                </div>

                <button type="button" id="google-login-btn" class="btn-google">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google"
                        width="20">
                    <span>Đăng nhập bằng Google</span>
                </button>
            </form>

            <div class="login-text">
                Đã có tài khoản? <a href="<?php echo BASE_URL; ?>public/index.php?page=login">Đăng nhập ngay</a>
            </div>
        </div>
    </div>

    <!-- Toggle Password Script -->
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.querySelector(targetId);
                const eyeIcon = this.querySelector('svg');

                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);

                if (type === 'text') {
                    eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                } else {
                    eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                }
            });
        });
    </script>

    <!-- Firebase -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.0.0/firebase-app.js";
        import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/10.0.0/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "AIzaSyALO-2UL8aJKPW8DdOr9bofn8FqKbHEAxY",
            authDomain: "shoptnd-ac1b6.firebaseapp.com",
            projectId: "shoptnd-ac1b6",
            storageBucket: "shoptnd-ac1b6.firebasestorage.app",
            messagingSenderId: "707060274885",
            appId: "1:707060274885:web:63c8f340f7d1443edac2ac",
            measurementId: "G-11K2GFQDM7"
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();

        const googleBtn = document.getElementById('google-login-btn');
        if (googleBtn) {
            googleBtn.addEventListener('click', () => {
                // Prevent double click
                googleBtn.disabled = true;
                const originalText = googleBtn.innerHTML;
                googleBtn.innerHTML = '<span>Đang kết nối...</span>';

                signInWithPopup(auth, provider)
                    .then((result) => {
                        return result.user.getIdToken();
                    })
                    .then((idToken) => {
                        const base = '<?php echo BASE_URL; ?>';
                        fetch(base + 'app/Controllers/user/FirebaseAuthController.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ idToken: idToken })
                        })
                            .then(async response => {
                                const text = await response.text();
                                try {
                                    const data = JSON.parse(text);
                                    if (data.success) {
                                        window.location.href = base + 'public/index.php';
                                    } else {
                                        alert('Lỗi đăng nhập: ' + (data.message || 'Không xác định'));
                                        googleBtn.disabled = false;
                                        googleBtn.innerHTML = originalText;
                                    }
                                } catch (e) {
                                    console.error('Server error (Non-JSON):', text);
                                    alert('Lỗi máy chủ (500 Error). Vui lòng kiểm tra Console (F12) để biết chi tiết.\nNội dung: ' + text.substring(0, 100));
                                    googleBtn.disabled = false;
                                    googleBtn.innerHTML = originalText;
                                }
                            })
                            .catch((error) => {
                                console.error("Fetch error:", error);
                                alert("Lỗi kết nối: " + error.message);
                                googleBtn.disabled = false;
                                googleBtn.innerHTML = originalText;
                            });
                    })
                    .catch((error) => {
                        console.error("Google Auth Error:", error);
                        // Only alert if it's NOT a closed-popup-by-user error
                        if (error.code !== 'auth/popup-closed-by-user' && error.code !== 'auth/cancelled-popup-request') {
                            alert("Lỗi xác thực Google: " + error.message);
                        } else {
                            console.log("User closed the popup or cancelled.");
                        }
                        googleBtn.disabled = false;
                        googleBtn.innerHTML = originalText;
                    });
            });
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>