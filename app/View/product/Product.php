<?php


require_once '../../../config/config.php';
require_once('../../../app/Controllers/product/ProductUserController.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';
// Kiểm tra đăng nhập
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">

<body>
    <?php renderHeader($pdo); ?>
    <div class="container"
        style="max-width: 1000px; margin: 40px auto 0 auto; background: #fff; padding: 32px 24px; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
        <h2 style="margin-bottom: 24px; font-weight: 600; color: #1a237e;">Sản phẩm của tôi</h2>
        <a href="sell.php" class="btn btn-primary btn-sell mb-3 w-100"
            style="padding: 14px 0; font-size: 1.1rem; border-radius: 10px; font-weight: 600;">
            <i class="fas fa-plus"></i> Đăng sản phẩm mới
        </a>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="products-table"
                style="border-radius: 12px; overflow: hidden; background: #fafbfc;">
                <thead style="background: #e3eafc; color: #1a237e; font-weight: 600; border-bottom: 2px solid #b6c6e6;">
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Giá</th>
                        <th>Danh mục</th>
                        <th>Tình trạng</th>
                        <th>Địa chỉ</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr id="row-<?= $product['id'] ?>"
                        data-category_id="<?= htmlspecialchars($product['category_id']) ?>"
                        data-condition_status="<?= htmlspecialchars($product['condition_status']) ?>"
                        data-location="<?= htmlspecialchars($product['location']) ?>"
                        data-description="<?= htmlspecialchars($product['description']) ?>">
                        <td>
                            <?php if (!empty($product['image_path'])): ?>
                            <img src="/WebMuaBanDoCu/public/<?php echo htmlspecialchars($product['image_path']); ?>" alt="Ảnh sản phẩm" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">                              
                            <?php else: ?>
                            <div
                                style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border-radius:8px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['title']) ?></td>
                        <td><?= number_format($product['price'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= htmlspecialchars($product['category_id']) ?></td>
                        <td><?= htmlspecialchars($product['condition_status']) ?></td>
                        <td><?= htmlspecialchars($product['location']) ?></td>
                        <td style="max-width: 180px; white-space: pre-line; overflow: hidden; text-overflow: ellipsis;">
                            <?= htmlspecialchars($product['description']) ?>
                        </td>
                        <td>
                            <span
                                class="badge bg-<?php echo ($product['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                <?= htmlspecialchars($product['status']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="#" class="delete-btn delete text-danger" data-id="<?= $product['id'] ?>"><i
                                    class="fas fa-trash"></i> Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php footer(); ?>
</body>

</html>