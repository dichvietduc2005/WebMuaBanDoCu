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

    $main_image_path = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $fileName = uniqid() . '_' . basename($_FILES['main_image']['name']);
    $targetPath = '../../../public/uploads/products/' . $fileName;
        if (move_uploaded_file($_FILES['main_image']['tmp_name'], $targetPath)) {
        $main_image_path = 'uploads/products/' . $fileName;
        }
    }
$image_paths = [];
if (!empty($_FILES['images']['name'][0])) {
    $count = count($_FILES['images']['name']);
    if ($count > 3) {
        session_start();
        $_SESSION['sell_error'] = "Chỉ được phép tải lên tối đa 3 ảnh mô tả!";
        header("Location: ../../View/product/sell.php");
        exit;
    }
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $targetPath = '../../../public/uploads/products/' . $fileName;
            if (move_uploaded_file($tmp_name, $targetPath)) {
                $image_paths[] = 'uploads/products/' . $fileName;
            }
        }
    }
}

    $uploadDir = '../../../public/uploads/products/';
    if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    }
   
    
    if (addProduct($pdo, $user_id, $data, $slug)) {
        $product_id = $pdo->lastInsertId();
    // Lưu ảnh đại diện
    if ($main_image_path) {
        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, created_at) VALUES (?, ?, 1, NOW())");
        $stmt->execute([$product_id, $main_image_path]);
    }
    // Lưu ảnh mô tả
    foreach ($image_paths as $path) {
        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, created_at) VALUES (?, ?, 0, NOW())");
        $stmt->execute([$product_id, $path]);
    }
        echo "Đăng bán thành công, chờ admin duyệt!";
    } else {
        echo "Có lỗi xảy ra, vui lòng thử lại!";
    }
}
?>