<?php
require_once '../config/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: user/login.php');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fb;
            color: #333;
        }
        .container {
            max-width: 800px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        textarea {
            height: 120px;
            resize: vertical;
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
        }
        .btn:hover {
            background: #2667cc;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="TrangChu.php" class="back-link"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        
        <div class="header">
            <h1><i class="fas fa-store"></i> Đăng bán sản phẩm</h1>
            <p>Đăng bán đồ cũ của bạn một cách dễ dàng</p>
        </div>
               
        <form method="POST" action="../modules/sell/handler.php" >
            <div class="form-group">
                <label for="title">Tiêu đề sản phẩm</label>
                <input type="text" id="title" name="title" placeholder="Nhập tiêu đề sản phẩm..." required>
            </div>
            
            <div class="form-group">
                <label for="category">Danh mục</label>
                <select id="category" name="category_id" required>
                    <option value="">Chọn danh mục</option>
                    <option value="1">Điện thoại & Máy tính bảng</option>
                    <option value="2">Laptop & Máy tính</option>
                    <option value="3">Thời trang & Phụ kiện</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Giá bán (VNĐ)</label>
                <input type="number" id="price" name="price" placeholder="0" min="1000" required>
            </div>
            
            <div class="form-group">
                <label for="condition">Tình trạng</label>
                <select id="condition" name="condition_status" required>
                    <option value="">Chọn tình trạng</option>
                    <option value="new">Mới</option>
                    <option value="like_new">Như mới</option>
                    <option value="good">Tốt</option>
                    <option value="fair">Khá tốt</option>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Địa Chỉ</label>
                <input type="text" id="location" name="location">
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả sản phẩm</label>
                <textarea id="description" name="description" placeholder="Mô tả chi tiết về sản phẩm..." required></textarea>
            </div>
            
            <div class="form-group">
                <label for="images">Hình ảnh sản phẩm</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-upload"></i> Đăng bán sản phẩm
            </button>
        </form>
    </div>
</body>
</html>
