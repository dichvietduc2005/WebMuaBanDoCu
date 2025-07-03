<?php
require_once(__DIR__ . '/../../../config/config.php');

// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set header để trả về JSON
header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Không có quyền truy cập'
    ]);
    exit;
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !$action) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu tham số cần thiết'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Lấy thông tin sản phẩm
    $stmt = $pdo->prepare('SELECT user_id, title FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }
    
    $user_id = $product['user_id'];
    $title = htmlspecialchars($product['title']);
    $notificationMessage = '';
    $result = false;
    
    // Xử lý các hành động
    switch ($action) {
        case 'approve':
            // Cập nhật trạng thái sản phẩm thành 'active'
            $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');
            $result = $stmt->execute(['active', $id]);
            $notificationMessage = "Sản phẩm <b>$title</b> của bạn đã được duyệt";
            break;
            
        case 'reject':
            // Cập nhật trạng thái sản phẩm thành 'rejected'
            $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');
            $result = $stmt->execute(['rejected', $id]);
            $notificationMessage = "Sản phẩm <b>$title</b> của bạn đã bị từ chối";
            break;
            
        case 'delete':
            // Xóa sản phẩm
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $result = $stmt->execute([$id]);
            $notificationMessage = "Sản phẩm <b>$title</b> của bạn đã bị xóa";
            break;
            
        default:
            throw new Exception('Hành động không hợp lệ');
    }
    
    // Tạo thông báo cho người dùng nếu thao tác thành công
    if ($result && $notificationMessage) {
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$user_id, $notificationMessage]);
    }
    
    $pdo->commit();
    
    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => getActionMessage($action),
        'action' => $action,
        'productId' => $id
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getActionMessage($action) {
    switch ($action) {
        case 'approve':
            return 'Duyệt sản phẩm thành công';
        case 'reject':
            return 'Từ chối sản phẩm thành công';
        case 'delete':
            return 'Xóa sản phẩm thành công';
        default:
            return 'Thao tác thành công';
    }
}
?>