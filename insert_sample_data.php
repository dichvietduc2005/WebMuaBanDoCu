<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Insert Sample Data</h2>";

try {
    $inserted = false;
    
    // Kiểm tra và insert categories nếu chưa có
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "<h3>Inserting categories...</h3>";
        $categories_sql = "
        INSERT INTO categories (name, slug, description, status) VALUES
        ('Điện thoại & Máy tính bảng', 'dien-thoai-may-tinh-bang', 'Điện thoại di động, smartphone, máy tính bảng các loại', 'active'),
        ('Laptop & Máy tính', 'laptop-may-tinh', 'Laptop, máy tính để bàn, linh kiện máy tính', 'active'),
        ('Thời trang & Phụ kiện', 'thoi-trang-phu-kien', 'Quần áo, giày dép, túi xách, phụ kiện thời trang', 'active'),
        ('Đồ gia dụng & Nội thất', 'do-gia-dung-noi-that', 'Đồ gia dụng, nội thất, đồ trang trí nhà cửa', 'active'),
        ('Xe cộ & Phương tiện', 'xe-co-phuong-tien', 'Xe máy, xe đạp, ô tô và phụ kiện xe', 'active')
        ";
        $pdo->exec($categories_sql);
        echo "<p>✓ Categories inserted.</p>";
        $inserted = true;
    }
    
    // Kiểm tra và insert users nếu chưa có
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "<h3>Inserting sample users...</h3>";
        $users_sql = "
        INSERT INTO users (username, email, password, full_name, phone, address, role, status) VALUES
        ('admin', 'admin@muabandocu.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0123456789', 'Hà Nội', 'admin', 'active'),
        ('nguyenvana', 'nguyenvana@email.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0987654321', '123 Đường ABC, Quận 1, TP.HCM', 'user', 'active'),
        ('tranthib', 'tranthib@email.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0912345678', '456 Đường XYZ, Quận 3, TP.HCM', 'user', 'active')
        ";
        $pdo->exec($users_sql);
        echo "<p>✓ Users inserted.</p>";
        $inserted = true;
    }
    
    // Kiểm tra và insert products nếu chưa có
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "<h3>Inserting sample products...</h3>";
        
        // Get first available category IDs
        $stmt = $pdo->query("SELECT id FROM categories ORDER BY id LIMIT 5");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $cat1 = $categories[0] ?? 1;
        $cat2 = $categories[1] ?? 2;
        
        // Get sample user IDs  
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'user' ORDER BY id LIMIT 2");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $user1 = $users[0] ?? 1;
        $user2 = $users[1] ?? $user1;
        
        $products_sql = "
        INSERT INTO products (user_id, category_id, title, slug, description, price, condition_status, status, location, views, featured, stock_quantity) VALUES
        ($user1, $cat1, 'iPhone 12 Pro Max 128GB', 'iphone-12-pro-max-128gb', 'iPhone 12 Pro Max màu xanh dương, dung lượng 128GB. Máy còn mới 95%, đầy đủ phụ kiện gốc.', 15000000, 'like_new', 'active', 'Quận 1, TP.HCM', 125, 1, 2),
        ($user2, $cat1, 'Samsung Galaxy S21 Ultra 256GB', 'samsung-galaxy-s21-ultra-256gb', 'Samsung Galaxy S21 Ultra màu đen, bộ nhớ 256GB. Máy sử dụng 6 tháng, còn bảo hành 18 tháng.', 18500000, 'good', 'active', 'Quận 3, TP.HCM', 98, 1, 1),
        ($user1, $cat2, 'MacBook Pro 16 inch 2019', 'macbook-pro-16-inch-2019', 'MacBook Pro 16 inch năm 2019, chip Intel Core i7, RAM 16GB, SSD 512GB.', 30000000, 'good', 'active', 'Quận 5, TP.HCM', 156, 1, 1),
        ($user2, $cat2, 'Dell XPS 13 9310', 'dell-xps-13-9310', 'Laptop Dell XPS 13 9310, màn hình 13.3 inch Full HD, CPU Intel Core i5 thế hệ 11.', 21750000, 'like_new', 'active', 'Quận 7, TP.HCM', 89, 1, 1)
        ";
        $pdo->exec($products_sql);
        echo "<p>✓ Products inserted.</p>";
        
        // Insert product images - sử dụng product IDs vừa tạo
        $stmt = $pdo->query("SELECT id FROM products ORDER BY id LIMIT 4");
        $product_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($product_ids) >= 4) {
            $images_sql = "
            INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES
            ({$product_ids[0]}, 'uploads/products/iphone12pro.jpg', 1, 1),
            ({$product_ids[1]}, 'uploads/products/samsung_s21_ultra.jpg', 1, 1),
            ({$product_ids[2]}, 'uploads/products/macbook_pro_16.jpg', 1, 1),
            ({$product_ids[3]}, 'uploads/products/dell_xps_13.jpg', 1, 1)
            ";
            $pdo->exec($images_sql);
            echo "<p>✓ Product images inserted.</p>";
        }
        $inserted = true;
    }
    
    if ($inserted) {
        echo "<h3 style='color: green;'>Sample data inserted successfully!</h3>";
    } else {
        echo "<h2>Data already exists</h2>";
    }
    
    // Show current counts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE status = 'active'");
    $result = $stmt->fetch();
    echo "<p>Active Categories: " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $result = $stmt->fetch();
    echo "<p>Active Products: " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE status = 'active' AND featured = 1");
    $result = $stmt->fetch();
    echo "<p>Featured Products: " . $result['count'] . "</p>";
    
    echo "<p><a href='../../public/TrangChu.php'>Go to Home Page</a></p>";
    
} catch (PDOException $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
