<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/NotificationModel.php';

// Handle Actions
if (isset($_GET['action'])) {
    // Suppress warnings and clean output
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    if (ob_get_length()) ob_clean();
    
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_GET['action'];

    if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $userId = $_POST['user_id'] ?? 'all';
            if ($userId === 'all') $userId = null;

            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            // Validate inputs
            if (empty($title)) {
                echo json_encode(['success' => false, 'message' => 'Tiêu đề không được để trống']);
                exit;
            }
            
            if (empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Nội dung không được để trống']);
                exit;
            }

            $data = [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => 'admin'
            ];

            $result = createNotification($pdo, $data);
            
            if ($result === true) {
                echo json_encode(['success' => true, 'message' => 'Gửi thông báo thành công!']);
            } else {
                // Result is an error array
                $errorMsg = is_array($result) ? ($result['error'] ?? 'Unknown error') : 'Lỗi khi gửi thông báo';
                echo json_encode(['success' => false, 'message' => $errorMsg, 'debug' => $result]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        try {
            if (deleteNotification($pdo, $_POST['id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'search_users') {
        try {
            $users = getAllUsers($pdo);
            echo json_encode(['success' => true, 'users' => $users]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
}
