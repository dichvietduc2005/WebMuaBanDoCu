<?php
// app/Controllers/admin/NotificationTemplateController.php

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/NotificationTemplateModel.php';

// Check admin permission
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $message_template = $_POST['message_template'] ?? '';

        if ($id && $title && $message_template) {
            $result = updateTemplate($pdo, $id, [
                'title' => $title,
                'message_template' => $message_template
            ]);

            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $result = toggleTemplate($pdo, $id);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
        }
    } elseif ($action === 'trigger_cart') {
        require_once __DIR__ . '/../../Services/AbandonedCartService.php';
        try {
            $service = new AbandonedCartService($pdo);
            // Manual trigger: check carts older than 1 minute (test mode)
            $count = $service->checkAbandonedCarts(1);
            echo json_encode(['success' => true, 'message' => "Đã quét và thêm $count thông báo vào hàng đợi (Test Mode: > 1 phút)."]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } elseif ($action === 'trigger_send') {
        require_once __DIR__ . '/../../Models/admin/NotificationQueueModel.php';
        require_once __DIR__ . '/../../Models/admin/NotificationModel.php';
        try {
            $pending = getPendingNotifications($pdo, 50);

            $count = 0;
            foreach ($pending as $item) {
                $data = json_decode($item['data'], true);
                $message = $item['message_template'];
                if ($data) {
                    foreach ($data as $key => $value) {
                        $message = str_replace("{{$key}}", $value, $message);
                    }
                }
                $notificationData = [
                    'user_id' => $item['user_id'],
                    'title' => $item['title'],
                    'message' => $message,
                    'type' => $item['type']
                ];
                if (createNotification($pdo, $notificationData)) {
                    markAsSent($pdo, $item['id']);
                    $count++;
                } else {
                    markAsFailed($pdo, $item['id']);
                }
            }
            echo json_encode(['success' => true, 'message' => "Đã gửi thành công $count thông báo."]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'list') {
        $templates = getAllTemplates($pdo);
        echo json_encode(['success' => true, 'data' => $templates]);
    }
}
