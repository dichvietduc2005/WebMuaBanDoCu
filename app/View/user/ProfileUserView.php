<?php
require_once __DIR__ . '/../../Components/header/Header.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/profile_user.css?v=1.12">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/user_box_chat.css?v=1.2">

</head>

<body>
    <?php renderHeader($pdo); ?>

    <div class="back-button">
        <a href="/WebMuaBanDoCu/app/View/Home.php">
            <div><- Back</div>
        </a>
    </div>

    <div class="container">
        <h2><i class="fas fa-user-circle"></i> Thông tin cá nhân</h2>

        <div class="info-group">
            <label>Username:</label>
            <input id="user_name" type="text"
                value="<?php echo $_SESSION['username'] ?? '' ?>" disabled>
        </div>

        <div class="info-group">
            <label>Họ và tên:</label>
            <input id="user_full_name" type="text"
                value="<?php echo $_SESSION['user_name'] ?? '' ?>" disabled>
        </div>

        <div class="info-group">
            <label>Email:</label>
            <input id="user_email" type="email"
                value="<?php echo $_SESSION['user_email'] ?? '' ?>" disabled>
        </div>

        <div class="info-group">
            <label>Số điện thoại:</label>
            <input id="user_phone" type="text"
                value="<?php echo $_SESSION['user_phone'] ?? '' ?>" disabled>
        </div>

        <div class="info-group">
            <label>Địa chỉ:</label>
            <input id="user_address" type="text"
                value="<?php echo $_SESSION['user_address'] ?? '' ?>" disabled>
        </div>

        <div class="btn-group">
            <button id="#btn-edit" class="btn-edit"><i class="fas fa-edit"></i> Chỉnh sửa</button>
            <button id="#btn-save" class="btn-save"><i class="fas fa-save"></i> Lưu</button>
        </div>
    </div>

    <!-- Scripts -->

    <script>userId = <?php echo $_SESSION['user_id'] ?> </script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js?v=3"> </script>
    <script src="/WebMuaBanDoCu/public/assets/js/profile_user.js?v=6"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>
</body>

</html>