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

    if (addProduct($pdo, $user_id, $data, $slug)) {
        echo "Đăng bán thành công, chờ admin duyệt!";
    } else {
        echo "Có lỗi xảy ra, vui lòng thử lại!";
    }
}
?>