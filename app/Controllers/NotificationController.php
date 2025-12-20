<?php
// app/Controllers/NotificationController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/admin/NotificationModel.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'mark_read':
            $notificationId = $input['notification_id'] ?? null;
            if (!$notificationId) {
                echo json_encode(['success' => false, 'message' => 'Missing notification_id']);
                exit;
            }
            
            $result = markNotificationAsRead($pdo, $notificationId, $userId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Đã đánh dấu đã đọc' : 'Có lỗi xảy ra'
            ]);
            break;
            
        case 'mark_unread':
            $notificationId = $input['notification_id'] ?? null;
            if (!$notificationId) {
                echo json_encode(['success' => false, 'message' => 'Missing notification_id']);
                exit;
            }
            
            $result = markNotificationAsUnread($pdo, $notificationId, $userId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Đã đánh dấu chưa đọc' : 'Có lỗi xảy ra'
            ]);
            break;
            
        case 'mark_all_read':
            $count = markAllAsRead($pdo, $userId);
            echo json_encode([
                'success' => true,
                'count' => $count,
                'message' => "Đã đánh dấu $count thông báo là đã đọc"
            ]);
            break;
            
        case 'delete':
            $notificationId = $input['notification_id'] ?? null;
            if (!$notificationId) {
                echo json_encode(['success' => false, 'message' => 'Missing notification_id']);
                exit;
            }
            
            $result = deleteNotificationForUser($pdo, $notificationId, $userId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Đã xóa thông báo' : 'Có lỗi xảy ra'
            ]);
            break;
            
        case 'delete_all_read':
            $count = deleteAllReadNotifications($pdo, $userId);
            echo json_encode([
                'success' => true,
                'count' => $count,
                'message' => "Đã xóa $count thông báo đã đọc"
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get notifications by status
    $status = $_GET['status'] ?? 'all';
    $notifications = getNotificationsByStatus($pdo, $userId, $status);
    echo json_encode([
        'success' => true,
        'data' => $notifications
    ]);
}
