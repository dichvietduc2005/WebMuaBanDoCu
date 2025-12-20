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
        LEFT JOIN users u ON o.user_id = u.id
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
    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bộ lọc thống kê</h3>
            </div>

            <div class="flex flex-wrap items-center gap-3 ml-auto">
                <!-- Period Filter -->
                <select id="periodFilter" onchange="applyFilters()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="today">Hôm nay</option>
                    <option value="week">Tuần này</option>
                    <option value="month" selected>Tháng này</option>
                    <option value="year">Năm nay</option>
                    <option value="7days">7 ngày gần đây</option>
                    <option value="30days">30 ngày gần đây</option>
                </select>

                <!-- Year Filter (hidden by default, shown when period = 'year') -->
                <select id="yearFilter" onchange="applyFilters()" style="display:none;"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 text-sm">
                    <!-- Will be populated by JavaScript -->
                </select>

                <!-- Status Filter -->
                <select id="statusFilter" onchange="applyFilters()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="all">Tất cả đơn hàng</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="processing">Đang xử lý</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>

                <!-- Refresh Button -->
                <button onclick="applyFilters()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Làm mới
                </button>
            </div>
        </div>
    </div>

    <!-- Cards thống kê nhanh: 4 khối chính -->
    <div
        id="dashboard-cards"
        class="grid gap-5"
        style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1.25rem;">
        <!-- Người dùng -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-sky-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-sky-600 uppercase dark:text-sky-300">
                        Người dùng
                    </p>
                    <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span><?php echo number_format($totalUsers); ?></span>
                    </h2>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                        Đang hoạt động trên hệ thống
                    </p>
                    <?php
                    $userChangeClass = 'text-[11px] text-gray-500 dark:text-gray-400';
                    $userChangeIcon = 'fa-minus';
                    $userChangeLabel = 'Không có dữ liệu tháng trước';
                    if ($newUsersMonthChange !== null) {
                        $rounded = round($newUsersMonthChange, 1);
                        $prefix = $rounded > 0 ? '+' : '';
                        $userChangeLabel = $prefix . $rounded . '% người dùng mới so với tháng trước';
                        if ($rounded > 0) {
                            $userChangeClass = 'text-[11px] text-emerald-600';
                            $userChangeIcon = 'fa-arrow-up';
                        } elseif ($rounded < 0) {
                            $userChangeClass = 'text-[11px] text-red-600';
                            $userChangeIcon = 'fa-arrow-down';
                        }
                    }
                    ?>
                    <p class="<?php echo $userChangeClass; ?> mt-1 flex items-center gap-1">
                        <i class="fas <?php echo $userChangeIcon; ?>"></i>
                        <span><?php echo $userChangeLabel; ?></span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-sky-500 text-white shadow-md shadow-sky-500/40">
                    <i class="fas fa-user text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Đơn hàng -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-indigo-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-indigo-600 uppercase dark:text-indigo-300">
                        Đơn hàng
                    </p>
                    <h2 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span><?php echo number_format($totalOrders); ?></span>
                    </h2>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                        Tất cả trạng thái
                    </p>
                    <?php
                    $orderChangeClass = 'text-[11px] text-gray-500 dark:text-gray-400';
                    $orderChangeIcon = 'fa-minus';
                    $orderChangeLabel = 'Không có dữ liệu tháng trước';
                    if ($ordersMonthChange !== null) {
                        $rounded = round($ordersMonthChange, 1);
                        $prefix = $rounded > 0 ? '+' : '';
                        $orderChangeLabel = $prefix . $rounded . '% đơn hàng tháng này so với tháng trước';
                        if ($rounded > 0) {
                            $orderChangeClass = 'text-[11px] text-emerald-600';
                            $orderChangeIcon = 'fa-arrow-up';
                        } elseif ($rounded < 0) {
                            $orderChangeClass = 'text-[11px] text-red-600';
                            $orderChangeIcon = 'fa-arrow-down';
                        }
                    }
                    ?>
                    <p class="<?php echo $orderChangeClass; ?> mt-1 flex items-center gap-1">
                        <i class="fas <?php echo $orderChangeIcon; ?>"></i>
                        <span><?php echo $orderChangeLabel; ?></span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-500 text-white shadow-md shadow-indigo-500/40">
                    <i class="fas fa-box-open text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Doanh thu hôm nay -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-emerald-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-300">
                        Doanh thu hôm nay
                    </p>
                    <h2 class="mt-2 text-3xl font-semibold text-emerald-700 dark:text-emerald-300">
                        <?php echo number_format($todayRevenue, 0, ',', '.'); ?> đ
                    </h2>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                        Đơn đã thanh toán
                    </p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-emerald-500 text-white shadow-md shadow-emerald-500/40">
                    <i class="fas fa-coins text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Doanh thu tháng -->
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm dark:border-gray-800 dark:from-amber-950/40 dark:to-gray-950 cursor-default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-wide text-amber-600 uppercase dark:text-amber-300">
                        Doanh thu tháng này
                    </p>
                    <h2 class="mt-2 text-3xl font-semibold text-amber-700 dark:text-amber-300">
                        <?php echo number_format($monthRevenue, 0, ',', '.'); ?> đ
                    </h2>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                        <?php echo date('m / Y'); ?>
                    </p>
                    <?php
                    $revChangeClass = 'text-[11px] text-gray-500 dark:text-gray-400';
                    $revChangeIcon = 'fa-minus';
                    $revChangeLabel = 'Không có dữ liệu tháng trước';
                    if ($monthRevenueChange !== null) {
                        $rounded = round($monthRevenueChange, 1);
                        $prefix = $rounded > 0 ? '+' : '';
                        $revChangeLabel = $prefix . $rounded . '% doanh thu so với tháng trước';
                        if ($rounded > 0) {
                            $revChangeClass = 'text-[11px] text-emerald-600';
                            $revChangeIcon = 'fa-arrow-up';
                        } elseif ($rounded < 0) {
                            $revChangeClass = 'text-[11px] text-red-600';
                            $revChangeIcon = 'fa-arrow-down';
                        }
                    }
                    ?>
                    <p class="<?php echo $revChangeClass; ?> mt-1 flex items-center gap-1">
                        <i class="fas <?php echo $revChangeIcon; ?>"></i>
                        <span><?php echo $revChangeLabel; ?></span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/40">
                    <i class="fas fa-wallet text-sm"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Revenue Table -->
    <div id="revenue-table" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bảng Thống kê Tổng hợp</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Chỉ số
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Giá trị
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tăng trưởng
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Doanh thu</span>
                            </div>
                        </td>
                        <td id="table-revenue" class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-white">
                            <?= number_format($monthRevenue, 0, ',', '.') ?> ₫
                        </td>
                        <td id="table-revenue-growth" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                +0%
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Số đơn hàng</span>
                            </div>
                        </td>
                        <td id="table-orders" class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-white">
                            <?= number_format($ordersThisMonth) ?>
                        </td>
                        <td id="table-orders-growth" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                +0%
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Khách hàng mới</span>
                            </div>
                        </td>
                        <td id="table-new-users" class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-white">
                            <?= number_format($newUsersThisMonth) ?>
                        </td>
                        <td id="table-users-growth" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                +0%
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Giá trị đơn TB (AOV)</span>
                            </div>
                        </td>
                        <td id="table-aov" class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-white">
                            <?= $ordersThisMonth > 0 ? number_format($monthRevenue / $ordersThisMonth, 0, ',', '.') : 0 ?> ₫
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <span class="text-xs text-gray-500 dark:text-gray-400">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hàng thứ hai: Biểu đồ + sản phẩm đang bán -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md lg:col-span-2 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                    Biểu đồ Doanh thu
                </h3>
            </div>
            <div class="h-72">
                <canvas id="revenueChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Top Selling Products Table -->
        <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Top Sản phẩm bán chạy
                </h3>
            </div>

            <div id="topProductsTable" class="overflow-y-auto max-h-80">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-8">Đang tải...</p>
            </div>

            <div class="pt-3 mt-1 border-t border-gray-100 dark:border-gray-800">
                <p class="text-xs font-medium tracking-wide text-gray-400 uppercase">
                    Sản phẩm đang bán
                </p>
                <h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                    <?php echo number_format($activeProducts); ?>
                </h2>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                    Đã được admin duyệt và đang hiển thị
                </p>
            </div>
        </div>
    </div>

    <!-- Hàng thứ ba: Đơn hàng gần đây + Người dùng mới -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 mb-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md lg:col-span-2 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                    Đơn hàng gần đây
                </h3>
            </div>
            <?php if (empty($recentOrders)): ?>
                <p class="text-xs text-gray-500 dark:text-gray-400">Chưa có đơn hàng nào.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-left text-gray-700 align-middle dark:text-gray-200">
                        <thead class="text-[11px] font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/60 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2">Mã đơn</th>
                                <th class="px-3 py-2">Khách hàng</th>
                                <th class="px-3 py-2 text-right">Tổng tiền</th>
                                <th class="px-3 py-2">Trạng thái</th>
                                <th class="px-3 py-2 whitespace-nowrap">Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                    <td class="px-3 py-2 text-xs font-medium text-gray-800 dark:text-gray-100">
                                        #<?php echo (int)$order['id']; ?>
                                    </td>
                                    <td class="px-3 py-2">
                                        <?php echo htmlspecialchars($order['customer_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <?php echo number_format((float)($order['total_amount'] ?? 0), 0, ',', '.'); ?> đ
                                    </td>
                                    <td class="px-3 py-2">
                                        <?php
                                        $status = strtolower((string)($order['status'] ?? ''));
                                        $payStatus = strtolower((string)($order['payment_status'] ?? ''));
                                        $label = 'Khác';
                                        $classes = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                                        if ($payStatus === 'success' || $payStatus === 'paid' || str_contains($status, 'thành công')) {
                                            $label = 'Thành công';
                                            $classes = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';
                                        } elseif ($status === 'pending' || str_contains($status, 'chờ')) {
                                            $label = 'Chờ xử lý';
                                            $classes = 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';
                                        } elseif ($status === 'cancelled' || str_contains($status, 'hủy')) {
                                            $label = 'Đã hủy';
                                            $classes = 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300';
                                        }
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full <?php echo $classes; ?>">
                                            <?php echo $label; ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        <?php echo htmlspecialchars($order['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                    Người dùng mới 7 ngày gần đây
                </h3>
            </div>
            <div class="h-64">
                <canvas id="newUsersChart" class="w-full h-full"></canvas>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Khách hàng thân thiết
                </h3>
            </div>

            <div id="topCustomersTable" class="space-y-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-8">Đang tải...</p>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN + init script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
    const revenueData = <?php echo json_encode($revenueByDay, JSON_UNESCAPED_UNICODE); ?>;
    const ordersStatusData = <?php echo json_encode($ordersByStatus, JSON_UNESCAPED_UNICODE); ?>;
    const newUsersData = <?php echo json_encode($newUsersByDay, JSON_UNESCAPED_UNICODE); ?>;

    // Helper: build labels & datasets
    function buildTimeSeries(data, labelKey, valueKey) {
        const labels = data.map(item => item[labelKey]);
        const values = data.map(item => Number(item[valueKey] || 0));
        return {
            labels,
            values
        };
    }

    function renderTopProducts(products) {
        const container = document.getElementById('topProductsTable');
        if (!products || products.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-500 dark:text-gray-400 text-center py-8">Chưa có dữ liệu</p>';
            return;
        }

        let html = `
    <table class="min-w-full text-xs">
      <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800">
        <tr class="text-left text-gray-600 dark:text-gray-300">
          <th class="px-3 py-2">Sản phẩm</th>
          <th class="px-3 py-2 text-right">Đã bán</th>
          <th class="px-3 py-2 text-right">Doanh thu</th>
          <th class="px-3 py-2 text-right">Tồn</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
  `;

        products.forEach((product, index) => {
            const stockClass = product.stock_quantity < 10 ? 'text-red-600 font-semibold' : 'text-gray-600 dark:text-gray-300';
            html += `
      <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
        <td class="px-3 py-2">
          <div class="flex items-center gap-2">
            <span class="text-gray-400">${index + 1}.</span>
            <span class="font-medium text-gray-900 dark:text-white truncate max-w-[150px]" title="${product.name}">${product.name}</span>
          </div>
        </td>
        <td class="px-3 py-2 text-right font-semibold text-blue-600">${product.total_sold}</td>
        <td class="px-3 py-2 text-right text-green-600 font-semibold">${new Intl.NumberFormat('vi-VN').format(product.total_revenue)}₫</td>
        <td class="px-3 py-2 text-right ${stockClass}">${product.stock_quantity}</td>
      </tr>
    `;
        });

        html += `
      </tbody>
    </table>
  `;

        container.innerHTML = html;
    }
    // Render Top Customers
    function renderTopCustomers(customers) {
        const container = document.getElementById('topCustomersTable');
        if (!customers || customers.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-500 dark:text-gray-400 text-center py-8">Chưa có dữ liệu</p>';
            return;
        }

        let html = '';
        customers.forEach((customer, index) => {
            const avatarUrl = customer.avatar || '<?= BASE_URL ?>public/assets/images/default-avatar.png';
            html += `
      <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <div class="flex items-center gap-3">
          <div class="relative">
            <img src="${avatarUrl}" alt="${customer.username}" class="w-10 h-10 rounded-full object-cover border-2 border-purple-200 dark:border-purple-800">
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
                            suggestedMax: rev.values && rev.values.length ?
                                Math.max(...rev.values) * 1.2 :
                                1
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

        // Đơn hàng theo trạng thái
        const statusLabels = ordersStatusData.map(item => item.status || 'khác');
        const statusValues = ordersStatusData.map(item => Number(item.count || 0));
        const ctxStatus = document.getElementById('ordersStatusChart');
        if (ctxStatus && window.Chart) {
            new Chart(ctxStatus, {
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
            new Chart(ctxNewUsers, {
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
    async function applyFilters() {
        const period = document.getElementById('periodFilter').value;
        const year = document.getElementById('yearFilter').value || new Date().getFullYear();
        const status = document.getElementById('statusFilter').value;

        // Show loading state
        const cards = document.getElementById('dashboard-cards');
        if (cards) {
            cards.style.opacity = '0.5';
            cards.style.pointerEvents = 'none';
        }

        try {
            const response = await fetch(
                `<?= BASE_URL ?>app/Controllers/admin/DashboardController.php?action=get_stats&period=${period}&year=${year}&status=${status}`
            );
            const result = await response.json();

            if (result.success) {
                updateDashboard(result.data);
            } else {
                alert('Lỗi khi tải dữ liệu: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error applying filters:', error);
            alert('Lỗi kết nối!');
        } finally {
            if (cards) {
                cards.style.opacity = '1';
                cards.style.pointerEvents = 'auto';
            }
        }
    }

    // Update dashboard with new data
    function updateDashboard(data) {
        // Update revenue card
        const revenueEl = document.querySelector('#dashboard-cards > div:nth-child(1) h2 span');
        if (revenueEl) {
            revenueEl.textContent = new Intl.NumberFormat('vi-VN').format(data.revenue) + ' ₫';
        }

        // Update orders card
        const ordersEl = document.querySelector('#dashboard-cards > div:nth-child(3) h2 span');
        if (ordersEl) {
            ordersEl.textContent = new Intl.NumberFormat('vi-VN').format(data.orders_count);
        }

        // Update new users card
        const usersEl = document.querySelector('#dashboard-cards > div:nth-child(2) h2 span');
        if (usersEl) {
            usersEl.textContent = new Intl.NumberFormat('vi-VN').format(data.new_users);
        }

        // Update growth rate indicators
        const growthEl = document.querySelector('#dashboard-cards > div:nth-child(1) p:last-child span');
        if (growthEl && data.growth_rate !== undefined) {
            const rounded = Math.round(data.growth_rate * 10) / 10;
            const prefix = rounded > 0 ? '+' : '';
            growthEl.textContent = `${prefix}${rounded}% so với kỳ trước`;

            const parentEl = growthEl.parentElement;
            parentEl.className = rounded > 0 ? 'text-[11px] text-emerald-600 mt-1 flex items-center gap-1' :
                rounded < 0 ? 'text-[11px] text-red-600 mt-1 flex items-center gap-1' :
                'text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1';
        }

        // Update revenue table
        const tableRevenue = document.getElementById('table-revenue');
        if (tableRevenue) {
            tableRevenue.textContent = new Intl.NumberFormat('vi-VN').format(data.revenue) + ' ₫';
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
        // TODO: Update charts with new data
        console.log('Dashboard updated with new data:', data);
    }

    // Load years on page load
    loadAvailableYears();
    if (typeof initialDashboardData !== 'undefined') {
        if (initialDashboardData.top_products) {
            renderTopProducts(initialDashboardData.top_products);
        }
        if (initialDashboardData.top_customers) {
            renderTopCustomers(initialDashboardData.top_customers);
        }
    }
</script>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>