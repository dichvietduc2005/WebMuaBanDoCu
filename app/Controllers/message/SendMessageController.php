<?php
require_once('../../../config/config.php');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ' . BASE_URL . 'app/View/user/login.php');
    exit;
}

if ($_POST['role'] === 'user') {
    try {

        $sql = "SELECT * FROM box_chat WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $box_chat_id = null;

        if (!$row) {
            $sql = "INSERT INTO box_chat (user_id) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $box_chat_id = $pdo->lastInsertId();
        } else {
            $sql = "UPDATE box_chat SET is_read = 0 WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $box_chat_id = $row['id'];
        }

        $sql = "INSERT INTO messages (box_chat_id, role, content, sent_at) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$box_chat_id, 'user', $_POST['content']]);

        echo "success";
    } catch (PDOException $e) {
        echo ($e->getMessage());
        exit;
    }
    exit;
} else {
    try {
        $targetUserId = $_POST['box_chat_id']; // JS sends user_id here

        // 1. Tìm hoặc tạo box_chat cho user này
        $stmt = $pdo->prepare("SELECT id FROM box_chat WHERE user_id = ?");
        $stmt->execute([$targetUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $boxChatId = $row['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO box_chat (user_id) VALUES (?)");
            $stmt->execute([$targetUserId]);
            $boxChatId = $pdo->lastInsertId();
        }

        // 2. Insert tin nhắn với boxChatId thực tế
        $sql = "INSERT INTO messages (box_chat_id, role, content, sent_at) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$boxChatId, 'admin', $_POST['content']]);

        // 3. Update trạng thái (vẫn dùng user_id là đúng cho query này)
        $sql = "UPDATE box_chat SET is_read = 1 WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$targetUserId]);

        echo "success";
    } catch (PDOException $e) {
        echo ($e->getMessage());
        exit;
    }
}



?>