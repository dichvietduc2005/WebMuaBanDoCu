<?php
require_once __DIR__ . '/../../../config/config.php';

function createNotification($pdo, $data) {
    try {
        $sql = "INSERT INTO notifications (user_id, title, message, type, is_read) 
                VALUES (:user_id, :title, :message, :type, 0)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':user_id' => $data['user_id'] ?: null, // NULL = All users
            ':title' => $data['title'],
            ':message' => $data['message'],
            ':type' => $data['type'] ?? 'admin'
        ]);
        return $result ? true : ['error' => 'Execute failed', 'info' => $stmt->errorInfo()];
    } catch (PDOException $e) {
        return ['error' => $e->getMessage(), 'code' => $e->getCode()];
    }
}

function getSentNotifications($pdo) {
    try {
        $sql = "SELECT n.*, u.username 
                FROM notifications n 
                LEFT JOIN users u ON n.user_id = u.id 
                WHERE n.type = 'admin'
                ORDER BY n.created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function deleteNotification($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

function getAllUsers($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, username, email FROM users WHERE role != 'admin' ORDER BY username ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// User notification management functions
function markNotificationAsRead($pdo, $notificationId, $userId) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function markNotificationAsUnread($pdo, $notificationId, $userId) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 0 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function markAllAsRead($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        return 0;
    }
}

function deleteNotificationForUser($pdo, $notificationId, $userId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function deleteAllReadNotifications($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = :user_id AND is_read = 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        return 0;
    }
}

function getNotificationsByStatus($pdo, $userId, $status = 'all') {
    try {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id";
        
        if ($status === 'unread') {
            $sql .= " AND is_read = 0";
        } elseif ($status === 'read') {
            $sql .= " AND is_read = 1";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
