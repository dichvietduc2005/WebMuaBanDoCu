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
    <title>Đăng nhập - Web Mua Bán Đồ Cũ</title>
    <link href="<?php echo BASE_URL; ?>public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Mobile Responsive CSS for Auth Pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-auth-pages.css">

    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-image: url('<?php echo BASE_URL; ?>public/assets/images/nen2.jpg');
            /* Sử dụng hình nền có sẵn */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            margin: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;

            gap: 20px;
        }

        .login-header h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.25);
            border-color: var(--primary-color);
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            background: #2e59d9;
            transform: translateY(-2px);
        }

        .btn-google {
            background: #fff;
            color: #444;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.3s;
            margin: auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-google:hover {
            background: #f8f9fa;
            border-color: #bbb;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 15px 0;
            color: #888;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #858796;
        }

        .divider span {
            padding: 0 10px;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
            border: none;
        }

        .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .fuck-bootstrap-mb {
            margin-bottom: 10px;
        }

        .fuck-bootstrap-flex-row-center{
            display: flex;
            flex-direction: row;
            justify-content: center;
        }

    </style>
</head>

<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header text-center mb-4">
                <h2>Chào mừng trở lại</h2>
                <p class="text-muted">Đăng nhập vào Web Mua Bán Đồ Cũ</p>
            </div>

            <form method="POST">

                <div class="fuck-bootstrap-mb">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-envelope me-1 text-muted"></i> Email
                    </label>
                    <input type="email" class="form-control" name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="example@mail.com"
                        required>
                </div>

                <div class="">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-lock me-1 text-muted"></i> Mật khẩu
                    </label>
                    <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                </div>


                <div class="mt-4 ">
                    <div class="form-check">
                        <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                        <label for="remember_me" class="form-check-label small text-muted">Ghi nhớ tôi</label>
                    </div>
                    <div class="text-right links">
                        <a href="<?php echo BASE_URL; ?>app/View/user/forgot_password.php"
                            class="small text-decoration-none">Quên
                            mật khẩu?</a>
                    </div>
                </div>

                <div class="fuck-bootstrap-flex-row-center">
                    <div>
                        <button type="submit" class="btn btn-primary btn-login">ĐĂNG NHẬP</button>
                    </div>
                    
                </div>
                
                <div class="divider">
                    <span>Hoặc đăng nhập với</span>
                </div>

                <div class="">
                    <button type="button" id="google-login-btn" class="btn btn-google shadow-sm px-4"
                        style="width: auto; ">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg"
                            alt="Google">
                        <span>Google</span>
                    </button>

                </div>
            </form>

            <div class="text-center mt-4 links ">
                <p class="text-muted small">Bạn chưa có tài khoản?
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=register">Đăng ký ngay</a>
                </p>
                <a href="<?php echo BASE_URL; ?>public/index.php" class="text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.0.0/firebase-app.js";
        import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/10.0.0/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "AIzaSyAEQKDAGHeRG6gULhVKsHvFNza_HJ68kiI",
            authDomain: "uthsocial-a2f90.firebaseapp.com",
            projectId: "uthsocial-a2f90",
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();

        document.getElementById('google-login-btn').addEventListener('click', () => {
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
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) window.location.href = base + 'public/index.php';
                            else alert(data.message);
                        });
                })
                .catch((error) => console.error("Lỗi Google Login:", error));
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>