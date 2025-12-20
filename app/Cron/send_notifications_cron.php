<?php
// app/Cron/send_notifications_cron.php

// Đảm bảo chỉ chạy từ CLI hoặc được bảo vệ
if (php_sapi_name() !== 'cli' && !isset($_GET['secret_key'])) {
    die('Access denied');
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/admin/NotificationQueueModel.php';
require_once __DIR__ . '/../Models/admin/NotificationModel.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Lấy các thông báo cần gửi
    $pending = getPendingNotifications($pdo, 50);
    $count = 0;

    foreach ($pending as $item) {
        $data = json_decode($item['data'], true);
        
        // Thay thế biến trong template
        $message = $item['message_template'];
        if ($data) {
            foreach ($data as $key => $value) {
                $message = str_replace("{{$key}}", $value, $message);
            }
        }

        // Tạo thông báo thực tế
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

    echo "Successfully processed $count notifications.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("[" . date('Y-m-d H:i:s') . "] Send Notification Cron Error: " . $e->getMessage());
}
