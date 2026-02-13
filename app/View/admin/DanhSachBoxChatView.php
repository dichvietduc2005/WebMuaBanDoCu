<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  header('Location: ' . BASE_URL . 'app/View/user/login.php');
  exit;
}

$currentAdminPage = 'messages';
$pageTitle = 'Tin nhắn từ người dùng';

// Danh sách box chat chưa đọc
$chat_list = [];
$unread_count = 0;
try {
  // Lấy toàn bộ box chat + tin nhắn cuối cùng (preview)
  $sql = "
        SELECT 
            u.username, 
            u.full_name,
            b.user_id,
            b.is_read,
            m_last.content AS last_message,
            m_last.sent_at AS last_sent_at
        FROM 
            box_chat AS b
        JOIN 
            users AS u ON b.user_id = u.id
        LEFT JOIN (
            SELECT m1.box_chat_id, m1.content, m1.sent_at
            FROM messages m1
            INNER JOIN (
                SELECT box_chat_id, MAX(sent_at) AS last_sent_at
                FROM messages
                GROUP BY box_chat_id
            ) latest ON latest.box_chat_id = m1.box_chat_id AND latest.last_sent_at = m1.sent_at
        ) AS m_last ON m_last.box_chat_id = b.user_id
        WHERE u.id != :current_user_id
        ORDER BY 
            b.is_read ASC,        -- chưa đọc trước
            COALESCE(m_last.sent_at, '1970-01-01') DESC
    ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['current_user_id' => $_SESSION['user_id']]);
  $chat_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Đếm số box chưa đọc để hiển thị ở header
  foreach ($chat_list as $row) {
    if ((int) ($row['is_read'] ?? 0) === 0) {
      $unread_count++;
    }
  }
} catch (PDOException $e) {
  error_log('Admin messages error: ' . $e->getMessage());
  $chat_list = [];
}

// Helper hiển thị thời gian tương đối (x phút trước)
function timeAgo(?string $datetime): string
{
  if (!$datetime) {
    return '';
  }
  try {
    $time = new DateTime($datetime);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $time->getTimestamp();
    if ($diff < 60)
      return 'vừa xong';
    $minutes = floor($diff / 60);
    if ($minutes < 60)
      return $minutes . ' phút trước';
    $hours = floor($minutes / 60);
    if ($hours < 24)
      return $hours . ' giờ trước';
    $days = floor($hours / 24);
    if ($days < 7)
      return $days . ' ngày trước';
    return $time->format('d/m/Y H:i');
  } catch (Exception $e) {
    return '';
  }
}

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-[260px_minmax(0,1fr)] -mx-4 md:-mx-6 px-4 md:px-6">
  <!-- Cột danh sách hội thoại -->
  <div
    class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex-shrink-0">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
      <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
          <path
            d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
        </svg>
        Hộp chat người dùng
      </h2>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        <?php echo $unread_count; ?> cuộc hội thoại chưa đọc
      </p>
    </div>

    <div class="h-[500px] overflow-y-auto p-3 space-y-2">
      <?php if (empty($chat_list)): ?>
        <div class="flex flex-col items-center justify-center h-full text-center px-4">
          <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-900 dark:text-white">Không có tin nhắn mới</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tất cả tin nhắn đã được đọc</p>
        </div>
      <?php else: ?>
        <?php foreach ($chat_list as $chat_item): ?>
          <div
            class="user-card group p-3 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:hover:border-blue-600 dark:hover:bg-gray-800/60 cursor-pointer transition-all duration-200"
            data-username="<?php echo htmlspecialchars($chat_item['username'], ENT_QUOTES, 'UTF-8'); ?>"
            data-user-id="<?php echo (int) $chat_item['user_id']; ?>">
            <div class="flex items-start gap-3">
              <!-- Avatar -->
              <div class="relative flex-shrink-0">
                <div
                  class="w-11 h-11 rounded-full bg-gray-600 flex items-center justify-center text-white font-semibold text-sm">
                  <?php echo strtoupper(substr($chat_item['username'], 0, 2)); ?>
                </div>
                <span
                  class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full"></span>
              </div>

              <!-- Thông tin user -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    <?php echo htmlspecialchars($chat_item['full_name'] ?: $chat_item['username'], ENT_QUOTES, 'UTF-8'); ?>
                  </h3>
                  <?php if ((int) ($chat_item['is_read'] ?? 0) === 0): ?>
                    <span
                      class="flex items-center justify-center min-w-[18px] h-5 px-1.5 text-[10px] font-semibold text-white bg-blue-600 rounded-full">
                      1
                    </span>
                  <?php endif; ?>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                  @<?php echo htmlspecialchars($chat_item['username'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <?php
                $lastMessage = $chat_item['last_message'] ?? '';
                $preview = $lastMessage ? mb_strimwidth($lastMessage, 0, 40, '...', 'UTF-8') : 'Chưa có tin nhắn';
                $timeLabel = !empty($chat_item['last_sent_at']) ? timeAgo($chat_item['last_sent_at']) : '';
                ?>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1 truncate">
                  <?php echo htmlspecialchars($preview, ENT_QUOTES, 'UTF-8'); ?>
                  <?php if ($timeLabel): ?>
                    <span class="mx-1 text-gray-400">•</span>
                    <span class="text-gray-400"><?php echo htmlspecialchars($timeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                </p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Cột khung chat -->
  <div class="w-full min-w-0">
    <div
      class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03] h-[650px] flex flex-col w-full">
      <!-- Header chat -->
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 rounded-t-2xl">
        <div class="flex items-center gap-3" id="chatheader">
          <div
            class="w-10 h-10 rounded-full bg-gray-400 flex items-center justify-center text-white font-semibold text-sm">
            ?
          </div>
          <div class="flex-1">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">
              💬 Chọn người dùng để chat
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Chọn một user ở danh sách bên trái
            </p>
          </div>
        </div>
      </div>

      <!-- Khung tin nhắn -->
      <div class="flex-1 overflow-y-auto p-5 bg-gray-50 dark:bg-gray-900/40" id="chatBox"
        style="scroll-behavior: smooth;">
        <div id="messagesBox" class="space-y-4">
          <!-- Tin nhắn sẽ được thêm ở đây -->
          <div class="flex items-center justify-center h-full">
            <div class="text-center">
              <div
                class="w-20 h-20 mx-auto rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
              </div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Bắt đầu cuộc trò chuyện</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Chọn người dùng từ danh sách để xem tin nhắn</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Input area -->
      <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900/40 rounded-b-2xl">
        <div class="flex gap-2">
          <input type="text" onkeydown="on_key_press(event)" id="messageInput" placeholder="Nhập tin nhắn của bạn..."
            class="flex-1 px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 transition-all">
          <button id="sendButton" onclick="send_messages()"
            class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-sm hover:shadow-md disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
            disabled>
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h13M13 5l7 7-7 7" />
            </svg>
            Gửi
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  let userId = null;
  let can_jump_bottom = true;
</script>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin_chat_system.js?v=3"></script>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>