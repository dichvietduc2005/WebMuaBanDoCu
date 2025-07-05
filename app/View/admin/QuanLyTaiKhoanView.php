<?php 
require_once('../../../config/config.php'); 
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/danh_sach_tai_khoan_admin.css">
    <script src="/WebMuaBanDoCu/public/assets/js/admin_accounts_action.js?v=1"></script>
</head>

<body>
<?php renderHeader($pdo); ?>

    <h1 style="text-align:center; margin:30px 0; font-weight:700;">Quản lý tài khoản</h1>

    <!-- Overlay for popup -->
    <div id="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:1000;"></div>

    <!-- Popup action board -->
    <div id="board-skill" user-id=""
        style="display:none; padding:20px 30px; background:#ffffff; position:fixed; left:50%; top:50%; transform:translate(-50%, -50%); box-shadow:0 4px 16px rgba(0,0,0,0.2); border-radius:8px; z-index:1001; text-align:center;">
        <p id="username-display" style="font-weight:bold; margin-bottom:16px;"></p>
        <div style="display:flex; gap:20px; justify-content:center;">
            <button id="block-account-button" class="btn btn-danger">Khóa</button>
            <button id="unlock-account-button" class="btn btn-success">Mở khóa</button>
        </div>
    </div>

    <div class="table-container">
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
                    $statusClass = 'status-' . htmlspecialchars($row['status']);
                    echo "<td><span class='status-badge {$statusClass}'>" . htmlspecialchars($row['status']) . "</span></td>";
                    echo "</tr>";
                }
                ?>

            </tbody>
        </table>
    </div>
    <?php footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>