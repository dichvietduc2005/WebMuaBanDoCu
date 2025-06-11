<?php
// để test kết nối MySQL với WampServer
echo "<h2>Test Kết Nối MySQL - WampServer</h2>";

// Kiểm tra extension PHP
echo "<h3>1. Kiểm tra PHP Extensions:</h3>";
if (extension_loaded('pdo')) {
    echo "✅ PDO extension: <span style='color:green'>Có</span><br>";
} else {
    echo "❌ PDO extension: <span style='color:red'>Không có</span><br>";
}

if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL extension: <span style='color:green'>Có</span><br>";
} else {
    echo "❌ PDO MySQL extension: <span style='color:red'>Không có</span><br>";
}

if (extension_loaded('mysqli')) {
    echo "✅ MySQLi extension: <span style='color:green'>Có</span><br>";
} else {
    echo "❌ MySQLi extension: <span style='color:red'>Không có</span><br>";
}

echo "<br>";

// Thông tin kết nối
$db_host = 'localhost';
$db_name = 'muabandocu';
$db_user = 'root';
$db_pass = '';
$db_port = 3306;

echo "<h3>2. Thông tin kết nối:</h3>";
echo "Host: $db_host<br>";
echo "Port: $db_port<br>";
echo "Database: $db_name<br>";
echo "User: $db_user<br>";
echo "Password: " . (empty($db_pass) ? '(trống)' : '***') . "<br><br>";

// Test kết nối cơ bản với MySQL
echo "<h3>3. Test kết nối MySQL Server:</h3>";
try {
    $pdo_test = new PDO("mysql:host=$db_host;port=$db_port;charset=utf8", $db_user, $db_pass);
    $pdo_test->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ <span style='color:green'>Kết nối MySQL Server thành công!</span><br>";
    
    // Lấy version MySQL
    $version = $pdo_test->query('SELECT VERSION()')->fetchColumn();
    echo "MySQL Version: $version<br>";
    
} catch(PDOException $e) {
    echo "❌ <span style='color:red'>Lỗi kết nối MySQL Server: " . $e->getMessage() . "</span><br>";
}

echo "<br>";

// Test kết nối với database cụ thể
echo "<h3>4. Test kết nối Database '$db_name':</h3>";
try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ <span style='color:green'>Kết nối Database '$db_name' thành công!</span><br>";
    
    // Kiểm tra bảng payment_history
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'payment_history'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Bảng 'payment_history' tồn tại<br>";
            
            // Đếm số record
            $count = $pdo->query("SELECT COUNT(*) FROM payment_history")->fetchColumn();
            echo "📊 Số record trong bảng: $count<br>";
        } else {
            echo "⚠️ Bảng 'payment_history' không tồn tại<br>";
        }
    } catch(PDOException $e) {
        echo "⚠️ Lỗi kiểm tra bảng: " . $e->getMessage() . "<br>";
    }
    
} catch(PDOException $e) {
    echo "❌ <span style='color:red'>Lỗi kết nối Database '$db_name': " . $e->getMessage() . "</span><br>";
    
    // Gợi ý khắc phục
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<br><strong>🔧 Gợi ý khắc phục:</strong><br>";
        echo "1. Database '$db_name' không tồn tại<br>";
        echo "2. Hãy tạo database này trong phpMyAdmin<br>";
        echo "3. Hoặc thay đổi tên database trong config.php<br>";
    }
}

echo "<br>";

// Liệt kê tất cả database có sẵn
echo "<h3>5. Danh sách Database có sẵn:</h3>";
try {
    if (isset($pdo_test)) {
        $databases = $pdo_test->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<ul>";
        foreach ($databases as $db) {
            if ($db === $db_name) {
                echo "<li><strong style='color:green'>$db</strong> (đang sử dụng)</li>";
            } else {
                echo "<li>$db</li>";
            }
        }
        echo "</ul>";
    }
} catch(PDOException $e) {
    echo "Không thể lấy danh sách database: " . $e->getMessage() . "<br>";
}

echo "<br>";

// Test bằng MySQLi (backup method)
echo "<h3>6. Test kết nối bằng MySQLi:</h3>";
if (function_exists('mysqli_connect')) {
    $mysqli = @mysqli_connect($db_host, $db_user, $db_pass, '', $db_port);
    if ($mysqli) {
        echo "✅ <span style='color:green'>MySQLi kết nối thành công!</span><br>";
        mysqli_close($mysqli);
    } else {
        echo "❌ <span style='color:red'>MySQLi kết nối thất bại: " . mysqli_connect_error() . "</span><br>";
    }
} else {
    echo "❌ MySQLi function không có sẵn<br>";
}

echo "<br>";

// Thông tin PHP
echo "<h3>7. Thông tin PHP:</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

echo "<br><hr>";
echo "<p><strong>📍 Nếu vẫn gặp lỗi, hãy:</strong></p>";
echo "<ol>";
echo "<li>Kiểm tra WampServer có đang chạy không (icon màu xanh)</li>";
echo "<li>Restart WampServer</li>";
echo "<li>Kiểm tra port 3306 có bị xung đột không</li>";
echo "<li>Tạo database '$db_name' trong phpMyAdmin</li>";
echo "<li>Kiểm tra firewall/antivirus</li>";
echo "</ol>";
?>
