-- Cleaned schema for `muabandocu`
-- ------------------------------------------------------------
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ------------------------------------------------------------
-- 1. USERS
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    avatar VARCHAR(255),
    role ENUM('user','admin') DEFAULT 'user',
    status ENUM('active','inactive') DEFAULT 'active',
    last_login DATETIME,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME,
    email_verified_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. CATEGORIES
-- ------------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. PRODUCTS
-- ------------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    price DECIMAL(15,0) NOT NULL,
    condition_status ENUM('new','like_new','good','fair','poor') NOT NULL,
    status ENUM('pending','active','reject','sold') DEFAULT 'pending',
    location VARCHAR(255),
    views INT DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    stock_quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. PRODUCT IMAGES
-- ------------------------------------------------------------
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. BOX CHAT
-- ------------------------------------------------------------
CREATE TABLE box_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_box_chat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. MESSAGES (belongs to a box_chat)
-- ------------------------------------------------------------
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    box_chat_id INT NOT NULL,
    role VARCHAR(10),
    content TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_box FOREIGN KEY (box_chat_id) REFERENCES box_chat(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. REVIEW PRODUCTS (1 user đánh giá 1 product duy nhất)
-- ------------------------------------------------------------
CREATE TABLE review_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    content TEXT,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    username VARCHAR(50),
    UNIQUE KEY ux_user_product (user_id, product_id),
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. CARTS & CART ITEMS
-- ------------------------------------------------------------
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    session_id VARCHAR(128) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_price DECIMAL(15,0) NOT NULL,
    condition_snapshot VARCHAR(20),
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ux_cart_product (cart_id, product_id),
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. ORDERS & ORDER ITEMS
-- ------------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    buyer_id INT NOT NULL,
    total_amount DECIMAL(15,0) NOT NULL,
    status ENUM('pending','success','failed','cancelled') DEFAULT 'pending',
    payment_method ENUM('vnpay','bank_transfer','cod') DEFAULT 'vnpay',
    payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
    notes TEXT,
    vnpay_transaction_id VARCHAR(100),
    vnpay_response_code VARCHAR(10),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    product_price DECIMAL(15,0) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(15,0) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. NOTIFICATIONS
-- ------------------------------------------------------------
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. PASSWORD RESETS
-- ------------------------------------------------------------
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_email (email),
    KEY idx_token (token),
    KEY idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12. REMEMBER TOKENS
-- ------------------------------------------------------------
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_remember_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13. RATE LIMITS
-- ------------------------------------------------------------
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_action_identifier (action, identifier),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 14. USER LOGS
-- ------------------------------------------------------------
CREATE TABLE user_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_id (user_id),
    KEY idx_action (action),
    CONSTRAINT fk_user_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 15. PRODUCT STATUS LOGS
-- ------------------------------------------------------------
CREATE TABLE product_status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    old_status VARCHAR(20),
    new_status VARCHAR(20),
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_product_id (product_id),
    KEY idx_user_id (user_id),
    CONSTRAINT fk_status_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_status_logs_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 16. SYSTEM NOTIFICATIONS
-- ------------------------------------------------------------
CREATE TABLE system_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    type ENUM('news','announcement','maintenance','update') DEFAULT 'news',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------

-- INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
-- (1, 1, 'Sản phẩm <b>IPhone 11</b> của bạn đã được bán và thanh toán thành công!', 1, '2025-06-19 18:39:39'),
-- (2, 1, 'Sản phẩm <b>iPad Pro 11 inch 2021</b> của bạn đã được bán và thanh toán thành công!', 0, '2025-06-19 18:45:09'),
-- (3, 1, 'Sản phẩm <b>123</b> của bạn đã được bán và thanh toán thành công!', 1, '2025-06-19 18:46:33'),
-- (4, 1, 'Sản phẩm <b>IPhone 11</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-20 22:07:19'),
-- (5, 1, 'Sản phẩm <b>Iphone 123</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-20 22:15:56'),
-- (6, 1, 'Sản phẩm <b>Iphone 123</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-20 22:15:56'),
-- (7, 1, 'Sản phẩm <b>12312</b> của bạn đã bị admin xóa khỏi hệ thống.', 1, '2025-06-26 16:51:31'),
-- (8, 1, 'Sản phẩm <b>12312</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-26 16:51:51'),
-- (9, 1, 'Sản phẩm <b>12312</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-26 16:51:51'),
-- (10, 1, 'Sản phẩm <b>1232</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-30 22:57:05'),
-- (11, 1, 'Sản phẩm <b>IPhone 13</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-01 14:38:56'),
-- (12, 1, 'Sản phẩm <b>Áo Khoác Da Cá Sấu</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-01 15:42:48'),
-- (13, 1, 'Sản phẩm <b>Xe đạp thể thao</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-01 22:51:29'),
-- (14, 1, 'Sản phẩm <b>1212</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-02 11:24:21'),
-- (15, 1, 'Sản phẩm <b>IP10</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-03 14:58:18'),
-- (16, 1, 'Sản phẩm <b>hsahd</b> của bạn đã được duyệt', 1, '2025-07-03 23:35:17'),
-- (17, 1, 'Sản phẩm <b>hsahd</b> của bạn đã bị từ chối', 1, '2025-07-03 23:35:21'),
-- (18, 1, 'Sản phẩm <b>hsahd</b> của bạn đã bị xóa', 1, '2025-07-03 23:35:24'),
-- (19, 1, 'Sản phẩm <b>123</b> của bạn đã được duyệt', 1, '2025-07-03 23:40:04'),
-- (20, 1, 'Sản phẩm <b>34324</b> của bạn đã bị từ chối', 1, '2025-07-03 23:41:46'),
-- (21, 1, 'Sản phẩm <b>seqads</b> của bạn đã bị xóa', 1, '2025-07-03 23:41:51'),
-- (22, 1, 'Sản phẩm <b>DICH </b> của bạn đã được duyệt', 1, '2025-07-04 00:10:56'),
-- (23, 1, 'Sản phẩm <b>iisaidi</b> của bạn đã được duyệt', 0, '2025-07-04 14:25:51'),
-- (24, 1, 'Sản phẩm <b>ádas</b> của bạn đã được duyệt', 0, '2025-07-04 17:15:58'),
-- (25, 1, 'Sản phẩm <b>123123</b> của bạn đã bị từ chối', 0, '2025-07-04 17:16:00');

-- Thêm dữ liệu categories
INSERT INTO categories (name, slug, description) VALUES
('Điện thoại & Máy tính bảng', 'dien-thoai-may-tinh-bang', 'Điện thoại di động, smartphone, máy tính bảng các loại'),
('Laptop & Máy tính', 'laptop-may-tinh', 'Laptop, máy tính để bàn, linh kiện máy tính'),
('Thời trang & Phụ kiện', 'thoi-trang-phu-kien', 'Quần áo, giày dép, túi xách, phụ kiện thời trang'),
('Đồ gia dụng & Nội thất', 'do-gia-dung-noi-that', 'Đồ gia dụng, nội thất, đồ trang trí nhà cửa'),
('Xe cộ & Phương tiện', 'xe-co-phuong-tien', 'Xe máy, xe đạp, ô tô và phụ kiện xe'),
('Sách & Văn phòng phẩm', 'sach-van-phong-pham', 'Sách, truyện, văn phòng phẩm, dụng cụ học tập'),
('Thể thao & Giải trí', 'the-thao-giai-tri', 'Dụng cụ thể thao, đồ chơi, thiết bị giải trí'),
('Điện máy & Công nghệ', 'dien-may-cong-nghe', 'Tivi, tủ lạnh, máy giặt, thiết bị điện tử'),
('Mẹ và bé', 'me-va-be', 'Đồ dùng cho mẹ và bé, đồ chơi trẻ em, quần áo trẻ em'),
('Âm nhạc & Nhạc cụ', 'am-nhac-nhac-cu', 'Nhạc cụ, phụ kiện âm nhạc, thiết bị phòng thu'),
('Sức khỏe & Làm đẹp', 'suc-khoe-lam-dep', 'Mỹ phẩm, thiết bị chăm sóc sức khỏe, thực phẩm chức năng'),
('Thú cưng & Phụ kiện', 'thu-cung-phu-kien', 'Đồ dùng, thức ăn, phụ kiện cho thú cưng'),
('Game & Console', 'game-console', 'Máy chơi game, thiết bị console, phụ kiện chơi game'),
('Dụng cụ & Máy móc', 'dung-cu-may-moc', 'Dụng cụ cầm tay, máy móc, thiết bị công nghiệp nhỏ'),
('Đồ cổ & Sưu tầm', 'do-co-suu-tam', 'Đồ cổ, đồ sưu tầm, tem, tiền xu'),
('Nhà cửa & Vườn', 'nha-cua-vuon', 'Dụng cụ làm vườn, trang trí nhà cửa, thiết bị ngoài trời'),
('Phim & Đĩa nhạc', 'phim-dia-nhac', 'DVD, Blu-ray, đĩa nhạc, băng cassette'),
('Vé & Sự kiện', 've-su-kien', 'Vé xem ca nhạc, thể thao, hội chợ, sự kiện'),
('Việc làm & Dịch vụ', 'viec-lam-dich-vu', 'Tin tuyển dụng, dịch vụ cá nhân, gia sư, sửa chữa'),
('Nhạc số & Phần mềm', 'nhac-so-phan-mem', 'Mã thẻ nhạc số, key phần mềm, bản quyền game'),
('Drone & Thiết bị bay', 'drone-thiet-bi-bay', 'Drone, flycam, phụ kiện thiết bị bay điều khiển'),
('Thiết bị thông minh', 'thiet-bi-thong-minh', 'Smartwatch, vòng đeo sức khỏe, loa thông minh'),
('Đồ ăn & Thực phẩm', 'do-an-thuc-pham', 'Đồ ăn vặt, thực phẩm khô, đặc sản vùng miền'),
('Voucher & Coupon', 'voucher-coupon', 'Mã giảm giá, voucher mua sắm, phiếu quà tặng');

-- Migration to add status and is_hidden columns to cart_items table
-- Run this SQL to add the necessary columns for cart status management

ALTER TABLE cart_items 
ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER condition_snapshot,
ADD COLUMN is_hidden TINYINT(1) DEFAULT 0 AFTER status;

-- Update existing records to have default values
UPDATE cart_items SET status = 'active', is_hidden = 0 WHERE status IS NULL OR is_hidden IS NULL;

-- Add index for better performance on hidden items
CREATE INDEX idx_cart_items_status_hidden ON cart_items(status, is_hidden);

-- Optional: Add comment to document the purpose
ALTER TABLE cart_items COMMENT 'Cart items with status tracking - active/sold and visibility control';

-- Step 1: Drop the existing foreign key constraint
ALTER TABLE order_items DROP FOREIGN KEY fk_order_items_product;

-- Step 2: Modify product_id to allow NULL values
ALTER TABLE order_items MODIFY COLUMN product_id INT NULL;

-- Step 3: Add new foreign key constraint with ON DELETE SET NULL
ALTER TABLE order_items 
ADD CONSTRAINT fk_order_items_product 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;

-- Step 4: Add index for better performance on product_id lookups
CREATE INDEX idx_order_items_product_id ON order_items(product_id);

-- Step 5: Add comment to document the change
ALTER TABLE order_items COMMENT 'Order items with product reference that can be NULL if product is deleted by admin';

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */; 