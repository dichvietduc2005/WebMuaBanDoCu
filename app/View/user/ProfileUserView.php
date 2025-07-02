<?php
session_start()
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/profile_user.css">
    <script>
        USER_ID = '<?php echo $_SESSION["user_id"] ?>';
    </script>
    <script src="/WebMuaBanDoCu/public/assets/js/profile_user.js"></script>
</head>

<body>

    <div class="back-button">
        <a href="/WebMuaBanDoCu/app/View/Home.php"> <div><- Back</div></a>
    </div>

    <div class="container">
        <h2><i class="fas fa-user-circle"></i> Thông tin cá nhân</h2>

        <div class="info-group">
            <label>Username:</label>
            <input id="user_name" type="text" value="<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''?>" disabled>
        </div>

        <div class="info-group">
            <label>Họ và tên:</label>
            <input id="user_full_name" type="text" value="<?php echo isset($_SESSION['user_full_name']) ?  $_SESSION['user_full_name'] : ''?>" disabled>
        </div>

        <div class="info-group">
            <label>Email:</label>
            <input id="user_email" type="email" value="<?php echo  isset($_SESSION['user_email']) ?  $_SESSION['user_email'] : ''?>" disabled>
        </div>

        <div class="info-group">
            <label>Số điện thoại:</label>
            <input id="user_phone" type="text" value="<?php echo isset($_SESSION['user_phone']) ?  $_SESSION['user_phone'] : ''?>" disabled>
        </div>

        <div class="info-group">
            <label>Địa chỉ:</label>
            <input id="user_address" type="text" value="<?php echo isset($_SESSION['user_address']) ?  $_SESSION['user_address'] : ''?>" disabled>
        </div>

        <div class="btn-group">
            <button id="#btn-edit" class="btn-edit"><i class="fas fa-edit"></i> Chỉnh sửa</button>
            <button id="#btn-save" class="btn-save"><i class="fas fa-save"></i> Lưu</button>
        </div>
    </div>

</body>

</html>