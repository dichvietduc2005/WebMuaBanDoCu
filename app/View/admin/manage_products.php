<?php
require_once '../../../config/config.php';

require_once '../../Controllers/admin/AdminController.php';
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /WebMuaBanDoCu/app/View/user/login.php');
    exit;
}

$all_products = getAllProducts($pdo);
$featured_products = array_filter($all_products, function($p) { return $p['featured']; });
$regular_products = array_filter($all_products, function($p) { return !$p['featured']; });
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Quản lý tất cả sản phẩm</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../../public/assets/css/index.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/products_admin.css">
    <style>
        .featured-badge {
            background: linear-gradient(45deg, #ff6b6b, #ffa500);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        .featured-row {
            background: #fffbf0 !important;
            border-left: 4px solid #ffa500;
        }
    </style>
</head>

<body>
    <?php renderHeader($pdo); ?>
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090"></div>
    <div class="container1">

        <h2><i class="fas fa-cogs"></i> Quản lý tất cả sản phẩm</h2>
        <p class="text-muted mb-4">Quản lý trạng thái nổi bật và các thuộc tính khác của sản phẩm</p>

        <?php if (empty($all_products)): ?>
            <p>Không có sản phẩm nào.</p>
        <?php else: ?>

        <!-- BẢNG SẢN PHẨM NỔI BẬT -->
        <h4 class="mt-4"><i class="fas fa-star"></i> Sản phẩm nổi bật</h4>
        <table id="featured-table">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Người đăng</th>
                    <th>Giá</th>
                    <th>Tình trạng</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($featured_products as $product): ?>
                <tr data-product-id="<?= $product['id'] ?>" class="featured-row">
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
                    <td>
                        <?= htmlspecialchars($product['title']) ?>
                        <br><span class="featured-badge"><i class="fas fa-star"></i> Nổi bật</span>
                    </td>
                    <td><?= htmlspecialchars($product['username']) ?></td>
                    <td><?= number_format($product['price']) ?> VNĐ</td>
                    <td><?= htmlspecialchars($product['condition_status']) ?></td>
                    <td>
                        <span class="badge bg-success">
                            <?= htmlspecialchars($product['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($product['created_at']) ?></td>
                    <td class="actions">
                        <a href="../../Models/admin/AdminModelAPI.php?action=toggle_featured&id=<?= $product['id'] ?>" 
                           class="btn btn-warning action-btn">
                           <i class="fas fa-star-half-alt"></i> Bỏ nổi bật
                        </a>
                        <a href="../../Models/admin/AdminModelAPI.php?action=delete&id=<?= $product['id'] ?>" 
                           class="btn btn-danger action-btn delete">
                           <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- BẢNG SẢN PHẨM THƯỜNG -->
        <h4 class="mt-5"><i class="fas fa-list"></i> Sản phẩm khác</h4>
        <table id="regular-table">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Người đăng</th>
                    <th>Giá</th>
                    <th>Tình trạng</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($regular_products as $product): ?>
                <tr data-product-id="<?= $product['id'] ?>">
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
                    <td>
                        <?= htmlspecialchars($product['title']) ?>
                    </td>
                    <td><?= htmlspecialchars($product['username']) ?></td>
                    <td><?= number_format($product['price']) ?> VNĐ</td>
                    <td><?= htmlspecialchars($product['condition_status']) ?></td>
                    <td>
                        <span class="badge bg-success">
                            <?= htmlspecialchars($product['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($product['created_at']) ?></td>
                    <td class="actions">
                        <a href="../../Models/admin/AdminModelAPI.php?action=toggle_featured&id=<?= $product['id'] ?>" 
                           class="btn btn-info action-btn">
                           <i class="fas fa-star"></i> Đặt nổi bật
                        </a>
                        <a href="../../Models/admin/AdminModelAPI.php?action=delete&id=<?= $product['id'] ?>" 
                           class="btn btn-danger action-btn delete">
                           <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
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
