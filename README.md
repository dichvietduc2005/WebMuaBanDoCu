# 🛒 Web Mua Bán Đồ Cũ - Hệ thống E-commerce PHP

Một hệ thống mua bán đồ cũ trực tuyến được xây dựng bằng PHP thuần, MySQL, Bootstrap và jQuery. Hệ thống hỗ trợ đăng ký/đăng nhập, quản lý sản phẩm, giỏ hàng, thanh toán VNPay và quản trị viên.

## 📋 Tính năng chính

### 🔐 **Xác thực người dùng**
- Đăng ký tài khoản với xác thực email
- Đăng nhập với Remember Me
- Quên mật khẩu và đặt lại
- Phân quyền người dùng (user/admin)
- Bảo mật CSRF token

### 🛍️ **Quản lý sản phẩm**
- Đăng bán sản phẩm với hình ảnh
- Phân loại theo danh mục
- Tìm kiếm và lọc sản phẩm
- Quản lý trạng thái (pending/active/sold)
- Hệ thống đánh giá tình trạng sản phẩm

### 🛒 **Giỏ hàng & Đặt hàng**
- Thêm sản phẩm vào giỏ hàng
- Cập nhật số lượng, xóa sản phẩm
- Checkout với thông tin giao hàng
- Lịch sử đơn hàng chi tiết

### 💳 **Thanh toán**
- Tích hợp VNPay Gateway
- Thanh toán trực tuyến an toàn
- Xử lý callback và IPN
- Hoàn tiền tự động

### 👨‍💼 **Quản trị viên**
- Duyệt sản phẩm chờ
- Quản lý người dùng
- Thống kê đơn hàng
- Quản lý danh mục

## 🗃️ Cấu trúc Database

### 👥 **Bảng `users`** - Quản lý người dùng
```sql
- id (PK): ID người dùng
- full_name: Họ tên đầy đủ
- email: Email đăng nhập (UNIQUE)
- password: Mật khẩu đã mã hóa
- phone: Số điện thoại
- address: Địa chỉ
- city: Thành phố
- role: Vai trò (user/admin)
- email_verified: Trạng thái xác thực email
- created_at, updated_at: Thời gian tạo/cập nhật
```

### 🏷️ **Bảng `categories`** - Danh mục sản phẩm
```sql
- id (PK): ID danh mục
- name: Tên danh mục
- slug: Đường dẫn thân thiện SEO
- description: Mô tả danh mục
- created_at: Thời gian tạo
```

### 📦 **Bảng `products`** - Sản phẩm
```sql
- id (PK): ID sản phẩm
- seller_id (FK): ID người bán
- category_id (FK): ID danh mục
- title: Tiêu đề sản phẩm
- slug: Đường dẫn SEO
- description: Mô tả chi tiết
- price: Giá bán
- condition_status: Tình trạng (new/like_new/good/fair/poor)
- location: Địa điểm
- stock_quantity: Số lượng tồn kho
- status: Trạng thái (pending/active/sold/inactive)
- featured: Sản phẩm nổi bật
- views: Lượt xem
- created_at, updated_at: Thời gian tạo/cập nhật
```

### 🖼️ **Bảng `product_images`** - Hình ảnh sản phẩm
```sql
- id (PK): ID hình ảnh
- product_id (FK): ID sản phẩm
- image_path: Đường dẫn file
- is_primary: Ảnh chính (1/0)
- uploaded_at: Thời gian upload
```

### 🛒 **Bảng `carts`** - Giỏ hàng chính
```sql
- id (PK): ID giỏ hàng
- user_id (FK): ID người dùng
- created_at, updated_at: Thời gian tạo/cập nhật
```

### 📝 **Bảng `cart_items`** - Chi tiết giỏ hàng
```sql
- id (PK): ID item
- cart_id (FK): ID giỏ hàng
- product_id (FK): ID sản phẩm
- quantity: Số lượng
- added_price: Giá khi thêm vào giỏ
- condition_snapshot: Tình trạng khi thêm
- added_at, updated_at: Thời gian thêm/cập nhật
```

### 📋 **Bảng `orders`** - Đơn hàng
```sql
- id (PK): ID đơn hàng
- buyer_id (FK): ID người mua
- order_number: Mã đơn hàng (UNIQUE)
- total_amount: Tổng tiền
- status: Trạng thái (pending/confirmed/shipping/delivered/cancelled)
- payment_method: Phương thức thanh toán
- payment_status: Trạng thái thanh toán
- billing_info: Thông tin thanh toán (JSON)
- shipping_info: Thông tin giao hàng (JSON)
- notes: Ghi chú
- created_at, updated_at: Thời gian tạo/cập nhật
```

### 🛍️ **Bảng `order_items`** - Chi tiết đơn hàng
```sql
- id (PK): ID item đơn hàng
- order_id (FK): ID đơn hàng
- product_id (FK): ID sản phẩm
- product_name: Tên sản phẩm (snapshot)
- price: Giá tại thời điểm mua
- quantity: Số lượng
- subtotal: Thành tiền
```

### 🔐 **Bảng `remember_tokens`** - Token Remember Me
```sql
- id (PK): ID token
- user_id (FK): ID người dùng
- token: Token mã hóa
- created_at: Thời gian tạo
```

### 📊 **Bảng `user_activities`** - Nhật ký hoạt động
```sql
- id (PK): ID hoạt động
- user_id (FK): ID người dùng
- action: Hành động thực hiện
- description: Mô tả chi tiết
- ip_address: Địa chỉ IP
- user_agent: Thông tin trình duyệt
- created_at: Thời gian thực hiện
```

## 🏗️ Kiến trúc hệ thống

### 📁 **Cấu trúc thư mục**
```
WebMuaBanDoCu/
├── app/
│   ├── Controllers/        # Logic xử lý nghiệp vụ
│   │   ├── auth_helper.php # Hỗ trợ xác thực
│   │   ├── cart/          # Xử lý giỏ hàng
│   │   ├── order/         # Xử lý đơn hàng
│   │   ├── payment/       # Xử lý thanh toán
│   │   ├── product/       # Xử lý sản phẩm
│   │   └── user/          # Xử lý người dùng
│   ├── Models/            # Tương tác database
│   │   ├── cart/          # Models giỏ hàng
│   │   ├── order/         # Models đơn hàng
│   │   ├── product/       # Models sản phẩm
│   │   └── user/          # Models người dùng
│   ├── View/              # Giao diện người dùng
│   │   ├── admin/         # Trang quản trị
│   │   ├── cart/          # Trang giỏ hàng
│   │   ├── checkout/      # Trang thanh toán
│   │   ├── order/         # Trang đơn hàng
│   │   ├── product/       # Trang sản phẩm
│   │   └── user/          # Trang người dùng
│   ├── Components/        # Component tái sử dụng
│   └── helpers.php        # Hàm hỗ trợ chung
├── config/
│   └── config.php         # Cấu hình database, VNPay
├── public/
│   ├── assets/           # CSS, JS, hình ảnh
│   ├── uploads/          # File upload
│   └── TrangChu.php      # Trang chủ
├── modules/              # Modules xử lý AJAX
│   ├── cart/            # AJAX giỏ hàng
│   └── payment/         # AJAX thanh toán
└── data/
    └── database_complete_fixed.sql # Database schema
```

### 🔄 **Luồng hoạt động chính**

#### 1. **Đăng ký/Đăng nhập**
```
User → View/user/login.php → Models/user/Auth.php → Database
     ← JSON response/redirect ← Controllers/auth_helper.php ←
```

#### 2. **Duyệt sản phẩm**
```
User → View/Home.php → Controllers/extra/ExtraController.php → Database
     ← HTML render ← Models/product/ProductModel.php ←
```

#### 3. **Thêm vào giỏ hàng**
```
User → JavaScript AJAX → modules/cart/handler.php → Controllers/cart/CartController.php
     ← JSON response ← helpers.php ← Models/cart/CartModel.php ← Database
```

#### 4. **Checkout & Thanh toán**
```
User → View/checkout/index.php → modules/payment/vnpay/create_payment.php
     ← Redirect to VNPay ← Controllers/payment/create_payment.php ← Database
     
VNPay callback → Controllers/payment/return.php → Update order status → Database
```

#### 5. **Quản lý đơn hàng**
```
User → View/order/order_history.php → Controllers/order/OrderController.php
     ← Order list HTML ← Models/order/ReOrder.php ← Database
```

### 🔧 **Công nghệ sử dụng**

- **Backend**: PHP 7.4+, MySQL 8.0+
- **Frontend**: HTML5, CSS3, Bootstrap 4.6, jQuery 1.11.3
- **Payment**: VNPay Gateway Integration
- **Security**: CSRF Protection, Password Hashing, SQL Injection Prevention
- **Architecture**: MVC Pattern với Custom Router

## 🚀 Hướng dẫn cài đặt

### 1. **Cài đặt Database**
```bash
# Tạo database
mysql -u root -p
CREATE DATABASE muabandocu;
USE muabandocu;

# Import schema
SOURCE /path/to/WebMuaBanDoCu/data/database_complete_fixed.sql;
```

### 2. **Cấu hình môi trường**
```php
// Chỉnh sửa config/config.php
$host = 'localhost';
$dbname = 'muabandocu';
$username = 'root';
$password = '';

// VNPay credentials
$vnp_TmnCode = "YOUR_TMN_CODE";
$vnp_HashSecret = "YOUR_HASH_SECRET";
```

### 3. **Tạo thư mục uploads**
```bash
mkdir -p public/uploads/products
chmod 755 public/uploads/products
```

### 4. **Kiểm tra hoạt động**
- Truy cập: `http://localhost/WebMuaBanDoCu/public/TrangChu.php`
- Đăng ký tài khoản mới
- Thử các chức năng: duyệt sản phẩm, giỏ hàng, thanh toán

## 🔒 Bảo mật

- ✅ SQL Injection prevention với PDO prepared statements
- ✅ XSS protection với htmlspecialchars()
- ✅ CSRF token validation
- ✅ Password hashing với password_hash()
- ✅ Session security với secure flags
- ✅ File upload validation
- ✅ Input sanitization và validation

## 🐛 Troubleshooting

### Lỗi "Call to a member function prepare() on int"
**Nguyên nhân**: Biến `$pdo` không được truyền đúng vào functions
**Giải pháp**: Đã sửa trong `app/helpers.php` và các Controllers

### Lỗi "Cannot redeclare function"
**Nguyên nhân**: Function được định nghĩa nhiều lần
**Giải pháp**: Sử dụng `if (!function_exists())` wrapper

### Lỗi đường dẫn CSS/JS
**Nguyên nhân**: Relative path không đúng khi di chuyển files
**Giải pháp**: Đã chuẩn hóa paths trong tất cả View files
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
