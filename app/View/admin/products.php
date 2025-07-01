<?php
require_once '../../../config/config.php';

require_once '../../Controllers/product/ProductController.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';
include_once __DIR__ . '/../../Components/header/Header.php';
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

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../../public/assets/css/index.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/products_admin.css">
</head>

<body>
    <?php renderHeader($pdo); ?>
    <div class="container1">

        <h2>Sản phẩm chờ duyệt</h2>
        <?php if (empty($pending_products)): ?>
        <p>Không có sản phẩm nào chờ duyệt.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>Hình ảnh</th>
                <th>Tiêu đề</th>
                <th>Người đăng</th>
                <th>Giá</th>
                <th>Ngày đăng</th>
                <th>Hành động</th>
            </tr>
            <?php foreach ($pending_products as $product): ?>
            <tr>
                <td>
                    <?php if (!empty($product['image_path'])): ?>
                    <img src="/WebMuaBanDoCu/public/<?php echo htmlspecialchars($product['image_path']); ?>"
                        alt="Ảnh sản phẩm" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                    <div
                        style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border-radius:8px;">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($product['title']) ?></td>
                <td><?= htmlspecialchars($product['username']) ?></td>
                <td><?= number_format($product['price']) ?> VNĐ</td>
                <td><?= htmlspecialchars($product['created_at']) ?></td>
                <td class="actions">
                    <a href="../../Models/admin/AdminModel.php?action=approve&id=<?= $product['id'] ?>">Duyệt</a>
                    <a href="../../Models/admin/AdminModel.php?action=reject&id=<?= $product['id'] ?>">Từ chối</a>
                    <a href="../../Models/admin/AdminModel.php?action=delete&id=<?= $product['id'] ?>" class="delete"
                        onclick="return confirm('Bạn chắc chắn muốn xóa?');">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php footer(); ?>
</body>

</html>