<?php
require_once('../../../config/config.php');


if (!isset($_POST['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit;
}

$userId = $_POST['user_id'] ?? null;
if (!$userId) {
    throw new Exception("User ID is required");
}


try {
    // 1. Tìm box_chat_id từ user_id
    $stmt = $pdo->prepare("SELECT id FROM box_chat WHERE user_id = ?");
    $stmt->execute([$userId]);
    $boxChat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$boxChat) {
        // Chưa có box chat -> Chưa có tin nhắn
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }

    $boxChatId = $boxChat['id'];

    // 2. Lấy tin nhắn theo box_chat_id thực tế
    $stmt = $pdo->prepare("SELECT id, box_chat_id, role, content, sent_at FROM messages WHERE box_chat_id = ? ORDER BY sent_at ASC");
    $stmt->execute([$boxChatId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($messages);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}




?>