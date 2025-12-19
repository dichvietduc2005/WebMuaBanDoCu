<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/AdminLogModel.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

$currentAdminPage = 'admin_logs';
$pageTitle = 'Lịch sử thao tác admin';

$adminId = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : null;
$action = $_GET['action'] ?? '';
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$filters = [
    'admin_id' => $adminId,
    'action' => $action,
    'product_id' => $productId,
    'from_date' => $fromDate,
    'to_date' => $toDate,
];

$logs = getAdminActionLogs($pdo, $filters);

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

  <div class="space-y-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Lịch sử thao tác admin
          </h2>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Theo dõi các thao tác duyệt, từ chối, ẩn, xóa, gắn nổi bật sản phẩm do admin thực hiện.
          </p>
        </div>
        <form method="get" class="flex flex-wrap items-end gap-2 text-xs md:justify-end">
          <input type="hidden" name="page" value="admin_logs">
          <div class="flex flex-col">
            <label for="admin_id" class="mb-1 text-[11px] text-gray-500 dark:text-gray-400">Admin ID</label>
            <input
              type="number"
              id="admin_id"
              name="admin_id"
              value="<?php echo htmlspecialchars((string)$adminId, ENT_QUOTES, 'UTF-8'); ?>"
              class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              placeholder="VD: 1"
            >
          </div>
          <div class="flex flex-col">
            <label for="action" class="mb-1 text-[11px] text-gray-500 dark:text-gray-400">Action</label>
            <input
              type="text"
              id="action"
              name="action"
              value="<?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>"
              class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              placeholder="vd: product_approve"
            >
          </div>
          <div class="flex flex-col">
            <label for="product_id" class="mb-1 text-[11px] text-gray-500 dark:text-gray-400">Product ID</label>
            <input
              type="number"
              id="product_id"
              name="product_id"
              value="<?php echo htmlspecialchars((string)$productId, ENT_QUOTES, 'UTF-8'); ?>"
              class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              placeholder="VD: 10"
            >
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
          Chưa có log thao tác nào khớp với bộ lọc hiện tại.
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700 align-middle dark:text-gray-200">
            <thead class="text-xs font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/60 dark:text-gray-400">
              <tr>
                <th class="px-4 py-3">Thời gian</th>
                <th class="px-4 py-3">Admin</th>
                <th class="px-4 py-3">Action</th>
                <th class="px-4 py-3">Product ID</th>
                <th class="px-4 py-3">Chi tiết</th>
                <th class="px-4 py-3">IP</th>
                <th class="px-4 py-3">User Agent</th>
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
                          $detailsPreview = implode(' • ', array_slice($pairs, 0, 4));
                      } else {
                          $detailsPreview = htmlspecialchars(mb_strimwidth($log['details'], 0, 120, '...'), ENT_QUOTES, 'UTF-8');
                      }
                  }
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                  <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap dark:text-gray-400">
                    <?php echo htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex flex-col">
                      <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                        <?php echo htmlspecialchars($log['admin_username'] ?? ('#' . $log['admin_id']), ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                      <span class="text-[11px] text-gray-500 dark:text-gray-400">
                        ID: <?php echo (int)$log['admin_id']; ?>
                      </span>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                      <?php echo htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <?php if (!empty($log['product_id'])): ?>
                      <a
                        href="<?php echo BASE_URL; ?>public/admin/index.php?page=products&product_id=<?php echo (int)$log['product_id']; ?>"
                        class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-300"
                      >
                        #<?php echo (int)$log['product_id']; ?>
                      </a>
                    <?php else: ?>
                      <span class="text-xs text-gray-400">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">
                    <?php echo $detailsPreview !== '' ? $detailsPreview : '<span class="text-gray-400">-</span>'; ?>
                  </td>
                  <td class="px-4 py-3 text-[11px] text-gray-500 whitespace-nowrap dark:text-gray-400">
                    <?php echo htmlspecialchars($log['ip_address'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3 text-[11px] text-gray-500 max-w-xs truncate dark:text-gray-400">
                    <?php echo htmlspecialchars($log['user_agent'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
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


