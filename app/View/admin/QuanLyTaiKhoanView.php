<?php 
require_once __DIR__ . '/../../../config/config.php'; 

// Chỉ cho phép admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

// Thiết lập thông tin cho layout admin
$currentAdminPage = 'users';
$pageTitle = 'Quản lý tài khoản';

// Bộ lọc & phân trang
$q = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$page = $page > 0 ? $page : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$where = 'WHERE 1=1';
$params = [];

if ($q !== '') {
    $where .= " AND (username LIKE :kw1 OR email LIKE :kw2 OR full_name LIKE :kw3)";
    $params[':kw1'] = '%' . $q . '%';
    $params[':kw2'] = '%' . $q . '%';
    $params[':kw3'] = '%' . $q . '%';
}

if ($role !== '' && in_array($role, ['user', 'admin'], true)) {
    $where .= " AND role = :role";
    $params[':role'] = $role;
}

if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
    $where .= " AND status = :status";
    $params[':status'] = $status;
}

// Đếm tổng bản ghi
$totalRows = 0;
$totalPages = 1;
$users = [];

// Thống kê nhanh cho summary cards
$totalUsersCount = 0;
$activeUsersCount = 0;
$inactiveUsersCount = 0;
$newUsersThisMonthCount = 0;
$adminUsersCount = 0;

try {
    // Thống kê tổng quan (không phụ thuộc bộ lọc)
    $totalUsersCount = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeUsersCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $inactiveUsersCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();
    $adminUsersCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $newUsersThisMonthCount = (int) $pdo->query("
        SELECT COUNT(*) FROM users
        WHERE YEAR(created_at) = YEAR(CURDATE())
          AND MONTH(created_at) = MONTH(CURDATE())
    ")->fetchColumn();

    $countSql = "SELECT COUNT(*) FROM users $where";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRows = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $limit));

    // Nếu page vượt quá totalPages thì kéo về cuối
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    $sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Admin user management error: ' . $e->getMessage());
    $users = [];
}

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

  <!-- Overlay & popup khóa/mở tài khoản (dùng cho JS admin_accounts_action.js) -->
  <div
    id="modal-overlay"
    class="fixed inset-0 z-40 hidden bg-black/40"
  ></div>

  <div
    id="board-skill"
    user-id=""
    data-action=""
    class="fixed z-50 hidden w-full max-w-md px-6 py-5 text-left bg-white border shadow-2xl rounded-2xl border-gray-200 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 dark:border-gray-700 dark:bg-gray-900"
  >
    <h3 id="account-modal-title" class="text-sm font-semibold text-gray-900 dark:text-white">
      Cập nhật trạng thái tài khoản
    </h3>
    <p id="username-display" class="mt-1 text-xs font-medium text-gray-600 dark:text-gray-300">
      Tài khoản
    </p>
    <p id="account-modal-description" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
      Bạn có chắc chắn muốn thay đổi trạng thái của tài khoản này? Thao tác này không xóa dữ liệu,
      nhưng sẽ ảnh hưởng đến khả năng đăng nhập của người dùng.
    </p>

    <div class="flex justify-end gap-3 mt-6">
      <button
        type="button"
        id="account-modal-cancel"
        class="px-4 py-2 text-xs font-semibold text-gray-500 transition-colors bg-gray-100 rounded-xl hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
      >
        Bỏ qua
      </button>
      <button
        type="button"
        id="block-account-button"
        class="hidden px-5 py-2 text-xs font-bold text-white transition-all bg-red-500 shadow-md rounded-xl hover:bg-red-600 shadow-red-200 dark:shadow-none"
      >
        <i class="fas fa-lock me-1.5"></i>Khóa ngay
      </button>
      <button
        type="button"
        id="unlock-account-button"
        class="hidden px-5 py-2 text-xs font-bold text-white transition-all bg-emerald-500 shadow-md rounded-xl hover:bg-emerald-600 shadow-emerald-200 dark:shadow-none"
      >
        <i class="fas fa-unlock me-1.5"></i>Mở khóa
      </button>
    </div>
  </div>

  <!-- Bộ lọc & bảng tài khoản -->
  <div class="space-y-4">
    <!-- Summary cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
      <!-- Tổng tài khoản -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-sky-50 to-white p-4 shadow-sm dark:border-gray-800 dark:from-sky-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-sky-600 uppercase dark:text-sky-300">
              Tổng tài khoản
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
              <?php echo number_format($totalUsersCount); ?>
            </h2>
            <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
              Trong đó <span class="font-semibold"><?php echo number_format($adminUsersCount); ?></span> admin
            </p>
          </div>
          <div class="flex items-center justify-center w-9 h-9 rounded-2xl bg-sky-500 text-white shadow-sm shadow-sky-500/40">
            <i class="fas fa-users text-xs"></i>
          </div>
        </div>
      </div>

      <!-- Active -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm dark:border-gray-800 dark:from-emerald-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-300">
              Đang hoạt động
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">
              <?php echo number_format($activeUsersCount); ?>
            </h2>
            <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
              Có thể đăng nhập và sử dụng hệ thống
            </p>
          </div>
          <div class="flex items-center justify-center w-9 h-9 rounded-2xl bg-emerald-500 text-white shadow-sm shadow-emerald-500/40">
            <i class="fas fa-user-check text-xs"></i>
          </div>
        </div>
      </div>

      <!-- Đã khóa -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-rose-50 to-white p-4 shadow-sm dark:border-gray-800 dark:from-rose-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-rose-600 uppercase dark:text-rose-300">
              Tài khoản bị khóa
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-rose-700 dark:text-rose-300">
              <?php echo number_format($inactiveUsersCount); ?>
            </h2>
            <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
              Không thể đăng nhập cho đến khi được mở khóa
            </p>
          </div>
          <div class="flex items-center justify-center w-9 h-9 rounded-2xl bg-rose-500 text-white shadow-sm shadow-rose-500/40">
            <i class="fas fa-user-lock text-xs"></i>
          </div>
        </div>
      </div>

      <!-- User mới tháng này -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-indigo-50 to-white p-4 shadow-sm dark:border-gray-800 dark:from-indigo-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-indigo-600 uppercase dark:text-indigo-300">
              User mới tháng này
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-indigo-700 dark:text-indigo-300">
              <?php echo number_format($newUsersThisMonthCount); ?>
            </h2>
            <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
              Tài khoản tạo trong tháng <?php echo date('m / Y'); ?>
            </p>
          </div>
          <div class="flex items-center justify-center w-9 h-9 rounded-2xl bg-indigo-500 text-white shadow-sm shadow-indigo-500/40">
            <i class="fas fa-user-plus text-xs"></i>
          </div>
        </div>
      </div>
    </div>
    <!-- Bộ lọc -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <form method="get" action="<?php echo BASE_URL; ?>public/admin/index.php" class="grid items-end grid-cols-1 gap-3 md:grid-cols-4">
        <input type="hidden" name="page" value="users">
        <div>
          <label class="block mb-1 text-xs font-medium text-gray-500 uppercase">Từ khóa</label>
          <input
            type="text"
            name="q"
            value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="Username, email, tên..."
            class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
          >
        </div>
        <div>
          <label class="block mb-1 text-xs font-medium text-gray-500 uppercase">Vai trò</label>
          <select
            name="role"
            class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
          >
            <option value="">Tất cả</option>
            <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
          </select>
        </div>
        <div>
          <label class="block mb-1 text-xs font-medium text-gray-500 uppercase">Trạng thái</label>
          <select
            name="status"
            class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
          >
            <option value="">Tất cả</option>
            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button
            type="submit"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
          >
            Lọc
          </button>
          <a
            href="<?php echo BASE_URL; ?>public/admin/index.php?page=users"
            class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-gray-600 border rounded-lg border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
          >
            Xóa lọc
          </a>
        </div>
      </form>
      <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        Tổng: <span class="font-semibold text-gray-700 dark:text-gray-200"><?php echo number_format($totalRows); ?></span> tài khoản
      </p>
    </div>

    <!-- Bảng tài khoản -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700 align-middle dark:text-gray-200">
          <thead class="text-xs font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/60 dark:text-gray-400">
            <tr>
              <th class="px-4 py-3">Username</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Họ tên</th>
              <th class="px-4 py-3">Vai trò</th>
              <th class="px-4 py-3">Trạng thái</th>
              <th class="px-4 py-3">Điện thoại</th>
              <th class="px-4 py-3">Địa chỉ</th>
              <th class="px-4 py-3 text-right">Hành động</th>
                </tr>
            </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <?php if (empty($users)): ?>
              <tr>
                <td colspan="7" class="px-4 py-6 text-sm text-center text-gray-500 dark:text-gray-400">
                  Không tìm thấy tài khoản nào.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($users as $row): ?>
                <?php
                  $statusClass = $row['status'] === 'active'
                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                    : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300';
                  $roleClass = $row['role'] === 'admin'
                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'
                    : 'bg-gray-50 text-gray-700 dark:bg-gray-700/50 dark:text-gray-200';
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                  <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3">
                    <?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3">
                    <?php echo htmlspecialchars($row['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php echo $roleClass; ?>">
                      <?php echo htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                      <?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3 max-w-xs truncate">
                    <?php echo htmlspecialchars($row['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <?php $isActive = $row['status'] === 'active'; ?>
                    <div class="inline-flex items-center justify-end gap-2">
                      <button
                        type="button"
                        class="js-user-action inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-xl transition-all <?php echo $isActive ? 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400'; ?>"
                        data-user-id="<?php echo (int) $row['id']; ?>"
                        data-username="<?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-action="<?php echo $isActive ? 'block' : 'unlock'; ?>"
                        title="<?php echo $isActive ? 'Khóa tài khoản' : 'Mở khóa tài khoản'; ?>"
                      >
                        <?php if ($isActive): ?>
                          <i class="fas fa-shield-halved"></i>
                          <span>Khóa</span>
                        <?php else: ?>
                          <i class="fas fa-lock-open"></i>
                          <span>Mở khóa</span>
                        <?php endif; ?>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
      </div>

      <!-- Phân trang -->
      <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between px-4 py-3 text-xs text-gray-500 border-t border-gray-100 dark:border-gray-800 dark:text-gray-400">
          <div>
            Trang
            <span class="font-semibold text-gray-700 dark:text-gray-200"><?php echo $page; ?></span>
            /
            <span class="font-semibold text-gray-700 dark:text-gray-200"><?php echo $totalPages; ?></span>
          </div>
          <div class="flex items-center gap-1">
            <?php
              $baseUrl = BASE_URL . 'public/admin/index.php?page=users';
              $queryBase = [
                'q' => $q,
                'role' => $role,
                'status' => $status,
              ];
              $buildLink = function ($p) use ($baseUrl, $queryBase) {
                  $queryBase['p'] = $p;
                  return $baseUrl . '&' . http_build_query($queryBase);
              };
            ?>
            <a
              href="<?php echo $page > 1 ? htmlspecialchars($buildLink($page - 1), ENT_QUOTES, 'UTF-8') : '#'; ?>"
              class="inline-flex items-center justify-center w-7 h-7 rounded-lg border text-xs <?php echo $page > 1 ? 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800' : 'border-gray-100 text-gray-300 cursor-not-allowed dark:border-gray-800 dark:text-gray-600'; ?>"
            >
              ‹
            </a>
            <a
              href="<?php echo $page < $totalPages ? htmlspecialchars($buildLink($page + 1), ENT_QUOTES, 'UTF-8') : '#'; ?>"
              class="inline-flex items-center justify-center w-7 h-7 rounded-lg border text-xs <?php echo $page < $totalPages ? 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800' : 'border-gray-100 text-gray-300 cursor-not-allowed dark:border-gray-800 dark:text-gray-600'; ?>"
            >
              ›
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Script xử lý khóa/mở khóa tài khoản hiện có -->
  <script src="<?php echo BASE_URL; ?>public/assets/js/admin_accounts_action.js?v=1"></script>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>
