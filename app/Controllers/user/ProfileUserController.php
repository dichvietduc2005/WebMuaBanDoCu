<?php
require_once '../../../config/config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo "Thiếu User ID";
    exit;
}

// 1. Handle Text Data
$username = $_POST['user_name'] ?? '';
$full_name = $_POST['user_full_name'] ?? '';
$email = $_POST['user_email'] ?? '';
$phone = $_POST['user_phone'] ?? '';
$address = $_POST['user_address'] ?? '';

// 2. Handle Avatar Upload
$avatarPath = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../../public/assets/uploads/avatars/';

    // Create directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileTmpPath = $_FILES['avatar']['tmp_name'];
    $fileName = $_FILES['avatar']['name'];
    $fileSize = $_FILES['avatar']['size'];
    $fileType = $_FILES['avatar']['type'];

    // Generate unique name
    $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
    $dest_path = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        // DB Path (Relative for frontend)
        $avatarPath = BASE_URL . 'public/assets/uploads/avatars/' . $newFileName;
    } else {
        echo "Lỗi upload ảnh";
        exit;
    }
}

// 3. Prepare SQL
$sql = "UPDATE users SET 
    username = ?, 
    full_name = ?, 
    email = ?, 
    phone = ?, 
    address = ?";

$params = [$username, $full_name, $email, $phone, $address];

// If avatar was uploaded, update that column too
if ($avatarPath) {
    $sql .= ", avatar = ?";
    $params[] = $avatarPath;
}

$sql .= " WHERE id = ?";
$params[] = $user_id;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 4. Update Session
    $_SESSION['user_name'] = $username; // Although usually username is fixed
    $_SESSION['user_full_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_phone'] = $phone;
    $_SESSION['user_address'] = $address;

    if ($avatarPath) {
        $_SESSION['user_avatar'] = $avatarPath;
    }

    echo "success";
} catch (PDOException $e) {
    echo "Lỗi database: " . $e->getMessage();
}
?>