<?php
// app/Controllers/admin/DashboardController.php

require_once __DIR__ . '/../../../config/config.php';

// Check admin permission
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

if ($action === 'get_stats') {
    $period = $_GET['period'] ?? 'month';
    $year = $_GET['year'] ?? date('Y');
    $orderStatus = $_GET['status'] ?? 'all';
    
    $data = getStatsData($pdo, $period, $year, $orderStatus);
    echo json_encode(['success' => true, 'data' => $data]);
    
} elseif ($action === 'get_years') {
    $years = getAvailableYears($pdo);
    echo json_encode(['success' => true, 'years' => $years]);
}

function getDateRange($period, $year = null) {
    $year = $year ?? date('Y');
    
    switch ($period) {
        case 'today':
            return [
                'start' => date('Y-m-d 00:00:00'),
                'end' => date('Y-m-d 23:59:59')
            ];
            
        case 'week':
            $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
            $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));
            return ['start' => $monday, 'end' => $sunday];
            
        case 'month':
            return [
                'start' => date('Y-m-01 00:00:00'),
                'end' => date('Y-m-t 23:59:59')
            ];
            
        case 'year':
            return [
                'start' => "$year-01-01 00:00:00",
                'end' => "$year-12-31 23:59:59"
            ];
            
        case '7days':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-7 days')),
                'end' => date('Y-m-d 23:59:59')
            ];
            
        case '30days':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-30 days')),
                'end' => date('Y-m-d 23:59:59')
            ];
            
        default:
            return [
                'start' => date('Y-m-01 00:00:00'),
                'end' => date('Y-m-t 23:59:59')
            ];
    }
}

function getStatsData($pdo, $period, $year, $orderStatus) {
    $range = getDateRange($period, $year);
    
    // Build status filter
    $statusFilter = '';
    if ($orderStatus !== 'all') {
        $statusFilter = " AND status = " . $pdo->quote($orderStatus);
    }
    
    // Revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM orders 
        WHERE created_at BETWEEN ? AND ? 
        AND payment_status = 'paid'
        $statusFilter
    ");
    $stmt->execute([$range['start'], $range['end']]);
    $revenue = (float) $stmt->fetchColumn();
    
    // Orders count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE created_at BETWEEN ? AND ?
        $statusFilter
    ");
    $stmt->execute([$range['start'], $range['end']]);
    $ordersCount = (int) $stmt->fetchColumn();
    
    // New users
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM users 
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$range['start'], $range['end']]);
    $newUsers = (int) $stmt->fetchColumn();
    
    // AOV (Average Order Value)
    $aov = $ordersCount > 0 ? $revenue / $ordersCount : 0;
    
    // Get previous period for growth calculation
    $prevRange = getPreviousPeriodRange($period, $year);
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM orders 
        WHERE created_at BETWEEN ? AND ? 
        AND payment_status = 'paid'
        $statusFilter
    ");
    $stmt->execute([$prevRange['start'], $prevRange['end']]);
    $prevRevenue = (float) $stmt->fetchColumn();
    
    $growthRate = $prevRevenue > 0 ? (($revenue - $prevRevenue) / $prevRevenue) * 100 : 0;
    
    // Orders by status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        GROUP BY status
    ");
    $stmt->execute([$range['start'], $range['end']]);
    $ordersByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Revenue by day (for chart)
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        AND payment_status = 'paid'
        $statusFilter
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$range['start'], $range['end']]);
    $revenueByDay = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get additional data
    $topProducts = getTopProducts($pdo, $range['start'], $range['end'], 10);
    $topCustomers = getTopCustomers($pdo, $range['start'], $range['end'], 5);
    $returnRate = getReturnRate($pdo, $range['start'], $range['end']);
    $recentOrders = getRecentOrdersDetailed($pdo, 10);
    
    return [
        'revenue' => $revenue,
        'orders_count' => $ordersCount,
        'new_users' => $newUsers,
        'aov' => $aov,
        'growth_rate' => $growthRate,
        'orders_by_status' => $ordersByStatus,
        'revenue_by_day' => $revenueByDay,
        'top_products' => $topProducts,
        'top_customers' => $topCustomers,
        'return_rate' => $returnRate,
        'recent_orders' => $recentOrders,
        'period' => $period,
        'year' => $year,
        'status_filter' => $orderStatus
    ];
}

function getPreviousPeriodRange($period, $year) {
    switch ($period) {
        case 'today':
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            return [
                'start' => "$yesterday 00:00:00",
                'end' => "$yesterday 23:59:59"
            ];
            
        case 'week':
            $prevMonday = date('Y-m-d 00:00:00', strtotime('monday last week'));
            $prevSunday = date('Y-m-d 23:59:59', strtotime('sunday last week'));
            return ['start' => $prevMonday, 'end' => $prevSunday];
            
        case 'month':
            $prevMonth = date('Y-m-d', strtotime('first day of last month'));
            $prevMonthEnd = date('Y-m-d', strtotime('last day of last month'));
            return [
                'start' => "$prevMonth 00:00:00",
                'end' => "$prevMonthEnd 23:59:59"
            ];
            
        case 'year':
            $prevYear = $year - 1;
            return [
                'start' => "$prevYear-01-01 00:00:00",
                'end' => "$prevYear-12-31 23:59:59"
            ];
            
        case '7days':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-14 days')),
                'end' => date('Y-m-d 23:59:59', strtotime('-7 days'))
            ];
            
        case '30days':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-60 days')),
                'end' => date('Y-m-d 23:59:59', strtotime('-30 days'))
            ];
            
        default:
            $prevMonth = date('Y-m-d', strtotime('first day of last month'));
            $prevMonthEnd = date('Y-m-d', strtotime('last day of last month'));
            return [
                'start' => "$prevMonth 00:00:00",
                'end' => "$prevMonthEnd 23:59:59"
            ];
    }
}

function getAvailableYears($pdo) {
    $stmt = $pdo->query("
        SELECT DISTINCT YEAR(created_at) as year 
        FROM orders 
        ORDER BY year DESC
    ");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add current year if not in list
    $currentYear = (int) date('Y');
    if (!in_array($currentYear, $years)) {
        array_unshift($years, $currentYear);
    }
    
    return $years;
}

// Get top selling products
function getTopProducts($pdo, $startDate, $endDate, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            p.image,
            p.price,
            p.stock_quantity,
            COALESCE(SUM(oi.quantity), 0) as total_sold,
            COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ?
        AND o.payment_status = 'paid'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT ?
    ");
    $stmt->execute([$startDate, $endDate, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get top customers
function getTopCustomers($pdo, $startDate, $endDate, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.email,
            u.avatar,
            COUNT(o.id) as order_count,
            COALESCE(SUM(o.total_amount), 0) as total_spent,
            MAX(o.created_at) as last_order_date
        FROM users u
        INNER JOIN orders o ON u.id = o.user_id
        WHERE o.created_at BETWEEN ? AND ?
        AND o.payment_status = 'paid'
        GROUP BY u.id
        ORDER BY total_spent DESC
        LIMIT ?
    ");
    $stmt->execute([$startDate, $endDate, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get return/cancellation rate
function getReturnRate($pdo, $startDate, $endDate) {
    // Total orders
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $totalOrders = (int) $stmt->fetchColumn();
    
    // Cancelled orders
    $stmt = $pdo->prepare("
        SELECT COUNT(*), COALESCE(SUM(total_amount), 0)
        FROM orders 
        WHERE created_at BETWEEN ? AND ?
        AND status = 'cancelled'
    ");
    $stmt->execute([$startDate, $endDate]);
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $cancelledCount = (int) $result[0];
    $cancelledAmount = (float) $result[1];
    
    $cancelRate = $totalOrders > 0 ? ($cancelledCount / $totalOrders) * 100 : 0;
    
    return [
        'total_orders' => $totalOrders,
        'cancelled_count' => $cancelledCount,
        'cancelled_amount' => $cancelledAmount,
        'cancel_rate' => $cancelRate
    ];
}

// Get recent orders with detailed info
function getRecentOrdersDetailed($pdo, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_code,
            o.total_amount,
            o.payment_status,
            o.status,
            o.created_at,
            u.id as user_id,
            u.username,
            u.email,
            u.avatar
        FROM orders o
        INNER JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
