<?php

require_once(__DIR__ . '/../../../config/config.php');
require_once(__DIR__ . '/../../Controllers/sell/SellController.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_current_user_id();
    if (!$user_id) {
        echo "Bạn cần đăng nhập để đăng bán!";
        exit;
    }
    $data = [
        'title' => $_POST['title'],
        'category_id' => $_POST['category_id'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'condition_status' => $_POST['condition_status'],
        'location' => $_POST['location']
    ];
    $slug = generateUniqueSlug($pdo, $data['title']);

    $uploadDir = '../../../public/uploads/products/';
    if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    }
    $image_paths = [];
if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($tmp_name, $targetPath)) {
                // Lưu đường dẫn tương đối để dùng cho web
                $image_paths[] = 'uploads/products/' . $fileName;
            }
        }
    }
}
    
    if (addProduct($pdo, $user_id, $data, $slug)) {
        $product_id = $pdo->lastInsertId();
        foreach ($image_paths as $idx => $path) {
            $is_primary = $idx === 0 ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$product_id, $path, $is_primary]);
        }
        echo "Đăng bán thành công, chờ admin duyệt!";
    } else {
        echo "Có lỗi xảy ra, vui lòng thử lại!";
    }
}
?>