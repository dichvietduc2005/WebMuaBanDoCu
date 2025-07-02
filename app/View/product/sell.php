<?php
require_once '../../../config/config.php';
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


    <style>
    body {
        /* background: #f5f7fb; */
    }

    .sell-card {
        max-width: 600px;
        margin: 40px auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(58, 134, 255, 0.10);
        padding: 36px 32px 28px 32px;
    }

    .sell-title {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 8px;
        gap: 12px;
    }

    .sell-title i {
        font-size: 2.2rem;
        color: #3a86ff;
    }

    .sell-desc {
        text-align: center;
        color: #666;
        margin-bottom: 32px;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 6px;
    }

    .form-control,
    select {
        border-radius: 10px;
        font-size: 1.1rem;
        padding: 12px;
        margin-bottom: 18px;
    }

    .btn-sell {
        width: 100%;
        padding: 14px 0;
        font-size: 1.1rem;
        border-radius: 10px;
        font-weight: 600;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 18px;
        color: #3a86ff;
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <?php renderHeader($pdo); ?>
    <div class="sell-card">
        <a href="../../../public/TrangChu.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
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
                <select id="category" name="category_id" class="form-control" required>
                    <option value="">Chọn danh mục</option>
                    <option value="1">Điện thoại & Máy tính bảng</option>
                    <option value="2">Laptop & Máy tính</option>
                    <option value="3">Thời trang & Phụ kiện</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Giá bán (VNĐ)</label>
                <input type="number" id="price" name="price" class="form-control" placeholder="0" min="1000" required>
            </div>
            <div class="mb-3">
                <label for="condition" class="form-label">Tình trạng</label>
                <select id="condition" name="condition_status" class="form-control" required>
                    <option value="">Chọn tình trạng</option>
                    <option value="new">Mới</option>
                    <option value="like_new">Như mới</option>
                    <option value="good">Tốt</option>
                    <option value="fair">Khá tốt</option>
                </select>
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
    document.getElementById('images_desc').addEventListener('change', function(e) {
        const errorDiv = document.getElementById('images_desc_error');
        if (this.files.length > 3) {
            errorDiv.textContent = 'Bạn chỉ được chọn tối đa 3 ảnh mô tả!';
            this.value = '';
        } else {
            errorDiv.textContent = '';
        }
    });
    </script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('.sell-card form');
    if (!form) {
        alert('Không tìm thấy form!');
        return;
    }
    form.addEventListener('submit', function(e) {
        var desc = document.getElementById('description').value.trim();
        var date = document.getElementById('purchase_date').value;
        var attach = document.getElementById('attachments').value.trim();

        var fullDesc = desc;
        if (date) {
            fullDesc += "\nMua từ: " + date;
        }
        if (attach) {
            fullDesc += "\nSản phẩm đính kèm: " + attach;
        }

        document.getElementById('description').value = fullDesc;

    });
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php footer(); ?>
</body>

</html>