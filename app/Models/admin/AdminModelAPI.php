<?php
// Tắt tất cả output trước khi bắt đầu
ob_start();

// Set JSON header ngay lập tức
header('Content-Type: application/json');

// Tắt error display để tránh HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Minimal config - chỉ load database
try {
    // Database connection
    $host = 'localhost';
    $dbname = 'muabandocu';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear any previous output
    ob_clean();
    
    // Check admin permission
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
    
    $pdo->beginTransaction();
    
    // Get product info
    $stmt = $pdo->prepare('SELECT user_id, title, featured FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }
    
    $user_id = $product['user_id'];
    $title = htmlspecialchars($product['title']);
    $result = false;
    $newFeaturedStatus = null;
    $autoUnfeaturedId = null;
    
    // Handle actions
    switch ($action) {
        case 'toggle_featured':
            $currentFeatured = $product['featured'];

            // Nếu sắp đặt sản phẩm thành nổi bật, đảm bảo tổng số nổi bật không vượt quá giới hạn
            if (!$currentFeatured) {
                $MAX_FEATURED = 12;
                // Đếm tổng số sản phẩm đang ở trạng thái nổi bật
                $countStmt = $pdo->query('SELECT COUNT(*) FROM products WHERE featured = 1');
                $featuredCount = (int) $countStmt->fetchColumn();

                // Nếu đã vượt giới hạn, tự động bỏ nổi bật sản phẩm lâu nhất
                if ($featuredCount >= $MAX_FEATURED) {
                    $oldestStmt = $pdo->query('SELECT id FROM products WHERE featured = 1 ORDER BY created_at ASC LIMIT 1');
                    $oldestId = $oldestStmt->fetchColumn();
                    if ($oldestId) {
                        $pdo->prepare('UPDATE products SET featured = 0 WHERE id = ?')->execute([$oldestId]);
                        $autoUnfeaturedId = (int) $oldestId;
                    }
                }
            }

            // Tiến hành toggle trạng thái nổi bật cho sản phẩm hiện tại
            $newFeatured = $currentFeatured ? 0 : 1;
            $stmt = $pdo->prepare('UPDATE products SET featured = ? WHERE id = ?');
            $result = $stmt->execute([$newFeatured, $id]);
            $newFeaturedStatus = $newFeatured;
            break;
            
        case 'approve':
            $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');
            $result = $stmt->execute(['active', $id]);
            break;
            
        case 'reject':
            $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');
            $result = $stmt->execute(['rejected', $id]);
            break;
            
        case 'delete':
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $result = $stmt->execute([$id]);
            break;
            
        default:
            throw new Exception('Hành động không hợp lệ');
    }
    
    if (!$result) {
        throw new Exception('Không thể thực hiện thao tác');
    }
    
    // Create notification for user
    $notificationMessage = '';
    switch ($action) {
        case 'toggle_featured':
            $statusText = $newFeaturedStatus ? 'được đặt làm sản phẩm nổi bật' : 'được bỏ khỏi danh sách nổi bật';
            $notificationMessage = "Sản phẩm '$title' của bạn đã $statusText";
            break;
        case 'approve':
            $notificationMessage = "Sản phẩm '$title' của bạn đã được duyệt";
            break;
        case 'reject':
            $notificationMessage = "Sản phẩm '$title' của bạn đã bị từ chối";
            break;
        case 'delete':
            $notificationMessage = "Sản phẩm '$title' của bạn đã bị xóa";
            break;
    }
    
    if ($notificationMessage) {
        try {
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$user_id, $notificationMessage]);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to create notification: " . $e->getMessage());
        }
    }
    
    $pdo->commit();
    
    // Return success response
    $response = [
        'success' => true,
        'message' => getActionMessage($action),
        'action' => $action,
        'productId' => $id
    ];
    
    if ($action === 'toggle_featured' && $newFeaturedStatus !== null) {
        $response['newFeaturedStatus'] = $newFeaturedStatus;
        $response['isFeatured'] = (bool)$newFeaturedStatus;
        if ($autoUnfeaturedId !== null) {
            $response['autoUnfeaturedId'] = $autoUnfeaturedId;
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    ob_end_flush();
}

function getActionMessage($action) {
    switch ($action) {
        case 'approve':
            return 'Duyệt sản phẩm thành công';
        case 'reject':
            return 'Từ chối sản phẩm thành công';
        case 'toggle_featured':
            return 'Cập nhật trạng thái nổi bật thành công';
        case 'delete':
            return 'Xóa sản phẩm thành công';
        default:
            return 'Thao tác thành công';
    }
}
?>
