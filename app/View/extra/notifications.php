<?php
require_once('../../../config/config.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /WebMuaBanDoCu/app/View/user/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread
$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo của tôi</title>
    <link rel="stylesheet" href="../../../public/assets/css/footer.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Mobile Responsive CSS for Notifications Page -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/mobile-notifications-page.css">
    <style>
        .notification-card {
            transition: all 0.3s ease;
        }
        .notification-card:hover {
            transform: translateY(-2px);
        }
        .notification-unread {
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
            border-left: 4px solid #3B82F6;
        }
        .notification-read {
            background: white;
            border-left: 4px solid #E5E7EB;
        }
        .tab-active {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
        }
        .tab-inactive {
            background: #F3F4F6;
            color: #6B7280;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            z-index: 10;
        }
        .dropdown-menu.show {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php renderHeader($pdo); ?>
    
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-bell text-blue-600 mr-3"></i>Thông báo
            </h1>
            <p class="text-gray-600">Quản lý tất cả thông báo của bạn</p>
        </div>

        <!-- Stats & Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-200">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-6">
                    <div>
                        <div class="text-2xl font-bold text-blue-600"><?= count($notifications) ?></div>
                        <div class="text-sm text-gray-600">Tổng số</div>
                    </div>
                    <div class="h-12 w-px bg-gray-300"></div>
                    <div>
                        <div class="text-2xl font-bold text-orange-600"><?= $unreadCount ?></div>
                        <div class="text-sm text-gray-600">Chưa đọc</div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="markAllAsRead()" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-check-double"></i>
                        <span>Đánh dấu tất cả đã đọc</span>
                    </button>
                    <button onclick="deleteAllRead()" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-trash"></i>
                        <span>Xóa đã đọc</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-6">
            <button onclick="filterNotifications('all')" 
                    class="tab-filter tab-active px-6 py-3 rounded-lg font-medium transition-all" 
                    data-tab="all">
                Tất cả (<?= count($notifications) ?>)
            </button>
            <button onclick="filterNotifications('unread')" 
                    class="tab-filter tab-inactive px-6 py-3 rounded-lg font-medium transition-all" 
                    data-tab="unread">
                Chưa đọc (<?= $unreadCount ?>)
            </button>
            <button onclick="filterNotifications('read')" 
                    class="tab-filter tab-inactive px-6 py-3 rounded-lg font-medium transition-all" 
                    data-tab="read">
                Đã đọc (<?= count($notifications) - $unreadCount ?>)
            </button>
        </div>

        <!-- Notifications List -->
        <div id="notifications-container">
            <?php if (empty($notifications)): ?>
                <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bell-slash text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Không có thông báo nào</h3>
                    <p class="text-gray-600">Bạn chưa có thông báo nào</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $noti): ?>
                    <div class="notification-card notification-<?= $noti['is_read'] ? 'read' : 'unread' ?> rounded-xl shadow-sm p-6 mb-4 relative"
                         data-id="<?= $noti['id'] ?>"
                         data-status="<?= $noti['is_read'] ? 'read' : 'unread' ?>">
                        
                        <!-- Icon & Content -->
                        <div class="flex items-start gap-4">
                            <!-- Icon based on type -->
                            <div class="flex-shrink-0">
                                <?php
                                $iconClass = 'fa-bell';
                                $iconColor = 'text-blue-600';
                                if ($noti['type'] === 'admin') {
                                    $iconClass = 'fa-user-shield';
                                    $iconColor = 'text-purple-600';
                                } elseif ($noti['type'] === 'system') {
                                    $iconClass = 'fa-shopping-cart';
                                    $iconColor = 'text-orange-600';
                                }
                                ?>
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                                    <i class="fas <?= $iconClass ?> text-xl <?= $iconColor ?>"></i>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <?php if (!empty($noti['title'])): ?>
                                    <h4 class="font-semibold text-gray-900 mb-1"><?= htmlspecialchars($noti['title']) ?></h4>
                                <?php endif; ?>
                                <p class="text-gray-700 mb-2"><?= nl2br(htmlspecialchars($noti['message'] ?? $noti['content'] ?? '')) ?></p>
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span><i class="far fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($noti['created_at'])) ?></span>
                                    <?php if ($noti['type']): ?>
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                            <?= $noti['type'] === 'admin' ? 'Admin' : 'Hệ thống' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Actions Dropdown -->
                            <div class="relative">
                                <button onclick="toggleDropdown(<?= $noti['id'] ?>)" 
                                        class="w-8 h-8 rounded-full hover:bg-gray-200 flex items-center justify-center transition-colors">
                                    <i class="fas fa-ellipsis-v text-gray-600"></i>
                                </button>
                                <div id="dropdown-<?= $noti['id'] ?>" class="dropdown-menu bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-[200px] mt-2">
                                    <?php if (!$noti['is_read']): ?>
                                        <button onclick="markAsRead(<?= $noti['id'] ?>)" 
                                                class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-3">
                                            <i class="fas fa-check text-green-600"></i>
                                            <span>Đánh dấu đã đọc</span>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="markAsUnread(<?= $noti['id'] ?>)" 
                                                class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-3">
                                            <i class="fas fa-envelope text-blue-600"></i>
                                            <span>Đánh dấu chưa đọc</span>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteNotification(<?= $noti['id'] ?>)" 
                                            class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-3 text-red-600">
                                        <i class="fas fa-trash"></i>
                                        <span>Xóa thông báo</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const API_URL = '/WebMuaBanDoCu/app/Controllers/NotificationController.php';
        
        // Toggle dropdown
        function toggleDropdown(id) {
            const dropdown = document.getElementById(`dropdown-${id}`);
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(d => {
                if (d.id !== `dropdown-${id}`) d.classList.remove('show');
            });
            dropdown.classList.toggle('show');
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show'));
            }
        });
        
        // Mark as read
        async function markAsRead(id) {
            try {
                const response = await fetch(`${API_URL}?action=mark_read`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ notification_id: id })
                });
                const data = await response.json();
                if (data.success) {
                    updateNotificationUI(id, 'read');
                    showToast(data.message, 'success');
                }
            } catch (error) {
                showToast('Có lỗi xảy ra', 'error');
            }
        }
        
        // Mark as unread
        async function markAsUnread(id) {
            try {
                const response = await fetch(`${API_URL}?action=mark_unread`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ notification_id: id })
                });
                const data = await response.json();
                if (data.success) {
                    updateNotificationUI(id, 'unread');
                    showToast(data.message, 'success');
                }
            } catch (error) {
                showToast('Có lỗi xảy ra', 'error');
            }
        }
        
        // Mark all as read
        async function markAllAsRead() {
            if (!confirm('Đánh dấu tất cả thông báo là đã đọc?')) return;
            
            try {
                const response = await fetch(`${API_URL}?action=mark_all_read`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                showToast('Có lỗi xảy ra', 'error');
            }
        }
        
        // Delete notification
        async function deleteNotification(id) {
            if (!confirm('Bạn có chắc muốn xóa thông báo này?')) return;
            
            try {
                const response = await fetch(`${API_URL}?action=delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ notification_id: id })
                });
                const data = await response.json();
                if (data.success) {
                    const card = document.querySelector(`[data-id="${id}"]`);
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(100px)';
                    setTimeout(() => card.remove(), 300);
                    showToast(data.message, 'success');
                    updateCounts();
                }
            } catch (error) {
                showToast('Có lỗi xảy ra', 'error');
            }
        }
        
        // Delete all read
        async function deleteAllRead() {
            if (!confirm('Xóa tất cả thông báo đã đọc?')) return;
            
            try {
                const response = await fetch(`${API_URL}?action=delete_all_read`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                showToast('Có lỗi xảy ra', 'error');
            }
        }
        
        // Update notification UI
        function updateNotificationUI(id, status) {
            const card = document.querySelector(`[data-id="${id}"]`);
            card.setAttribute('data-status', status);
            card.className = `notification-card notification-${status} rounded-xl shadow-sm p-6 mb-4 relative`;
            
            // Update dropdown button
            const dropdown = document.getElementById(`dropdown-${id}`);
            const isRead = status === 'read';
            dropdown.innerHTML = `
                <button onclick="${isRead ? 'markAsUnread' : 'markAsRead'}(${id})" 
                        class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-3">
                    <i class="fas ${isRead ? 'fa-envelope' : 'fa-check'} text-${isRead ? 'blue' : 'green'}-600"></i>
                    <span>Đánh dấu ${isRead ? 'chưa đọc' : 'đã đọc'}</span>
                </button>
                <button onclick="deleteNotification(${id})" 
                        class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-3 text-red-600">
                    <i class="fas fa-trash"></i>
                    <span>Xóa thông báo</span>
                </button>
            `;
            
            updateCounts();
        }
        
        // Filter notifications
        function filterNotifications(status) {
            const cards = document.querySelectorAll('.notification-card');
            const tabs = document.querySelectorAll('.tab-filter');
            
            // Update tab styles
            tabs.forEach(tab => {
                if (tab.dataset.tab === status) {
                    tab.className = 'tab-filter tab-active px-6 py-3 rounded-lg font-medium transition-all';
                } else {
                    tab.className = 'tab-filter tab-inactive px-6 py-3 rounded-lg font-medium transition-all';
                }
            });
            
            // Filter cards
            cards.forEach(card => {
                if (status === 'all') {
                    card.style.display = 'block';
                } else {
                    card.style.display = card.dataset.status === status ? 'block' : 'none';
                }
            });
        }
        
        // Update counts
        function updateCounts() {
            const allCards = document.querySelectorAll('.notification-card');
            const unreadCards = document.querySelectorAll('[data-status="unread"]');
            const readCards = document.querySelectorAll('[data-status="read"]');
            
            document.querySelector('[data-tab="all"]').textContent = `Tất cả (${allCards.length})`;
            document.querySelector('[data-tab="unread"]').textContent = `Chưa đọc (${unreadCards.length})`;
            document.querySelector('[data-tab="read"]').textContent = `Đã đọc (${readCards.length})`;
        }
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Clear notification badges
        function clearNotificationBadges() {
            const headerBadges = document.querySelectorAll('.notifications-bell .badge, .badge.bg-danger:not(.cart-count)');
            headerBadges.forEach(badge => {
                if (badge && !badge.classList.contains('cart-count')) {
                    badge.style.display = 'none';
                }
            });
        }
        
        localStorage.setItem('notifications_updated', Date.now().toString());
        
        document.addEventListener('DOMContentLoaded', function() {
            clearNotificationBadges();
            setTimeout(() => {
                if (window.refreshNotifications) {
                    window.refreshNotifications();
                }
            }, 500);
        });
        
        window.addEventListener('beforeunload', function() {
            localStorage.setItem('notifications_updated', Date.now().toString());
        });
    </script>
    
    <script>userId = <?php echo $_SESSION['user_id'] ?></script>
    <script src="/WebMuaBanDoCu/public/assets/js/user_chat_system.js"></script>
    <?php footer(); ?>
</body>

</html>