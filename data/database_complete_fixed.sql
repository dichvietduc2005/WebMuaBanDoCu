-- File SQL hoàn chỉnh cho Web Mua Bán Đồ Cũ
-- Tạo database và cấu trúc bảng theo đúng yêu cầu của code

-- Tạo database
CREATE DATABASE IF NOT EXISTS muabandocu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE muabandocu;

-- Tắt foreign key checks tạm thời
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa các bảng nếu tồn tại (theo thứ tự dependency)
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS user_logs;
DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Bật lại foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Bảng người dùng
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    avatar VARCHAR(255),
    `role` VARCHAR(10) DEFAULT 'user' CHECK (`role` IN ('user', 'admin')),
    `status` VARCHAR(10) DEFAULT 'active' CHECK (`status` IN ('active', 'inactive')),
    last_login DATETIME NULL,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_username` (`username`),
    INDEX `idx_status` (`status`),
    INDEX `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng danh mục sản phẩm
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    status VARCHAR(10) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng sản phẩm do người dùng đăng bán
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    price DECIMAL(15, 0) NOT NULL,
    condition_status ENUM('new', 'like_new', 'good', 'fair', 'poor') NOT NULL,
    status ENUM('active', 'sold', 'inactive') DEFAULT 'active',
    location VARCHAR(255),
    views INT DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    stock_quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng hình ảnh của sản phẩm
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng để lưu remember me tokens cho đăng nhập
CREATE TABLE remember_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `user_id` (`user_id`),
    INDEX `token` (`token`),
    INDEX `expires_at` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng để lưu log hoạt động của user
CREATE TABLE user_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `user_id` (`user_id`),
    INDEX `action` (`action`),
    INDEX `created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng để lưu password reset tokens
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `email` (`email`),
    INDEX `token` (`token`),
    INDEX `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng giỏ hàng
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL COMMENT 'NULL nếu guest',
    session_id VARCHAR(128) NULL COMMENT 'UUID cho guest',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ux_carts_user (user_id),
    UNIQUE KEY ux_carts_session (session_id),
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết giỏ hàng
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_price DECIMAL(15,0) NOT NULL,
    condition_snapshot VARCHAR(20),
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY ux_cart_product (cart_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL,
    buyer_id INT(11) NOT NULL,
    total_amount DECIMAL(15,0) NOT NULL,
    status ENUM('pending','success','failed','cancelled') DEFAULT 'pending',
    payment_method ENUM('vnpay','bank_transfer','cod') DEFAULT 'vnpay',
    payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
    notes TEXT,
    vnpay_transaction_id VARCHAR(100) DEFAULT NULL,
    vnpay_response_code VARCHAR(10) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY order_number (order_number),
    KEY buyer_id (buyer_id),
    KEY status (status),
    KEY payment_status (payment_status),
    FOREIGN KEY (buyer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    product_price DECIMAL(15,0) NOT NULL,
    quantity INT(11) NOT NULL,
    subtotal DECIMAL(15,0) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY order_id (order_id),
    KEY product_id (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================
-- DỮ LIỆU MẪU
-- ================================

-- Tắt foreign key checks tạm thời
SET FOREIGN_KEY_CHECKS = 0;

-- Thêm dữ liệu mẫu cho bảng users
INSERT INTO users (username, email, password, full_name, phone, address, role, status) VALUES
('admin', 'admin@muabandocu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0123456789', 'Hà Nội', 'admin', 'active'),
('nguyenvana', 'nguyenvana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0987654321', '123 Đường ABC, Quận 1, TP.HCM', 'user', 'active'),
('tranthib', 'tranthib@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0912345678', '456 Đường XYZ, Quận 3, TP.HCM', 'user', 'active'),
('phamvanc', 'phamvanc@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Văn C', '0901234567', '789 Đường DEF, Quận 5, TP.HCM', 'user', 'active'),
('hoangthid', 'hoangthid@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoàng Thị D', '0898765432', '321 Đường GHI, Quận 7, TP.HCM', 'user', 'active'),
('vuvane', 'vuvane@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vũ Văn E', '0876543210', '654 Đường JKL, Quận 2, TP.HCM', 'user', 'active');

-- Thêm dữ liệu mẫu cho bảng categories
INSERT INTO categories (name, slug, description, status) VALUES
('Điện thoại & Máy tính bảng', 'dien-thoai-may-tinh-bang', 'Điện thoại di động, smartphone, máy tính bảng các loại', 'active'),
('Laptop & Máy tính', 'laptop-may-tinh', 'Laptop, máy tính để bàn, linh kiện máy tính', 'active'),
('Thời trang & Phụ kiện', 'thoi-trang-phu-kien', 'Quần áo, giày dép, túi xách, phụ kiện thời trang', 'active'),
('Đồ gia dụng & Nội thất', 'do-gia-dung-noi-that', 'Đồ gia dụng, nội thất, đồ trang trí nhà cửa', 'active'),
('Xe cộ & Phương tiện', 'xe-co-phuong-tien', 'Xe máy, xe đạp, ô tô và phụ kiện xe', 'active'),
('Sách & Văn phòng phẩm', 'sach-van-phong-pham', 'Sách, truyện, văn phòng phẩm, dụng cụ học tập', 'active'),
('Thể thao & Giải trí', 'the-thao-giai-tri', 'Dụng cụ thể thao, đồ chơi, thiết bị giải trí', 'active'),
('Điện máy & Công nghệ', 'dien-may-cong-nghe', 'Tivi, tủ lạnh, máy giặt, thiết bị điện tử', 'active'),
('Mẹ và bé', 'me-va-be', 'Đồ dùng cho mẹ và bé, đồ chơi trẻ em, quần áo trẻ em', 'active');

-- Thêm dữ liệu mẫu cho bảng products
INSERT INTO products (user_id, category_id, title, slug, description, price, condition_status, status, location, views, featured, stock_quantity) VALUES
(2, 1, 'iPhone 12 Pro Max 128GB', 'iphone-12-pro-max-128gb', 'iPhone 12 Pro Max màu xanh dương, dung lượng 128GB. Máy còn mới 95%, đầy đủ phụ kiện gốc bao gồm hộp, sạc, tai nghe. Pin còn 89%, không bể vỡ, không ngấm nước.', 15000000, 'like_new', 'active', 'Quận 1, TP.HCM', 125, 1, 2),
(3, 1, 'Samsung Galaxy S21 Ultra 256GB', 'samsung-galaxy-s21-ultra-256gb', 'Samsung Galaxy S21 Ultra màu đen, bộ nhớ 256GB. Máy sử dụng 6 tháng, còn bảo hành 18 tháng. Camera zoom 100x hoạt động tốt, màn hình không trầy xước.', 18500000, 'good', 'active', 'Quận 3, TP.HCM', 98, 1, 1),
(2, 2, 'MacBook Pro 16 inch 2019', 'macbook-pro-16-inch-2019', 'MacBook Pro 16 inch năm 2019, chip Intel Core i7, RAM 16GB, SSD 512GB. Máy hoạt động mượt mà, pin còn tốt. Có một vài vết trầy nhỏ ở vỏ nhưng không ảnh hưởng sử dụng.', 30000000, 'good', 'active', 'Quận 5, TP.HCM', 156, 1, 1),
(4, 2, 'Dell XPS 13 9310', 'dell-xps-13-9310', 'Laptop Dell XPS 13 9310, màn hình 13.3 inch Full HD, CPU Intel Core i5 thế hệ 11, RAM 8GB, SSD 256GB. Máy nhẹ, pin trâu, phù hợp cho sinh viên và dân văn phòng.', 21750000, 'like_new', 'active', 'Quận 7, TP.HCM', 89, 1, 1),
(3, 1, 'iPad Pro 11 inch 2021', 'ipad-pro-11-inch-2021', 'iPad Pro 11 inch năm 2021, chip M1, bộ nhớ 128GB, WiFi + Cellular. Kèm theo Apple Pencil và Smart Keyboard. Máy còn mới, ít sử dụng.', 20900000, 'like_new', 'active', 'Quận 2, TP.HCM', 76, 1, 1),
(5, 4, 'Máy ảnh Sony A7 III', 'may-anh-sony-a7-iii', 'Máy ảnh Sony A7 III body, còn bảo hành 8 tháng. Kèm theo lens kit 28-70mm và thẻ nhớ 64GB. Máy chụp đẹp, quay video 4K.', 25000000, 'good', 'active', 'Quận 1, TP.HCM', 67, 1, 1),
(4, 3, 'Áo khoác Nike xuất khẩu', 'ao-khoac-nike-xuat-khau', 'Áo khoác Nike xuất khẩu, size M, màu đen. Chất liệu tốt, giữ ấm tốt. Mặc ít lần, còn như mới.', 350000, 'like_new', 'active', 'Quận 9, TP.HCM', 45, 0, 3),
(6, 3, 'Giày Adidas Ultraboost 22', 'giay-adidas-ultraboost-22', 'Giày chạy bộ Adidas Ultraboost 22, size 42, màu trắng đen. Mặc được 2-3 tháng, đế còn tốt, không bị mòn nhiều.', 1800000, 'good', 'active', 'Quận 10, TP.HCM', 32, 0, 1),
(5, 6, 'Bộ sách Harry Potter tiếng Việt', 'bo-sach-harry-potter-tieng-viet', 'Bộ 7 tập Harry Potter bản tiếng Việt, nhà xuất bản Trẻ. Sách còn mới, ít đọc, không rách hay bị gãy gáy.', 450000, 'like_new', 'active', 'Quận 4, TP.HCM', 28, 0, 1),
(2, 7, 'Bàn bi-a mini', 'ban-bi-a-mini', 'Bàn bi-a mini cho gia đình, kích thước 120x60cm. Đầy đủ phụ kiện gồm cơ, bóng, phấn. Thích hợp cho trẻ em và người lớn.', 2500000, 'good', 'active', 'Quận 6, TP.HCM', 19, 0, 1);

-- Thêm dữ liệu mẫu cho bảng product_images
INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES
(1, 'uploads/products/iphone12pro.jpg', 1, 1),
(2, 'uploads/products/samsung_s21_ultra.jpg', 1, 1),
(3, 'uploads/products/macbook_pro_16.jpg', 1, 1),
(4, 'uploads/products/dell_xps_13.jpg', 1, 1),
(5, 'uploads/products/ipad_pro_11.jpg', 1, 1),
(6, 'uploads/products/sony_a7_iii.jpg', 1, 1);

-- Thêm dữ liệu mẫu cho bảng carts
INSERT INTO carts (user_id, session_id) VALUES
(2, NULL),
(3, NULL),
(4, NULL),
(5, NULL),
(6, NULL),
(NULL, 'guest_session_001'),
(NULL, 'guest_session_002');

-- Thêm dữ liệu mẫu cho bảng cart_items
INSERT INTO cart_items (cart_id, product_id, quantity, added_price, condition_snapshot) VALUES
(1, 1, 1, 15000000, 'like_new'),
(1, 3, 1, 30000000, 'good'),
(2, 2, 1, 18500000, 'good'),
(3, 4, 1, 21750000, 'like_new'),
(3, 5, 1, 20900000, 'like_new');

-- Thêm dữ liệu mẫu cho bảng orders
INSERT INTO orders (order_number, buyer_id, total_amount, status, payment_method, payment_status, notes, created_at) VALUES
('ORD-2023-00001', 2, 15000000, 'success', 'vnpay', 'paid', 'Đơn hàng mua iPhone 12 Pro Max', '2023-06-10 10:30:00'),
('ORD-2023-00002', 3, 30000000, 'success', 'vnpay', 'paid', 'Đơn hàng mua MacBook Pro', '2023-06-12 14:15:00'),
('ORD-2023-00003', 4, 18500000, 'pending', 'vnpay', 'pending', 'Đơn hàng mua Samsung Galaxy S21', '2023-06-14 09:20:00'),
('ORD-2023-00004', 5, 42650000, 'success', 'vnpay', 'paid', 'Đơn hàng mua laptop và iPad', '2023-06-13 16:45:00'),
('ORD-2023-00005', 6, 25000000, 'cancelled', 'vnpay', 'failed', 'Đơn hàng mua máy ảnh - đã hủy', '2023-06-11 11:30:00'),
('ORD-2023-00006', 2, 2150000, 'success', 'vnpay', 'paid', 'Đơn hàng mua giày và áo', '2023-06-09 13:00:00'),
('ORD-2023-00007', 3, 450000, 'success', 'bank_transfer', 'paid', 'Đơn hàng mua sách', '2023-06-08 15:30:00'),
('ORD-2023-00008', 4, 2500000, 'pending', 'vnpay', 'pending', 'Đơn hàng mua bàn bi-a', '2023-06-15 08:45:00');

-- Thêm dữ liệu mẫu cho bảng order_items
INSERT INTO order_items (order_id, product_id, product_title, product_price, quantity, subtotal) VALUES
(1, 1, 'iPhone 12 Pro Max 128GB', 15000000, 1, 15000000),
(2, 3, 'MacBook Pro 16 inch 2019', 30000000, 1, 30000000),
(3, 2, 'Samsung Galaxy S21 Ultra 256GB', 18500000, 1, 18500000),
(4, 4, 'Dell XPS 13 9310', 21750000, 1, 21750000),
(4, 5, 'iPad Pro 11 inch 2021', 20900000, 1, 20900000),
(5, 6, 'Máy ảnh Sony A7 III', 25000000, 1, 25000000),
(6, 7, 'Áo khoác Nike xuất khẩu', 350000, 1, 350000),
(6, 8, 'Giày Adidas Ultraboost 22', 1800000, 1, 1800000),
(7, 9, 'Bộ sách Harry Potter tiếng Việt', 450000, 1, 450000),
(8, 10, 'Bàn bi-a mini', 2500000, 1, 2500000);

-- Cập nhật AUTO_INCREMENT cho các bảng
ALTER TABLE users AUTO_INCREMENT = 7;
ALTER TABLE categories AUTO_INCREMENT = 10;
ALTER TABLE products AUTO_INCREMENT = 11;
ALTER TABLE product_images AUTO_INCREMENT = 7;
ALTER TABLE remember_tokens AUTO_INCREMENT = 1;
ALTER TABLE user_logs AUTO_INCREMENT = 1;
ALTER TABLE password_resets AUTO_INCREMENT = 1;
ALTER TABLE carts AUTO_INCREMENT = 8;
ALTER TABLE cart_items AUTO_INCREMENT = 6;
ALTER TABLE orders AUTO_INCREMENT = 9;
ALTER TABLE order_items AUTO_INCREMENT = 11;


-- Update status
ALTER TABLE products MODIFY status ENUM('pending', 'active', 'reject', 'sold') DEFAULT 'pending';


-- Ẩn sản phẩm nổi bật nếu hết hàng
SELECT p.*, pi.image_path, c.name as category_name 
FROM products p 
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active' AND p.featured = 1 AND p.stock_quantity > 0
ORDER BY p.created_at DESC 
LIMIT 8


-- Bảng Notification
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bật lại foreign key checks

-- bỏ vào thấy lỗi
-- SET FOREIGN_KEY_CHECKS = 1;

-- COMMIT;
