<?php
require_once('../../../config/config.php');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Không có quyền']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$message_id = $_POST['message_id'] ?? null;
$new_content = $_POST['content'] ?? null;

if (!$message_id || !$new_content) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu thông tin']);
    exit;
}

try {
    // Kiểm tra tin nhắn có tồn tại và thuộc về admin không
    $stmt = $pdo->prepare("SELECT id, role, sent_at FROM messages WHERE id = ? AND role = 'admin'");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        http_response_code(404);
        echo json_encode(['error' => 'Tin nhắn không tồn tại hoặc không có quyền sửa']);
        exit;
    }
    
    // Kiểm tra thời gian: chỉ cho phép sửa trong 10 phút
    $sent_at = new DateTime($message['sent_at']);
    $now = new DateTime();
    $diff_minutes = ($now->getTimestamp() - $sent_at->getTimestamp()) / 60;
    
    if ($diff_minutes > 10) {
        http_response_code(403);
        echo json_encode(['error' => 'Chỉ có thể sửa tin nhắn trong vòng 10 phút']);
        exit;
    }
    
    // Cập nhật tin nhắn
    $stmt = $pdo->prepare("UPDATE messages SET content = ? WHERE id = ?");
    $stmt->execute([$new_content, $message_id]);
    
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật tin nhắn']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi: ' . $e->getMessage()]);
}

