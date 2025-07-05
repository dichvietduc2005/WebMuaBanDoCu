-- File chèn dữ liệu mẫu cho Web Mua Bán Đồ Cũ
-- Chạy file này sau khi đã import database_structure_only.sql

USE muabandocu;

-- Thêm dữ liệu users
INSERT INTO users (username, email, password, full_name, phone, address, role, status) VALUES
('admin', 'admin@muabandocu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0123456789', 'Hà Nội', 'admin', 'active'),
('nguyenvana', 'nguyenvana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0987654321', '123 Đường ABC, Quận 1, TP.HCM', 'user', 'active'),
('tranthib', 'tranthib@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0912345678', '456 Đường XYZ, Quận 3, TP.HCM', 'user', 'active'),
('phamvanc', 'phamvanc@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Văn C', '0901234567', '789 Đường DEF, Quận 5, TP.HCM', 'user', 'active'),
('hoangthid', 'hoangthid@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoàng Thị D', '0898765432', '321 Đường GHI, Quận 7, TP.HCM', 'user', 'active'),
('vuvane', 'vuvane@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vũ Văn E', '0876543210', '654 Đường JKL, Quận 2, TP.HCM', 'user', 'active');

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

-- Thêm dữ liệu products
INSERT INTO products (user_id, category_id, title, slug, description, price, condition_status, status, location, views, featured, stock_quantity) VALUES
(2, 1, 'iPhone 12 Pro Max 128GB', 'iphone-12-pro-max-128gb', 'iPhone 12 Pro Max màu xanh dương, dung lượng 128GB. Máy còn mới 95%, đầy đủ phụ kiện gốc bao gồm hộp, sạc, tai nghe. Pin còn 89%, không bể vỡ, không ngấm nước.', 15000000, 'like_new', 'active', 'Quận 1, TP.HCM', 125, 1, 2),
(3, 1, 'Samsung Galaxy S21 Ultra 256GB', 'samsung-galaxy-s21-ultra-256gb', 'Samsung Galaxy S21 Ultra màu đen, bộ nhớ 256GB. Máy sử dụng 6 tháng, còn bảo hành 18 tháng. Camera zoom 100x hoạt động tốt, màn hình không trầy xước.', 18500000, 'good', 'active', 'Quận 3, TP.HCM', 98, 1, 1),
(2, 2, 'MacBook Pro 16 inch 2019', 'macbook-pro-16-inch-2019', 'MacBook Pro 16 inch năm 2019, chip Intel Core i7, RAM 16GB, SSD 512GB. Máy hoạt động mượt mà, pin còn tốt. Có một vài vết trầy nhỏ ở vỏ nhưng không ảnh hưởng sử dụng.', 30000000, 'good', 'active', 'Quận 5, TP.HCM', 156, 1, 1),
(4, 2, 'Dell XPS 13 9310', 'dell-xps-13-9310', 'Laptop Dell XPS 13 9310, màn hình 13.3 inch Full HD, CPU Intel Core i5 thế hệ 11, RAM 8GB, SSD 256GB. Máy nhẹ, pin trâu, phù hợp cho sinh viên và dân văn phòng.', 21750000, 'like_new', 'active', 'Quận 7, TP.HCM', 89, 1, 1),
(3, 1, 'iPad Pro 11 inch 2021', 'ipad-pro-11-inch-2021', 'iPad Pro 11 inch năm 2021, chip M1, bộ nhớ 128GB, WiFi + Cellular. Kèm theo Apple Pencil và Smart Keyboard. Máy còn mới, ít sử dụng.', 20900000, 'like_new', 'active', 'Quận 2, TP.HCM', 76, 1, 1),
(5, 8, 'Máy ảnh Sony A7 III', 'may-anh-sony-a7-iii', 'Máy ảnh Sony A7 III body, còn bảo hành 8 tháng. Kèm theo lens kit 28-70mm và thẻ nhớ 64GB. Máy chụp đẹp, quay video 4K.', 25000000, 'good', 'active', 'Quận 1, TP.HCM', 67, 1, 1),
(4, 3, 'Áo khoác Nike xuất khẩu', 'ao-khoac-nike-xuat-khau', 'Áo khoác Nike xuất khẩu, size M, màu đen. Chất liệu tốt, giữ ấm tốt. Mặc ít lần, còn như mới.', 350000, 'like_new', 'active', 'Quận 9, TP.HCM', 45, 0, 3),
(6, 3, 'Giày Adidas Ultraboost 22', 'giay-adidas-ultraboost-22', 'Giày chạy bộ Adidas Ultraboost 22, size 42, màu trắng đen. Mặc được 2-3 tháng, đế còn tốt, không bị mòn nhiều.', 1800000, 'good', 'active', 'Quận 10, TP.HCM', 32, 0, 1),
(5, 6, 'Bộ sách Harry Potter tiếng Việt', 'bo-sach-harry-potter-tieng-viet', 'Bộ 7 tập Harry Potter bản tiếng Việt, nhà xuất bản Trẻ. Sách còn mới, ít đọc, không rách hay bị gãy gáy.', 450000, 'like_new', 'active', 'Quận 4, TP.HCM', 28, 0, 1),
(2, 7, 'Bàn bi-a mini', 'ban-bi-a-mini', 'Bàn bi-a mini cho gia đình, kích thước 120x60cm. Đầy đủ phụ kiện gồm cơ, bóng, phấn. Thích hợp cho trẻ em và người lớn.', 2500000, 'good', 'active', 'Quận 6, TP.HCM', 19, 0, 1);

-- Thêm dữ liệu product_images
INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES
(1, 'uploads/products/iphone12pro.jpg', 1, 1),
(2, 'uploads/products/samsung_s21_ultra.jpg', 1, 1),
(3, 'uploads/products/macbook_pro_16.jpg', 1, 1),
(4, 'uploads/products/dell_xps_13.jpg', 1, 1),
(5, 'uploads/products/ipad_pro_11.jpg', 1, 1),
(6, 'uploads/products/sony_a7_iii.jpg', 1, 1);

-- Thêm dữ liệu carts
INSERT INTO carts (user_id, session_id) VALUES
(2, NULL),
(3, NULL),
(4, NULL),
(5, NULL),
(6, NULL),
(NULL, 'guest_session_001'),
(NULL, 'guest_session_002');

-- Thêm dữ liệu cart_items
INSERT INTO cart_items (cart_id, product_id, quantity, added_price, condition_snapshot) VALUES
(1, 1, 1, 15000000, 'like_new'),
(1, 3, 1, 30000000, 'good'),
(2, 2, 1, 18500000, 'good'),
(3, 4, 1, 21750000, 'like_new'),
(3, 5, 1, 20900000, 'like_new');

-- Thêm dữ liệu orders
INSERT INTO orders (order_number, buyer_id, total_amount, status, payment_method, payment_status, notes, created_at) VALUES
('ORD-2023-00001', 2, 15000000, 'success', 'vnpay', 'paid', 'Đơn hàng mua iPhone 12 Pro Max', '2023-06-10 10:30:00'),
('ORD-2023-00002', 3, 30000000, 'success', 'vnpay', 'paid', 'Đơn hàng mua MacBook Pro', '2023-06-12 14:15:00'),
('ORD-2023-00003', 4, 18500000, 'pending', 'vnpay', 'pending', 'Đơn hàng mua Samsung Galaxy S21', '2023-06-14 09:20:00'),
('ORD-2023-00004', 5, 42650000, 'success', 'vnpay', 'paid', 'Đơn hàng mua laptop và iPad', '2023-06-13 16:45:00'),
('ORD-2023-00005', 6, 25000000, 'cancelled', 'vnpay', 'failed', 'Đơn hàng mua máy ảnh - đã hủy', '2023-06-11 11:30:00'),
('ORD-2023-00006', 2, 2150000, 'success', 'vnpay', 'paid', 'Đơn hàng mua giày và áo', '2023-06-09 13:00:00'),
('ORD-2023-00007', 3, 450000, 'success', 'bank_transfer', 'paid', 'Đơn hàng mua sách', '2023-06-08 15:30:00'),
('ORD-2023-00008', 4, 2500000, 'pending', 'vnpay', 'pending', 'Đơn hàng mua bàn bi-a', '2023-06-15 08:45:00');

-- Thêm dữ liệu order_items
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

COMMIT;
