-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 04, 2025 lúc 02:27 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `muabandocu`
--


--chạy bảng xong xóa 3 dòng này tránh mất dữ liệu




-- Xóa bảng 'messages' nếu nó tồn tại để tránh lỗi khóa ngoại
DROP TABLE IF EXISTS messages;

-- Xóa bảng 'box_chat' nếu nó tồn tại để tránh lỗi khóa ngoại
DROP TABLE IF EXISTS box_chat;

-- Xóa bảng 'review_products' nếu nó tồn tại
DROP TABLE IF EXISTS review_products;
-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `box_chat`
--

CREATE TABLE box_chat(
    id INT NOT NULL,
    user_id INT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) 
);
--
-- Đang đổ dữ liệu cho bảng `box_chat`
--

INSERT INTO `box_chat` (`user_id`, `is_read`) VALUES
(7, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL nếu guest',
  `session_id` varchar(128) DEFAULT NULL COMMENT 'UUID cho guest',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(2, 3, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(3, 4, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(4, 5, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(5, 6, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(6, NULL, 'guest_session_001', '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(7, NULL, 'guest_session_002', '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(8, 7, NULL, '2025-06-20 22:23:19', '2025-06-20 22:23:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_price` decimal(15,0) NOT NULL,
  `condition_snapshot` varchar(20) DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `added_price`, `condition_snapshot`, `added_at`, `updated_at`) VALUES
(1, 1, 1, 1, 15000000, 'like_new', '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(2, 1, 3, 1, 30000000, 'good', '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(3, 2, 2, 1, 18500000, 'good', '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(4, 3, 4, 1, 21750000, 'like_new', '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(5, 3, 5, 1, 20900000, 'like_new', '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(76, 8, 3, 1, 30000000, 'good', '2025-07-04 16:55:11', '2025-07-04 16:55:11'),
(77, 8, 4, 1, 21750000, 'like_new', '2025-07-04 16:55:16', '2025-07-04 16:55:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(10) DEFAULT 'active' CHECK (`status` in ('active','inactive')),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Điện thoại & Máy tính bảng', 'dien-thoai-may-tinh-bang', 'Điện thoại di động, smartphone, máy tính bảng các loại', NULL, 'active', '2025-06-20 22:10:39'),
(2, 'Laptop & Máy tính', 'laptop-may-tinh', 'Laptop, máy tính để bàn, linh kiện máy tính', NULL, 'active', '2025-06-20 22:10:39'),
(3, 'Thời trang & Phụ kiện', 'thoi-trang-phu-kien', 'Quần áo, giày dép, túi xách, phụ kiện thời trang', NULL, 'active', '2025-06-20 22:10:39'),
(4, 'Đồ gia dụng & Nội thất', 'do-gia-dung-noi-that', 'Đồ gia dụng, nội thất, đồ trang trí nhà cửa', NULL, 'active', '2025-06-20 22:10:39'),
(5, 'Xe cộ & Phương tiện', 'xe-co-phuong-tien', 'Xe máy, xe đạp, ô tô và phụ kiện xe', NULL, 'active', '2025-06-20 22:10:39'),
(6, 'Sách & Văn phòng phẩm', 'sach-van-phong-pham', 'Sách, truyện, văn phòng phẩm, dụng cụ học tập', NULL, 'active', '2025-06-20 22:10:39'),
(7, 'Thể thao & Giải trí', 'the-thao-giai-tri', 'Dụng cụ thể thao, đồ chơi, thiết bị giải trí', NULL, 'active', '2025-06-20 22:10:39'),
(8, 'Điện máy & Công nghệ', 'dien-may-cong-nghe', 'Tivi, tủ lạnh, máy giặt, thiết bị điện tử', NULL, 'active', '2025-06-20 22:10:39'),
(9, 'Mẹ và bé', 'me-va-be', 'Đồ dùng cho mẹ và bé, đồ chơi trẻ em, quần áo trẻ em', NULL, 'active', '2025-06-20 22:10:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE messages (
    id INT NOT NULL,
    box_chat_id INT NOT NULL, 
    role VARCHAR(10), 
    content TEXT NOT NULL, 
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (box_chat_id) REFERENCES box_chat(user_id) 
);
--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`box_chat_id`, `role`, `content`, `sent_at`) VALUES
(7, 'user', 'hiu', '2025-07-02 21:01:55'),
(7, 'admin', 'ÁD', '2025-07-04 14:07:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 8, 'Sản phẩm <b>IPhone 11</b> của bạn đã được bán và thanh toán thành công!', 1, '2025-06-19 18:39:39'),
(2, 3, 'Sản phẩm <b>iPad Pro 11 inch 2021</b> của bạn đã được bán và thanh toán thành công!', 0, '2025-06-19 18:45:09'),
(3, 8, 'Sản phẩm <b>123</b> của bạn đã được bán và thanh toán thành công!', 1, '2025-06-19 18:46:33'),
(4, 8, 'Sản phẩm <b>IPhone 11</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-20 22:07:19'),
(5, 7, 'Sản phẩm <b>Iphone 123</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-20 22:15:56'),
(6, 7, 'Sản phẩm <b>Iphone 123</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-20 22:15:56'),
(7, 7, 'Sản phẩm <b>12312</b> của bạn đã bị admin xóa khỏi hệ thống.', 1, '2025-06-26 16:51:31'),
(8, 7, 'Sản phẩm <b>12312</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-26 16:51:51'),
(9, 7, 'Sản phẩm <b>12312</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-26 16:51:51'),
(10, 7, 'Sản phẩm <b>1232</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-06-30 22:57:05'),
(11, 7, 'Sản phẩm <b>IPhone 13</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-01 14:38:56'),
(12, 7, 'Sản phẩm <b>Áo Khoác Da Cá Sấu</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-01 15:42:48'),
(13, 7, 'Sản phẩm <b>Xe đạp thể thao</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-01 22:51:29'),
(14, 7, 'Sản phẩm <b>1212</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-02 11:24:21'),
(15, 7, 'Sản phẩm <b>IP10</b> của bạn đã được admin duyệt và đăng bán.', 1, '2025-07-03 14:58:18'),
(16, 7, 'Sản phẩm <b>hsahd</b> của bạn đã được duyệt', 1, '2025-07-03 23:35:17'),
(17, 7, 'Sản phẩm <b>hsahd</b> của bạn đã bị từ chối', 1, '2025-07-03 23:35:21'),
(18, 7, 'Sản phẩm <b>hsahd</b> của bạn đã bị xóa', 1, '2025-07-03 23:35:24'),
(19, 7, 'Sản phẩm <b>123</b> của bạn đã được duyệt', 1, '2025-07-03 23:40:04'),
(20, 7, 'Sản phẩm <b>34324</b> của bạn đã bị từ chối', 1, '2025-07-03 23:41:46'),
(21, 7, 'Sản phẩm <b>seqads</b> của bạn đã bị xóa', 1, '2025-07-03 23:41:51'),
(22, 7, 'Sản phẩm <b>DICH </b> của bạn đã được duyệt', 1, '2025-07-04 00:10:56'),
(23, 7, 'Sản phẩm <b>iisaidi</b> của bạn đã được duyệt', 0, '2025-07-04 14:25:51'),
(24, 7, 'Sản phẩm <b>ádas</b> của bạn đã được duyệt', 0, '2025-07-04 17:15:58'),
(25, 7, 'Sản phẩm <b>123123</b> của bạn đã bị từ chối', 0, '2025-07-04 17:16:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `total_amount` decimal(15,0) NOT NULL,
  `status` enum('pending','success','failed','cancelled') DEFAULT 'pending',
  `payment_method` enum('vnpay','bank_transfer','cod') DEFAULT 'vnpay',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `vnpay_transaction_id` varchar(100) DEFAULT NULL,
  `vnpay_response_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `total_amount`, `status`, `payment_method`, `payment_status`, `notes`, `vnpay_transaction_id`, `vnpay_response_code`, `created_at`, `updated_at`) VALUES
(1, 'ORD-2023-00001', 2, 15000000, 'success', 'vnpay', 'paid', 'Đơn hàng mua iPhone 12 Pro Max', NULL, NULL, '2023-06-10 03:30:00', '2025-06-20 15:10:39'),
(2, 'ORD-2023-00002', 3, 30000000, 'success', 'vnpay', 'paid', 'Đơn hàng mua MacBook Pro', NULL, NULL, '2023-06-12 07:15:00', '2025-06-20 15:10:39'),
(3, 'ORD-2023-00003', 4, 18500000, 'pending', 'vnpay', 'pending', 'Đơn hàng mua Samsung Galaxy S21', NULL, NULL, '2023-06-14 02:20:00', '2025-06-20 15:10:39'),
(4, 'ORD-2023-00004', 5, 42650000, 'success', 'vnpay', 'paid', 'Đơn hàng mua laptop và iPad', NULL, NULL, '2023-06-13 09:45:00', '2025-06-20 15:10:39'),
(5, 'ORD-2023-00005', 6, 25000000, 'cancelled', 'vnpay', 'failed', 'Đơn hàng mua máy ảnh - đã hủy', NULL, NULL, '2023-06-11 04:30:00', '2025-06-20 15:10:39'),
(6, 'ORD-2023-00006', 2, 2150000, 'success', 'vnpay', 'paid', 'Đơn hàng mua giày và áo', NULL, NULL, '2023-06-09 06:00:00', '2025-06-20 15:10:39'),
(7, 'ORD-2023-00007', 3, 450000, 'success', 'bank_transfer', 'paid', 'Đơn hàng mua sách', NULL, NULL, '2023-06-08 08:30:00', '2025-06-20 15:10:39'),
(8, 'ORD-2023-00008', 4, 2500000, 'pending', 'vnpay', 'pending', 'Đơn hàng mua bàn bi-a', NULL, NULL, '2023-06-15 01:45:00', '2025-06-20 15:10:39'),
(9, 'ORDER_20250620222335_4310', 7, 12312312312, 'pending', 'vnpay', 'pending', 'Thanh toan don hang tu Web Mua Ban Do Cu\nGhi chú khách hàng: 123123\nĐịa chỉ giao hàng: 123123, Hà Nội\nSĐT người nhận: 0912312312', NULL, NULL, '2025-06-20 15:23:54', '2025-06-20 15:23:54'),
(10, 'ORD-20250701-6863FA2015D78', 7, 85000200, 'pending', 'vnpay', 'pending', 'Thanh toan don hang\nGhi chú khách hàng: 123\nĐịa chỉ giao hàng: 123, 123\nSĐT người nhận: 123123123', NULL, NULL, '2025-07-01 15:09:34', '2025-07-01 15:09:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_price` decimal(15,0) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(15,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_title`, `product_price`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 'iPhone 12 Pro Max 128GB', 15000000, 1, 15000000, '2025-06-20 15:10:39'),
(2, 2, 3, 'MacBook Pro 16 inch 2019', 30000000, 1, 30000000, '2025-06-20 15:10:39'),
(3, 3, 2, 'Samsung Galaxy S21 Ultra 256GB', 18500000, 1, 18500000, '2025-06-20 15:10:39'),
(4, 4, 4, 'Dell XPS 13 9310', 21750000, 1, 21750000, '2025-06-20 15:10:39'),
(5, 4, 5, 'iPad Pro 11 inch 2021', 20900000, 1, 20900000, '2025-06-20 15:10:39'),
(6, 5, 6, 'Máy ảnh Sony A7 III', 25000000, 1, 25000000, '2025-06-20 15:10:39'),
(7, 6, 7, 'Áo khoác Nike xuất khẩu', 350000, 1, 350000, '2025-06-20 15:10:39'),
(8, 6, 8, 'Giày Adidas Ultraboost 22', 1800000, 1, 1800000, '2025-06-20 15:10:39'),
(9, 7, 9, 'Bộ sách Harry Potter tiếng Việt', 450000, 1, 450000, '2025-06-20 15:10:39'),
(10, 8, 10, 'Bàn bi-a mini', 2500000, 1, 2500000, '2025-06-20 15:10:39'),
(11, 9, 12, 'Iphone 123', 12312312312, 1, 12312312312, '2025-06-20 15:23:54'),
(12, 10, 19, 'Áo Khoác Da Cá Sấu', 50000000, 1, 50000000, '2025-07-01 15:09:34'),
(13, 10, 18, 'IPhone 13', 20000200, 1, 20000200, '2025-07-01 15:09:34'),
(14, 10, 1, 'iPhone 12 Pro Max 128GB', 15000000, 1, 15000000, '2025-07-01 15:09:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(15,0) NOT NULL,
  `condition_status` enum('new','like_new','good','fair','poor') NOT NULL,
  `status` enum('pending','active','reject','sold') DEFAULT 'pending',
  `location` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `user_id`, `category_id`, `title`, `slug`, `description`, `price`, `condition_status`, `status`, `location`, `views`, `featured`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'iPhone 12 Pro Max 128GB', 'iphone-12-pro-max-128gb', 'iPhone 12 Pro Max màu xanh dương, dung lượng 128GB. Máy còn mới 95%, đầy đủ phụ kiện gốc bao gồm hộp, sạc, tai nghe. Pin còn 89%, không bể vỡ, không ngấm nước.', 15000000, 'like_new', 'active', 'Quận 1, TP.HCM', 125, 1, 2, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(2, 3, 1, 'Samsung Galaxy S21 Ultra 256GB', 'samsung-galaxy-s21-ultra-256gb', 'Samsung Galaxy S21 Ultra màu đen, bộ nhớ 256GB. Máy sử dụng 6 tháng, còn bảo hành 18 tháng. Camera zoom 100x hoạt động tốt, màn hình không trầy xước.', 18500000, 'good', 'active', 'Quận 3, TP.HCM', 98, 1, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(3, 2, 2, 'MacBook Pro 16 inch 2019', 'macbook-pro-16-inch-2019', 'MacBook Pro 16 inch năm 2019, chip Intel Core i7, RAM 16GB, SSD 512GB. Máy hoạt động mượt mà, pin còn tốt. Có một vài vết trầy nhỏ ở vỏ nhưng không ảnh hưởng sử dụng.', 30000000, 'good', 'active', 'Quận 5, TP.HCM', 156, 1, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(4, 4, 2, 'Dell XPS 13 9310', 'dell-xps-13-9310', 'Laptop Dell XPS 13 9310, màn hình 13.3 inch Full HD, CPU Intel Core i5 thế hệ 11, RAM 8GB, SSD 256GB. Máy nhẹ, pin trâu, phù hợp cho sinh viên và dân văn phòng.', 21750000, 'like_new', 'active', 'Quận 7, TP.HCM', 89, 1, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(5, 3, 1, 'iPad Pro 11 inch 2021', 'ipad-pro-11-inch-2021', 'iPad Pro 11 inch năm 2021, chip M1, bộ nhớ 128GB, WiFi + Cellular. Kèm theo Apple Pencil và Smart Keyboard. Máy còn mới, ít sử dụng.', 20900000, 'like_new', 'active', 'Quận 2, TP.HCM', 76, 1, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(6, 5, 4, 'Máy ảnh Sony A7 III', 'may-anh-sony-a7-iii', 'Máy ảnh Sony A7 III body, còn bảo hành 8 tháng. Kèm theo lens kit 28-70mm và thẻ nhớ 64GB. Máy chụp đẹp, quay video 4K.', 25000000, 'good', 'active', 'Quận 1, TP.HCM', 67, 1, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(7, 4, 3, 'Áo khoác Nike xuất khẩu', 'ao-khoac-nike-xuat-khau', 'Áo khoác Nike xuất khẩu, size M, màu đen. Chất liệu tốt, giữ ấm tốt. Mặc ít lần, còn như mới.', 350000, 'like_new', 'active', 'Quận 9, TP.HCM', 45, 0, 3, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(8, 6, 3, 'Giày Adidas Ultraboost 22', 'giay-adidas-ultraboost-22', 'Giày chạy bộ Adidas Ultraboost 22, size 42, màu trắng đen. Mặc được 2-3 tháng, đế còn tốt, không bị mòn nhiều.', 1800000, 'good', 'active', 'Quận 10, TP.HCM', 32, 0, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(9, 5, 6, 'Bộ sách Harry Potter tiếng Việt', 'bo-sach-harry-potter-tieng-viet', 'Bộ 7 tập Harry Potter bản tiếng Việt, nhà xuất bản Trẻ. Sách còn mới, ít đọc, không rách hay bị gãy gáy.', 450000, 'like_new', 'active', 'Quận 4, TP.HCM', 28, 0, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(10, 2, 7, 'Bàn bi-a mini', 'ban-bi-a-mini', 'Bàn bi-a mini cho gia đình, kích thước 120x60cm. Đầy đủ phụ kiện gồm cơ, bóng, phấn. Thích hợp cho trẻ em và người lớn.', 2500000, 'good', 'active', 'Quận 6, TP.HCM', 19, 0, 1, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(12, 7, 1, 'Iphone 12311', 'iphone-123', '1231', 1231231, 'fair', 'active', '12311', 0, 0, 0, '2025-06-20 22:14:01', '2025-06-20 23:13:00'),
(14, 7, 1, '12312', '12312-1', '123', 1231231, 'new', 'active', '123', 0, 0, 1, '2025-06-26 16:39:00', '2025-06-26 16:51:51'),
(15, 7, 1, '1232', '1232', '123', 1212123, 'new', 'active', '123 2 3 trug', 0, 0, 1, '2025-06-30 22:56:47', '2025-06-30 22:57:05'),
(18, 7, 1, 'IPhone 13', 'iphone-13', 'HN', 20000200, 'new', 'active', '123', 0, 0, 1, '2025-07-01 12:09:02', '2025-07-01 14:38:56'),
(19, 7, 3, 'Áo Khoác Da Cá Sấu', '-o-kho-c-da-c-s-u', '123', 50000000, 'new', 'active', 'HN', 0, 0, 1, '2025-07-01 15:42:38', '2025-07-01 15:42:48'),
(28, 7, 1, '1212', '1212', '21321\r\nMua từ: 2025-07-13\r\nSản phẩm đính kèm: md', 123123, 'like_new', 'active', '123123', 0, 0, 1, '2025-07-02 00:04:00', '2025-07-02 11:24:21'),
(35, 7, 1, 'IP10', 'ip10', 'DICH', 100000, 'good', 'active', '1213', 0, 0, 1, '2025-07-03 12:38:29', '2025-07-03 14:58:18'),
(45, 7, 1, '123', '123', '213\r\nMua từ: 2025-07-02\r\nSản phẩm đính kèm: kc', 1231111, 'like_new', 'active', '123', 0, 0, 1, '2025-07-03 22:42:03', '2025-07-03 23:40:04'),
(46, 7, 1, '34324', '34324', '123123\r\nMua từ: 2025-07-14\r\nSản phẩm đính kèm: kc', 12313111, 'good', '', '123123', 0, 0, 1, '2025-07-03 23:41:01', '2025-07-03 23:41:46'),
(50, 7, 2, 'ádas', '-das', 'sadasd\r\nMua từ: 2025-07-10\r\nSản phẩm đính kèm: kc', 12312312, 'like_new', 'active', '1111123213', 0, 0, 1, '2025-07-04 14:32:16', '2025-07-04 17:15:58'),
(51, 7, 2, '123123', '123123', '213123231\r\nMua từ: 2025-07-05\r\nSản phẩm đính kèm: kc', 123123, 'good', '', '123123', 0, 0, 1, '2025-07-04 16:37:17', '2025-07-04 17:16:00'),
(52, 7, 2, '12312313', '12312313', '3sasad\r\nMua từ: 2025-07-15\r\nSản phẩm đính kèm: kc', 12312321, 'new', 'pending', '123213', 0, 0, 1, '2025-07-04 17:14:35', '2025-07-04 17:14:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 1, 'uploads/products/iphone12pro.jpg', 1, 1, '2025-06-20 22:10:39'),
(2, 2, 'uploads/products/samsung_s21_ultra.jpg', 1, 1, '2025-06-20 22:10:39'),
(3, 3, 'uploads/products/macbook_pro_16.jpg', 1, 1, '2025-06-20 22:10:39'),
(4, 4, 'uploads/products/dell_xps_13.jpg', 1, 1, '2025-06-20 22:10:39'),
(5, 5, 'uploads/products/ipad_pro_11.jpg', 1, 1, '2025-06-20 22:10:39'),
(6, 6, 'uploads/products/sony_a7_iii.jpg', 1, 1, '2025-06-20 22:10:39'),
(8, 18, 'uploads/products/68636d6e84179_1234.jpg', 1, 0, '2025-07-01 12:09:02'),
(9, 19, 'uploads/products/68639f7e8ad5c_OIP (1).jpg', 1, 0, '2025-07-01 15:42:38'),
(10, 19, 'uploads/products/68639f7e8b3f4_OIP (4).jpg', 0, 0, '2025-07-01 15:42:38'),
(11, 19, 'uploads/products/68639f7e8b5bd_OIP (3).jpg', 0, 0, '2025-07-01 15:42:38'),
(12, 19, 'uploads/products/68639f7e8b731_OIP (2).jpg', 0, 0, '2025-07-01 15:42:38'),
(27, 28, 'uploads/products/68641500c7b70_OIP (1).jpg', 1, 0, '2025-07-02 00:04:00'),
(28, 28, 'uploads/products/68641500c8433_OIP (6).jpg', 0, 0, '2025-07-02 00:04:00'),
(35, 35, 'uploads/products/686617552e7c8_....jpg', 1, 0, '2025-07-03 12:38:29'),
(45, 45, 'uploads/products/6866a4cba6507_... - Copy.jpg', 1, 0, '2025-07-03 22:42:03'),
(46, 46, 'uploads/products/6866b29d99314_... - Copy.jpg', 1, 0, '2025-07-03 23:41:01'),
(50, 50, 'uploads/products/686783806e0fd_... - Copy.jpg', 1, 0, '2025-07-04 14:32:16'),
(51, 51, 'uploads/products/6867a0cd1e6cc_... - Copy.jpg', 1, 0, '2025-07-04 16:37:17'),
(52, 52, 'uploads/products/6867a98b5caf5_... - Copy.jpg', 1, 0, '2025-07-04 17:14:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_status_logs`
--

CREATE TABLE `product_status_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `identifier` varchar(255) NOT NULL COMMENT 'Email hoặc IP address',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `system_notifications`
--

CREATE TABLE `system_notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `type` enum('news','announcement','maintenance','update') DEFAULT 'news',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(10) DEFAULT 'user' CHECK (`role` in ('user','admin')),
  `status` varchar(10) DEFAULT 'active' CHECK (`status` in ('active','inactive')),
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `avatar`, `role`, `status`, `last_login`, `login_attempts`, `locked_until`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@muabandocu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0123456789', 'Hà Nội', NULL, 'admin', 'active', NULL, 0, NULL, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(2, 'nguyenvana', 'nguyenvana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0987654321', '123 Đường ABC, Quận 1, TP.HCM', NULL, 'user', 'active', NULL, 0, NULL, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(3, 'tranthib', 'tranthib@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0912345678', '456 Đường XYZ, Quận 3, TP.HCM', NULL, 'user', 'active', NULL, 0, NULL, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(4, 'phamvanc', 'phamvanc@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Văn C', '0901234567', '789 Đường DEF, Quận 5, TP.HCM', NULL, 'user', 'active', NULL, 0, NULL, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(5, 'hoangthid', 'hoangthid@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoàng Thị D', '0898765432', '321 Đường GHI, Quận 7, TP.HCM', NULL, 'user', 'active', NULL, 0, NULL, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(6, 'vuvane', 'vuvane@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vũ Văn E', '0876543210', '654 Đường JKL, Quận 2, TP.HCM', NULL, 'user', 'active', NULL, 0, NULL, NULL, '2025-06-20 22:10:39', '2025-06-20 22:10:39'),
(7, 'dichvietduc', 'dvd192005@gmail.com', '$2y$10$c89goYo960yWdFxYhpawBeFrwlSJXAiG10NVUdQ9t.a4mXAQbnUwa', 'dich duc', '123123123', NULL, NULL, 'admin', 'active', '2025-07-04 13:47:12', 0, NULL, NULL, '2025-06-20 22:13:36', '2025-07-04 13:47:12'),
(8, 'dichvietduc123', 'd@gmail.com', '$2y$10$f5RxVAic4ocV7d9KSjFONOMpl/W2c26nJ.2oHWu7I3Zkv/UnLZvFK', '123213', '09123123', NULL, NULL, 'user', 'active', '2025-07-03 23:43:19', 0, NULL, NULL, '2025-07-03 23:43:09', '2025-07-03 23:43:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `box_chat`
--
ALTER TABLE `box_chat`
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_carts_user` (`user_id`),
  ADD UNIQUE KEY `ux_carts_session` (`session_id`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_cart_product` (`cart_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD KEY `box_chat_id` (`box_chat_id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `status` (`status`),
  ADD KEY `payment_status` (`payment_status`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `product_status_logs`
--
ALTER TABLE `product_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Chỉ mục cho bảng `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action_identifier` (`action`,`identifier`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Chỉ mục cho bảng `system_notifications`
--
ALTER TABLE `system_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_created` (`status`,`created_at`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- Chỉ mục cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT cho bảng `product_status_logs`
--
ALTER TABLE `product_status_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `system_notifications`
--
ALTER TABLE `system_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `box_chat`
--
ALTER TABLE `box_chat`
  ADD CONSTRAINT `box_chat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`box_chat_id`) REFERENCES `box_chat` (`user_id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_status_logs`
--
ALTER TABLE `product_status_logs`
  ADD CONSTRAINT `product_status_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_status_logs_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
CREATE TABLE review_products (
    id INT NOT NULL,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    content TEXT,
    sent_at DATETIME,
    username VARCHAR(50),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE box_chat( user_id INT NOT NULL, is_read TINYINT(1) DEFAULT 0, FOREIGN KEY (user_id) REFERENCES users(id) );

