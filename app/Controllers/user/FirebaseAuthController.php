<?php
require_once(__DIR__ . '/../../../config/config.php');
require_once(__DIR__ . '/../../../vendor/autoload.php'); // Load Composer

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;


// 1. Nhận idToken từ Frontend gửi lên
$data = json_decode(file_get_contents('php://input'), true);
$idTokenString = $data['idToken'] ?? '';

if (empty($idTokenString)) {
    echo json_encode(['success' => false, 'message' => 'Token không được để trống']);
    exit();
}

try {
    // 2. Khởi tạo Firebase Admin với file JSON đã tải ở Bước 2
    $factory = (new Factory)->withServiceAccount(__DIR__ . '/../../../config/firebase_admin_sdk.json');
    $authFirebase = $factory->createAuth();

    // 3. Xác thực mã idToken gửi từ Client
    $verifiedIdToken = $authFirebase->verifyIdToken($idTokenString);

    // 4. LẤY 3 DỮ LIỆU BẠN CẦN TỪ TOKEN ĐÃ XÁC THỰC
    $userEmail = $verifiedIdToken->claims()->get('email');      // Email của người dùng
    $fullName = $verifiedIdToken->claims()->get('name');       // Tên đầy đủ
    $googleId = $verifiedIdToken->claims()->get('sub');        // UID duy nhất của Firebase

    // --- Tiếp tục logic xử lý với Database của bạn ---
    $auth = new Auth($pdo);

    // Kiểm tra user tồn tại (tận dụng lớp Auth hiện có)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$userEmail]);
    $user = $stmt->fetch();

    if (!$user) {
        // Đăng ký mới nếu chưa có
        $randomPass = bin2hex(random_bytes(10));
        $username = explode('@', $userEmail)[0] . rand(100, 999);

        // Capture and check registration result
        $registerResult = $auth->register($username, $userEmail, $randomPass, $fullName);

        if (!$registerResult['success']) {
            error_log("Firebase user registration failed for: $userEmail - " . $registerResult['message']);
            echo json_encode(['success' => false, 'message' => 'Không thể tạo tài khoản: ' . $registerResult['message']]);
            exit();
        }

        // Re-fetch the newly created user
        $stmt->execute([$userEmail]);
        $user = $stmt->fetch();

        // Double-check user was created
        if (!$user) {
            error_log("Firebase user re-fetch failed after registration for: $userEmail");
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi tạo tài khoản. Vui lòng thử lại.']);
            exit();
        }
    }

    // Thiết lập Session đăng nhập
    if ($user && $user['status'] === 'active') {
        // Vì startUserSession là private trong Auth.php, bạn cần gọi thông qua một hàm public 
        // hoặc chuyển nó sang public trong file Auth.php
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        // ... các session khác tương tự Auth.php
        $_SESSION['success_toast'] = "Đăng nhập bằng Google thành công!";
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tài khoản đã bị khóa']);
    }

} catch (FailedToVerifyToken $e) {
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ: ' . $e->getMessage()]);
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}