<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/NotificationTemplateModel.php';
require_once __DIR__ . '/../../Models/admin/NotificationModel.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login.php');
    exit;
}

$currentAdminPage = 'notifications';
$pageTitle = 'Gửi thông báo';

$sentNotifications = getSentNotifications($pdo);
$users = getAllUsers($pdo);
$templates = getAllTemplates($pdo);

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Page Header with Icon -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            Thông báo hệ thống
        </h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Gửi thông báo tới người dùng hoặc toàn bộ hệ thống.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form gửi thông báo -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-xl">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Soạn thông báo</h2>
                </div>
                
                <form id="sendNotificationForm" onsubmit="sendNotification(event)">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Gửi tới
                                </span>
                            </label>
                            <select name="user_id" class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all">
                                <option value="all">📢 Tất cả người dùng</option>
                                <optgroup label="Người dùng cụ thể">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Tiêu đề <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" required 
                                   class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all" 
                                   placeholder="VD: Bảo trì hệ thống">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Nội dung <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message" rows="5" required 
                                      class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all resize-none" 
                                      placeholder="Nhập nội dung thông báo..."></textarea>
                        </div>
                        
                        <button type="submit" 
                                class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transform transition hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Gửi ngay
                        </button>
                    </div>
                </form>
            </div>

            <!-- Cấu hình tự động -->
            <div class="mt-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-xl">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Cấu hình tự động</h2>
                </div>

                <!-- Manual Triggers -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Chạy thủ công (Test)
                    </h3>
                    <div class="space-y-3">
                        <button onclick="triggerAction('trigger_cart')" class="w-full flex items-center justify-center px-4 py-2 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors text-sm font-medium border border-indigo-200 dark:border-indigo-800">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Quét giỏ hàng bỏ quên
                        </button>
                        <button onclick="triggerAction('trigger_send')" class="w-full flex items-center justify-center px-4 py-2 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors text-sm font-medium border border-green-200 dark:border-green-800">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Gửi thông báo chờ
                        </button>
                    </div>
                </div>

                <!-- Templates List -->
                <div class="space-y-4">
                    <?php foreach ($templates as $template): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300">
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="p-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <?php if ($template['code'] === 'cart_abandoned'): ?>
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" 
                                               onchange="toggleTemplate(<?= $template['id'] ?>)" 
                                               <?= $template['is_active'] ? 'checked' : '' ?>>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    </label>
                                    </div>
                                    <div class="mt-3">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                            <?= htmlspecialchars($template['title']) ?>
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            <?= htmlspecialchars($template['message_template']) ?>
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                                                    <?= $template['code'] ?>
                                                </span>
                                                <span>•</span>
                                                <span><?= $template['type'] ?></span>
                                            </div>
                                            <button onclick="editTemplate(<?= $template['id'] ?>, '<?= addslashes($template['title']) ?>', '<?= addslashes($template['message_template']) ?>')"
                                                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-lg transition-colors text-xs font-medium flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Chỉnh sửa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Danh sách đã gửi -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Lịch sử gửi</h3>
                        <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                            <?= count($sentNotifications) ?> thông báo
                        </span>
                    </div>
                </div>
                
                <?php if (empty($sentNotifications)): ?>
                    <div class="p-12 text-center">
                        <div class="mx-auto w-20 h-20 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Chưa có thông báo nào</h3>
                        <p class="text-gray-500 dark:text-gray-400">Gửi thông báo đầu tiên của bạn!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tiêu đề</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Người nhận</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Thời gian</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($sentNotifications as $notif): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200">
                                        <td class="px-6 py-4">
                                            <div class="flex items-start gap-3">
                                                <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg mt-0.5">
                                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($notif['title'] ?? 'Thông báo hệ thống') ?></div>
                                                    <?php 
                                                    $message = htmlspecialchars($notif['message'] ?? $notif['content'] ?? '');
                                                    $messageId = 'msg-' . $notif['id'];
                                                    ?>
                                                    <div id="<?= $messageId ?>" class="text-sm text-gray-500 dark:text-gray-400 mt-1 cursor-pointer group" 
                                                         onclick="toggleMessage('<?= $messageId ?>')"
                                                         title="Click để xem đầy đủ">
                                                        <div class="message-preview line-clamp-2"><?= $message ?></div>
                                                        <div class="message-full hidden whitespace-pre-wrap"><?= $message ?></div>
                                                        <?php if (strlen($message) > 100): ?>
                                                            <button class="text-xs text-purple-600 dark:text-purple-400 hover:underline mt-1 toggle-btn">
                                                                Xem thêm
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($notif['user_id']): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                                    <?= htmlspecialchars($notif['username']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 border border-green-200 dark:border-green-800">
                                                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>
                                                    Tất cả
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button onclick="deleteNotification(<?= $notif['id'] ?>)" 
                                                    class="inline-flex items-center justify-center p-2 text-red-600 hover:text-white bg-red-50 hover:bg-gradient-to-r hover:from-red-500 hover:to-red-600 rounded-lg transition-all duration-200 hover:shadow-lg transform hover:scale-110">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="editForm" onsubmit="updateTemplate(event)">
                <input type="hidden" name="id" id="edit_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Chỉnh sửa mẫu thông báo
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tiêu đề</label>
                                    <input type="text" name="title" id="edit_title" required
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nội dung mẫu</label>
                                    <textarea name="message_template" id="edit_message" rows="4" required
                                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"></textarea>
                                    <p class="mt-1 text-xs text-gray-500">Có thể dùng các biến: {username}, {count}, {product_name}...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Lưu thay đổi
                    </button>
                    <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleMessage(id) {
    const container = document.getElementById(id);
    const preview = container.querySelector('.message-preview');
    const full = container.querySelector('.message-full');
    const btn = container.querySelector('.toggle-btn');
    
    if (preview.classList.contains('line-clamp-2')) {
        preview.classList.add('hidden');
        full.classList.remove('hidden');
        if (btn) btn.textContent = 'Thu gọn';
    } else {
        preview.classList.remove('hidden');
        full.classList.add('hidden');
        if (btn) btn.textContent = 'Xem thêm';
    }
}

async function sendNotification(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang gửi...';
    btn.disabled = true;

    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/NotificationController.php?action=send', {
            method: 'POST',
            body: formData
        });
        
        const responseText = await response.text();
        console.log('Response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response was:', responseText);
            alert('Lỗi: Server trả về dữ liệu không hợp lệ. Vui lòng kiểm tra console (F12).');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            return;
        }
        
        if (data.success) {
            btn.innerHTML = '<svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Thành công!';
            setTimeout(() => location.reload(), 500);
        } else {
            alert(data.message || 'Lỗi không xác định');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        alert('Có lỗi xảy ra khi gửi request: ' + error.message);
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }
}

async function deleteNotification(id) {
    if (!confirm('Bạn có chắc muốn xóa thông báo này?')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/NotificationController.php?action=delete', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Lỗi khi xóa');
        }
    } catch (error) {
        alert('Có lỗi xảy ra!');
    }
}

// Automation Scripts
async function toggleTemplate(id) {
    try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/NotificationTemplateController.php?action=toggle', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (!data.success) {
            alert('Có lỗi xảy ra!');
            location.reload();
        }
    } catch (error) {
        console.error(error);
        alert('Lỗi kết nối!');
    }
}

function openEditModal(template) {
    document.getElementById('edit_id').value = template.id;
    document.getElementById('edit_title').value = template.title;
    document.getElementById('edit_message').value = template.message_template;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

async function updateTemplate(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/NotificationTemplateController.php?action=update', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Lỗi khi cập nhật');
        }
    } catch (error) {
        console.error(error);
        alert('Lỗi kết nối!');
    }
}

async function triggerAction(action) {
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Đang xử lý...';
    btn.disabled = true;

    try {
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/NotificationTemplateController.php?action=' + action, {
            method: 'POST'
        });
        const data = await response.json();
        alert(data.message);
    } catch (error) {
        console.error(error);
        alert('Lỗi kết nối!');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// Edit template function
function editTemplate(id, title, message) {
    document.getElementById('editTemplateId').value = id;
    document.getElementById('editTemplateTitle').value = title;
    document.getElementById('editTemplateMessage').value = message;
    document.getElementById('editTemplateModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editTemplateModal').classList.add('hidden');
}

async function saveTemplate() {
    const id = document.getElementById('editTemplateId').value;
    const title = document.getElementById('editTemplateTitle').value;
    const message = document.getElementById('editTemplateMessage').value;
    
    if (!title || !message) {
        alert('Vui lòng điền đầy đủ thông tin!');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('title', title);
    formData.append('message_template', message);
    
    try {
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/NotificationTemplateController.php?action=update', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            closeEditModal();
            location.reload();
        } else {
            alert(data.message || 'Lỗi khi cập nhật');
        }
    } catch (error) {
        console.error(error);
        alert('Lỗi kết nối!');
    }
}

// Insert placeholder at cursor position
function insertPlaceholder(placeholder) {
    const textarea = document.getElementById('editTemplateMessage');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    // Insert placeholder at cursor position
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    
    // Move cursor after inserted placeholder
    const newCursorPos = start + placeholder.length;
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    textarea.focus();
}
</script>

<!-- Edit Template Modal -->
<div id="editTemplateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Chỉnh sửa Template
                </h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-6 space-y-4">
            <input type="hidden" id="editTemplateId">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tiêu đề
                </label>
                <input type="text" id="editTemplateTitle" 
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nội dung tin nhắn
                </label>
                <textarea id="editTemplateMessage" rows="6"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                          placeholder="Nhập nội dung tin nhắn..."></textarea>
                <div class="mt-3">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-3">
                        💡 Click để chèn placeholder:
                    </p>
                    
                    <!-- User placeholders -->
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">👤 Người dùng:</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="insertPlaceholder('{username}')" 
                                    class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-lg transition-colors text-xs font-medium border border-purple-200 dark:border-purple-800">
                                {username}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{new_users_7days}')" 
                                    class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-lg transition-colors text-xs font-medium border border-purple-200 dark:border-purple-800">
                                {new_users_7days}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{total_users}')" 
                                    class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-lg transition-colors text-xs font-medium border border-purple-200 dark:border-purple-800">
                                {total_users}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Revenue placeholders -->
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">💰 Doanh thu:</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="insertPlaceholder('{revenue_today}')" 
                                    class="px-3 py-1.5 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 rounded-lg transition-colors text-xs font-medium border border-green-200 dark:border-green-800">
                                {revenue_today}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{revenue_week}')" 
                                    class="px-3 py-1.5 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 rounded-lg transition-colors text-xs font-medium border border-green-200 dark:border-green-800">
                                {revenue_week}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{revenue_month}')" 
                                    class="px-3 py-1.5 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 rounded-lg transition-colors text-xs font-medium border border-green-200 dark:border-green-800">
                                {revenue_month}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{revenue_year}')" 
                                    class="px-3 py-1.5 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 rounded-lg transition-colors text-xs font-medium border border-green-200 dark:border-green-800">
                                {revenue_year}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Order placeholders -->
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">📦 Đơn hàng:</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="insertPlaceholder('{orders_today}')" 
                                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-lg transition-colors text-xs font-medium border border-blue-200 dark:border-blue-800">
                                {orders_today}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{orders_week}')" 
                                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-lg transition-colors text-xs font-medium border border-blue-200 dark:border-blue-800">
                                {orders_week}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{orders_month}')" 
                                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-lg transition-colors text-xs font-medium border border-blue-200 dark:border-blue-800">
                                {orders_month}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Order status placeholders -->
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">📊 Đơn hàng theo trạng thái:</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="insertPlaceholder('{orders_pending}')" 
                                    class="px-3 py-1.5 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/30 dark:hover:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300 rounded-lg transition-colors text-xs font-medium border border-yellow-200 dark:border-yellow-800">
                                {orders_pending}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{orders_processing}')" 
                                    class="px-3 py-1.5 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-300 rounded-lg transition-colors text-xs font-medium border border-orange-200 dark:border-orange-800">
                                {orders_processing}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{orders_completed}')" 
                                    class="px-3 py-1.5 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 rounded-lg transition-colors text-xs font-medium border border-green-200 dark:border-green-800">
                                {orders_completed}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{orders_cancelled}')" 
                                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-lg transition-colors text-xs font-medium border border-red-200 dark:border-red-800">
                                {orders_cancelled}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Cart placeholders -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">🛒 Giỏ hàng:</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="insertPlaceholder('{count}')" 
                                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg transition-colors text-xs font-medium border border-indigo-200 dark:border-indigo-800">
                                {count}
                            </button>
                            <button type="button" onclick="insertPlaceholder('{cart_id}')" 
                                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg transition-colors text-xs font-medium border border-indigo-200 dark:border-indigo-800">
                                {cart_id}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3 justify-end">
            <button onclick="closeEditModal()" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Hủy
            </button>
            <button onclick="saveTemplate()" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Lưu thay đổi
            </button>
        </div>
    </div>
</div>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>
