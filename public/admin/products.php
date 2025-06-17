<?php
require_once '../../config/config.php';
require_once '../../modules/admin/product/functions.php';
$_SESSION['role'] = 'admin';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit;
}

$pending_products = getPendingProducts($pdo);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm chờ duyệt</title>
    <link rel="stylesheet" href="style.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f7fb;
    }

    .container {
        max-width: 900px;
        margin: 40px auto;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px #0001;
        padding: 30px;
    }

    h2 {
        color: #3a86ff;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
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

    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #3a86ff;
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="../TrangChu.php" class="back-link">&larr; Về trang chủ</a>
        <h2>Sản phẩm chờ duyệt</h2>
        <?php if (empty($pending_products)): ?>
        <p>Không có sản phẩm nào chờ duyệt.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>Tiêu đề</th>
                <th>Người đăng</th>
                <th>Giá</th>
                <th>Ngày đăng</th>
                <th>Hành động</th>
            </tr>
            <?php foreach ($pending_products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['title']) ?></td>
                <td><?= htmlspecialchars($product['username']) ?></td>
                <td><?= number_format($product['price']) ?> VNĐ</td>
                <td><?= htmlspecialchars($product['created_at']) ?></td>
                <td class="actions">
                    <a href="../../modules/admin/product/handler.php?action=approve&id=<?= $product['id'] ?>">Duyệt</a>
                    <a href="../../modules/admin/product/handler.php?action=reject&id=<?= $product['id'] ?>">Từ chối</a>
                    <a href="../../modules/admin/product/handler.php?action=delete&id=<?= $product['id'] ?>"
                        class="delete" onclick="return confirm('Bạn chắc chắn muốn xóa?');">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</body>

</html>