<?php
require_once '../../../config/config.php';

require_once '../../Controllers/admin/AdminController.php';
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /WebMuaBanDoCu/app/View/user/login.php');
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
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/user_box_chat.css?v=1.2">
</head>

<body>
    <?php renderHeader($pdo); ?>
     <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090"></div>
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
                <th>Tình trạng</th>
                <th>Mô Tả</th>
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
                <td><?= htmlspecialchars($product['condition_status']) ?></td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td><?= htmlspecialchars($product['created_at']) ?></td>
                <td class="actions">
    <a href="../../Models/admin/AdminModel.php?action=approve&id=<?= $product['id'] ?>" 
       class="btn btn-success action-btn">
       <i class="fas fa-check"></i> Duyệt
    </a>
       
    <a href="../../Models/admin/AdminModel.php?action=reject&id=<?= $product['id'] ?>" 
       class="btn btn-warning action-btn">
       <i class="fas fa-times"></i> Từ chối
    </a>
       
    <a href="../../Models/admin/AdminModel.php?action=delete&id=<?= $product['id'] ?>" 
       class="btn btn-danger action-btn delete">
       <i class="fas fa-trash"></i> Xóa
    </a>
</td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php footer(); ?>
    <script>userId = <?php echo $_SESSION['user_id'] ?> </script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js?v=3"> </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script> 
<script src="<?php echo BASE_URL; ?>public/assets/js/admin_Product.js"></script>
    
</body>


</html>