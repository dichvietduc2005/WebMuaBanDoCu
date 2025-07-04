<?php
require_once('../../../config/config.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  header('Location: /WebMuaBanDoCu/app/View/user/login.php');
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tin nhắn từ người dùng</title>
  <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/admin_box_chat.css?v=1">
  <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/quan_ly_box_chat_admin.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <script>
    let userId = null;
    let can_jump_bottom = true;
  </script>
  <script src="/WebMuaBanDoCu/public/assets/js/admin_chat_system.js?v=1"></script>

</head>

<body>
  <?php renderHeader($pdo); ?>

  <div class="chat-container" id="chatBox">

    <div class="chat-header" id="chatheader">💬 Chat với người dùng</div>

    <div class="container-messages" id="containerMessages">
      <div class="chat-messages" id="messagesBox">
        <!-- Tin nhắn sẽ được thêm ở đây -->

      </div>
    </div>

    <div class="chat-input">
      <input type="text" id="messageInput" placeholder="Nhập tin nhắn...">
      <button onclick="send_messages()">Gửi</button>
    </div>


  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Tên Người Dùng</th>
          <th>Họ và tên</th>
          <th>Đã đọc</th>
        </tr>
      </thead>
      <tbody>

        <?php
        $sql = "SELECT * FROM box_chat WHERE is_read = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
          $stmt_user_full_name = $pdo->prepare("SELECT * FROM users WHERE id = ?");
          $stmt_user_full_name->execute([$row['user_id']]);
          $username_row = $stmt_user_full_name->fetch(PDO::FETCH_ASSOC);

          echo "<tr class='user-row' data-user-id='" . htmlspecialchars($row['user_id']) . "'>";
          echo "<td>" . htmlspecialchars($row['username']) . "</td>";
          echo "<td>" . htmlspecialchars($username_row['full_name']) . "</td>";
          echo "<td>" . ($row['is_read'] ? 'Yes' : 'No') . "</td>";
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