<?php
$config_path = __DIR__ . '/../../../config/config.php';
require_once($config_path);

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'public/index.php');
    exit();
}

$auth = new Auth($pdo);
$error_message = '';
$success_message = '';

if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success_message = 'Đăng xuất thành công!';
}

if (isset($_SESSION['logout_message'])) {
    $success_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    if (!empty($email) && !empty($password)) {
        $result = $auth->login($email, $password, $remember_me);

        if ($result['success']) {
            $_SESSION['success_toast'] = "Đăng nhập thành công!";
            $redirect_url = BASE_URL . 'public/index.php';
            if (isset($_SESSION['login_redirect_url'])) {
                $redirect_url = $_SESSION['login_redirect_url'];
                unset($_SESSION['login_redirect_url']);
            } elseif (isset($_GET['redirect'])) {
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
    <title>Đăng nhập - Trao Đổi Đồ Cũ</title>
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

        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            height: auto;
            max-height: 90vh;
            display: flex;
            position: relative;
        }

        /* Banner Section */
        .login-banner {
            flex: 1.1;
            /* Updated to use local image */
            background-image: url('<?php echo BASE_URL; ?>public/assets/images/login_bg.png');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            /* Text at bottom */
            padding: 40px;
            color: #1a1a1a;
            /* Dark text for light background */
        }

        /* Subtle gradient to ensure text readability if needed, but keeping it light */
        .login-banner::after {
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
            /* Primary color for title */
        }

        .banner-text {
            font-size: 1rem;
            color: #4b5563;
        }

        /* Form Section */
        .login-form-wrapper {
            flex: 1;
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
            margin-bottom: 1.5rem;
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

        .forgot-password-link {
            display: block;
            text-align: right;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password-link:hover {
            text-decoration: underline;
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

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
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

        .register-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .register-text a {
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

        @media (max-width: 992px) {
            body {
                overflow: auto;
                height: auto;
                min-height: 100vh;
            }

            .login-container {
                max-width: 500px;
                height: auto;
                flex-direction: column;
                max-height: none;
            }

            .login-banner {
                display: none;
            }

            .login-form-wrapper {
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Banner -->
        <div class="login-banner">
            <div class="banner-content">
                <div class="banner-title">Thanh toán an toàn,<br>Mua sắm tiện lợi.</div>
                <div class="banner-text">Hàng ngàn sản phẩm chất lượng đang chờ đón bạn.</div>
            </div>
        </div>

        <!-- Form -->
        <div class="login-form-wrapper">
            <a href="<?php echo BASE_URL; ?>" class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 11V7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7V11M5 9H19L20 21H4L5 9Z"
                        stroke="#0d6efd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>WebMuaBanDoCu</span>
            </a>

            <div class="form-header">
                <h2>Bắt đầu khám phá</h2>
                <p>Đăng nhập để vào thế giới đồ cũ chất lượng.</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-custom alert-dismissible fade show">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-custom alert-dismissible fade show">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="name@example.com"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mật khẩu</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••"
                            required style="padding-right: 40px;">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <!-- Eye Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-eye">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    <a href="<?php echo BASE_URL; ?>app/View/user/forgot_password.php" class="forgot-password-link">Quên
                        mật khẩu?</a>
                </div>

                <div class="form-group mt-1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label text-secondary small" for="remember_me">Ghi nhớ đăng nhập</label>
                    </div>
                </div>

                <button type="submit" class="btn-primary-custom">Đăng nhập</button>

                <div class="divider">
                    <span>hoặc</span>
                </div>

                <button type="button" id="google-login-btn" class="btn-google">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google"
                        width="20">
                    <span>Đăng nhập bằng Google</span>
                </button>
            </form>

            <div class="register-text">
                Không có tài khoản? <a href="<?php echo BASE_URL; ?>public/index.php?page=register">Đăng ký ngay</a>
            </div>
        </div>
    </div>

    <!-- Toggle Password Script -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = togglePassword.querySelector('svg');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // toggle the eye icon
            if (type === 'text') {
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
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
                                        console.error('Login failed:', data);
                                    }
                                } catch (e) {
                                    console.error('Server error (Non-JSON):', text);
                                    alert('Lỗi máy chủ (500 Error). Vui lòng kiểm tra Console (F12) để biết chi tiết.\nNội dung: ' + text.substring(0, 100));
                                }
                            })
                            .catch((error) => {
                                console.error("Fetch error:", error);
                                alert("Lỗi kết nối: " + error.message);
                            });
                    })
                    .catch((error) => {
                        console.error("Google Auth Error:", error);
                        alert("Lỗi xác thực Google: " + error.message);
                    });
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>