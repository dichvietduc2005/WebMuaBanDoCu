<?php
require_once('../../../config/config.php');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /WebMuaBanDoCu/app/View/user/login.php');
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
            $box_chat_id = $user_id;
        } else {
            $sql = "UPDATE box_chat SET is_read = 0 WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $box_chat_id = $row['user_id'];
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
    try{
        $sql = "INSERT INTO messages (box_chat_id, role, content, sent_at) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['box_chat_id'], 'admin', $_POST['content']]);
    
        $sql = "UPDATE box_chat SET is_read = 1 WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['box_chat_id']]);
        echo "success";
    }catch (PDOException $e) {
        echo ($e->getMessage());
        exit;
    }
}



?>