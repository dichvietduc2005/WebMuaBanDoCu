INSERT INTO notification_templates (code, title, message_template, type, is_active) VALUES 
('cart_abandoned', 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào {username}, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1)
ON DUPLICATE KEY UPDATE title = VALUES(title);
