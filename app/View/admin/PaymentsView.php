<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

$currentAdminPage = 'payments';
$pageTitle = 'Lịch sử thanh toán & doanh thu';

// Lọc đơn hàng theo trạng thái thanh toán
$paymentStatus = $_GET['payment_status'] ?? '';
$allowedStatuses = ['pending', 'paid', 'failed'];

$where = 'WHERE 1=1';
$params = [];

if ($paymentStatus && in_array($paymentStatus, $allowedStatuses, true)) {
    $where .= ' AND payment_status = :ps';
    $params[':ps'] = $paymentStatus;
}

// Thống kê tổng quan
$totalRevenue = 0;
$totalOrders = 0;
$successOrders = 0;
$pendingOrders = 0;
$failedOrders = 0;
$todayRevenue = 0;
$monthRevenue = 0;

// Dữ liệu cho biểu đồ
$revenueByDay = [];
$ordersByPaymentStatus = [];
$ordersByOrderStatus = [];

// Lấy danh sách đơn gần đây
$orders = [];

try {
    // Thống kê tổng quan
    $statsSql = "
        SELECT 
            COUNT(*) as total_orders,
            COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as success_orders,
            COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_orders,
            COUNT(CASE WHEN payment_status = 'failed' THEN 1 END) as failed_orders,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as total_revenue,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' AND DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END), 0) as today_revenue,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN total_amount ELSE 0 END), 0) as month_revenue
        FROM orders
        $where
    ";
    $statsStmt = $pdo->prepare($statsSql);
    foreach ($params as $key => $value) {
        $statsStmt->bindValue($key, $value);
    }
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $totalOrders = (int)($stats['total_orders'] ?? 0);
    $successOrders = (int)($stats['success_orders'] ?? 0);
    $pendingOrders = (int)($stats['pending_orders'] ?? 0);
    $failedOrders = (int)($stats['failed_orders'] ?? 0);
    $totalRevenue = (float)($stats['total_revenue'] ?? 0);
    $todayRevenue = (float)($stats['today_revenue'] ?? 0);
    $monthRevenue = (float)($stats['month_revenue'] ?? 0);

    // Doanh thu theo ngày (7 ngày gần nhất)
    $revenueSql = "
        SELECT DATE(created_at) AS date, SUM(total_amount) AS total
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND payment_status = 'paid'
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at)
    ";
    $revenueStmt = $pdo->query($revenueSql);
    $revenueByDay = $revenueStmt->fetchAll(PDO::FETCH_ASSOC);

    // Đơn hàng theo trạng thái thanh toán
    $paymentStatusSql = "
        SELECT payment_status, COUNT(*) AS count
        FROM orders
        GROUP BY payment_status
    ";
    $paymentStatusStmt = $pdo->query($paymentStatusSql);
    $ordersByPaymentStatus = $paymentStatusStmt->fetchAll(PDO::FETCH_ASSOC);

    // Đơn hàng theo trạng thái đơn hàng
    $orderStatusSql = "
        SELECT status, COUNT(*) AS count
        FROM orders
        GROUP BY status
    ";
    $orderStatusStmt = $pdo->query($orderStatusSql);
    $ordersByOrderStatus = $orderStatusStmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách đơn hàng
    $sql = "
        SELECT 
            o.id,
            o.order_number,
            o.buyer_id,
            o.total_amount,
            o.status,
            o.payment_method,
            o.payment_status,
            o.created_at,
            u.username,
            u.email
        FROM orders o
        LEFT JOIN users u ON o.buyer_id = u.id
        $where
        ORDER BY o.created_at DESC
        LIMIT 100
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Admin payments view error: ' . $e->getMessage());
    $orders = [];
}

// Helper functions để hiển thị trạng thái bằng tiếng Việt
function renderOrderStatusBadge(?string $status): string {
    $status = strtolower((string)$status);
    $map = [
        'pending' => ['Chờ xử lý', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        'success' => ['Thành công', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'completed' => ['Hoàn thành', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'failed' => ['Thất bại', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'],
        'cancelled' => ['Đã hủy', 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
    ];
    [$label, $classes] = $map[$status] ?? ['Không rõ', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];
    return '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full ' . $classes . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
}

function renderPaymentStatusBadge(?string $status): string {
    $status = strtolower((string)$status);
    $map = [
        'pending' => ['Chờ thanh toán', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        'paid' => ['Đã thanh toán', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'success' => ['Đã thanh toán', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'failed' => ['Thanh toán thất bại', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'],
    ];
    [$label, $classes] = $map[$status] ?? ['Không rõ', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];
    return '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full ' . $classes . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
}

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

  <div class="space-y-4">
    <!-- Summary Cards -->
    <div class="grid gap-5 md:grid-cols-4">
      <!-- Tổng doanh thu -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-emerald-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-300">
              Tổng doanh thu
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-emerald-700 dark:text-emerald-300">
              <?php echo number_format($totalRevenue, 0, ',', '.'); ?> đ
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
              Tất cả đơn đã thanh toán
            </p>
          </div>
          <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-emerald-500 text-white shadow-md shadow-emerald-500/40">
            <i class="fas fa-coins text-sm"></i>
          </div>
        </div>
      </div>

      <!-- Doanh thu hôm nay -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-sky-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-sky-600 uppercase dark:text-sky-300">
              Doanh thu hôm nay
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-sky-700 dark:text-sky-300">
              <?php echo number_format($todayRevenue, 0, ',', '.'); ?> đ
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

      <!-- Doanh thu tháng này -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-indigo-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-indigo-600 uppercase dark:text-indigo-300">
              Doanh thu tháng này
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-indigo-700 dark:text-indigo-300">
              <?php echo number_format($monthRevenue, 0, ',', '.'); ?> đ
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
              <?php echo date('m / Y'); ?>
            </p>
          </div>
          <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-500 text-white shadow-md shadow-indigo-500/40">
            <i class="fas fa-wallet text-sm"></i>
          </div>
        </div>
      </div>

      <!-- Tổng đơn hàng -->
      <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-amber-950/40 dark:to-gray-950">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold tracking-wide text-amber-600 uppercase dark:text-amber-300">
              Tổng đơn hàng
            </p>
            <h2 class="mt-2 text-3xl font-semibold text-amber-700 dark:text-amber-300">
              <?php echo number_format($totalOrders); ?>
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
              Đã thanh toán: <?php echo $successOrders; ?> | Chờ: <?php echo $pendingOrders; ?>
            </p>
          </div>
          <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/40">
            <i class="fas fa-box-open text-sm"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Biểu đồ -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
      <!-- Doanh thu 7 ngày gần nhất -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
            Doanh thu 7 ngày gần nhất
          </h3>
        </div>
        <div class="h-72">
          <canvas id="revenueChart" class="w-full h-full"></canvas>
        </div>
      </div>

      <!-- Đơn hàng theo trạng thái thanh toán -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
            Đơn hàng theo trạng thái thanh toán
          </h3>
        </div>
        <div class="h-72 max-w-xs mx-auto">
          <canvas id="paymentStatusChart" class="w-full h-full"></canvas>
        </div>
      </div>
    </div>

    <!-- Bộ lọc -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
        Lịch sử thanh toán
      </h2>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Danh sách đơn hàng và trạng thái thanh toán (giả lập). Dữ liệu này cũng được dùng cho biểu đồ doanh thu trên Dashboard.
      </p>

      <form method="get" class="flex flex-wrap items-end gap-3 mt-4">
        <input type="hidden" name="page" value="payments">
        <div>
          <label class="block mb-1 text-xs font-medium text-gray-500 uppercase">Trạng thái thanh toán</label>
          <select
            name="payment_status"
            class="w-40 px-3 py-2 text-sm border rounded-lg border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
          >
            <option value="">Tất cả</option>
            <option value="pending" <?php echo $paymentStatus === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
            <option value="paid" <?php echo $paymentStatus === 'paid' ? 'selected' : ''; ?>>Đã thanh toán</option>
            <option value="failed" <?php echo $paymentStatus === 'failed' ? 'selected' : ''; ?>>Thất bại</option>
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
            href="<?php echo BASE_URL; ?>public/admin/index.php?page=payments"
            class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-gray-600 border rounded-lg border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
          >
            Xóa lọc
          </a>
        </div>
      </form>
    </div>

    <!-- Bảng lịch sử thanh toán -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700 align-middle dark:text-gray-200">
          <thead class="text-xs font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/60 dark:text-gray-400">
            <tr>
              <th class="px-4 py-3">Mã đơn</th>
              <th class="px-4 py-3">Khách hàng</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Tổng tiền</th>
              <th class="px-4 py-3">PT thanh toán</th>
              <th class="px-4 py-3">Trạng thái đơn</th>
              <th class="px-4 py-3">Trạng thái thanh toán</th>
              <th class="px-4 py-3">Thời gian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="8" class="px-4 py-6 text-sm text-center text-gray-500 dark:text-gray-400">
                  Không có đơn hàng nào phù hợp.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $order): ?>
                <?php
                  $ps = $order['payment_status'];
                  $statusPaymentClass = match ($ps) {
                      'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                      'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                      'failed' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                      default => 'bg-gray-50 text-gray-700 dark:bg-gray-700/50 dark:text-gray-200',
                  };
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                  <td class="px-4 py-3 text-xs font-mono text-indigo-700 dark:text-indigo-300">
                    <?php echo htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3">
                    <?php echo htmlspecialchars($order['username'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3 text-xs">
                    <?php echo htmlspecialchars($order['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <?php echo number_format($order['total_amount']); ?> đ
                  </td>
                  <td class="px-4 py-3 text-xs">
                    <?php echo htmlspecialchars($order['payment_method'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                  <td class="px-4 py-3 text-xs">
                    <?php echo renderOrderStatusBadge($order['status'] ?? null); ?>
                  </td>
                  <td class="px-4 py-3 text-xs">
                    <?php echo renderPaymentStatusBadge($order['payment_status'] ?? null); ?>
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap dark:text-gray-400">
                    <?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Chart.js CDN + init script -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
  <script>
    const revenueData = <?php echo json_encode($revenueByDay, JSON_UNESCAPED_UNICODE); ?>;
    const paymentStatusData = <?php echo json_encode($ordersByPaymentStatus, JSON_UNESCAPED_UNICODE); ?>;
    const orderStatusData = <?php echo json_encode($ordersByOrderStatus, JSON_UNESCAPED_UNICODE); ?>;

    // Helper: build labels & datasets
    function buildTimeSeries(data, labelKey, valueKey) {
      const labels = data.map(item => item[labelKey]);
      const values = data.map(item => Number(item[valueKey] || 0));
      return { labels, values };
    }

    document.addEventListener('DOMContentLoaded', () => {
      // Doanh thu theo ngày
      const rev = buildTimeSeries(revenueData, 'date', 'total');
      const ctxRevenue = document.getElementById('revenueChart');
      if (ctxRevenue && window.Chart) {
        new Chart(ctxRevenue, {
          type: 'line',
          data: {
            labels: rev.labels,
            datasets: [{
              label: 'Doanh thu (đ)',
              data: rev.values,
              borderColor: '#4f46e5',
              backgroundColor: 'rgba(79,70,229,0.15)',
              tension: 0.3,
              fill: true,
              borderWidth: 2,
              pointRadius: 3
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
                  },
                  callback: (value) => new Intl.NumberFormat('vi-VN').format(value)
                },
                suggestedMax: rev.values && rev.values.length
                  ? Math.max(...rev.values) * 1.2
                  : 1
              },
              x: {
                ticks: {
                  maxRotation: 0,
                  autoSkip: true,
                  maxTicksLimit: 7,
                  font: {
                    family: 'Roboto, sans-serif'
                  }
                }
              }
            }
          }
        });
      }

      // Đơn hàng theo trạng thái thanh toán
      const paymentStatusLabels = paymentStatusData.map(item => {
        const status = String(item.payment_status || '').toLowerCase();
        const map = {
          'pending': 'Chờ thanh toán',
          'paid': 'Đã thanh toán',
          'success': 'Đã thanh toán',
          'failed': 'Thanh toán thất bại'
        };
        return map[status] || status;
      });
      const paymentStatusValues = paymentStatusData.map(item => Number(item.count || 0));
      const ctxPaymentStatus = document.getElementById('paymentStatusChart');
      if (ctxPaymentStatus && window.Chart) {
        new Chart(ctxPaymentStatus, {
          type: 'doughnut',
          data: {
            labels: paymentStatusLabels,
            datasets: [{
              data: paymentStatusValues,
              backgroundColor: paymentStatusData.map(item => {
                const status = String(item.payment_status || '').toLowerCase();
                if (status === 'paid' || status === 'success') {
                  return '#22c55e'; // xanh lá
                } else if (status === 'pending') {
                  return '#facc15'; // vàng
                } else if (status === 'failed') {
                  return '#ef4444'; // đỏ
                }
                return '#6b7280'; // xám
              }),
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
                },
                callbacks: {
                  label: (ctx) => {
                    const label = ctx.label || '';
                    const value = ctx.parsed || 0;
                    const total = paymentStatusValues.reduce((sum, v) => sum + v, 0) || 1;
                    const percent = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} đơn (${percent}%)`;
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


