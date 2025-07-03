<?php 
require_once('../../../config/config.php'); 

if ($_SESSION['user_role'] != 'admin') {
    header("Location: /WebMuaBanDoCu/app/View/Home.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/danh_sach_tai_khoan_admin.css">
    <script src="/WebMuaBanDoCu/public/assets/js/admin_accounts_action.js?v=1"></script>
</head>

<body>
    <h1>Quản lý tài khoản</h1>

    <div id="#board-skill" user-id=""
        style="visibility: hidden; padding: 10px;height: 100px;background-color: burlywood;position: fixed;left: 50%;top: 50%;translate: -50%;display: flex; justify-content: center; padding-top: 20px; gap: 40px;align-items: center;">
        <p style="position: absolute; top: 0;">~<span id="#username-display"></span>~</p>
        <button id="block-account-button" style="flex-basis: 200px; height: 50px">Khóa</button>
        <button id="unlock-account-button" style="flex-basis: 200px; height: 50px">Mở khóa</button>
    </div>


    <div>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Fullname</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $stmt = $pdo->prepare("SELECT * FROM users");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $row) {
                    echo "<tr class='user-row' data-user-id='{$row['id']}' data-username='" . htmlspecialchars($row['username'], ENT_QUOTES) . "'>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['full_name'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['address'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "</tr>";
                }
                ?>

            </tbody>
        </table>
    </div>


</body>

</html>