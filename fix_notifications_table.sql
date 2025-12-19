-- SQL để sửa bảng notifications
-- Chạy câu lệnh này trong phpMyAdmin

-- Thêm cột title nếu chưa có
ALTER TABLE `notifications` 
ADD COLUMN IF NOT EXISTS `title` VARCHAR(255) NOT NULL AFTER `user_id`;

-- Thêm cột type nếu chưa có
ALTER TABLE `notifications` 
ADD COLUMN IF NOT EXISTS `type` VARCHAR(50) DEFAULT 'admin' AFTER `message`;

-- Kiểm tra lại cấu trúc bảng
DESCRIBE `notifications`;

-- Nếu gặp lỗi với IF NOT EXISTS (MySQL cũ), dùng cách này:
-- ALTER TABLE `notifications` ADD COLUMN `title` VARCHAR(255) NOT NULL AFTER `user_id`;
-- ALTER TABLE `notifications` ADD COLUMN `type` VARCHAR(50) DEFAULT 'admin' AFTER `message`;
