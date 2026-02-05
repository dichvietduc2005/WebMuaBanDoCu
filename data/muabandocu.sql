-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th1 11, 2026 lúc 06:54 AM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

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

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_action_logs`
--

DROP TABLE IF EXISTS `admin_action_logs`;
CREATE TABLE IF NOT EXISTS `admin_action_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin` (`admin_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_action_created` (`action`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_action_logs`
--

INSERT INTO `admin_action_logs` (`id`, `admin_id`, `action`, `product_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'product_delete', 23, '{\"old_status\":\"pending\",\"reason\":null,\"new_status\":\"deleted\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2025-12-19 17:33:10'),
(2, 1, 'product_delete', 21, '{\"old_status\":\"active\",\"reason\":null,\"new_status\":\"deleted\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2025-12-19 18:17:04'),
(3, 1, 'product_toggle_featured', 24, '{\"old_status\":\"pending\",\"reason\":null,\"old_featured\":0,\"new_featured\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2025-12-19 18:55:24'),
(4, 1, 'product_approve', 24, '{\"old_status\":\"pending\",\"reason\":null,\"new_status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2025-12-19 18:55:35'),
(5, 1, 'product_delete', 24, '{\"old_status\":\"active\",\"reason\":null,\"new_status\":\"deleted\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2025-12-19 21:55:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_banner_images`
--

DROP TABLE IF EXISTS `admin_banner_images`;
CREATE TABLE IF NOT EXISTS `admin_banner_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `animation_type` enum('fade','slide','zoom','none') COLLATE utf8mb4_unicode_ci DEFAULT 'fade',
  `transition_duration` int DEFAULT '500',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_banner_images`
--

INSERT INTO `admin_banner_images` (`id`, `image_path`, `title`, `description`, `event_type`, `is_active`, `sort_order`, `animation_type`, `transition_duration`, `created_at`, `updated_at`) VALUES
(5, '/public/uploads/banners/banner_1766598991_694c294fd45fd.png', 'anh1', NULL, 'default', 1, 0, 'fade', 500, '2025-12-25 00:56:31', '2025-12-25 02:35:39'),
(6, '/public/uploads/banners/banner_1766599007_694c295f3adb4.png', 'anh2', NULL, 'default', 1, 1, 'fade', 500, '2025-12-25 00:56:47', '2025-12-25 02:35:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_theme_events`
--

DROP TABLE IF EXISTS `admin_theme_events`;
CREATE TABLE IF NOT EXISTS `admin_theme_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `theme_config` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_dates` (`start_date`,`end_date`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_theme_settings`
--

DROP TABLE IF EXISTS `admin_theme_settings`;
CREATE TABLE IF NOT EXISTS `admin_theme_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('color','image','text','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_theme_settings`
--

INSERT INTO `admin_theme_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'primary_color', '#006400', 'color', NULL, '2025-12-24 20:26:28', '2026-01-09 09:41:38'),
(2, 'secondary_color', '#c8102e', 'color', NULL, '2025-12-24 20:26:28', '2026-01-09 09:41:38'),
(3, 'accent_color', '#ffd700', 'color', NULL, '2025-12-24 20:26:28', '2026-01-09 09:41:38'),
(4, 'background_color', '#ffffff', 'color', NULL, '2025-12-24 20:26:28', '2026-01-09 09:41:38'),
(5, 'text_color', '#1f2937', 'color', NULL, '2025-12-24 20:26:28', '2026-01-09 09:41:38'),
(6, 'sidebar_bg', '#ffffff', 'color', NULL, '2025-12-24 20:26:28', '2026-01-09 09:41:38'),
(7, 'header_bg', '#ffffff', 'color', NULL, '2025-12-24 20:26:28', '2026-01-09 09:41:38'),
(8, 'enable_banner', '0', 'boolean', NULL, '2025-12-24 20:26:28', '2025-12-25 18:32:50'),
(9, 'banner_height', '200', 'text', NULL, '2025-12-24 20:26:28', '2025-12-25 18:32:50'),
(10, 'animation_enabled', '1', 'boolean', NULL, '2025-12-24 20:26:28', '2025-12-25 18:32:50'),
(11, 'current_event', 'default', 'text', 'Sự kiện hiện tại', '2025-12-24 20:26:28', '2025-12-24 20:26:28'),
(30, 'background_image', 'public/uploads/backgrounds/background_1766591811_694c0d4306f81.png', 'text', NULL, '2025-12-24 22:56:51', '2025-12-24 22:57:35'),
(42, 'hero_background_image', 'public/uploads/hero/hero_bg_1766604803_694c400336e6a.png', 'image', NULL, '2025-12-25 00:26:56', '2025-12-25 02:33:23'),
(51, 'hero_title', 'Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường', 'text', NULL, '2025-12-25 00:27:24', '2025-12-25 00:27:24'),
(52, 'hero_subtitle', 'Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý. Hàng ngàn sản phẩm chất lượng đang chờ bạn!', 'text', NULL, '2025-12-25 00:27:24', '2025-12-25 00:27:24'),
(53, 'hero_button1_text', 'Mua sắm ngay', 'text', NULL, '2025-12-25 00:27:24', '2025-12-25 00:27:24'),
(54, 'hero_button2_text', 'Đăng bán đồ', 'text', NULL, '2025-12-25 00:27:24', '2025-12-25 00:27:24'),
(104, 'website_background', '', 'image', NULL, '2025-12-25 01:10:15', '2025-12-25 18:32:47'),
(155, 'active_preset_id', '2', 'text', NULL, '2025-12-25 03:09:26', '2026-01-09 09:41:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `box_chat`
--

DROP TABLE IF EXISTS `box_chat`;
CREATE TABLE IF NOT EXISTS `box_chat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_box_chat_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `box_chat`
--

INSERT INTO `box_chat` (`id`, `user_id`, `is_read`) VALUES
(1, 1, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '2025-07-05 13:50:51', '2025-07-05 13:50:51'),
(2, 2, NULL, '2025-12-19 19:34:46', '2025-12-19 19:34:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_price` decimal(15,0) NOT NULL,
  `condition_snapshot` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `is_hidden` tinyint(1) DEFAULT '0',
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_cart_product` (`cart_id`,`product_id`),
  KEY `fk_cart_items_product` (`product_id`),
  KEY `idx_cart_items_status_hidden` (`status`,`is_hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cart items with status tracking - active/sold and visibility control';

--
-- Đang đổ dữ liệu cho bảng `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `added_price`, `condition_snapshot`, `status`, `is_hidden`, `added_at`, `updated_at`) VALUES
(24, 1, 5, 1, 299000, 'new', 'sold', 1, '2025-07-06 04:17:33', '2025-07-06 04:24:20'),
(27, 2, 22, 1, 160000, 'new', 'active', 0, '2025-12-19 19:34:46', '2025-12-19 19:34:46'),
(28, 2, 20, 1, 6700000, 'new', 'active', 0, '2025-12-19 19:34:50', '2025-12-19 19:34:50'),
(35, 2, 10, 1, 80000, 'new', 'active', 0, '2026-01-09 07:57:20', '2026-01-09 07:57:20'),
(39, 1, 9, 1, 300000, 'new', 'sold', 1, '2026-01-11 08:32:30', '2026-01-11 13:19:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Điện thoại & Máy tính bảng', 'dien-thoai-may-tinh-bang', 'Điện thoại di động, smartphone, máy tính bảng các loại', NULL, 'active', '2025-07-05 10:56:44'),
(2, 'Laptop & Máy tính', 'laptop-may-tinh', 'Laptop, máy tính để bàn, linh kiện máy tính', NULL, 'active', '2025-07-05 10:56:44'),
(3, 'Thời trang & Phụ kiện', 'thoi-trang-phu-kien', 'Quần áo, giày dép, túi xách, phụ kiện thời trang', NULL, 'active', '2025-07-05 10:56:44'),
(4, 'Đồ gia dụng & Nội thất', 'do-gia-dung-noi-that', 'Đồ gia dụng, nội thất, đồ trang trí nhà cửa', NULL, 'active', '2025-07-05 10:56:44'),
(5, 'Xe cộ & Phương tiện', 'xe-co-phuong-tien', 'Xe máy, xe đạp, ô tô và phụ kiện xe', NULL, 'active', '2025-07-05 10:56:44'),
(6, 'Sách & Văn phòng phẩm', 'sach-van-phong-pham', 'Sách, truyện, văn phòng phẩm, dụng cụ học tập', NULL, 'active', '2025-07-05 10:56:44'),
(7, 'Thể thao & Giải trí', 'the-thao-giai-tri', 'Dụng cụ thể thao, đồ chơi, thiết bị giải trí', NULL, 'active', '2025-07-05 10:56:44'),
(8, 'Điện máy & Công nghệ', 'dien-may-cong-nghe', 'Tivi, tủ lạnh, máy giặt, thiết bị điện tử', NULL, 'active', '2025-07-05 10:56:44'),
(9, 'Mẹ và bé', 'me-va-be', 'Đồ dùng cho mẹ và bé, đồ chơi trẻ em, quần áo trẻ em', NULL, 'active', '2025-07-05 10:56:44'),
(10, 'Âm nhạc & Nhạc cụ', 'am-nhac-nhac-cu', 'Nhạc cụ, phụ kiện âm nhạc, thiết bị phòng thu', NULL, 'active', '2025-07-05 10:56:44'),
(11, 'Sức khỏe & Làm đẹp', 'suc-khoe-lam-dep', 'Mỹ phẩm, thiết bị chăm sóc sức khỏe, thực phẩm chức năng', NULL, 'active', '2025-07-05 10:56:44'),
(12, 'Thú cưng & Phụ kiện', 'thu-cung-phu-kien', 'Đồ dùng, thức ăn, phụ kiện cho thú cưng', NULL, 'active', '2025-07-05 10:56:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL,
  `max_discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_order_value` decimal(10,2) DEFAULT '0.00',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `usage_limit` int DEFAULT '0',
  `used_count` int DEFAULT '0',
  `status` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `max_discount_amount`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `used_count`, `status`, `created_at`) VALUES
(1, '6M53Q', 'percent', 10.00, 0.00, 200000.00, '2025-12-19 18:42:00', '2025-12-20 18:42:00', 100, 0, 1, '2025-12-19 11:42:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `box_chat_id` int NOT NULL,
  `role` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_messages_box` (`box_chat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `box_chat_id`, `role`, `content`, `sent_at`) VALUES
(1, 1, 'user', 'hui', '2025-07-05 11:50:12'),
(2, 1, 'admin', 'hi', '2025-07-05 11:51:24'),
(3, 1, 'user', 'hello', '2025-07-05 12:05:57'),
(4, 1, 'admin', 'chào em', '2025-07-05 12:06:10'),
(5, 1, 'admin', '', '2025-07-05 12:06:18'),
(6, 1, 'user', 'ccc', '2025-07-05 12:07:14'),
(7, 1, 'admin', 'aa', '2025-07-05 12:08:48'),
(8, 1, 'user', 'aaaa', '2025-07-05 12:12:35'),
(9, 1, 'admin', 'cc', '2025-07-05 16:31:09'),
(10, 1, 'user', 'ca', '2025-07-05 16:31:43'),
(11, 1, 'user', 'aaaa', '2025-07-06 03:18:36'),
(12, 1, 'user', 'aaa', '2025-07-06 03:32:52'),
(13, 1, 'user', 'aa', '2025-07-06 23:25:26'),
(14, 1, 'admin', 'hi', '2025-12-19 17:03:38'),
(15, 1, 'admin', 'sao', '2025-12-19 17:20:05'),
(16, 1, 'admin', 'hello', '2025-12-19 17:28:01'),
(17, 1, 'admin', 'hello', '2025-12-19 17:34:21'),
(18, 1, 'admin', 'hi', '2025-12-19 17:43:49'),
(19, 1, 'admin', 'hi', '2025-12-19 17:52:38'),
(20, 1, 'admin', 'hi', '2025-12-19 18:01:32'),
(21, 1, 'user', 'vcl chat', '2025-12-24 20:05:23'),
(22, 1, 'admin', 'oke r', '2025-12-25 17:05:49'),
(23, 1, 'user', 'test standard chat', '2025-12-25 17:57:55'),
(24, 1, 'user', 'Hello test message', '2025-12-25 18:43:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notifications_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, '', 'Sản phẩm <b>IPhone 11</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-06-19 18:39:39'),
(2, 1, '', 'Sản phẩm <b>iPad Pro 11 inch 2021</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-06-19 18:45:09'),
(3, 1, '', 'Sản phẩm <b>123</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-06-19 18:46:33'),
(4, 1, '', 'Sản phẩm <b>IPhone 11</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-06-20 22:07:19'),
(5, 1, '', 'Sản phẩm <b>Iphone 123</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-06-20 22:15:56'),
(6, 1, '', 'Sản phẩm <b>Iphone 123</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-06-20 22:15:56'),
(7, 1, '', 'Sản phẩm <b>12312</b> của bạn đã bị admin xóa khỏi hệ thống.', 'admin', 1, '2025-06-26 16:51:31'),
(8, 1, '', 'Sản phẩm <b>12312</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-06-26 16:51:51'),
(9, 1, '', 'Sản phẩm <b>12312</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-06-26 16:51:51'),
(10, 1, '', 'Sản phẩm <b>1232</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-06-30 22:57:05'),
(11, 1, '', 'Sản phẩm <b>IPhone 13</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-07-01 14:38:56'),
(12, 1, '', 'Sản phẩm <b>Áo Khoác Da Cá Sấu</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-07-01 15:42:48'),
(13, 1, '', 'Sản phẩm <b>Xe đạp thể thao</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-07-01 22:51:29'),
(14, 1, '', 'Sản phẩm <b>1212</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-07-02 11:24:21'),
(15, 1, '', 'Sản phẩm <b>IP10</b> của bạn đã được admin duyệt và đăng bán.', 'admin', 1, '2025-07-03 14:58:18'),
(16, 1, '', 'Sản phẩm <b>hsahd</b> của bạn đã được duyệt', 'admin', 1, '2025-07-03 23:35:17'),
(17, 1, '', 'Sản phẩm <b>hsahd</b> của bạn đã bị từ chối', 'admin', 1, '2025-07-03 23:35:21'),
(18, 1, '', 'Sản phẩm <b>hsahd</b> của bạn đã bị xóa', 'admin', 1, '2025-07-03 23:35:24'),
(19, 1, '', 'Sản phẩm <b>123</b> của bạn đã được duyệt', 'admin', 1, '2025-07-03 23:40:04'),
(20, 1, '', 'Sản phẩm <b>34324</b> của bạn đã bị từ chối', 'admin', 1, '2025-07-03 23:41:46'),
(21, 1, '', 'Sản phẩm <b>seqads</b> của bạn đã bị xóa', 'admin', 1, '2025-07-03 23:41:51'),
(22, 1, '', 'Sản phẩm <b>DICH </b> của bạn đã được duyệt', 'admin', 1, '2025-07-04 00:10:56'),
(23, 1, '', 'Sản phẩm <b>iisaidi</b> của bạn đã được duyệt', 'admin', 1, '2025-07-04 14:25:51'),
(24, 1, '', 'Sản phẩm <b>ádas</b> của bạn đã được duyệt', 'admin', 1, '2025-07-04 17:15:58'),
(25, 1, '', 'Sản phẩm <b>123123</b> của bạn đã bị từ chối', 'admin', 1, '2025-07-04 17:16:00'),
(26, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 11:38:59'),
(27, 1, '', 'Sản phẩm <b>Áo nike</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 13:51:55'),
(28, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 13:58:30'),
(29, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 13:58:32'),
(30, 1, '', 'Sản phẩm <b>Áo nike</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 13:59:55'),
(31, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 16:32:27'),
(32, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được bỏ khỏi danh sách nổi bật', 'admin', 1, '2025-07-05 16:32:29'),
(33, 1, '', 'Sản phẩm \'Iphone 14\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 16:50:37'),
(34, 1, '', 'Sản phẩm \'Iphone 14\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 16:50:39'),
(35, 1, '', 'Sản phẩm \'Iphone 12 Pro Max\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 16:50:41'),
(36, 1, '', 'Sản phẩm \'Iphone 12 Pro Max\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 16:50:41'),
(37, 1, '', 'Sản phẩm \'Xe Dream LD \' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:00'),
(38, 1, '', 'Sản phẩm \'Gà Xám Mỹ Cuban\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:02'),
(39, 1, '', 'Sản phẩm \'Gà Xám Mỹ Cuban\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:05'),
(40, 1, '', 'Sản phẩm \'Kính Hiệu RayBan\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:06'),
(41, 1, '', 'Sản phẩm \'Kính Hiệu RayBan\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:07'),
(42, 1, '', 'Sản phẩm \'Attack on Titan 28 Đặc Biệt\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:08'),
(43, 1, '', 'Sản phẩm \'Attack on Titan 28 Đặc Biệt\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:08'),
(44, 1, '', 'Sản phẩm \'Ghế tập ngồi cho bé ăn dặm cao cấp\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:10'),
(45, 1, '', 'Sản phẩm \'Ghế tập ngồi cho bé ăn dặm cao cấp\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:10'),
(46, 1, '', 'Sản phẩm \'Laptop Dell Inspiron Core i5 4210\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:11'),
(47, 1, '', 'Sản phẩm \'Laptop Dell Inspiron Core i5 4210\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:11'),
(48, 1, '', 'Sản phẩm \'Bàn Ghế Gỗ\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:15'),
(49, 1, '', 'Sản phẩm \'Bàn Ghế Gỗ\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:15'),
(50, 1, '', 'Sản phẩm \'Quạt Toshiba\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:16'),
(51, 1, '', 'Sản phẩm \'Quạt Toshiba\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:18'),
(52, 1, '', 'Sản phẩm \'Đàn Guitar\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 18:15:19'),
(53, 1, '', 'Sản phẩm \'Đàn Guitar\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 18:15:19'),
(54, 1, '', 'Sản phẩm \'Gà Xám Mỹ Cuban\' của bạn đã được bỏ khỏi danh sách nổi bật', 'admin', 1, '2025-07-05 18:59:34'),
(55, 1, '', 'Sản phẩm \'Quạt Toshiba\' của bạn đã được bỏ khỏi danh sách nổi bật', 'admin', 1, '2025-07-05 18:59:38'),
(56, 1, '', 'Sản phẩm \'Xe Dream LD \' của bạn đã được duyệt', 'admin', 1, '2025-07-05 19:10:06'),
(57, 1, '', 'Sản phẩm <b>Xe Dream LD </b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 19:12:09'),
(58, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 19:22:44'),
(59, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 19:22:45'),
(60, 1, '', 'Sản phẩm <b>Áo nike</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 19:24:22'),
(61, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 19:47:29'),
(62, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 19:47:29'),
(63, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 19:47:30'),
(64, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 19:47:30'),
(65, 1, '', 'Sản phẩm <b>Áo nike</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 19:48:09'),
(66, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-05 19:58:17'),
(67, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 19:58:18'),
(68, 1, '', 'Sản phẩm <b>Áo nike</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 19:59:33'),
(69, 1, '', 'Sản phẩm <b>Áo nike</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 20:11:11'),
(70, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-05 20:20:49'),
(71, 1, '', 'Sản phẩm <b>Áo nike</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-05 20:21:26'),
(72, 1, '', 'Sản phẩm \'Kính Hiệu RayBan\' của bạn đã bị xóa', 'admin', 1, '2025-07-05 23:53:58'),
(73, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã bị xóa', 'admin', 1, '2025-07-06 00:46:19'),
(74, 1, '', 'Sản phẩm \'Xe Dream LD \' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-06 01:17:19'),
(75, 1, '', 'Sản phẩm \'Xe Dream LD \' của bạn đã được duyệt', 'admin', 1, '2025-07-06 01:17:20'),
(76, 1, '', 'Sản phẩm <b>Đàn Guitar</b> của bạn đã được bán và thanh toán thành công!', 'admin', 1, '2025-07-06 04:24:20'),
(77, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-06 11:54:45'),
(78, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã được duyệt', 'admin', 1, '2025-07-06 11:54:46'),
(79, 1, '', 'Sản phẩm \'Attack on Titan từ vol 29-32\' của bạn đã được duyệt', 'admin', 1, '2025-07-07 01:33:04'),
(80, 1, '', 'Sản phẩm \'Attack on Titan từ vol 29-32\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-07-07 01:33:12'),
(81, 1, '', 'Sản phẩm \'a\' của bạn đã bị xóa', 'admin', 1, '2025-12-19 17:33:10'),
(82, 1, '', 'Sản phẩm \'Áo nike\' của bạn đã bị xóa', 'admin', 1, '2025-12-19 18:17:04'),
(83, 2, '', 'Sản phẩm \'aaaaaaaaaaaaaaaaaaaaaaaa\' của bạn đã được đặt làm sản phẩm nổi bật', 'admin', 1, '2025-12-19 18:55:24'),
(84, 2, '', 'Sản phẩm \'aaaaaaaaaaaaaaaaaaaaaaaa\' của bạn đã được duyệt', 'admin', 1, '2025-12-19 18:55:35'),
(85, NULL, 'aaaaa', 'aaaaaaaa', 'admin', 0, '2025-12-19 19:09:23'),
(86, NULL, 'aaaaaavvvbbbbbb', 'aabbbb', 'admin', 0, '2025-12-19 19:19:57'),
(87, 1, 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào Nguyenthinh_3103, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1, '2025-12-19 19:52:39'),
(88, 2, 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào Nguyenthinh_3101111, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1, '2025-12-19 19:52:39'),
(89, 1, 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào Nguyenthinh_3103, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1, '2025-12-19 19:52:39'),
(90, 2, 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào Nguyenthinh_3101111, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1, '2025-12-19 19:52:39'),
(91, 1, 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào Nguyenthinh_3103, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1, '2025-12-19 19:52:39'),
(92, 2, 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào Nguyenthinh_3101111, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1, '2025-12-19 19:52:39'),
(93, 2, '', 'Sản phẩm \'aaaaaaaaaaaaaaaaaaaaaaaa\' của bạn đã bị xóa', 'admin', 0, '2025-12-19 21:55:40'),
(94, 1, '', 'Sản phẩm <b>Ghế tập ngồi cho bé ăn dặm cao cấp</b> của bạn đã được bán và thanh toán thành công!', 'admin', 0, '2026-01-11 13:19:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notification_queue`
--

DROP TABLE IF EXISTS `notification_queue`;
CREATE TABLE IF NOT EXISTS `notification_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `template_code` varchar(50) NOT NULL,
  `data` json DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `notification_queue`
--

INSERT INTO `notification_queue` (`id`, `user_id`, `template_code`, `data`, `scheduled_at`, `sent_at`, `status`, `created_at`) VALUES
(1, 1, 'cart_abandoned', '{\"cart_id\": 1, \"username\": \"Nguyenthinh_3103\", \"item_count\": 3}', '2025-12-19 19:49:51', '2025-12-19 19:52:39', 'sent', '2025-12-19 12:49:51'),
(2, 2, 'cart_abandoned', '{\"cart_id\": 2, \"username\": \"Nguyenthinh_3101111\", \"item_count\": 2}', '2025-12-19 19:49:51', '2025-12-19 19:52:39', 'sent', '2025-12-19 12:49:51'),
(3, 1, 'cart_abandoned', '{\"cart_id\": 1, \"username\": \"Nguyenthinh_3103\", \"item_count\": 3}', '2025-12-19 19:49:58', '2025-12-19 19:52:39', 'sent', '2025-12-19 12:49:58'),
(4, 2, 'cart_abandoned', '{\"cart_id\": 2, \"username\": \"Nguyenthinh_3101111\", \"item_count\": 2}', '2025-12-19 19:49:58', '2025-12-19 19:52:39', 'sent', '2025-12-19 12:49:58'),
(5, 1, 'cart_abandoned', '{\"cart_id\": 1, \"username\": \"Nguyenthinh_3103\", \"item_count\": 3}', '2025-12-19 19:52:36', '2025-12-19 19:52:39', 'sent', '2025-12-19 12:52:36'),
(6, 2, 'cart_abandoned', '{\"cart_id\": 2, \"username\": \"Nguyenthinh_3101111\", \"item_count\": 2}', '2025-12-19 19:52:36', '2025-12-19 19:52:39', 'sent', '2025-12-19 12:52:36'),
(7, 2, 'cart_abandoned', '{\"count\": 2, \"cart_id\": 2, \"username\": \"Nguyenthinh_3101111\"}', '2025-12-28 07:35:52', NULL, 'pending', '2025-12-28 00:35:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notification_settings`
--

DROP TABLE IF EXISTS `notification_settings`;
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `template_code` varchar(50) NOT NULL,
  `trigger_condition` varchar(100) NOT NULL,
  `delay_hours` int DEFAULT '0',
  `is_enabled` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `template_code` (`template_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notification_templates`
--

DROP TABLE IF EXISTS `notification_templates`;
CREATE TABLE IF NOT EXISTS `notification_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message_template` text NOT NULL,
  `type` enum('manual','cart','order','product','marketing','system') DEFAULT 'manual',
  `is_active` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `code`, `title`, `message_template`, `type`, `is_active`, `created_at`) VALUES
(1, 'cart_abandoned', 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào {username}, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1, '2025-12-19 12:52:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buyer_id` int NOT NULL,
  `total_amount` decimal(15,0) NOT NULL,
  `status` enum('pending','success','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` enum('vnpay','bank_transfer','cod') COLLATE utf8mb4_unicode_ci DEFAULT 'vnpay',
  `payment_status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `vnpay_transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vnpay_response_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_orders_buyer` (`buyer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `total_amount`, `status`, `payment_method`, `payment_status`, `notes`, `vnpay_transaction_id`, `vnpay_response_code`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20250705-6868CB51AD789', 1, 1000000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: 81A, Đường Huỳnh Thị Hai, Phường Tân Chánh Hiệp, Quận 12, ccc\nSĐT người nhận: nguyenthinhk520', NULL, NULL, '2025-07-05 13:51:04', '2025-07-05 13:51:55'),
(2, 'ORD-20250705-6868CD396DFC4', 1, 1000000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: 81A, Đường Huỳnh Thị Hai, Phường Tân Chánh Hiệp, Quận 12, ccc\nSĐT người nhận: nguyenthinhk520', NULL, NULL, '2025-07-05 13:59:17', '2025-07-05 13:59:55'),
(3, 'ORD-20250705-68691646D9274', 1, 6700000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, TP. Hồ Chí Minh\nSĐT người nhận: 0123213123', NULL, NULL, '2025-07-05 19:10:51', '2025-07-05 19:12:09'),
(4, 'ORD-20250705-68691955BC988', 1, 1000000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, TP. Hồ Chí Minh\nSĐT người nhận: 0123213123', NULL, NULL, '2025-07-05 19:23:53', '2025-07-05 19:24:22'),
(5, 'ORD-20250705-68691EEDE51FE', 1, 1000000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, Hà Nội\nSĐT người nhận: 0123213123', NULL, NULL, '2025-07-05 19:47:46', '2025-07-05 19:48:09'),
(7, 'ORD-20250705-686921A0B0EE4', 1, 1000000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, TP. Hồ Chí Minh\nSĐT người nhận: 0123213123', NULL, NULL, '2025-07-05 19:59:16', '2025-07-05 19:59:33'),
(8, 'ORD-20250705-68692453C4666', 1, 1000000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, TP. Hồ Chí Minh\nSĐT người nhận: 0123213123', NULL, NULL, '2025-07-05 20:10:49', '2025-07-05 20:11:11'),
(9, 'ORD-20250705-686926B940B61', 1, 1000000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, TP. Hồ Chí Minh\nSĐT người nhận: 0123213123', NULL, NULL, '2025-07-05 20:21:00', '2025-07-05 20:21:26'),
(10, 'ORD-20250706-686996716EDF1', 1, 299000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, TP. Hồ Chí Minh\nSĐT người nhận: 0123213123', NULL, NULL, '2025-07-06 04:23:59', '2025-07-06 04:24:20'),
(11, 'ORD-20251219-6945728859E82', 1, 380000, 'pending', 'vnpay', 'pending', 'Thanh toan don hang\nĐịa chỉ giao hàng: sad, aaaa\nSĐT người nhận: 0123213123', NULL, NULL, '2025-12-19 22:43:20', '2025-12-19 22:43:20'),
(12, 'ORD-20260111-69632D8DBD1C2', 1, 20300000, 'pending', 'vnpay', 'pending', 'Thanh toan don hang\nĐịa chỉ giao hàng: 46A, TP. Hồ Chí Minh\nSĐT người nhận: 0945553902', NULL, NULL, '2026-01-11 11:56:53', '2026-01-11 11:56:53'),
(13, 'ORD-20260111-69632E102F7B7', 1, 20300000, 'pending', 'vnpay', 'pending', 'Thanh toan don hang\nĐịa chỉ giao hàng: 46A, TP. Hồ Chí Minh\nSĐT người nhận: 0945553902', NULL, NULL, '2026-01-11 11:59:00', '2026-01-11 11:59:00'),
(14, 'ORD-20260111-6963310711CC7', 1, 20300000, 'failed', 'vnpay', 'failed', 'Thanh toan don hang\nĐịa chỉ giao hàng: 46A, Hà Nội\nSĐT người nhận: 0945553902', NULL, NULL, '2026-01-11 12:11:43', '2026-01-11 12:13:38'),
(15, 'ORD-20260111-6963406B10B8F', 1, 300000, 'success', 'vnpay', 'paid', 'Thanh toan don hang\nĐịa chỉ giao hàng: 46A, Xã Cuôr Đăng, Tỉnh Đắk Lắk, Tỉnh Đắk Lắk\nSĐT người nhận: 0945553902', NULL, NULL, '2026-01-11 13:17:21', '2026-01-11 13:19:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_price` decimal(15,0) NOT NULL,
  `quantity` int NOT NULL,
  `subtotal` decimal(15,0) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order` (`order_id`),
  KEY `idx_order_items_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Order items with product reference that can be NULL if product is deleted by admin';

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_title`, `product_price`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 'Áo nike', 1000000, 1, 1000000, '2025-07-05 13:51:04'),
(2, 2, 2, 'Áo nike', 1000000, 1, 1000000, '2025-07-05 13:59:17'),
(3, 3, 14, 'Xe Dream LD ', 6700000, 1, 6700000, '2025-07-05 19:10:51'),
(4, 4, 15, 'Áo nike', 1000000, 1, 1000000, '2025-07-05 19:23:53'),
(5, 5, NULL, 'Áo nike', 1000000, 1, 1000000, '2025-07-05 19:47:46'),
(7, 7, 18, 'Áo nike', 1000000, 1, 1000000, '2025-07-05 19:59:16'),
(8, 8, 16, 'Áo nike', 1000000, 1, 1000000, '2025-07-05 20:10:49'),
(9, 9, NULL, 'Áo nike', 1000000, 1, 1000000, '2025-07-05 20:21:00'),
(10, 10, 5, 'Đàn Guitar', 299000, 1, 299000, '2025-07-06 04:23:59'),
(11, 11, 10, 'Attack on Titan 28 Đặc Biệt', 80000, 1, 80000, '2025-12-19 22:43:20'),
(12, 11, 9, 'Ghế tập ngồi cho bé ăn dặm cao cấp', 300000, 1, 300000, '2025-12-19 22:43:20'),
(13, 12, 3, 'Iphone 12 Pro Max', 20000000, 1, 20000000, '2026-01-11 11:56:53'),
(14, 12, 9, 'Ghế tập ngồi cho bé ăn dặm cao cấp', 300000, 1, 300000, '2026-01-11 11:56:53'),
(15, 13, 3, 'Iphone 12 Pro Max', 20000000, 1, 20000000, '2026-01-11 11:59:00'),
(16, 13, 9, 'Ghế tập ngồi cho bé ăn dặm cao cấp', 300000, 1, 300000, '2026-01-11 11:59:00'),
(17, 14, 3, 'Iphone 12 Pro Max', 20000000, 1, 20000000, '2026-01-11 12:11:43'),
(18, 14, 9, 'Ghế tập ngồi cho bé ăn dặm cao cấp', 300000, 1, 300000, '2026-01-11 12:11:43'),
(19, 15, 9, 'Ghế tập ngồi cho bé ăn dặm cao cấp', 300000, 1, 300000, '2026-01-11 13:17:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(3, 'nguyenthinhk52005@gmail.com', '2ac533242b6064dbddd6c1cba0e59a6a9c2d2a46eb77fdddd806ba95c1584dbd', '2025-07-05 17:01:58', '2025-07-05 16:01:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,0) NOT NULL,
  `condition_status` enum('new','like_new','good','fair','poor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','active','reject','sold') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `views` int DEFAULT '0',
  `featured` tinyint(1) DEFAULT '0',
  `stock_quantity` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_products_user` (`user_id`),
  KEY `fk_products_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `user_id`, `category_id`, `title`, `slug`, `description`, `price`, `condition_status`, `status`, `location`, `views`, `featured`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 8, 'Áo nike', 'a-o-nike', 'oke\r\nMua từ: 2019-11-11\r\nSản phẩm đính kèm: đầy đủ', 1000000, 'like_new', 'active', 'hồ chí minh', 0, 0, 0, '2025-07-05 11:28:10', '2025-07-05 19:55:49'),
(2, 1, 3, 'Áo nike', 'a-o-nike-1', 'aw\r\nMua từ: 1111-11-11\r\nSản phẩm đính kèm: aw', 1000000, 'like_new', 'active', 'acr', 0, 0, 0, '2025-07-05 13:58:09', '2025-07-05 19:58:17'),
(3, 1, 1, 'Iphone 12 Pro Max', 'iphone-12-pro-max', 'Máy còn nguyên zin, hoạt động ổn định mọi chức năng.\r\nCó vài vết xước dăm nhỏ do sử dụng bình thường, không ảnh hưởng hiệu năng.\r\nMua từ: 2019-04-19\r\nSản phẩm đính kèm: Đầy đủ', 20000000, 'fair', 'active', 'Hà Nội', 0, 0, 1, '2025-07-05 16:46:14', '2025-07-07 01:33:12'),
(4, 1, 1, 'Iphone 14', 'iphone-14', 'mới 100% nguyên seal, màu sắc sang trọng, hiệu năng mạnh mẽ với chip A15 Bionic. Màn hình Super Retina XDR sắc nét, camera kép hỗ trợ chụp ảnh chân thực, quay video chất lượng cao. Thiết kế hiện đại, dung lượng pin tốt, hỗ trợ 5G, Face ID mượt mà.\r\nMua từ: 2020-04-19\r\nSản phẩm đính kèm: Đầy đủ', 13000000, 'new', 'active', 'Hà Nội', 0, 1, 1, '2025-07-05 16:50:21', '2025-07-05 16:50:39'),
(5, 1, 10, 'Đàn Guitar', '-a-n-guitar', 'Nhạc cụ truyền thống, Gỗ mun, 6 dây, Hình dáng guitar\r\nAuditorium\r\nMua từ: 2025-07-01\r\nSản phẩm đính kèm: Không có', 299000, 'new', 'sold', 'hồ chí minh', 0, 1, 0, '2025-07-05 17:38:50', '2025-07-06 04:24:20'),
(6, 1, 8, 'Quạt Toshiba', 'qua-t-toshiba', 'Dọn nhà cần thanh lý quạt tohiba còn hoạt động tốt, ai có nhu cầu mua lh cảm ơn đã xem tin\r\nMua từ: 2024-02-05\r\nSản phẩm đính kèm: ổn áp điện', 400000, 'good', 'active', 'Vũng Tàu', 0, 0, 1, '2025-07-05 17:46:19', '2025-07-05 18:59:38'),
(7, 1, 4, 'Bàn Ghế Gỗ', 'ba-n-gh-g-', 'Mình có bộ bàn ghế salông bằng gỗ y như hình nay chuyển nhà cần nhượng lại cho ace nào đang cần nhé\r\nMua từ: 2024-01-05\r\nSản phẩm đính kèm: Không có', 500000, 'like_new', 'active', 'hồ chí minh', 0, 1, 1, '2025-07-05 17:50:47', '2025-07-05 18:15:15'),
(8, 1, 2, 'Laptop Dell Inspiron Core i5 4210', 'laptop-dell-inspiron-core-i5-4210', 'Laptop Dell Inspiron 3542 Core i5 4210U / Ram 8G/ SSD 128G\r\nLaptop cơ bản tốt, mọi chức năng hoạt động ổn định\r\nMua từ: 2023-03-05\r\nSản phẩm đính kèm: sạc kèm theo máy', 2450000, 'like_new', 'active', 'Kiên Giang', 0, 1, 1, '2025-07-05 17:52:54', '2025-07-05 18:15:11'),
(9, 1, 9, 'Ghế tập ngồi cho bé ăn dặm cao cấp', 'gh-t-p-ng-i-cho-b-n-d-m-cao-c-p', 'Ghế tập ăn dặm cho bé ngồi\r\ncó 4 nấc tháo lắp, có khay ăn và tháo vệ sinh dễ dàng\r\n\"HÀNG MỚI NGUYÊN KIỆN\"\r\nMua từ: 2023-03-10\r\nSản phẩm đính kèm: Không có', 300000, 'new', 'sold', 'Kiên Giang', 0, 1, 0, '2025-07-05 17:56:28', '2026-01-11 13:19:17'),
(10, 1, 6, 'Attack on Titan 28 Đặc Biệt', 'attack-on-titan-28-c-bi-t', 'Nhà xuất bản: NXB Trẻ\r\nTác giả: Hajime Isayama\r\nLoại bìa: Bìa Mềm\r\nMua từ: 2025-05-08\r\nSản phẩm đính kèm: Quà tặng đế lót ly', 80000, 'new', 'active', 'hồ chí minh', 0, 1, 1, '2025-07-05 18:06:54', '2025-07-05 18:15:08'),
(12, 1, 12, 'Gà Xám Mỹ Cuban', 'g-x-m-m-cuban', 'xám 1kg420 cao ráo  tay khung xương dầy tốt .nạp ăn miệng cực tốt.  sung tới pin  ko có gà sổ cắn long xấu gà thanh lý\r\nMua từ: 2025-04-09\r\nSản phẩm đính kèm: Lòng Nuôi Gà', 750000, 'new', 'active', 'hồ chí minh', 0, 0, 1, '2025-07-05 18:12:45', '2025-07-05 18:59:34'),
(14, 1, 5, 'Xe Dream LD ', 'xe-dream-ld-', 'aaaaaa\r\nMua từ: 2025-04-01\r\nSản phẩm đính kèm: Không có', 6700000, 'poor', 'active', 'hồ chí minh', 0, 0, 0, '2025-07-05 19:09:57', '2025-07-05 19:12:09'),
(15, 1, 3, 'Áo nike', 'a-o-nike-2', 'kk\r\nMua từ: 1111-11-11\r\nSản phẩm đính kèm: aw', 1000000, 'poor', 'active', 'acr', 0, 1, 0, '2025-07-05 19:22:03', '2025-07-05 19:24:22'),
(16, 1, 10, 'Áo nike', 'a-o-nike-3', 'â\r\nMua từ: 1111-11-11\r\nSản phẩm đính kèm: aw', 1000000, 'like_new', 'sold', 'acr', 0, 1, 0, '2025-07-05 19:35:37', '2025-07-05 20:11:11'),
(18, 1, 10, 'Áo nike', 'a-o-nike-5', 'q\r\nMua từ: 1111-11-11\r\nSản phẩm đính kèm: aw', 1000000, 'new', 'sold', 'acr', 0, 1, 0, '2025-07-05 19:57:59', '2025-07-05 19:59:33'),
(20, 1, 8, 'Xe Dream LD ', 'xe-dream-ld--1', 'hgvj\r\nMua từ: 2025-04-01\r\nSản phẩm đính kèm: Không có', 6700000, 'new', 'active', 'hồ chí minh', 0, 1, 1, '2025-07-06 01:17:15', '2025-07-06 01:17:20'),
(22, 1, 6, 'Attack on Titan từ vol 29-32', 'attack-on-titan-t-vol-29-32', 'Tên Nhà Cung Cấp	NXB Trẻ\r\nTác giả	Hajime Isayama\r\nNgười Dịch	Torarika\r\nMua từ: 2025-07-08\r\nSản phẩm đính kèm: Không có', 160000, 'new', 'active', 'hồ chí minh', 0, 1, 1, '2025-07-07 01:32:55', '2025-07-07 01:33:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_product_images_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 1, 'uploads/products/6868a9dadcb2e_nike.png', 1, 0, '2025-07-05 11:28:10'),
(2, 2, 'uploads/products/6868a9dadcb2e_nike.jpg', 1, 0, '2025-07-05 13:58:09'),
(3, 3, 'uploads/products/6868f466ddf5c_d38c610959de62653ccae2ee24e00a4b-2936816393925489881.jpg', 1, 0, '2025-07-05 16:46:14'),
(4, 3, 'uploads/products/6868f466de103_31a695038ffd93a1f231d440a9a08e95-2936816393928246668.jpg', 0, 0, '2025-07-05 16:46:14'),
(5, 3, 'uploads/products/6868f466de203_3444c27c6212652327aa9d52c1009065-2936841498044145035.jpg', 0, 0, '2025-07-05 16:46:14'),
(6, 3, 'uploads/products/6868f466e135f_f8cb94af9a365874d7ed44d5bd715081-2936816393899070441.jpg', 0, 0, '2025-07-05 16:46:14'),
(7, 4, 'uploads/products/6868f55d303df_68636c339165d_R.jpg', 1, 0, '2025-07-05 16:50:21'),
(8, 4, 'uploads/products/6868f55d305ca_Screenshot 2025-07-05 164919.png', 0, 0, '2025-07-05 16:50:21'),
(9, 4, 'uploads/products/6868f55d30908_Screenshot 2025-07-05 164940.png', 0, 0, '2025-07-05 16:50:21'),
(10, 4, 'uploads/products/6868f55d30ce2_Screenshot 2025-07-05 164955.png', 0, 0, '2025-07-05 16:50:21'),
(11, 5, 'uploads/products/686900bae7a45_vn-11134207-7qukw-lj19wvwsuubw8d.webp', 1, 0, '2025-07-05 17:38:50'),
(12, 5, 'uploads/products/686900bae7bdd_vn-11134207-7qukw-lj19wvwsuuvm0c.webp', 0, 0, '2025-07-05 17:38:50'),
(13, 5, 'uploads/products/686900bae7cd2_vn-11134207-7qukw-lj19wvwtaaks3f.webp', 0, 0, '2025-07-05 17:38:50'),
(14, 5, 'uploads/products/686900bae7e08_vn-11134207-7r98o-lri60cgnvnpg16.webp', 0, 0, '2025-07-05 17:38:50'),
(15, 6, 'uploads/products/6869027b15814_1b7a1ebb4bdf2e02167c29f9a45ea30a-2938715611422374574.jpg', 1, 0, '2025-07-05 17:46:19'),
(16, 6, 'uploads/products/6869027b15af4_2e1f983c8b9d3437460a60fe276adaca-2938715612635322503.jpg', 0, 0, '2025-07-05 17:46:19'),
(17, 6, 'uploads/products/6869027b15c0d_8788d7c895885c28800d233270479234-2938715612836487891.jpg', 0, 0, '2025-07-05 17:46:19'),
(18, 6, 'uploads/products/6869027b15cf8_df28bf051524683657ea511821b0d4c1-2938715611501974772.jpg', 0, 0, '2025-07-05 17:46:19'),
(19, 7, 'uploads/products/686903872a63c_6f548ba2dccff9def7499969b65a674f-2937294774874619162.jpg', 1, 0, '2025-07-05 17:50:47'),
(20, 7, 'uploads/products/686903872a8b9_16efe11873362e4c7c192d2ae40071d4-2937294774850270969.jpg', 0, 0, '2025-07-05 17:50:47'),
(21, 7, 'uploads/products/686903872ac15_6690630d937a7452bcefaaa3304cddb9-2937294775176218429.jpg', 0, 0, '2025-07-05 17:50:47'),
(22, 8, 'uploads/products/686904063392e_b2942f46825ed20f0c77a2c9b79129eb-2938838313482792819.jpg', 1, 0, '2025-07-05 17:52:54'),
(23, 8, 'uploads/products/6869040633aec_151160176a23941b18434580dd6de401-2938838314791481203.jpg', 0, 0, '2025-07-05 17:52:54'),
(24, 8, 'uploads/products/6869040633bfb_b3887211d7884a6da282c719d3eea0e9-2938838313197601301.jpg', 0, 0, '2025-07-05 17:52:54'),
(25, 8, 'uploads/products/6869040633d58_df3d0b21a62df80540ba9d3c1c2b6b2e-2938838316029754030.jpg', 0, 0, '2025-07-05 17:52:54'),
(26, 9, 'uploads/products/686904dc4411a_24933d07c0f323a744ecfe074edc08dc-2936211009311364946.jpg', 1, 0, '2025-07-05 17:56:28'),
(27, 9, 'uploads/products/686904dc4432d_2bff3f50dfb660b9f22d0ec49ddf88c1-2936211009183224439.jpg', 0, 0, '2025-07-05 17:56:28'),
(28, 9, 'uploads/products/686904dc4444c_70c555e2533b2d8ed39b7b8dc375928a-2936211009297102156.jpg', 0, 0, '2025-07-05 17:56:28'),
(29, 10, 'uploads/products/6869074ecd8ab_vn-11134207-7ra0g-m8v8usnhpsw257@resize_w900_nl.webp', 1, 0, '2025-07-05 18:06:54'),
(30, 10, 'uploads/products/6869074ecdbe7_489060621_967334988901332_6757284436025903028_n.jpg', 0, 0, '2025-07-05 18:06:54'),
(31, 10, 'uploads/products/6869074ecdd7b_Screenshot 2025-07-05 180413.png', 0, 0, '2025-07-05 18:06:54'),
(36, 12, 'uploads/products/686908ad564fd_e4d3215b22d96b7af4839de4b034e4f4-2938887076581507605.jpg', 1, 0, '2025-07-05 18:12:45'),
(37, 12, 'uploads/products/686908ad5672b_4e484d5d61881e485119fd6badfeac81-2938887075273453427.jpg', 0, 0, '2025-07-05 18:12:45'),
(38, 12, 'uploads/products/686908ad569fa_98143796ba7ecc6c758f37cf579457df-2938887076749913971.jpg', 0, 0, '2025-07-05 18:12:45'),
(39, 12, 'uploads/products/686908ad56dcc_ada579855269d0f64ccd34fae239a7e0-2938887075248716462.jpg', 0, 0, '2025-07-05 18:12:45'),
(44, 14, 'uploads/products/68691615b4585_ff2eda6c02172fee67b34ca24c92aa46-2938750875373491768.jpg', 1, 0, '2025-07-05 19:09:57'),
(45, 15, 'uploads/products/686918eb50423_6868a9dadcb2e_nike.png', 1, 0, '2025-07-05 19:22:03'),
(46, 16, 'uploads/products/68691c1930977_0e4c3bc660420aa5036cc8ae29e57483-2936812959101563222.jpg', 1, 0, '2025-07-05 19:35:37'),
(48, 18, 'uploads/products/68692157c71e8_0e4c3bc660420aa5036cc8ae29e57483-2936812959101563222.jpg', 1, 0, '2025-07-05 19:57:59'),
(50, 20, 'uploads/products/68696c2b20dd9_530b2412f5606fcede250a4f22e43ff5-2936812958400724558.jpg', 1, 0, '2025-07-06 01:17:15'),
(52, 22, 'uploads/products/686ac15780316_vn-11134207-7ra0g-m9424574c80737.webp', 1, 0, '2025-07-07 01:32:55'),
(53, 22, 'uploads/products/686ac157805d7_vn-11134207-7ras8-mb6ekcgo9r6f9e.webp', 0, 0, '2025-07-07 01:32:55'),
(54, 22, 'uploads/products/686ac1578091f_vn-11134207-7ra0g-m9p8tyt379ju1b.webp', 0, 0, '2025-07-07 01:32:55'),
(55, 22, 'uploads/products/686ac15780c58_vn-11134207-7ra0g-maky8m0um4zlb3.webp', 0, 0, '2025-07-07 01:32:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_status_logs`
--

DROP TABLE IF EXISTS `product_status_logs`;
CREATE TABLE IF NOT EXISTS `product_status_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `old_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_action_identifier` (`action`,`identifier`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `action`, `identifier`, `ip_address`, `created_at`) VALUES
(1, 'password_reset', 'nguyenthinhk52005@gmail.com', '192.168.253.1', '2025-07-05 16:01:19'),
(2, 'password_reset', 'nguyenthinhk52005@gmail.com', '192.168.253.1', '2025-07-05 16:01:34'),
(3, 'password_reset', 'nguyenthinhk52005@gmail.com', '192.168.253.1', '2025-07-05 16:01:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review_products`
--

DROP TABLE IF EXISTS `review_products`;
CREATE TABLE IF NOT EXISTS `review_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int DEFAULT '5',
  `is_recommended` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_user_product` (`user_id`,`product_id`),
  KEY `fk_review_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `review_products`
--

INSERT INTO `review_products` (`id`, `user_id`, `product_id`, `content`, `sent_at`, `username`, `rating`, `is_recommended`) VALUES
(1, 1, 20, 'Người bán uy tín', '2025-07-07 01:10:13', 'Nguyenthinh_3103', 5, 1),
(4, 1, 6, 'Người bán uy tín', '2025-07-07 01:15:17', 'Nguyenthinh_3103', 5, 1),
(5, 1, 10, 'Nhìn phong cách vẽ tôi không thích', '2025-12-25 04:32:33', 'Nguyenthinh_3103', 1, 1),
(6, 1, 3, 'Sản phẩm không dùng được', '2025-12-25 17:03:55', 'Nguyenthinh_3103', 2, 1),
(7, 1, 4, 'Oke, ủng hộ shop nha', '2025-12-28 07:48:38', 'Nguyenthinh_3103', 5, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `system_notifications`
--

DROP TABLE IF EXISTS `system_notifications`;
CREATE TABLE IF NOT EXISTS `system_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `type` enum('news','announcement','maintenance','update') COLLATE utf8mb4_unicode_ci DEFAULT 'news',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status_created` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `theme_presets`
--

DROP TABLE IF EXISTS `theme_presets`;
CREATE TABLE IF NOT EXISTS `theme_presets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'palette',
  `primary_color` varchar(7) NOT NULL,
  `secondary_color` varchar(7) NOT NULL,
  `accent_color` varchar(7) NOT NULL,
  `background_color` varchar(7) DEFAULT '#ffffff',
  `text_color` varchar(7) DEFAULT '#1f2937',
  `sidebar_bg` varchar(7) DEFAULT '#ffffff',
  `header_bg` varchar(7) DEFAULT '#ffffff',
  `is_system` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `theme_presets`
--

INSERT INTO `theme_presets` (`id`, `name`, `icon`, `primary_color`, `secondary_color`, `accent_color`, `background_color`, `text_color`, `sidebar_bg`, `header_bg`, `is_system`, `is_active`, `created_at`) VALUES
(1, 'Tết', 'palette', '#da251d', '#ffd700', '#2e8b57', '#ffffff', '#1f2937', '#ffffff', '#ffffff', 0, 0, '2025-12-24 20:09:24'),
(2, 'Giáng sinh', 'palette', '#006400', '#c8102e', '#ffd700', '#ffffff', '#1f2937', '#ffffff', '#ffffff', 0, 1, '2025-12-24 20:13:38'),
(3, 'Trung thu', 'palette', '#fdb813', '#d82c2c', '#003366', '#ffffff', '#1f2937', '#ffffff', '#ffffff', 0, 0, '2025-12-24 20:14:25'),
(4, 'Halloween ', 'celebration', '#ff7518', '#878787', '#800080', '#ffffff', '#1f2937', '#ffffff', '#ffffff', 0, 0, '2025-12-24 20:15:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `avatar`, `role`, `status`, `last_login`, `login_attempts`, `locked_until`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'Nguyenthinh_3103', 'nguyenthinhk52005@gmail.com', '$2y$10$.0S29JySJJF8qjsWyk1Mw.dBe5mme5jR6zPwpXZp5FcEfB.4huiee', 'Nguyễn Hoàng Gia Thịnh', '0945553902', '46A', NULL, 'admin', 'active', '2026-01-11 09:53:21', 0, NULL, NULL, '2025-07-05 10:35:06', '2026-01-11 09:53:21'),
(2, 'Nguyenthinh_3101111', 'nguyen@gmail.com', '$2y$10$KKGRuUj/PG4qV.lIsiKF.e1M4MbmKomZru1XQYr8JRB603Cd5.pTq', 'Nguyễn Hoàng Gia Thịnh', '', NULL, NULL, 'user', 'active', '2026-01-09 00:42:12', 0, NULL, NULL, '2025-12-19 17:45:49', '2026-01-09 00:42:12'),
(3, 'testuser', 'test@example.com', '$2y$10$Ih4UQDnOzRkrlXmIkWEq/exyszo7uflugteGHsGYCccZV.Q2xCHJm', 'Test User', '0123456789', NULL, NULL, 'user', 'active', '2025-12-24 20:23:22', 0, NULL, NULL, '2025-12-24 20:22:59', '2026-01-11 08:49:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_logs`
--

DROP TABLE IF EXISTS `user_logs`;
CREATE TABLE IF NOT EXISTS `user_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `box_chat`
--
ALTER TABLE `box_chat`
  ADD CONSTRAINT `fk_box_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_box` FOREIGN KEY (`box_chat_id`) REFERENCES `box_chat` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_products_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_status_logs`
--
ALTER TABLE `product_status_logs`
  ADD CONSTRAINT `fk_status_logs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_status_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_remember_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `review_products`
--
ALTER TABLE `review_products`
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `fk_user_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
