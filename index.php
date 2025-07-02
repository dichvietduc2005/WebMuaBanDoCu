<?php
/**
 * File kiểm tra cơ bản cho website
 */

// Hiển thị thông báo đơn giản
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra trang web</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 100px auto;
            max-width: 800px;
            padding: 20px;
        }
        .success {
            color: green;
            padding: 20px;
            border: 1px solid green;
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
    <h1>Kiểm tra website</h1>
    <div class="success">
        <h2>Website hoạt động bình thường!</h2>
        <p>Trang này đang chạy từ thư mục gốc của website.</p>
    </div>
    
    <p>Thử truy cập các liên kết sau để kiểm tra các thư mục khác:</p>
    <a href="public/index.php" class="button">Truy cập public/index.php</a>
    <a href="app/View/Home.php" class="button">Truy cập app/View/Home.php</a>
</body>
</html> 