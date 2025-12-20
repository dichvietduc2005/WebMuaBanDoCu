<?php
// app/Models/admin/NotificationQueueModel.php

function addToQueue($pdo, $userId, $templateCode, $data, $scheduledAt) {
    $sql = "INSERT INTO notification_queue (user_id, template_code, data, scheduled_at, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $userId,
        $templateCode,
        json_encode($data),
        $scheduledAt
    ]);
}

function getPendingNotifications($pdo, $limit = 50) {
    $sql = "SELECT q.*, t.title, t.message_template, t.type 
            FROM notification_queue q
            JOIN notification_templates t ON q.template_code = t.code
            WHERE q.status = 'pending' AND q.scheduled_at <= NOW() AND t.is_active = 1
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markAsSent($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE notification_queue SET status = 'sent', sent_at = NOW() WHERE id = ?");
    return $stmt->execute([$id]);
}

function markAsFailed($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE notification_queue SET status = 'failed' WHERE id = ?");
    return $stmt->execute([$id]);
}

function cleanupOldQueue($pdo, $days = 30) {
    $stmt = $pdo->prepare("DELETE FROM notification_queue WHERE status IN ('sent', 'failed') AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
    return $stmt->execute([$days]);
}
