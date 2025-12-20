<?php
// Test file để debug notification API
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/NotificationModel.php';

header('Content-Type: application/json');

// Test connection
if (!isset($pdo)) {
    echo json_encode(['error' => 'PDO not initialized', 'config_loaded' => false]);
    exit;
}

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications");
    $count = $stmt->fetchColumn();
    echo json_encode([
        'status' => 'OK',
        'pdo_connected' => true,
        'notifications_count' => $count,
        'test' => 'Database connection successful'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
