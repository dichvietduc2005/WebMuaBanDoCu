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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/quan_ly_box_chat_admin.css">
  <link rel="stylesheet" href="/WebMuaBanDoCu/public/assets/css/admin_box_chat.css?v=1">

</head>

<body>
  <?php renderHeader($pdo); ?>

  <div class="chat-container-admin" id="chatBox">
    <div class="chat-header-admin" id="chatheader">💬 Chat với người dùng</div>
    <div class="container-messages-admin" id="containerMessages">
      <div class="chat-messages-admin" id="messagesBox">
        <!-- Tin nhắn sẽ được thêm ở đây -->

      </div>
    </div>

    <div class="chat-input-admin">
      <input type="text" onkeydown="on_key_press(event)" id="messageInput" placeholder="Nhập tin nhắn...">
      <button onclick="send_messages()"><i class="fa-solid fa-paper-plane"></i></button>
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
        $sql = "
        SELECT 
            u.username, 
            u.full_name,
            b.user_id,
            b.is_read
        FROM 
            box_chat AS b
        JOIN 
            users AS u ON b.user_id = u.id
        WHERE 
            b.is_read = 0
    ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $chat_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($chat_list as $chat_item) {
          echo "<tr class='user-row' data-username='" . htmlspecialchars($chat_item['username']) . "' data-user-id='" . htmlspecialchars($chat_item['user_id']) . "'>";
          echo "<td>" . htmlspecialchars($chat_item['username']) . "</td>";
          echo "<td>" . htmlspecialchars($chat_item['full_name']) . "</td>";
          echo "<td>No</td>";
          echo "</tr>";
        }

        ?>

      </tbody>
    </table>
  </div>
  <?php footer(); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let userId = null;
    let can_jump_bottom = true;
  </script>
  <script src="/WebMuaBanDoCu/public/assets/js/admin_chat_system.js?v=1"></script>
</body>

</html>