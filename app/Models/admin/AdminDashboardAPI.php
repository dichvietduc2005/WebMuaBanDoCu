<?php
// API đơn giản cho dashboard admin: trả về một số số liệu realtime

require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('DB connection not available');
    }

    // Tổng đơn hàng
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = (int)$stmt->fetchColumn();

    // Số đơn đã thanh toán
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'");
    $paidOrders = (int)$stmt->fetchColumn();

    // Đơn đang chờ xử lý
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $pendingOrders = (int)$stmt->fetchColumn();

    // Tin nhắn chưa đọc
    $stmt = $pdo->query("SELECT COUNT(*) FROM box_chat WHERE is_read = 0");
    $unreadMessages = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'metrics' => [
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'pending_orders' => $pendingOrders,
            'unread_messages' => $unreadMessages,
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('AdminDashboardAPI error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
    ]);
}


