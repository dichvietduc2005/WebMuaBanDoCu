-- Tạo bảng system_notifications cho tin tức hệ thống
CREATE TABLE IF NOT EXISTS `system_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `type` enum('news','announcement','maintenance','update') DEFAULT 'news',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert dữ liệu mẫu
INSERT INTO `system_notifications` (`title`, `content`, `type`, `status`) VALUES
('Chào mừng bạn đến với HIHand Shop!', 'Cảm ơn bạn đã tham gia cộng đồng mua bán đồ cũ lớn nhất Việt Nam. Hãy khám phá những sản phẩm tuyệt vời!', 'announcement', 'active'),
('Tính năng chat mới đã ra mắt', 'Giờ đây bạn có thể trò chuyện trực tiếp với người bán để thương lượng giá cả và hỏi thêm thông tin sản phẩm.', 'update', 'active'),
('Khuyến mãi đặc biệt cuối tuần', 'Miễn phí phí đăng tin cho tất cả sản phẩm trong 3 ngày. Nhanh tay đăng bán những món đồ bạn không dùng đến!', 'news', 'active'),
('Cập nhật chính sách bảo mật', 'Chúng tôi đã nâng cấp hệ thống bảo mật để đảm bảo thông tin cá nhân của bạn được bảo vệ tối đa.', 'announcement', 'active'); 