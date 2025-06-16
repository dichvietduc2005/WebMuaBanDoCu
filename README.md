# Hướng dẫn thiết lập Web Mua Bán Đồ Cũ

## ⚠️ GIẢI QUYẾT LỖI "Column not found: quantity"

Lỗi này xảy ra do vấn đề về thứ tự tạo bảng và foreign key. Để tránh lỗi, hãy import từng bước:

## 1. Import Database (Phương pháp an toàn)

### Bước 1: Import cấu trúc bảng
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Tạo database mới tên `muabandocu` (nếu chưa có)
3. Chọn database `muabandocu`
4. Import file: **`database_structure_only.sql`** trước
5. Kiểm tra tất cả bảng đã được tạo thành công

### Bước 2: Import dữ liệu mẫu
1. Sau khi import cấu trúc thành công
2. Import file: **`database_sample_data.sql`**
3. Kiểm tra dữ liệu đã được thêm

### Phương pháp thay thế: MySQL command line
```bash
mysql -u root -p
CREATE DATABASE IF NOT EXISTS muabandocu;
USE muabandocu;

# Import cấu trúc trước
SOURCE C:/wamp64/www/WebMuaBanDoCu/database_structure_only.sql;

# Import dữ liệu sau
SOURCE C:/wamp64/www/WebMuaBanDoCu/database_sample_data.sql;
```

## 2. Thiết lập thư mục uploads
1. Mở trình duyệt, truy cập: `http://localhost/WebMuaBanDoCu/public/setup.php`
2. File sẽ tự động tạo thư mục và hình ảnh placeholder

## 3. Kiểm tra hoạt động
1. Truy cập: `http://localhost/WebMuaBanDoCu/public/test_db.php` để kiểm tra database
2. Truy cập: `http://localhost/WebMuaBanDoCu/public/TrangChu.php` để xem trang chủ

## 4. Cấu trúc database đã được sửa

### So sánh với code:
- ✅ Bảng `users` với đầy đủ trường cần thiết
- ✅ Bảng `categories` với slug và description  
- ✅ Bảng `products` với các trường: `featured`, `views`, `stock_quantity`
- ✅ Bảng `product_images` với `is_primary` để xác định ảnh đại diện
- ✅ Bảng `carts` và `cart_items` hỗ trợ cả user và guest
- ✅ Bảng `orders` với đầy đủ status: `pending`, `success`, `failed`, `cancelled`
- ✅ Bảng `order_items` với thông tin chi tiết đơn hàng

### Dữ liệu mẫu:
- ✅ 6 users (1 admin + 5 users thường)
- ✅ 9 categories đa dạng
- ✅ 10 products với 6 sản phẩm featured
- ✅ 6 product images
- ✅ 7 carts với cart_items
- ✅ 8 orders với order_items tương ứng

## 5. Tính năng đã hoạt động:
- ✅ Hiển thị sản phẩm nổi bật trên trang chủ
- ✅ Thêm sản phẩm vào giỏ hàng (AJAX)
- ✅ Đếm số lượng giỏ hàng
- ✅ Hiển thị đơn hàng gần đây (nếu đã đăng nhập)
- ✅ Navigation menu với đường dẫn đúng
- ✅ Toast notification khi thêm vào giỏ hàng
- ✅ Responsive design

## 6. Các trang đã tạo:
- ✅ `TrangChu.php` - Trang chủ chính
- ✅ `products.php` - Danh sách sản phẩm  
- ✅ `categories.php` - Danh sách danh mục
- ✅ `search.php` - Tìm kiếm sản phẩm
- ✅ `sell.php` - Đăng bán (placeholder)
- ✅ `test_db.php` - Kiểm tra database
- ✅ `setup.php` - Thiết lập uploads

## 7. Lưu ý quan trọng:
- Mật khẩu mặc định cho tất cả user: `password`
- Admin account: `admin` / `password`
- Database name: `muabandocu`
- Upload folder: `uploads/products/`
