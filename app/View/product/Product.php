<?php

require_once '../../../config/config.php';
require_once('../../../app/Controllers/product/ProductUserController.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: user/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$products = getUserProducts($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm của tôi - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/Product.css">


</head>

<body>
    <div class="container">
        <a href="../Home.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        <div class="header">
            <h1><i class="fas fa-box"></i> Sản phẩm của tôi</h1>
            <p>Quản lý các sản phẩm bạn đã đăng bán</p>
        </div>
        <a href="sell.php" class="btn"><i class="fas fa-plus"></i> Đăng sản phẩm mới</a>
        <table id="products-table">
            <tr>
                <th>Tiêu đề</th>
                <th>Giá</th>
                <th>Danh mục</th>
                <th>Tình trạng</th>
                <th>Địa chỉ</th>
                <th>Mô tả</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
            <?php foreach ($products as $product): ?>
            <tr id="row-<?= $product['id'] ?>" data-category_id="<?= htmlspecialchars($product['category_id']) ?>"
                data-condition_status="<?= htmlspecialchars($product['condition_status']) ?>"
                data-location="<?= htmlspecialchars($product['location']) ?>"
                data-description="<?= htmlspecialchars($product['description']) ?>">
                <td class="title"><?= htmlspecialchars($product['title']) ?></td>
                <td class="price"><?= number_format($product['price']) ?> VNĐ</td>
                <td class="category"><?= htmlspecialchars($product['category_id']) ?></td>
                <td class="condition"><?= htmlspecialchars($product['condition_status']) ?></td>
                <td class="location"><?= htmlspecialchars($product['location']) ?></td>
                <td class="description"><?= htmlspecialchars($product['description']) ?></td>
                <td class="status"><?= htmlspecialchars($product['status']) ?></td>
                <td class="actions">
                    <a href="#" class="edit-btn" data-id="<?= $product['id'] ?>"><i class="fas fa-edit"></i> Sửa</a>
                    <a href="#" class="delete-btn delete" data-id="<?= $product['id'] ?>"><i class="fas fa-trash"></i>
                        Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Modal sửa sản phẩm -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Sửa sản phẩm</h2>
            <form id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Tiêu đề</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id" id="edit_category_id" required>
                        <option value="1">Điện thoại & Máy tính bảng</option>
                        <option value="2">Laptop & Máy tính</option>
                        <option value="3">Thời trang & Phụ kiện</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Giá</label>
                    <input type="number" name="price" id="edit_price" required>
                </div>
                <div class="form-group">
                    <label>Tình trạng</label>
                    <select name="condition_status" id="edit_condition_status" required>
                        <option value="new">Mới</option>
                        <option value="like_new">Như mới</option>
                        <option value="good">Tốt</option>
                        <option value="fair">Khá tốt</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="location" id="edit_location">
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" id="edit_description" required></textarea>
                </div>
                <button type="submit" class="btn"><i class="fas fa-save"></i> Lưu</button>
            </form>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <script src="../../../public/assets/js/my_products.js"></script>

</html>