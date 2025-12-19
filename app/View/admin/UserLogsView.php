<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/UserLogModel.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

$currentAdminPage = 'user_logs';
$pageTitle = 'Lịch sử hoạt động người dùng';

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$action = $_GET['action'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$filters = [
    'user_id' => $userId,
    'action' => $action,
    'from_date' => $fromDate,
    'to_date' => $toDate,
];

$logs = getUserActionLogs($pdo, $filters);
$actionTypes = getUserActionTypes($pdo);

// Map action types to Vietnamese labels
$actionLabels = [
    'login' => 'Đăng nhập',
    'logout' => 'Đăng xuất',
    'login_failed' => 'Đăng nhập thất bại',
    'auto_login' => 'Tự động đăng nhập',
    'register' => 'Đăng ký',
    'create_product' => 'Đăng sản phẩm',
    'create_order' => 'Tạo đơn hàng',
    'payment_success' => 'Thanh toán thành công',
    'payment_failed' => 'Thanh toán thất bại',
    'update_profile' => 'Cập nhật thông tin',
    'change_password' => 'Đổi mật khẩu',
    'add_to_cart' => 'Thêm vào giỏ hàng',
    'remove_from_cart' => 'Xóa khỏi giỏ hàng',
    'view_product' => 'Xem sản phẩm',
    'view_products' => 'Xem danh sách sản phẩm',
    'search' => 'Tìm kiếm',
    'review_product' => 'Đánh giá sản phẩm',
];

function getActionLabel(string $action): string {
    global $actionLabels;
    return $actionLabels[$action] ?? ucfirst(str_replace('_', ' ', $action));
}

function getActionBadgeClass(string $action): string {
    $map = [
        'login' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        'logout' => 'bg-gray-50 text-gray-700 dark:bg-gray-700/50 dark:text-gray-200',
        'login_failed' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
        'auto_login' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        'register' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
        'create_product' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300',
        'create_order' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        'payment_success' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        'payment_failed' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
        'update_profile' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
        'change_password' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300',
    ];
    return $map[$action] ?? 'bg-gray-50 text-gray-700 dark:bg-gray-700/50 dark:text-gray-200';
}

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

  <div class="space-y-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Lịch sử hoạt động người dùng
          </h2>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Theo dõi các hành vi và hoạt động của người dùng trên hệ thống.
          </p>
        </div>
        <form method="get" action="<?php echo BASE_URL; ?>public/admin/index.php" class="flex flex-wrap items-end gap-2 text-xs md:justify-end">
          <input type="hidden" name="page" value="user_logs">
          <div class="flex flex-col">
            <label for="user_id" class="mb-1 text-[11px] text-gray-500 dark:text-gray-400">User ID</label>
            <input
              type="number"
              id="user_id"
              name="user_id"
              value="<?php echo htmlspecialchars((string)$userId, ENT_QUOTES, 'UTF-8'); ?>"
              class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              placeholder="VD: 1"
            >
          </div>
          <div class="flex flex-col">
            <label for="action" class="mb-1 text-[11px] text-gray-500 dark:text-gray-400">Hành động</label>
            <select
              id="action"
              name="action"
              class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
            >
              <option value="">Tất cả</option>
              <?php foreach ($actionTypes as $act): ?>
                <option value="<?php echo htmlspecialchars($act, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $action === $act ? 'selected' : ''; ?>>
                  <?php echo getActionLabel($act); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="flex flex-col">
            <label for="from_date" class="mb-1 text-[11px] text-gray-500 dark:text-gray-400">Từ ngày</label>
            <input
              type="date"
              id="from_date"
              name="from_date"
              value="<?php echo htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8'); ?>"
              class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
            >
          </div>
          <div class="flex flex-col">
            <label for="to_date" class="mb-1 text-[11px] text-gray-500 dark:text-gray-400">Đến ngày</label>
            <input
              type="date"
              id="to_date"
              name="to_date"
              value="<?php echo htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8'); ?>"
              class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
            >
          </div>
          <button
            type="submit"
            class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
          >
            <i class="mr-1 fas fa-filter"></i> Lọc
          </button>
        </form>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <?php if (empty($logs)): ?>
        <div class="px-6 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
          Chưa có log hoạt động nào khớp với bộ lọc hiện tại.
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700 align-middle dark:text-gray-200">
            <thead class="text-xs font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/60 dark:text-gray-400">
              <tr>
                <th class="px-4 py-3">Thời gian</th>
                <th class="px-4 py-3">Người dùng</th>
                <th class="px-4 py-3">Hành động</th>
                <th class="px-4 py-3">Mô tả</th>
                <th class="px-4 py-3">Chi tiết</th>
                <th class="px-4 py-3">IP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <?php foreach ($logs as $log): ?>
                <?php
                  $detailsPreview = '';
                  if (!empty($log['details'])) {
                      $decoded = json_decode($log['details'], true);
                      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                          $pairs = [];
                          foreach ($decoded as $k => $v) {
                              if ($v === null || $v === '') continue;
                              if (is_array($v)) continue;
                              $pairs[] = htmlspecialchars($k . ': ' . (string)$v, ENT_QUOTES, 'UTF-8');
                          }
                          $detailsPreview = implode(' • ', array_slice($pairs, 0, 3));
                      } else {
                          $detailsPreview = htmlspecialchars(mb_strimwidth($log['details'], 0, 100, '...'), ENT_QUOTES, 'UTF-8');
                      }
                  }
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                  <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap dark:text-gray-400">
                    <?php echo htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3">
                    <?php if (!empty($log['user_id'])): ?>
                      <div class="flex flex-col">
                        <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                          <?php echo htmlspecialchars($log['username'] ?? $log['full_name'] ?? ('#' . $log['user_id']), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">
                          ID: <?php echo (int)$log['user_id']; ?>
                        </span>
                      </div>
                    <?php else: ?>
                      <span class="text-xs text-gray-400">Khách</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full <?php echo getActionBadgeClass($log['action']); ?>">
                      <?php echo getActionLabel($log['action']); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">
                    <?php echo htmlspecialchars(mb_strimwidth($log['description'] ?? '', 0, 80, '...'), ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                    <?php echo $detailsPreview !== '' ? $detailsPreview : '<span class="text-gray-400">-</span>'; ?>
                  </td>
                  <td class="px-4 py-3 text-[11px] text-gray-500 whitespace-nowrap dark:text-gray-400">
                    <?php echo htmlspecialchars($log['ip_address'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>

