<?php

require_once '../config/config.php';
require_once('../modules/my_products/functions.php');

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
    <link rel="stylesheet" href="../assets/css/index.css">
    <!-- <style>
    body {
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f5f7fb;
        color: #333;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #3a86ff;
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .btn {
        background: #3a86ff;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s;
        margin-bottom: 20px;
    }

    .btn:hover {
        background: #2667cc;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th,
    td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        text-align: left;
    }

    th {
        background: #3a86ff;
        color: #fff;
    }

    tr:hover {
        background: #f0f8ff;
    }

    .actions a {
        margin-right: 10px;
        text-decoration: none;
        color: #3a86ff;
        font-weight: bold;
    }

    .actions a.delete {
        color: #e63946;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: rgba(0, 0, 0, 0.3);
    }

    .modal-content {
        background: #fff;
        margin: 5% auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        position: relative;
    }

    .close {
        position: absolute;
        right: 20px;
        top: 15px;
        font-size: 28px;
        color: #888;
        cursor: pointer;
    }

    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #3a86ff;
        color: #fff;
        padding: 14px 24px;
        border-radius: 8px;
        display: none;
        z-index: 2000;
    }
    </style> -->
</head>

<body>
    <div class="container">
        <a href="TrangChu.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        <div class="header">
            <h1><i class="fas fa-box"></i> Sản phẩm của tôi</h1>
            <p>Quản lý các sản phẩm bạn đã đăng bán</p>
        </div>
        <a href="sell.php" class="btn"><i class="fas fa-plus"></i> Đăng sản phẩm mới</a>
        <table id="products-table">
            <tr>
                <th>Tiêu đề</th>
                <th>Giá</th>
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
    <script src="../assets/js/my_products.js"></script>
</body>

</html>