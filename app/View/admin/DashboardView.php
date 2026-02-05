<?php
require_once __DIR__ . '/../../../config/config.php';

// Đảm bảo chỉ admin truy cập trực tiếp file này (phòng trường hợp gọi ngoài router)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

// ====== Thống kê nhanh cho Dashboard ======
$totalUsers = 0;
$totalOrders = 0;
$todayRevenue = 0;
$monthRevenue = 0;
$activeProducts = 0;

// Số liệu phục vụ điều hướng nhanh / badge
$pendingProductsCount = 0;
$unreadMessagesCount = 0;

// Số liệu summary + tăng trưởng
$ordersThisMonth = 0;
$ordersLastMonth = 0;
$ordersMonthChange = null; // %

$newUsersThisMonth = 0;
$newUsersLastMonth = 0;
$newUsersMonthChange = null; // %

$lastMonthRevenue = 0;
$monthRevenueChange = null; // %

// Đơn hàng gần đây cho bảng "Đơn hàng gần đây"
$recentOrders = [];

// Dữ liệu cho biểu đồ
$revenueByDay = [];
$ordersByStatus = [];
$newUsersByDay = [];

try {
    // Tổng user (active)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
    $totalUsers = (int) $stmt->fetchColumn();

    // Tổng đơn hàng
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = (int) $stmt->fetchColumn();

    // Doanh thu hôm nay (đơn đã thanh toán)
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM orders 
        WHERE DATE(created_at) = CURDATE() 
          AND payment_status = 'paid'
    ");
    $todayRevenue = (float) $stmt->fetchColumn();

    // Doanh thu tháng hiện tại
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM orders 
        WHERE YEAR(created_at) = YEAR(CURDATE())
          AND MONTH(created_at) = MONTH(CURDATE())
          AND payment_status = 'paid'
    ");
    $monthRevenue = (float) $stmt->fetchColumn();

    // Doanh thu tháng trước
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0)
        FROM orders
        WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
          AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
          AND payment_status = 'paid'
    ");
    $lastMonthRevenue = (float) $stmt->fetchColumn();

    // Sản phẩm đang bán (đã duyệt / active)
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $activeProducts = (int) $stmt->fetchColumn();

    // Đơn hàng tháng hiện tại
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM orders
        WHERE YEAR(created_at) = YEAR(CURDATE())
          AND MONTH(created_at) = MONTH(CURDATE())
    ");
    $ordersThisMonth = (int) $stmt->fetchColumn();

    // Đơn hàng tháng trước
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM orders
        WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
          AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    ");
    $ordersLastMonth = (int) $stmt->fetchColumn();

    // Sản phẩm chờ duyệt
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'pending'");
    $pendingProductsCount = (int) $stmt->fetchColumn();

    // Box chat chưa đọc
    $stmt = $pdo->query("SELECT COUNT(*) FROM box_chat WHERE is_read = 0");
    $unreadMessagesCount = (int) $stmt->fetchColumn();

    // Người dùng mới tháng hiện tại
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM users
        WHERE YEAR(created_at) = YEAR(CURDATE())
          AND MONTH(created_at) = MONTH(CURDATE())
    ");
    $newUsersThisMonth = (int) $stmt->fetchColumn();

    // Người dùng mới tháng trước
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM users
        WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
          AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    ");
    $newUsersLastMonth = (int) $stmt->fetchColumn();

    // Doanh thu theo ngày (7 ngày gần nhất)
    $stmt = $pdo->query("
        SELECT DATE(created_at) AS date, SUM(total_amount) AS total
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND payment_status = 'paid'
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at)
    ");
    $revenueByDay = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Đơn hàng theo trạng thái
    $stmt = $pdo->query("
        SELECT status, COUNT(*) AS count
        FROM orders
        GROUP BY status
    ");
    $ordersByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Người dùng mới theo ngày (7 ngày gần nhất)
    $stmt = $pdo->query("
        SELECT DATE(created_at) AS date, COUNT(*) AS count
        FROM users
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at)
    ");
    $newUsersByDay = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Đơn hàng gần đây (5 đơn mới nhất)
    $stmt = $pdo->query("
        SELECT o.id,
               o.total_amount,
               o.status,
               o.payment_status,
               o.created_at,
               u.full_name AS customer_name
        FROM orders o
        LEFT JOIN users u ON o.buyer_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tính % thay đổi tháng này so với tháng trước
    if ($lastMonthRevenue > 0) {
        $monthRevenueChange = (($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
    }
    if ($ordersLastMonth > 0) {
        $ordersMonthChange = (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100;
    }
    if ($newUsersLastMonth > 0) {
        $newUsersMonthChange = (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100;
    }
} catch (PDOException $e) {
    error_log('Dashboard metrics error: ' . $e->getMessage());
    // Giữ nguyên giá trị mặc định (0 / mảng rỗng) nếu có lỗi
}

// Router public/admin/index.php đã set $currentAdminPage = 'dashboard'
// Tiêu đề trang cho layout
$pageTitle = 'Dashboard tổng quan';

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

<div class="space-y-6">
    <!-- Top Header & Filter Bar (Compact) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Thống kê hệ thống</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Dữ liệu cập nhật theo thời gian thực</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mr-2">Bộ lọc:</span>
            
            <select id="periodFilter" onchange="applyFilters()"
                class="px-3 py-1.5 border-none bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-xs font-medium focus:ring-0">
                <option value="today">Hôm nay</option>
                <option value="week">Tuần này</option>
                <option value="month" selected>Tháng này</option>
                <option value="year">Năm nay</option>
                <option value="7days">7 ngày gần đây</option>
                <option value="30days">30 ngày gần đây</option>
            </select>

            <select id="yearFilter" onchange="applyFilters()" style="display:none;"
                class="px-3 py-1.5 border-none bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-xs font-medium focus:ring-0">
            </select>

            <select id="statusFilter" onchange="applyFilters()"
                class="px-3 py-1.5 border-none bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-xs font-medium focus:ring-0">
                <option value="all">Tất cả đơn</option>
                <option value="pending">Chờ xử lý</option>
                <option value="processing">Đang xử lý</option>
                <option value="completed">Hoàn thành</option>
                <option value="cancelled">Đã hủy</option>
            </select>

            <button onclick="applyFilters()" 
                class="p-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm shadow-emerald-500/20 transition-all duration-200 active:scale-95" title="Làm mới dữ liệu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Top Row: Stat Cards (4 equal columns) -->
    <div id="dashboard-cards" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Doanh thu hôm nay (Moved to #1) -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-emerald-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-300">
                        Doanh thu hôm nay
                    </p>
                    <h2 class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">
                        <span id="card-today-revenue"><?php echo number_format($todayRevenue, 0, ',', '.'); ?></span> đ
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Thanh toán thành công
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                    <i class="fas fa-coins text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Đơn hàng (#2) -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-indigo-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-indigo-600 uppercase dark:text-indigo-300">
                        Quy mô đơn hàng
                    </p>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        <span id="card-total-orders"><?php echo number_format($totalOrders); ?></span>
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Tổng đơn trên hệ thống
                    </p>
                    <?php
                    $orderChangeClass = 'text-[11px] text-gray-400';
                    $orderChangeIcon = 'fa-minus';
                    $orderChangeLabel = 'Mới nhất';
                    if ($ordersMonthChange !== null) {
                        $rounded = round($ordersMonthChange, 1);
                        $prefix = $rounded > 0 ? '+' : '';
                        $orderChangeLabel = $prefix . $rounded . '%';
                        $orderChangeClass = $rounded >= 0 ? 'text-emerald-600' : 'text-red-600';
                        $orderChangeIcon = $rounded >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                    }
                    ?>
                    <p class="mt-1 flex items-center gap-1 <?php echo $orderChangeClass; ?> font-bold">
                        <i class="fas <?php echo $orderChangeIcon; ?> text-[10px]"></i>
                        <span><?php echo $orderChangeLabel; ?></span>
                        <span class="text-gray-400 font-normal">so với tháng trước</span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-500 text-white shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-shopping-basket text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Người dùng (#3) -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-sky-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-sky-600 uppercase dark:text-sky-300">
                        Tổng người dùng
                    </p>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        <span id="card-total-users"><?php echo number_format($totalUsers); ?></span>
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Thành viên đã đăng ký
                    </p>
                    <?php
                    $userChangeClass = 'text-[11px] text-gray-400';
                    $userChangeIcon = 'fa-minus';
                    $userChangeLabel = 'Mới nhất';
                    if ($newUsersMonthChange !== null) {
                        $rounded = round($newUsersMonthChange, 1);
                        $prefix = $rounded > 0 ? '+' : '';
                        $userChangeLabel = $prefix . $rounded . '%';
                        $userChangeClass = $rounded >= 0 ? 'text-emerald-600' : 'text-red-600';
                        $userChangeIcon = $rounded >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                    }
                    ?>
                    <p class="mt-1 flex items-center gap-1 <?php echo $userChangeClass; ?> font-bold">
                        <i class="fas <?php echo $userChangeIcon; ?> text-[10px]"></i>
                        <span><?php echo $userChangeLabel; ?></span>
                        <span class="text-gray-400 font-normal">tăng trưởng</span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500 text-white shadow-lg shadow-sky-500/20">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Doanh thu tháng (#4) -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-amber-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-amber-600 uppercase dark:text-amber-300">
                        Doanh thu tháng này
                    </p>
                    <h2 class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-300">
                        <span id="card-month-revenue"><?php echo number_format($monthRevenue, 0, ',', '.'); ?></span> đ
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Tháng <?php echo date('m/Y'); ?>
                    </p>
                    <?php
                    $revChangeClass = 'text-[11px] text-gray-400';
                    $revChangeIcon = 'fa-minus';
                    $revChangeLabel = '--%';
                    if ($monthRevenueChange !== null) {
                        $rounded = round($monthRevenueChange, 1);
                        $prefix = $rounded > 0 ? '+' : '';
                        $revChangeLabel = $prefix . $rounded . '%';
                        $revChangeClass = $rounded >= 0 ? 'text-emerald-600' : 'text-red-600';
                        $revChangeIcon = $rounded >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                    }
                    ?>
                    <p class="mt-1 flex items-center gap-1 <?php echo $revChangeClass; ?> font-bold">
                        <i class="fas <?php echo $revChangeIcon; ?> text-[10px]"></i>
                        <span><?php echo $revChangeLabel; ?></span>
                        <span class="text-gray-400 font-normal">thu nhập</span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
            </div>
        </div>
    </div>


    <!-- Hàng thứ hai: Biểu đồ + sản phẩm đang bán -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">
        <!-- Biểu đồ Doanh thu (65%) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-7 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90 uppercase tracking-widest">
                    Biểu đồ Doanh thu
                </h3>
            </div>
            <div class="h-80">
                <canvas id="revenueChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Top Selling Products (35%) -->
        <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-3 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Top Sản phẩm bán chạy
                </h3>
            </div>

            <div id="topProductsTable" class="overflow-y-auto flex-1 max-h-[500px] custom-scrollbar">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-8">Đang tải...</p>
            </div>

            <div class="pt-6 mt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase">Sản phẩm đang bán</p>
                    <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                        <?php echo number_format($activeProducts); ?>
                    </h2>
                </div>
                <div class="h-10 w-10 bg-indigo-50 dark:bg-indigo-950/30 rounded-full flex items-center justify-center text-indigo-600">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">
        <!-- Đơn hàng gần đây (60%) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Đơn hàng gần đây
                </h3>
                <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-wider">Xem tất cả</a>
            </div>
            <?php if (empty($recentOrders)): ?>
                <div class="flex flex-col items-center justify-center py-12">
                     <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="text-sm text-gray-400">Chưa có giao dịch nào phát sinh.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-left text-gray-700 align-middle dark:text-gray-200">
                        <thead class="text-[10px] font-bold tracking-widest text-gray-500 uppercase bg-gray-50/50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3">Mã đơn</th>
                                <th class="px-4 py-3">Khách hàng</th>
                                <th class="px-4 py-3 text-right">Tổng tiền</th>
                                <th class="px-4 py-3 text-center">Trạng thái</th>
                                <th class="px-4 py-3 whitespace-nowrap">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                                    <td class="px-4 py-3 font-bold text-indigo-600">
                                        #<?php echo (int)$order['id']; ?>
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        <?php echo htmlspecialchars($order['customer_name'] ?? 'Ẩn danh', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                        <?php echo number_format((float)($order['total_amount'] ?? 0), 0, ',', '.'); ?> đ
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                        $status = strtolower((string)($order['status'] ?? ''));
                                        $payStatus = strtolower((string)($order['payment_status'] ?? ''));
                                        $label = 'Khác';
                                        $classes = 'bg-gray-100 text-gray-700';
                                        if ($payStatus === 'success' || $payStatus === 'paid' || str_contains($status, 'thành công')) {
                                            $label = 'Thành công';
                                            $classes = 'bg-emerald-50 text-emerald-700';
                                        } elseif ($status === 'pending' || str_contains($status, 'chờ')) {
                                            $label = 'Chờ xử lý';
                                            $classes = 'bg-amber-50 text-amber-700';
                                        } elseif ($status === 'cancelled' || str_contains($status, 'hủy')) {
                                            $label = 'Đã hủy';
                                            $classes = 'bg-rose-50 text-rose-700';
                                        }
                                        ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md uppercase tracking-tight <?php echo $classes; ?>">
                                            <?php echo $label; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-[10px] text-gray-400 whitespace-nowrap">
                                        <?php echo date('H:i d/m', strtotime($order['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Khách hàng thân thiết (40%) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Khách hàng thân thiết
                </h3>
            </div>
            <div id="topCustomersTable" class="space-y-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-8">Đang đồng bộ dữ liệu...</p>
            </div>
            
            <div class="mt-8">
                <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Người dùng mới (7 ngày)</h3>
                <div class="h-40">
                    <canvas id="newUsersChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tầng cuối: Thống kê tổng hợp (Summary Table) (100%) -->
    <div id="revenue-table" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Bảng Thống kê Tổng hợp Kỳ báo cáo
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50/50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest">Chỉ số kinh doanh</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-500 uppercase tracking-widest">Giá trị thực tế</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-500 uppercase tracking-widest">Biến động (Growth)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg text-xs"><i class="fas fa-money-bill-wave"></i></span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Tổng doanh thu thanh toán</span>
                            </div>
                        </td>
                        <td id="table-revenue" class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-gray-900 dark:text-white">
                            <?= number_format($monthRevenue, 0, ',', '.') ?> ₫
                        </td>
                        <td id="table-revenue-growth" class="px-6 py-4 whitespace-nowrap text-right">
                             <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600">--</span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="p-2 bg-blue-50 text-blue-600 rounded-lg text-xs"><i class="fas fa-shopping-cart"></i></span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Quy mô đơn hàng</span>
                            </div>
                        </td>
                        <td id="table-orders" class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-gray-900 dark:text-white">
                            <?= number_format($ordersThisMonth) ?>
                        </td>
                        <td id="table-orders-growth" class="px-6 py-4 whitespace-nowrap text-right">
                             <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600">--</span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="p-2 bg-purple-50 text-purple-600 rounded-lg text-xs"><i class="fas fa-user-plus"></i></span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Phát triển người dùng mới</span>
                            </div>
                        </td>
                        <td id="table-new-users" class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-gray-900 dark:text-white">
                            <?= number_format($newUsersThisMonth) ?>
                        </td>
                        <td id="table-users-growth" class="px-6 py-4 whitespace-nowrap text-right">
                             <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600">--</span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="p-2 bg-amber-50 text-amber-600 rounded-lg text-xs"><i class="fas fa-chart-pie"></i></span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Giá trị trung bình đơn (AOV)</span>
                            </div>
                        </td>
                        <td id="table-aov" class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-amber-600">
                            <?= $ordersThisMonth > 0 ? number_format($monthRevenue / $ordersThisMonth, 0, ',', '.') : 0 ?> ₫
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-xs text-gray-400 font-bold">AVG/ORD</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js CDN + init script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
    const revenueData = <?php echo json_encode($revenueByDay, JSON_UNESCAPED_UNICODE); ?>;
    const ordersStatusData = <?php echo json_encode($ordersByStatus, JSON_UNESCAPED_UNICODE); ?>;
    const newUsersData = <?php echo json_encode($newUsersByDay, JSON_UNESCAPED_UNICODE); ?>;

    // Store chart instances globally to update them later
    window.charts = {
        revenue: null,
        status: null,
        users: null
    };
    
    const DEFAULT_AVATAR = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%239ca3af'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'%3E%3C/path%3E%3C/svg%3E";

    // Helper: build labels & datasets with zero-filling for empty dates
    function buildTimeSeries(data, labelKey, valueKey) {
        if (!data || data.length === 0) {
            // Default to today with 0 if no data
            return { 
                labels: [new Date().toLocaleDateString('en-CA')], // YYYY-MM-DD
                values: [0] 
            };
        }
        
        const labels = data.map(item => item[labelKey]);
        const values = data.map(item => Number(item[valueKey] || 0));
        
        return { labels, values };
    }

    function renderTopProducts(products) {
        const container = document.getElementById('topProductsTable');
        if (!products || products.length === 0) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center py-12"><i class="fas fa-box-open text-gray-200 text-4xl mb-4"></i><p class="text-xs text-gray-500 dark:text-gray-400">Đang đồng bộ dữ liệu...</p></div>';
            return;
        }

        let html = '<div class="space-y-3 pr-1">'; // pr-1 to avoid overlap with scrollbar

        products.forEach((product, index) => {
            const productImg = product.image ? (product.image.startsWith('http') ? product.image : '<?= BASE_URL ?>public/' + product.image) : '<?= BASE_URL ?>public/assets/images/placeholder.png';
            
            // Ranks 1, 2, 3 special colors
            let badgeClass = 'bg-gray-100 dark:bg-gray-700 text-gray-500';
            if(index === 0) badgeClass = 'bg-amber-100 text-amber-600 shadow-sm border border-amber-200';
            if(index === 1) badgeClass = 'bg-slate-100 text-slate-500 border border-slate-200';
            if(index === 2) badgeClass = 'bg-orange-100 text-orange-600 border border-orange-200';

            html += `
            <div class="group flex items-center justify-between p-2.5 rounded-2xl border border-gray-100/50 dark:border-gray-800/50 hover:border-indigo-100 dark:hover:border-indigo-900/50 hover:bg-indigo-50/30 dark:hover:bg-indigo-950/10 transition-all duration-300">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="relative flex-shrink-0">
                        <span class="absolute -top-1.5 -left-1.5 w-5 h-5 ${badgeClass} text-[10px] font-bold rounded-lg flex items-center justify-center z-10 shadow-sm transition-transform group-hover:scale-110">
                            ${index + 1}
                        </span>
                        <div class="w-12 h-12 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm">
                            <img src="${productImg}" alt="${product.name}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate" title="${product.name}">${product.name}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded-md">
                                <i class="fas fa-shopping-cart text-[8px]"></i> ${product.total_sold}
                            </span>
                            <span class="text-[10px] text-gray-400">Kho: ${product.stock_quantity}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-3 pl-2 border-l border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-emerald-600">${new Intl.NumberFormat('vi-VN').format(product.total_revenue)}₫</p>
                    <p class="text-[9px] text-gray-400 mt-0.5">${new Intl.NumberFormat('vi-VN').format(product.price)}₫</p>
                </div>
            </div>`;
        });

        html += '</div>';
        container.innerHTML = html;
        container.classList.add('custom-scrollbar');
    }
    // Render Top Customers
    function renderTopCustomers(customers) {
        const container = document.getElementById('topCustomersTable');
        if (!customers || customers.length === 0) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center py-12"><i class="fas fa-users-slash text-gray-200 text-4xl mb-4"></i><p class="text-xs text-gray-500 dark:text-gray-400">Chưa có khách hàng tiêu biểu</p></div>';
            return;
        }

        let html = '';
        customers.forEach((customer, index) => {
            const avatarUrl = customer.avatar ? (customer.avatar.startsWith('http') ? customer.avatar : '<?= BASE_URL ?>public/' + customer.avatar) : DEFAULT_AVATAR;
            html += `
      <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <div class="flex items-center gap-3">
          <div class="relative">
            <img src="${avatarUrl}" alt="${customer.username}" 
                 class="w-10 h-10 rounded-full object-cover border-2 border-purple-200 dark:border-purple-800"
                 onerror="this.onerror=null;this.src='${DEFAULT_AVATAR}';">
            <span class="absolute -top-1 -right-1 w-5 h-5 bg-purple-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">${index + 1}</span>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">${customer.username}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">${customer.order_count} đơn hàng</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm font-bold text-green-600">${new Intl.NumberFormat('vi-VN').format(customer.total_spent)}₫</p>
          <p class="text-xs text-gray-400">${new Date(customer.last_order_date).toLocaleDateString('vi-VN')}</p>
        </div>
      </div>
    `;
        });

        container.innerHTML = html;
    }
    document.addEventListener('DOMContentLoaded', () => {
        // Doanh thu theo ngày
        const rev = buildTimeSeries(revenueData, 'date', 'total');
        const ctxRevenue = document.getElementById('revenueChart');
        if (ctxRevenue && window.Chart) {
            window.charts.revenue = new Chart(ctxRevenue, {
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
                            min: 0,
                            ticks: {
                                precision: 0,
                                font: {
                                    family: 'Roboto, sans-serif'
                                },
                                callback: (value) => new Intl.NumberFormat('vi-VN').format(value)
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10,
                                font: {
                                    family: 'Roboto, sans-serif'
                                }
                            }
                        }
                    }
                }
            });
        }

        // Đơn hàng theo trạng thái
        const statusLabels = ordersStatusData.map(item => item.status || 'khác');
        const statusValues = ordersStatusData.map(item => Number(item.count || 0));
        const ctxStatus = document.getElementById('ordersStatusChart');
        if (ctxStatus && window.Chart) {
            window.charts.status = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: statusLabels.map((raw) => {
                            const label = String(raw || '').toLowerCase();

                            // Thành công
                            if (
                                label === 'paid' ||
                                label === 'completed' ||
                                label === 'success' ||
                                label.includes('thanh toán') ||
                                label.includes('thành công')
                            ) {
                                return '#22c55e'; // xanh lá
                            }

                            // Đang xử lý / chờ
                            if (
                                label === 'pending' ||
                                label === 'processing' ||
                                label.includes('chờ') ||
                                label.includes('đang xử lý')
                            ) {
                                return '#facc15'; // vàng
                            }

                            // Hủy / thất bại
                            if (
                                label === 'cancelled' ||
                                label === 'failed' ||
                                label.includes('hủy') ||
                                label.includes('thất bại')
                            ) {
                                return '#ef4444'; // đỏ
                            }

                            return '#6b7280'; // xám - trạng thái khác
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
                                padding: 12
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const label = ctx.label || '';
                                    const value = ctx.parsed || 0;
                                    const total = statusValues.reduce((sum, v) => sum + v, 0) || 1;
                                    const percent = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} đơn (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Người dùng mới theo ngày
        const nu = buildTimeSeries(newUsersData, 'date', 'count');
        const ctxNewUsers = document.getElementById('newUsersChart');
        if (ctxNewUsers && window.Chart) {
            window.charts.users = new Chart(ctxNewUsers, {
                type: 'bar',
                data: {
                    labels: nu.labels,
                    datasets: [{
                        label: 'Người dùng mới',
                        data: nu.values,
                        backgroundColor: '#22c55e',
                        borderRadius: 6
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
                                stepSize: 1,
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
    });

    // ===== DASHBOARD FILTER FUNCTIONS =====

    // Load available years on page load
    async function loadAvailableYears() {
        try {
            const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/DashboardController.php?action=get_years');
            const data = await response.json();

            if (data.success && data.years) {
                const yearSelect = document.getElementById('yearFilter');
                yearSelect.innerHTML = '';

                data.years.forEach(year => {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    if (year == new Date().getFullYear()) {
                        option.selected = true;
                    }
                    yearSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading years:', error);
        }
    }

    // Show/hide year selector based on period
    document.getElementById('periodFilter').addEventListener('change', function() {
        const yearFilter = document.getElementById('yearFilter');
        if (this.value === 'year') {
            yearFilter.style.display = 'block';
        } else {
            yearFilter.style.display = 'none';
        }
    });

    // Apply filters and reload dashboard data
    async function applyFilters(isInitial = false) {
        const period = document.getElementById('periodFilter').value;
        const year = document.getElementById('yearFilter').value || new Date().getFullYear();
        const status = document.getElementById('statusFilter').value;

        // Save to localStorage
        localStorage.setItem('admin_dashboard_period', period);
        localStorage.setItem('admin_dashboard_year', year);
        localStorage.setItem('admin_dashboard_status', status);

        // If it's initial load, we might not want to re-fetch if we already have initialDashboardData
        // But for simplicity and correctness with persistence, we re-fetch if localStorage was used
        if (isInitial && !localStorage.getItem('admin_dashboard_period')) return;

        // Show loading state
        const containers = ['dashboard-cards', 'topProductsTable', 'topCustomersTable', 'revenueChart', 'newUsersChart'];
        containers.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.opacity = '0.5';
        });

        try {
            const response = await fetch(
                `<?= BASE_URL ?>app/Controllers/admin/DashboardController.php?action=get_stats&period=${period}&year=${year}&status=${status}`
            );
            const result = await response.json();

            if (result.success) {
                updateDashboard(result.data);
            } else {
                console.error('Lỗi khi tải dữ liệu:', result.message);
            }
        } catch (error) {
            console.error('Error applying filters:', error);
        } finally {
            containers.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.opacity = '1';
            });
        }
    }

    // Update dashboard with new data
    function updateDashboard(data) {
        // Update stat cards
        const todayRevEl = document.getElementById('card-today-revenue');
        if (todayRevEl) {
            todayRevEl.textContent = new Intl.NumberFormat('vi-VN').format(data.today_revenue || 0); // Assuming data.today_revenue exists
        }

        const ordersEl = document.getElementById('card-total-orders');
        if (ordersEl) {
            ordersEl.textContent = new Intl.NumberFormat('vi-VN').format(data.total_orders || 0); // Assuming data.total_orders exists
        }

        const usersEl = document.getElementById('card-total-users');
        if (usersEl) {
            usersEl.textContent = new Intl.NumberFormat('vi-VN').format(data.total_users || 0); // Assuming data.total_users exists
        }

        const monthRevEl = document.getElementById('card-month-revenue');
        if (monthRevEl) {
            monthRevEl.textContent = new Intl.NumberFormat('vi-VN').format(data.revenue || 0); // Assuming data.revenue is for the selected period
        }

        // Update growth rate indicators (for orders, users, month revenue)
        // Note: The provided HTML has PHP logic for initial growth rates.
        // This JS part would update them dynamically if the AJAX response included specific growth rates for each card.
        // For now, I'll update the example growth rate for the main revenue card.
        const monthRevenueGrowthEl = document.querySelector('#dashboard-cards > div:nth-child(4) p:last-child span:first-of-type');
        const monthRevenueGrowthIcon = document.querySelector('#dashboard-cards > div:nth-child(4) p:last-child i');
        const monthRevenueGrowthParent = document.querySelector('#dashboard-cards > div:nth-child(4) p:last-child');

        if (monthRevenueGrowthEl && data.month_revenue_change !== undefined) {
            const rounded = Math.round(data.month_revenue_change * 10) / 10;
            const prefix = rounded > 0 ? '+' : '';
            monthRevenueGrowthEl.textContent = `${prefix}${rounded}%`;

            if (monthRevenueGrowthParent) {
                monthRevenueGrowthParent.className = `mt-1 flex items-center gap-1 ${rounded >= 0 ? 'text-emerald-600' : 'text-red-600'} font-bold`;
            }
            if (monthRevenueGrowthIcon) {
                monthRevenueGrowthIcon.className = `fas ${rounded >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'} text-[10px]`;
            }
        }

        const ordersMonthGrowthEl = document.querySelector('#dashboard-cards > div:nth-child(2) p:last-child span:first-of-type');
        const ordersMonthGrowthIcon = document.querySelector('#dashboard-cards > div:nth-child(2) p:last-child i');
        const ordersMonthGrowthParent = document.querySelector('#dashboard-cards > div:nth-child(2) p:last-child');
        if (ordersMonthGrowthEl && data.orders_month_change !== undefined) {
            const rounded = Math.round(data.orders_month_change * 10) / 10;
            const prefix = rounded > 0 ? '+' : '';
            ordersMonthGrowthEl.textContent = `${prefix}${rounded}%`;

            if (ordersMonthGrowthParent) {
                ordersMonthGrowthParent.className = `mt-1 flex items-center gap-1 ${rounded >= 0 ? 'text-emerald-600' : 'text-red-600'} font-bold`;
            }
            if (ordersMonthGrowthIcon) {
                ordersMonthGrowthIcon.className = `fas ${rounded >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'} text-[10px]`;
            }
        }

        const newUsersMonthGrowthEl = document.querySelector('#dashboard-cards > div:nth-child(3) p:last-child span:first-of-type');
        const newUsersMonthGrowthIcon = document.querySelector('#dashboard-cards > div:nth-child(3) p:last-child i');
        const newUsersMonthGrowthParent = document.querySelector('#dashboard-cards > div:nth-child(3) p:last-child');
        if (newUsersMonthGrowthEl && data.new_users_month_change !== undefined) {
            const rounded = Math.round(data.new_users_month_change * 10) / 10;
            const prefix = rounded > 0 ? '+' : '';
            newUsersMonthGrowthEl.textContent = `${prefix}${rounded}%`;

            if (newUsersMonthGrowthParent) {
                newUsersMonthGrowthParent.className = `mt-1 flex items-center gap-1 ${rounded >= 0 ? 'text-emerald-600' : 'text-red-600'} font-bold`;
            }
            if (newUsersMonthGrowthIcon) {
                newUsersMonthGrowthIcon.className = `fas ${rounded >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'} text-[10px]`;
            }
        }


        // Update revenue table
        const tableRevenue = document.getElementById('table-revenue');
        if (tableRevenue) {
            tableRevenue.textContent = new Intl.NumberFormat('vi-VN').format(data.revenue || 0) + ' ₫';
        }

        const tableOrders = document.getElementById('table-orders');
        if (tableOrders) {
            tableOrders.textContent = new Intl.NumberFormat('vi-VN').format(data.orders_count);
        }

        const tableUsers = document.getElementById('table-new-users');
        if (tableUsers) {
            tableUsers.textContent = new Intl.NumberFormat('vi-VN').format(data.new_users);
        }

        const tableAov = document.getElementById('table-aov');
        if (tableAov) {
            tableAov.textContent = new Intl.NumberFormat('vi-VN').format(data.aov) + ' ₫';
        }

        // Update growth badges in table
        const tableRevenueGrowth = document.getElementById('table-revenue-growth');
        if (tableRevenueGrowth && data.growth_rate !== undefined) {
            const rounded = Math.round(data.growth_rate * 10) / 10;
            const prefix = rounded > 0 ? '+' : '';
            const colorClass = rounded > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                rounded < 0 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

            tableRevenueGrowth.innerHTML = `
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
            ${prefix}${rounded}%
          </span>
        `;
        }
        if (data.top_products) {
            renderTopProducts(data.top_products);
        }
        if (data.top_customers) {
            renderTopCustomers(data.top_customers);
        }
        // Update charts with new data
        if (window.charts.revenue && data.revenue_by_day) {
            const rev = buildTimeSeries(data.revenue_by_day, 'date', 'revenue');
            window.charts.revenue.data.labels = rev.labels;
            window.charts.revenue.data.datasets[0].data = rev.values;
            window.charts.revenue.update();
        }

        if (window.charts.status && data.orders_by_status) {
            const statusLabels = data.orders_by_status.map(item => item.status || 'khác');
            const statusValues = data.orders_by_status.map(item => Number(item.count || 0));
            window.charts.status.data.labels = statusLabels;
            window.charts.status.data.datasets[0].data = statusValues;
            window.charts.status.update();
        }

        if (window.charts.users && data.new_users_by_day) {
            const nu = buildTimeSeries(data.new_users_by_day, 'date', 'count');
            window.charts.users.data.labels = nu.labels;
            window.charts.users.data.datasets[0].data = nu.values;
            window.charts.users.update();
        }

        console.log('Dashboard updated with new data:', data);
    }

    // Load years and restore filters on page load
    async function initDashboard() {
        // 1. Restore from localStorage
        const savedPeriod = localStorage.getItem('admin_dashboard_period');
        const savedYear = localStorage.getItem('admin_dashboard_year');
        const savedStatus = localStorage.getItem('admin_dashboard_status');

        if (savedPeriod) document.getElementById('periodFilter').value = savedPeriod;
        if (savedStatus) document.getElementById('statusFilter').value = savedStatus;
        
        // Trigger period change to show/hide year filter
        document.getElementById('periodFilter').dispatchEvent(new Event('change'));

        // 2. Load years
        await loadAvailableYears();
        
        // 3. Set year if saved
        if (savedYear) document.getElementById('yearFilter').value = savedYear;

        // 4. Initial render or fetch
        if (savedPeriod || savedStatus || savedYear) {
            applyFilters(true);
        } else if (typeof initialDashboardData !== 'undefined') {
            if (initialDashboardData.top_products) renderTopProducts(initialDashboardData.top_products);
            if (initialDashboardData.top_customers) renderTopCustomers(initialDashboardData.top_customers);
        }
    }

    initDashboard();
</script>

<!-- Custom Scrollbar & Utility Styles -->
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
</style>


<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>