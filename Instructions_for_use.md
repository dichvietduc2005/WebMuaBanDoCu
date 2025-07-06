# 📖 Hướng Dẫn Sử Dụng Web Mua Bán Đồ Cũ

## 🗃️ Cấu Hình Database

### 1. Tạo Database
```sql
-- Tạo database với tên 'muabandocu'
CREATE DATABASE muabandocu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import Database Schema
Database của dự án được lưu trong thư mục `/data/` với các file:

#### File SQL chính:
- **`database_2_done.sql`** - File database hoàn chỉnh (schema + data)
- **`database_sample_data.sql`** - Dữ liệu mẫu để test

#### Cách import:
1. **Sử dụng phpMyAdmin:**
   - Truy cập phpMyAdmin
   - Tạo database tên `muabandocu`
   - Click vào database vừa tạo
   - Chọn tab "Import"
   - Chọn file `data/database_2_done.sql`
   - Click "Go" để import

2. **Sử dụng Command Line:**
   ```bash
   # Tạo database
   mysql -u root -p -e "CREATE DATABASE muabandocu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Import database
   mysql -u root -p muabandocu < data/database_2_done.sql
   ```

### 3. Cấu Hình Kết Nối Database
File cấu hình database nằm trong `/config/config.php`:

```php
// Thông tin kết nối database
$db_host = 'localhost';        // Địa chỉ server database
$db_name = 'muabandocu';      // Tên database
$db_user = 'root';            // Username database
$db_pass = '';                // Password database
```

## 🔧 Cài Đặt Môi Trường

### 1. Yêu Cầu Hệ Thống
- **PHP:** >= 7.4
- **MySQL:** >= 5.7 hoặc MariaDB >= 10.2
- **Apache/Nginx:** Web server
- **Extension PHP cần thiết:**
  - PDO MySQL
  - GD Library (xử lý hình ảnh)
  - OpenSSL
  - Fileinfo

### 2. Cấu Hình Environment
Tạo file `.env` từ file mẫu:

```bash
# Copy file env.example thành .env
cp config/env.example config/.env
```

Chỉnh sửa file `.env`:
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=muabandocu
DB_USER=root
DB_PASS=your_password

# VNPAY Configuration (cho thanh toán)
VNPAY_TMN_CODE=your_vnpay_tmn_code
VNPAY_HASH_SECRET=your_vnpay_hash_secret
```

### 3. Cấu Hình Web Server

#### Apache (.htaccess)
```apache
RewriteEngine On

# Chuyển hướng đến public folder
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/$1 [L]

# Bảo mật - Chặn truy cập vào các file config
<Files ~ "^\.env$">
    Order allow,deny
    Deny from all
</Files>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/WebMuaBanDoCu/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

## 📁 Cấu Trúc Thư Mục

```
WebMuaBanDoCu/
├── app/                      # Mã nguồn ứng dụng
│   ├── Controllers/         # Xử lý logic
│   │   ├── auth_helper.php  # Xử lý xác thực
│   │   ├── admin/          # Controllers cho admin
│   │   ├── cart/           # Controllers cho giỏ hàng
│   │   ├── payment/        # Controllers cho thanh toán
│   │   ├── product/        # Controllers cho sản phẩm
│   │   └── user/           # Controllers cho người dùng
│   ├── Models/             # Xử lý dữ liệu
│   │   ├── user/           # Models cho người dùng
│   │   ├── product/        # Models cho sản phẩm
│   │   └── cart/           # Models cho giỏ hàng
│   ├── Views/              # Giao diện người dùng
│   │   ├── admin/          # Views cho admin
│   │   ├── product/        # Views cho sản phẩm
│   │   └── user/           # Views cho người dùng
│   ├── Components/         # Thành phần tái sử dụng
│   │   ├── header/         # Header component
│   │   ├── footer/         # Footer component
│   │   └── sidebar/        # Sidebar component
│   └── Core/               # Thư viện core
│       ├── Database.php    # Class kết nối DB
│       └── Autoloader.php  # Tự động load class
├── config/                 # Cấu hình
│   ├── config.php          # Cấu hình chính
│   ├── bootstrap.php       # Khởi động ứng dụng
│   └── env.example         # File cấu hình mẫu
├── data/                   # Dữ liệu database
│   ├── database_2_done.sql # Database hoàn chỉnh
│   └── database_sample_data.sql # Dữ liệu mẫu
├── public/                 # Thư mục public
│   ├── index.php           # Điểm vào chính
│   ├── assets/             # Tài nguyên static
│   │   ├── css/            # File CSS
│   │   ├── js/             # File JavaScript
│   │   └── images/         # Hình ảnh
│   └── uploads/            # File upload
│       └── products/       # Hình ảnh sản phẩm
├── logs/                   # File log
└── docs/                   # Tài liệu
```

## 🚀 Cách Chạy Dự Án

### 1. Cài Đặt Cục Bộ (Local)
```bash
# 1. Clone hoặc download dự án
git clone https://github.com/your-repo/WebMuaBanDoCu.git

# 2. Di chuyển vào thư mục dự án
cd WebMuaBanDoCu

# 3. Cấu hình database (tạo database và import SQL)
# Sử dụng phpMyAdmin hoặc command line như hướng dẫn ở trên

# 4. Cấu hình file .env
cp config/env.example config/.env
# Chỉnh sửa thông tin database trong file .env

# 5. Thiết lập quyền thư mục
chmod 755 public/uploads/
chmod 755 logs/

# 6. Chạy server (nếu dùng PHP built-in server)
php -S localhost:8000 -t public/
```

### 2. Truy Cập Ứng Dụng
- **Trang chủ:** `http://localhost:8000/`
- **Trang admin:** `http://localhost:8000/admin/`

### 3. Tài Khoản Mặc Định
Sau khi import database, bạn có thể sử dụng:
- **Admin:** 
  - Username: `admin`
  - Password: `password` (mã hóa trong DB)
- **User thường:**
  - Username: `nguyenvana`
  - Password: `password`

## 🔐 Bảo Mật

### 1. Cấu Hình Bảo Mật
- Thay đổi mật khẩu mặc định
- Đặt `display_errors = 0` trong production
- Sử dụng HTTPS
- Backup database định kỳ

### 2. Quyền Thư Mục
```bash
# Chỉ cho phép ghi trong thư mục uploads
chmod 755 public/uploads/
chmod 644 public/uploads/products/

# Bảo vệ file config
chmod 600 config/.env
```

## 💳 Cấu Hình Thanh Toán VNPay

### 1. Đăng Ký Tài Khoản VNPay
- Truy cập: https://vnpay.vn
- Đăng ký tài khoản merchant
- Lấy thông tin: `TMN_CODE` và `HASH_SECRET`

### 2. Cấu Hình trong .env
```env
VNPAY_TMN_CODE=your_merchant_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://your-domain.com/WebMuaBanDoCu/app/Controllers/payment/return.php
```

### 3. Test Thanh Toán
Sử dụng thẻ test VNPay:
- **Số thẻ:** 9704198526191432198
- **Tên:** NGUYEN VAN A
- **Ngày hết hạn:** 07/15
- **Mật khẩu:** 123456

## 🛠️ Troubleshooting

### 1. Lỗi Kết Nối Database
```
Database connection error: Connection refused
```
**Giải pháp:**
- Kiểm tra MySQL service đang chạy
- Xác nhận thông tin kết nối trong `config.php`
- Kiểm tra firewall

### 2. Lỗi Upload File
```
Failed to upload file
```
**Giải pháp:**
- Kiểm tra quyền thư mục `public/uploads/`
- Tăng `upload_max_filesize` trong php.ini
- Kiểm tra `post_max_size` trong php.ini

### 3. Lỗi Session
```
Session not working
```
**Giải pháp:**
- Kiểm tra `session.save_path` trong php.ini
- Đảm bảo thư mục session có quyền ghi
- Xóa cache browser

## 📊 Quản Lý Dữ Liệu

### 1. Backup Database
```bash
# Backup database
mysqldump -u root -p muabandocu > backup_$(date +%Y%m%d).sql

# Restore database
mysql -u root -p muabandocu < backup_20250706.sql
```

### 2. Logs
- **Payment logs:** `/logs/payment_debug.log`
- **Error logs:** PHP error log
- **Access logs:** Web server access log

### 3. Maintenance
```bash
# Optimize database
mysql -u root -p -e "OPTIMIZE TABLE muabandocu.products, muabandocu.orders;"

# Clean old sessions
find /tmp -name "sess_*" -type f -mtime +1 -delete
```

## 🎯 Tính Năng Chính

### 1. Quản Lý Sản Phẩm
- Đăng bán sản phẩm với nhiều hình ảnh
- Phân loại theo 24 danh mục
- Quản lý trạng thái (pending/active/sold/rejected)
- Tìm kiếm và lọc sản phẩm

### 2. Hệ Thống Giỏ Hàng
- Thêm/xóa sản phẩm (AJAX)
- Cập nhật số lượng
- Checkout và thanh toán

### 3. Quản Trị Viên
- Dashboard thống kê
- Duyệt sản phẩm
- Quản lý người dùng
- Quản lý đơn hàng

## 📞 Hỗ Trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra file log trong `/logs/`
2. Xem lại cấu hình trong `/config/`
3. Đọc documentation trong `/docs/`
4. Liên hệ developer

---

**Chúc bạn sử dụng thành công! 🎉**
