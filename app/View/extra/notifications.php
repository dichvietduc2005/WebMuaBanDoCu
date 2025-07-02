<?php
require_once('../../../config/config.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông báo của tôi</title>
    <link rel="stylesheet" href="../../../public/assets/css/footer.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/user_box_chat.css?v=1.2">
    <style>
        .notification {
            border-bottom: 1px solid #eee;
            padding: 16px 0;
        }

        .notification.unread {
            background: #e9f5ff;
        }

        .notification .time {
            color: #888;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <?php
    renderHeader($pdo);
    ?>
    <div class="container mt-5">
        <h2>Thông báo</h2>
        <?php if (empty($notifications)): ?>
            <p>Không có thông báo nào.</p>
        <?php else: ?>
            <?php foreach ($notifications as $noti): ?>
                <div class="notification<?php echo !$noti['is_read'] ? ' unread' : ''; ?>">
                    <div><?php echo $noti['message']; ?></div>
                    <div class="time"><?php echo date('d/m/Y H:i', strtotime($noti['created_at'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script>userId = <?php echo $_SESSION['user_id'] ?></script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php footer(); ?>
</body>

</html>