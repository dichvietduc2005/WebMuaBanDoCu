<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/UserLogModel.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

$currentAdminPage = 'user_behavior';
$pageTitle = 'Thống kê hành vi người dùng';

// Thống kê tổng quan
$stats = [
    'total_actions' => 0,
    'total_users' => 0,
    'actions_today' => 0,
    'actions_this_month' => 0,
    'top_actions' => [],
    'top_users' => [],
    'actions_by_day' => [],
    'actions_by_type' => []
];

try {
    // Tổng số hành động
    $stmt = $pdo->query("SELECT COUNT(*) FROM user_logs");
    $stats['total_actions'] = (int) $stmt->fetchColumn();

    // Tổng số người dùng đã có hoạt động
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM user_logs WHERE user_id IS NOT NULL");
    $stats['total_users'] = (int) $stmt->fetchColumn();

    // Hành động hôm nay
    $stmt = $pdo->query("SELECT COUNT(*) FROM user_logs WHERE DATE(created_at) = CURDATE()");
    $stats['actions_today'] = (int) $stmt->fetchColumn();

    // Hành động tháng này
    $stmt = $pdo->query("SELECT COUNT(*) FROM user_logs WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
    $stats['actions_this_month'] = (int) $stmt->fetchColumn();

    // Top 5 hành động phổ biến nhất
    $stmt = $pdo->query("
        SELECT action, COUNT(*) as count 
        FROM user_logs 
        GROUP BY action 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $stats['top_actions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 người dùng hoạt động nhiều nhất
    $stmt = $pdo->query("
        SELECT l.user_id, u.username, u.full_name, COUNT(*) as action_count
        FROM user_logs l
        LEFT JOIN users u ON l.user_id = u.id
        WHERE l.user_id IS NOT NULL
        GROUP BY l.user_id, u.username, u.full_name
        ORDER BY action_count DESC
        LIMIT 5
    ");
    $stats['top_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hành động theo ngày (7 ngày gần nhất)
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM user_logs
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at)
    ");
    $stats['actions_by_day'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hành động theo loại (top 10)
    $stmt = $pdo->query("
        SELECT action, COUNT(*) as count
        FROM user_logs
        GROUP BY action
        ORDER BY count DESC
        LIMIT 10
    ");
    $stats['actions_by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('User behavior dashboard error: ' . $e->getMessage());
}

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

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid gap-5 md:grid-cols-4">
      <!-- Tổng số hành động -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-indigo-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-indigo-600 uppercase dark:text-indigo-300">
              Tổng số hành động
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
              <?php echo number_format($stats['total_actions']); ?>
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
              Tất cả thời gian
            </p>
          </div>
          <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-500 text-white shadow-md shadow-indigo-500/40">
            <i class="fas fa-chart-line text-sm"></i>
          </div>
        </div>
      </div>

      <!-- Người dùng đã hoạt động -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-emerald-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-300">
              Người dùng đã hoạt động
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
              <?php echo number_format($stats['total_users']); ?>
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
              Có hoạt động trên hệ thống
            </p>
          </div>
          <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-emerald-500 text-white shadow-md shadow-emerald-500/40">
            <i class="fas fa-users text-sm"></i>
          </div>
        </div>
      </div>

      <!-- Hành động hôm nay -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-sky-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-sky-600 uppercase dark:text-sky-300">
              Hành động hôm nay
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
              <?php echo number_format($stats['actions_today']); ?>
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
              <?php echo date('d/m/Y'); ?>
            </p>
          </div>
          <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-sky-500 text-white shadow-md shadow-sky-500/40">
            <i class="fas fa-calendar-day text-sm"></i>
          </div>
        </div>
      </div>

      <!-- Hành động tháng này -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-amber-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-amber-600 uppercase dark:text-amber-300">
              Hành động tháng này
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
              <?php echo number_format($stats['actions_this_month']); ?>
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
              <?php echo date('m / Y'); ?>
            </p>
          </div>
          <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/40">
            <i class="fas fa-calendar-alt text-sm"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
      <!-- Hành động theo ngày (7 ngày gần nhất) -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
            Hành động theo ngày (7 ngày gần nhất)
          </h3>
        </div>
        <div class="h-72">
          <canvas id="actionsByDayChart" class="w-full h-full"></canvas>
        </div>
      </div>

      <!-- Hành động theo loại -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
            Hành động theo loại
          </h3>
        </div>
        <div class="h-72 max-w-xs mx-auto">
          <canvas id="actionsByTypeChart" class="w-full h-full"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Actions & Top Users -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
      <!-- Top 5 hành động phổ biến -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4">
          Top 5 hành động phổ biến nhất
        </h3>
        <div class="space-y-3">
          <?php if (empty($stats['top_actions'])): ?>
            <p class="text-xs text-gray-500 dark:text-gray-400">Chưa có dữ liệu</p>
          <?php else: ?>
            <?php foreach ($stats['top_actions'] as $index => $item): ?>
              <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900/40">
                <div class="flex items-center gap-3">
                  <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-xs font-semibold">
                    <?php echo $index + 1; ?>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      <?php echo getActionLabel($item['action']); ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      <?php echo number_format($item['count']); ?> lần
                    </p>
                  </div>
                </div>
                <div class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                  <?php echo number_format($item['count']); ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Top 5 người dùng hoạt động nhiều nhất -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-4">
          Top 5 người dùng hoạt động nhiều nhất
        </h3>
        <div class="space-y-3">
          <?php if (empty($stats['top_users'])): ?>
            <p class="text-xs text-gray-500 dark:text-gray-400">Chưa có dữ liệu</p>
          <?php else: ?>
            <?php foreach ($stats['top_users'] as $index => $user): ?>
              <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900/40">
                <div class="flex items-center gap-3">
                  <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-xs font-semibold">
                    <?php echo $index + 1; ?>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      <?php echo htmlspecialchars($user['username'] ?? $user['full_name'] ?? 'User #' . $user['user_id'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      ID: <?php echo (int)$user['user_id']; ?>
                    </p>
                  </div>
                </div>
                <div class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                  <?php echo number_format($user['action_count']); ?> hành động
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Link to detailed logs -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
            Xem chi tiết lịch sử hoạt động
          </h3>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Xem tất cả các hành động của người dùng với bộ lọc chi tiết
          </p>
        </div>
        <a
          href="<?php echo BASE_URL; ?>public/admin/index.php?page=user_logs"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
        >
          <i class="mr-2 fas fa-list"></i>
          Xem lịch sử chi tiết
        </a>
      </div>
    </div>
  </div>

  <!-- Chart.js CDN + init script -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
  <script>
    const actionsByDayData = <?php echo json_encode($stats['actions_by_day'], JSON_UNESCAPED_UNICODE); ?>;
    const actionsByTypeData = <?php echo json_encode($stats['actions_by_type'], JSON_UNESCAPED_UNICODE); ?>;

    document.addEventListener('DOMContentLoaded', () => {
      // Hành động theo ngày
      const dayLabels = actionsByDayData.map(item => item.date);
      const dayValues = actionsByDayData.map(item => Number(item.count || 0));
      const ctxDay = document.getElementById('actionsByDayChart');
      
      if (ctxDay && window.Chart) {
        new Chart(ctxDay, {
          type: 'line',
          data: {
            labels: dayLabels,
            datasets: [{
              label: 'Số lượng hành động',
              data: dayValues,
              borderColor: '#4f46e5',
              backgroundColor: 'rgba(79,70,229,0.15)',
              tension: 0.3,
              fill: true,
              borderWidth: 2,
              pointRadius: 4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                labels: {
                  font: {
                    family: 'Roboto, sans-serif'
                  }
                }
              },
              tooltip: {
                titleFont: {
                  family: 'Roboto, sans-serif'
                },
                bodyFont: {
                  family: 'Roboto, sans-serif'
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0,
                  font: {
                    family: 'Roboto, sans-serif'
                  }
                }
              },
              x: {
                ticks: {
                  font: {
                    family: 'Roboto, sans-serif'
                  }
                }
              }
            }
          }
        });
      }

      // Hành động theo loại
      const typeLabels = actionsByTypeData.map(item => {
        const actionMap = {
          'login': 'Đăng nhập',
          'logout': 'Đăng xuất',
          'create_product': 'Đăng sản phẩm',
          'add_to_cart': 'Thêm vào giỏ',
          'view_product': 'Xem sản phẩm',
          'search': 'Tìm kiếm',
          'create_order': 'Tạo đơn hàng',
          'payment_success': 'Thanh toán thành công',
          'review_product': 'Đánh giá',
          'view_products': 'Xem danh sách'
        };
        return actionMap[item.action] || item.action;
      });
      const typeValues = actionsByTypeData.map(item => Number(item.count || 0));
      const ctxType = document.getElementById('actionsByTypeChart');
      
      if (ctxType && window.Chart) {
        new Chart(ctxType, {
          type: 'doughnut',
          data: {
            labels: typeLabels,
            datasets: [{
              data: typeValues,
              backgroundColor: [
                '#4f46e5', '#22c55e', '#f59e0b', '#ef4444', '#06b6d4',
                '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1'
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  usePointStyle: true,
                  padding: 12,
                  font: {
                    family: 'Roboto, sans-serif',
                    size: 11
                  }
                }
              },
              tooltip: {
                titleFont: {
                  family: 'Roboto, sans-serif'
                },
                bodyFont: {
                  family: 'Roboto, sans-serif'
                },
                callbacks: {
                  label: (ctx) => {
                    const label = ctx.label || '';
                    const value = ctx.parsed || 0;
                    const total = typeValues.reduce((sum, v) => sum + v, 0) || 1;
                    const percent = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} (${percent}%)`;
                  }
                }
              }
            }
          }
        });
      }
    });
  </script>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>

