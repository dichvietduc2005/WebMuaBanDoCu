<?php
/**
 * File kiểm tra trong thư mục app
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra trang web - App Folder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 100px auto;
            max-width: 800px;
            padding: 20px;
        }
        .success {
            color: purple;
            padding: 20px;
            border: 1px solid purple;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1>Kiểm tra thư mục app</h1>
    <div class="success">
        <h2>Thư mục app hoạt động bình thường!</h2>
        <p>Trang này đang chạy từ thư mục app của website.</p>
    </div>
    
    <p>Truy cập các trang khác:</p>
    <a href="../index.php" class="button">Về trang chủ</a>
    <a href="../public/index.php" class="button">Thư mục public</a>
</body>
</html> 