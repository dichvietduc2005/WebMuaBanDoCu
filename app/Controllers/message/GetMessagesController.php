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
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE box_chat_id = ? ORDER BY sent_at ASC");
    $stmt->execute([$userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($messages);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}




?>