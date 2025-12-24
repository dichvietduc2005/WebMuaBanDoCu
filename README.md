# 🛒 Web Mua Bán Đồ Cũ - Hệ thống E-commerce PHP

Một hệ thống mua bán đồ cũ trực tuyến được xây dựng bằng PHP thuần, MySQL, Bootstrap và jQuery. Hệ thống hỗ trợ đăng ký/đăng nhập, quản lý sản phẩm, giỏ hàng, thanh toán VNPay và quản trị viên với kiến trúc MVC tự build.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)

## 📋 Tính năng đã triển khai

### 🔐 **Xác thực người dùng**
- ✅ Đăng ký tài khoản với validation đầy đủ
- ✅ Đăng nhập với Remember Me token
- ✅ Quên mật khẩu và đặt lại (email reset)
- ✅ Phân quyền người dùng (user/admin)
- ✅ Bảo mật với CSRF token và password hashing
- ✅ Session management an toàn

### 🛍️ **Quản lý sản phẩm**
- ✅ Đăng bán sản phẩm với upload nhiều hình ảnh
- ✅ Phân loại theo 9 danh mục đa dạng
- ✅ Tìm kiếm theo tên, mô tả và danh mục
- ✅ Quản lý trạng thái (pending/active/sold/reject)
- ✅ Hệ thống đánh giá tình trạng 5 cấp độ
- ✅ Sản phẩm nổi bật và view counter
- ✅ Slug SEO-friendly cho URL

### 🛒 **Giỏ hàng & Đặt hàng**
- ✅ Thêm sản phẩm vào giỏ hàng (AJAX)
- ✅ Cập nhật số lượng, xóa sản phẩm real-time
- ✅ Checkout với thông tin giao hàng đầy đủ
- ✅ Lịch sử đơn hàng chi tiết với phân trang
- ✅ Hủy đơn hàng và đặt lại (Re-order)
- ✅ Tính toán tổng tiền chính xác

### 💳 **Thanh toán VNPay**
- ✅ Tích hợp VNPay Gateway sandbox
- ✅ Thanh toán trực tuyến an toàn
- ✅ Xử lý callback và return URL
- ✅ Cập nhật trạng thái đơn hàng tự động
- ✅ Logging debug cho payment flows
- ✅ Validation và security hash

### 👨‍💼 **Quản trị viên**
- ✅ Dashboard admin riêng biệt
- ✅ Duyệt sản phẩm chờ phê duyệt
- ✅ Quản lý người dùng và phân quyền
- ✅ Thống kê đơn hàng theo trạng thái
- ✅ Quản lý danh mục sản phẩm

## 🗃️ Cấu trúc Database

### 👥 **Bảng `users`** - Quản lý người dùng (6 records)
```sql
id, username, email, password, full_name, phone, address, city, role, 
email_verified, avatar, last_login, created_at, updated_at
```
- **Admin account**: `admin@example.com` / `password`
- **Test users**: `user1@example.com` đến `user5@example.com` / `password`

### 🏷️ **Bảng `categories`** - Danh mục sản phẩm (9 categories)
```sql
id, name, slug, description, created_at
```
- Điện thoại & Máy tính bảng, Laptop & Máy tính, Thời trang & Phụ kiện
- Đồ gia dụng & Nội thất, Xe cộ & Phương tiện, Sách & Văn phòng phẩm
- Đồ chơi & Thể thao, Mỹ phẩm & Sức khỏe, Khác

### 📦 **Bảng `products`** - Sản phẩm (10 products)
```sql
id, seller_id, category_id, title, slug, description, price, condition_status,
location, stock_quantity, status, featured, views, created_at, updated_at
```
- **Condition levels**: new, like_new, good, fair, poor
- **Status**: pending, active, sold, reject
- **6 sản phẩm featured** với giá từ 350K - 30M VNĐ

### 🖼️ **Bảng `product_images`** - Hình ảnh sản phẩm (6 images)
```sql
id, product_id, image_path, is_primary, created_at
```
- Hỗ trợ nhiều ảnh cho 1 sản phẩm với `is_primary` flag

### 🛒 **Bảng `carts` & `cart_items`** - Giỏ hàng (7 carts, 5 items)
```sql
carts: id, user_id, session_id, created_at, updated_at
cart_items: id, cart_id, product_id, quantity, added_price, condition_snapshot, added_at, updated_at
```
- Hỗ trợ cả user đã đăng nhập và guest (session-based)

### 📋 **Bảng `orders` & `order_items`** - Đơn hàng (8 orders, 10 items)
```sql
orders: id, order_number, buyer_id, total_amount, status, payment_method, 
        payment_status, notes, created_at, updated_at
order_items: id, order_id, product_id, product_title, product_price, 
             quantity, subtotal
```
- **Order status**: pending, success, failed, cancelled
- **Payment status**: pending, paid, failed
- **Payment methods**: vnpay, bank_transfer

### 🔐 **Bảng Auth & Security**
```sql
remember_tokens: id, user_id, token, expires_at, created_at
user_logs: id, user_id, action, ip_address, user_agent, created_at
password_resets: id, email, token, expires_at, created_at
```

## 🏗️ Kiến trúc hệ thống

### 📁 **Cấu trúc thư mục**
```
WebMuaBanDoCu/
├── app/                   # Core application
│   ├── Controllers/       # Business logic
│   │   ├── admin/        # Admin controllers
│   │   ├── cart/         # Cart management
│   │   ├── order/        # Order processing
│   │   ├── payment/      # Payment handling
│   │   ├── product/      # Product management
│   │   ├── sell/         # Selling functions
│   │   ├── user/         # User management
│   │   └── extra/        # Additional features
│   ├── Models/           # Data access layer
│   │   ├── user/         # Auth & User models
│   │   ├── product/      # Product models
│   │   ├── cart/         # Cart models
│   │   └── order/        # Order models
│   ├── View/             # Presentation layer
│   │   ├── admin/        # Admin interface
│   │   ├── cart/         # Cart pages
│   │   ├── checkout/     # Checkout flow
│   │   ├── order/        # Order management
│   │   ├── payment/      # Payment pages
│   │   ├── product/      # Product pages
│   │   ├── user/         # User pages
│   │   └── extra/        # Additional pages
│   ├── Components/       # Reusable UI components
│   ├── modules/          # AJAX handlers
│   └── helpers.php       # Helper functions
├── config/
│   └── config.php        # Database & VNPay config
├── public/
│   ├── assets/          # CSS, JS, images
│   ├── uploads/         # User uploaded files
│   └── index.php        # Homepage (New Entry Point)
├── modules/             # AJAX request handlers
│   ├── cart/           # Cart AJAX
│   └── payment/        # Payment AJAX
└── data/
    └── database_complete_fixed.sql # Complete database schema
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
- **Upload**: File upload với validation
- **AJAX**: jQuery AJAX cho real-time updates

## 🚀 Hướng dẫn cài đặt

### 1. **Cài đặt Database**
```bash
# Tạo database
mysql -u root -p
CREATE DATABASE muabandocu;
USE muabandocu;

# Import schema và dữ liệu mẫu
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
- Truy cập: `http://localhost/WebMuaBanDoCu/public/index.php` (hoặc `/public/`)
- Đăng nhập admin: `admin@example.com` / `password`
- Test user: `user1@example.com` / `password`

## 🔒 Bảo mật

- ✅ **SQL Injection prevention** với PDO prepared statements
- ✅ **XSS protection** với htmlspecialchars()
- ✅ **CSRF token validation** cho forms
- ✅ **Password hashing** với password_hash()
- ✅ **Session security** với secure flags
- ✅ **File upload validation** (type, size, extension)
- ✅ **Input sanitization** và validation
- ✅ **Error handling** không expose sensitive info

## 📊 Thống kê hiện tại

### 🎯 **Dữ liệu mẫu có sẵn**
- ✅ **6 users** (1 admin + 5 users thường)
- ✅ **9 categories** đa dạng từ điện tử đến thời trang
- ✅ **10 products** với 6 sản phẩm featured
- ✅ **6 product images** với primary/secondary flags
- ✅ **7 carts** với 5 cart items
- ✅ **8 orders** với các trạng thái khác nhau
- ✅ **10 order items** tương ứng

### 🚀 **Tính năng đã hoạt động**
- ✅ Hiển thị sản phẩm nổi bật trên trang chủ
- ✅ Thêm sản phẩm vào giỏ hàng (AJAX) với toast notification
- ✅ Cập nhật số lượng giỏ hàng real-time
- ✅ Hiển thị đơn hàng gần đây (cho user đã đăng nhập)
- ✅ Navigation menu với đường dẫn chính xác
- ✅ Search functionality với filter
- ✅ Responsive design cho mobile/tablet
- ✅ Error handling và user feedback

### ❌ Lỗi "Call to a member function prepare() on int"
**Nguyên nhân**: Biến `$pdo` không được truyền đúng vào functions  
**Giải pháp**: ✅ Đã sửa trong `app/helpers.php` và các Controllers

### ❌ Lỗi "Cannot redeclare function"
**Nguyên nhân**: Function được định nghĩa nhiều lần  
**Giải pháp**: ✅ Sử dụng `if (!function_exists())` wrapper

### ❌ Lỗi đường dẫn CSS/JS
**Nguyên nhân**: Relative path không đúng khi di chuyển files  
**Giải pháp**: ✅ Đã chuẩn hóa paths trong tất cả View files

### ❌ Lỗi giỏ hàng không cập nhật
**Nguyên nhân**: Session user_id và function parameters không khớp  
**Giải pháp**: ✅ Đã fix logic session và parameter order

## 🎯 Roadmap phát triển

### 📝 **Mức 1 - Quan trọng nhất (Ưu tiên triển khai)**

#### 🌟 **Hệ thống đánh giá & Review**
```php
// Bảng reviews
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    user_id INT,
    rating INT(1) CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
- **Tính năng**: Đánh giá sao 1-5, comment, review có ảnh
- **UI**: Modal review, hiển thị rating trung bình
- **Logic**: Chỉ buyer đã mua mới được review

#### ❤️ **Wishlist/Yêu thích**
```php
// Bảng wishlists
CREATE TABLE wishlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);
```
- **Tính năng**: Save/unsave sản phẩm yêu thích
- **UI**: Heart icon toggle, trang wishlist riêng
- **Logic**: AJAX add/remove, counter

#### 🔍 **Tìm kiếm nâng cao**
- **Filter**: Giá min/max, tình trạng, địa điểm, seller rating
- **Sort**: Giá, ngày đăng, lượt xem, đánh giá
- **UI**: Sidebar filter, autocomplete search
- **Performance**: Full-text search index

#### 📊 **Dashboard thống kê**
```php
// Dashboard metrics
- Doanh thu theo tháng/năm
- Top sản phẩm bán chạy  
- Thống kê user active
- Conversion rate
- Payment method analytics
```

### 📝 **Mức 2 - Quan trọng**

#### 🔔 **Hệ thống thông báo**
```php
// Bảng notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    type ENUM('order', 'payment', 'product', 'system'),
    title VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
- **Real-time**: WebSocket hoặc polling
- **Types**: Đơn hàng mới, thanh toán thành công, sản phẩm được duyệt

#### 💬 **Chat/Nhắn tin**
```php
// Hệ thống chat buyer-seller
CREATE TABLE conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT,
    seller_id INT,
    product_id INT,
    last_message_at DATETIME
);

CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT,
    sender_id INT,
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
- **Tính năng**: Nhắn tin real-time giữa buyer và seller
- **UI**: Chat window, notification khi có tin nhắn mới
- **Logic**: Load more messages, đánh dấu đã đọc

#### 🎫 **Hệ thống mã giảm giá**
```php
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,
    type ENUM('fixed', 'percentage'),
    value DECIMAL(10,2),
    min_order_amount DECIMAL(10,2),
    max_uses INT,
    used_count INT DEFAULT 0,
    expires_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE
);
```
- **Tính năng**: Tạo và quản lý mã giảm giá
- **UI**: Form tạo mã, danh sách mã giảm giá
- **Logic**: Áp dụng mã giảm giá trong giỏ hàng, giới hạn số lần sử dụng

#### 🔍 **SEO Optimization**
- **Meta tags**: Dynamic title, description cho từng trang
- **Structured data**: Schema.org markup cho products
- **URL rewriting**: Friendly URLs với .htaccess
- **Sitemap**: Auto-generated XML sitemap

### 📝 **Mức 3 - Nâng cao**

#### 📱 **Progressive Web App (PWA)**
- **Service Worker**: Offline functionality
- **App Manifest**: Installable web app
- **Push Notifications**: Re-engagement

#### 🤖 **Chatbot AI**
- **Integration**: Dialogflow hoặc custom NLP
- **Features**: Product recommendations, FAQ, order tracking

#### 💰 **Ví điện tử nội bộ**
```php
CREATE TABLE wallets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    balance DECIMAL(15,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE wallet_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT,
    type ENUM('deposit', 'withdraw', 'payment', 'refund'),
    amount DECIMAL(15,2),
    description TEXT,
    reference_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
- **Tính năng**: Nạp tiền, rút tiền, thanh toán đơn hàng
- **UI**: Trang ví, lịch sử giao dịch
- **Logic**: Cập nhật số dư, ghi nhận giao dịch

#### 📧 **Email Marketing**
- **Newsletter**: Subscription management
- **Automation**: Welcome series, abandoned cart
- **Templates**: Responsive email templates

## 🔮 Tính năng bổ sung gợi ý

### 🛡️ **Bảo mật nâng cao**
- **Two-Factor Authentication (2FA)**
- **Rate limiting** cho API calls
- **IP whitelist/blacklist**
- **Audit logs** cho admin actions

### 📈 **Analytics & Tracking**
- **Google Analytics integration**
- **User behavior tracking**
- **A/B testing framework**
- **Performance monitoring**

### 🌐 **Đa ngôn ngữ & Đa tiền tệ**
- **i18n support** (Vietnamese, English)
- **Multi-currency** với exchange rates
- **Geo-location** based features

### 🚚 **Logistics & Shipping**
- **Shipping calculator** theo khu vực
- **Tracking integration** với đơn vị vận chuyển
- **Delivery time estimation**

### 🎮 **Gamification**
- **User levels** và badges
- **Referral program** với rewards
- **Daily check-in** bonuses
- **Loyalty points** system

## 👨‍💻 Đóng góp

1. Fork repository này
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

## 📞 Liên hệ

- **Email**: developer@webmuabandocu.com
- **Demo**: [http://localhost/WebMuaBanDoCu](http://localhost/WebMuaBanDoCu)
- **Documentation**: [Wiki](https://github.com/yourrepo/wiki)

---

**⭐ Nếu project này hữu ích, hãy cho chúng tôi một star!**
