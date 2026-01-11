-- Migration: Admin Theme Customization System
-- Tạo bảng để lưu cài đặt theme và banner cho admin panel

CREATE TABLE IF NOT EXISTS admin_theme_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('color', 'image', 'text', 'boolean', 'json') DEFAULT 'text',
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_banner_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    description TEXT,
    event_type VARCHAR(50) DEFAULT 'default',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    animation_type ENUM('fade', 'slide', 'zoom', 'none') DEFAULT 'fade',
    transition_duration INT DEFAULT 500,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_theme_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    theme_config JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default theme settings
INSERT INTO admin_theme_settings (setting_key, setting_value, setting_type, description) VALUES
('primary_color', '#4f46e5', 'color', 'Màu chủ đạo của theme'),
('secondary_color', '#7c3aed', 'color', 'Màu phụ của theme'),
('accent_color', '#10b981', 'color', 'Màu nhấn'),
('background_color', '#ffffff', 'color', 'Màu nền'),
('text_color', '#1f2937', 'color', 'Màu chữ chính'),
('sidebar_bg', '#ffffff', 'color', 'Màu nền sidebar'),
('header_bg', '#ffffff', 'color', 'Màu nền header'),
('enable_banner', '1', 'boolean', 'Bật/tắt banner'),
('banner_height', '200', 'text', 'Chiều cao banner (px)'),
('animation_enabled', '1', 'boolean', 'Bật/tắt animation'),
('current_event', 'default', 'text', 'Sự kiện hiện tại');

-- Insert default banner (placeholder)
INSERT INTO admin_banner_images (image_path, title, event_type, is_active, sort_order, animation_type) VALUES
('/public/assets/images/default-banner.jpg', 'Banner mặc định', 'default', 1, 0, 'fade');

