<?php
require_once('../../../config/config.php');
require_once('../../Models/user/Auth.php');

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: ../../../public/TrangChu.php');
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
            $redirect_url = '../../../public/TrangChu.php'; // Mặc định là trang chủ
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clerk.js Quickstart</title>
    <style>
        /* Tùy chỉnh CSS cơ bản để nhìn rõ hơn */
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
        }
        #app {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    
    <div id="app">
        Đang tải Clerk...
    </div>

    <script
        async
        crossorigin="anonymous"
        data-clerk-publishable-key="pk_test_ZWFnZXItZm9hbC05OS5jbGVyay5hY2NvdW50cy5kZXYk"
        src="https://eager-foal-99.clerk.accounts.dev/npm/@clerk/clerk-js@latest/dist/clerk.browser.js"
        type="text/javascript"
    ></script>

    <script>
        // Lắng nghe sự kiện 'load' của cửa sổ để đảm bảo DOM đã sẵn sàng
        window.addEventListener('load', async function () {
            // Tải và khởi tạo Clerk.js
            await Clerk.load();
            console.log('ClerkJS đã được tải và sẵn sàng.');

            // Lấy phần tử app để mount UI của Clerk
            const appDiv = document.getElementById('app');
            appDiv.innerHTML = ''; // Xóa nội dung "Đang tải Clerk..."

            // Kiểm tra trạng thái người dùng
            if (Clerk.user) {
                // Nếu người dùng đã đăng nhập, hiển thị nút người dùng (User Button)
                const userButtonDiv = document.createElement('div');
                userButtonDiv.id = 'user-button';
                appDiv.appendChild(userButtonDiv);
                Clerk.mountUserButton(userButtonDiv);
                console.log('Người dùng đã đăng nhập, hiển thị User Button.');
            } else {
                // Nếu người dùng chưa đăng nhập, hiển thị widget Đăng nhập (Sign In)
                const signInDiv = document.createElement('div');
                signInDiv.id = 'sign-in';
                appDiv.appendChild(signInDiv);
                Clerk.mountSignIn(signInDiv);
                console.log('Người dùng chưa đăng nhập, hiển thị Sign In widget.');
            }
        });
    </script>
</body>
</html>