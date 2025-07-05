<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/product/CategoryModel.php';
require_once __DIR__ . '/../../Models/product/StatusModel.php';
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /WebMuaBanDoCu/app/View/user/login.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng bán sản phẩm - Web Mua Bán Đồ Cũ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/user_box_chat.css?v=1.2">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/index.css">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/sell.css">
</head>

<body>
    <?php renderHeader($pdo); ?>
    <div class="sell-card">
        <div class="sell-title">
            <i class="fas fa-store"></i> Đăng bán sản phẩm
        </div>
        <div class="sell-desc">
            Đăng bán đồ cũ của bạn một cách dễ dàng
        </div>
        <form method="POST" action="../../Models/sell/SellModel.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Tiêu đề sản phẩm</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="Nhập tiêu đề sản phẩm..."
                    required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Danh mục</label>
                <div class="custom-select-wrapper">
                    <select id="category" name="category_id" class="form-control" required>
                        <option value="">Chọn danh mục</option>
                        <?php
                        // Lấy danh sách danh mục
                        $categories = fetchAllCategories($pdo);
                        foreach ($categories as $cat) {
                            echo '<option value="' . (int)$cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Giá bán (VNĐ)</label>
                <input type="number" id="price" name="price" class="form-control" placeholder="0" min="1000" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Tình trạng</label>
                <div class="custom-select-wrapper">
                    <select id="condition" name="condition_status" class="form-select condition-select" required>
                        <option value="">Chọn tình trạng</option>
                        <?php
                        $conditions = fetchConditionStatuses($pdo);
                        foreach ($conditions as $cond) {
                            // Hiển thị label tiếng Việt tương ứng
                            $labelMap = [
                                'new' => 'Mới',
                                'like_new' => 'Như mới',
                                'good' => 'Tốt',
                                'fair' => 'Khá tốt',
                                'poor' => 'Cũ'
                            ];
                            $label = $labelMap[$cond] ?? ucfirst($cond);
                            echo '<option value="' . htmlspecialchars($cond) . '">' . htmlspecialchars($label) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Địa Chỉ</label>
                <input type="text" id="location" name="location" class="form-control">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả sản phẩm</label>
                <textarea id="description" name="description" class="form-control"
                    placeholder="Mô tả chi tiết về sản phẩm..." required></textarea>
            </div>
            <div class="mb-3">
                <label for="purchase_date" class="form-label">Mua từ bao giờ <span style="color:red">*</span></label>
                <input type="date" id="purchase_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="attachments" class="form-label">Sản phẩm đính kèm <span style="color:red">*</span></label>
                <input type="text" id="attachments" class="form-control"
                    placeholder="Ví dụ: Sạc, hộp, giấy tờ,không có..." required>
            </div>
            <div class="mb-3">
                <label for="main_image" class="form-label">Hình ảnh đại diện sản phẩm</label>
                <input type="file" id="main_image" name="main_image" class="form-control" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label for="images_desc" class="form-label">Thêm ảnh mô tả (tối đa 3 ảnh)</label>
                <input type="file" id="images_desc" name="images[]" class="form-control" accept="image/*" multiple>
                <div id="images_desc_error" class="text-danger mt-1" style="font-size: 14px;"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sell">
                <i class="fas fa-upload"></i> Đăng bán sản phẩm
            </button>
        </form>
    </div>
    <script>
        userId = <?php echo $_SESSION['user_id'] ?>
    </script>
    <script src="/WebMuaBanDoCu/public/assets/js/main.js"> </script>

    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"> </script>
    <script src="/WebMuaBanDoCu/public/assets/js/sell.js"> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php footer(); ?>
</body>

</html>