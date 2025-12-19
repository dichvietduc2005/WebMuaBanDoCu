<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/CouponModel.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login.php');
    exit;
}

$currentAdminPage = 'coupons';
$pageTitle = 'Quản lý Mã giảm giá';

$coupons = getAllCoupons($pdo);

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Page Header with Stats -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                Mã giảm giá
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Quản lý các mã khuyến mãi cho khách hàng.
            </p>
            
            <!-- Quick Stats -->
            <div class="flex gap-4 mt-4">
                <?php 
                $totalCoupons = count($coupons);
                $activeCoupons = count(array_filter($coupons, fn($c) => $c['status'] == 1));
                $totalUsed = array_sum(array_column($coupons, 'used_count'));
                ?>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-blue-700 dark:text-blue-300"><?= $totalCoupons ?> mã</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-sm font-medium text-green-700 dark:text-green-300"><?= $activeCoupons ?> đang hoạt động</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300"><?= $totalUsed ?> lượt dùng</span>
                </div>
            </div>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openCreateModal()" 
                    class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl shadow-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tạo mã mới
            </button>
        </div>
    </div>

    <?php if (empty($coupons)): ?>
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="mx-auto w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Chưa có mã giảm giá nào</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Tạo mã giảm giá đầu tiên để thu hút khách hàng!</p>
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tạo mã đầu tiên
            </button>
        </div>
    <?php else: ?>
        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Mã Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Giảm giá</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Điều kiện</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Lượt dùng</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Thời hạn</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($coupons as $coupon): ?>
                            <?php 
                            $isExpired = $coupon['end_date'] && strtotime($coupon['end_date']) < time();
                            $isLimitReached = $coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit'];
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 <?= $coupon['status'] ? '' : 'opacity-60' ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 font-mono border-2 border-dashed border-gray-300 dark:from-gray-700 dark:to-gray-600 dark:text-white dark:border-gray-500">
                                            <?= htmlspecialchars($coupon['code']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <?php if ($coupon['discount_type'] === 'percent'): ?>
                                            <div class="px-3 py-1.5 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg shadow-md">
                                                <span class="text-white font-bold text-sm"><?= number_format($coupon['discount_value']) ?>%</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="px-3 py-1.5 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-lg shadow-md">
                                                <span class="text-white font-bold text-sm"><?= number_format($coupon['discount_value']) ?> ₫</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        <span class="font-medium">Đơn từ</span>
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400"><?= number_format($coupon['min_order_value']) ?> ₫</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1">
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-lg font-bold text-gray-900 dark:text-white"><?= $coupon['used_count'] ?></span>
                                                <span class="text-gray-400">/</span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400"><?= $coupon['usage_limit'] > 0 ? $coupon['usage_limit'] : '∞' ?></span>
                                            </div>
                                            <?php if ($coupon['usage_limit'] > 0): ?>
                                                <?php $percentage = min(100, ($coupon['used_count'] / $coupon['usage_limit']) * 100); ?>
                                                <div class="w-20 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mt-1 overflow-hidden">
                                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full transition-all duration-300" style="width: <?= $percentage ?>%"></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($coupon['end_date']): ?>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?= date('d/m/Y', strtotime($coupon['end_date'])) ?>
                                            </span>
                                            <?php if ($isExpired): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                                    Hết hạn
                                                </span>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Còn <?= ceil((strtotime($coupon['end_date']) - time()) / 86400) ?> ngày
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Vĩnh viễn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="toggleStatus(<?= $coupon['id'] ?>)" 
                                            class="relative inline-flex flex-shrink-0 h-7 w-14 border-2 border-transparent rounded-full cursor-pointer transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md <?= $coupon['status'] ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gray-300 dark:bg-gray-600' ?>">
                                        <span class="pointer-events-none inline-block h-6 w-6 rounded-full bg-white shadow-lg transform ring-0 transition-all duration-300 ease-in-out <?= $coupon['status'] ? 'translate-x-7' : 'translate-x-0' ?>">
                                            <?php if ($coupon['status']): ?>
                                                <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <?php else: ?>
                                                <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            <?php endif; ?>
                                        </span>
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button onclick="deleteCoupon(<?= $coupon['id'] ?>)" 
                                            class="inline-flex items-center justify-center p-2 text-red-600 hover:text-white bg-red-50 hover:bg-gradient-to-r hover:from-red-500 hover:to-red-600 rounded-lg transition-all duration-200 hover:shadow-lg transform hover:scale-110">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeCreateModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
            <form id="createCouponForm" onsubmit="submitCreate(event)">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h3 class="text-xl leading-6 font-bold text-white flex items-center gap-2" id="modal-title">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tạo mã giảm giá mới
                    </h3>
                </div>
                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Mã Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" required 
                                   class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm font-mono uppercase transition-all" 
                                   placeholder="VD: SALE50">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Loại giảm giá</label>
                                <select name="discount_type" 
                                        class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all">
                                    <option value="percent">Phần trăm (%)</option>
                                    <option value="fixed">Số tiền cố định (₫)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Giá trị giảm <span class="text-red-500">*</span></label>
                                <input type="number" name="discount_value" required min="0" 
                                       class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Đơn hàng tối thiểu (₫)</label>
                            <input type="number" name="min_order_value" value="0" min="0" 
                                   class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Ngày bắt đầu</label>
                                <input type="datetime-local" name="start_date" 
                                       class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Ngày kết thúc</label>
                                <input type="datetime-local" name="end_date" 
                                       class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Giới hạn lượt dùng</label>
                            <input type="number" name="usage_limit" value="0" min="0" 
                                   class="block w-full rounded-xl border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-3 text-sm transition-all" 
                                   placeholder="0 = Không giới hạn">
                        </div>
                        <div class="flex items-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                            <input type="checkbox" name="status" checked 
                                   class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label class="ml-3 block text-sm font-medium text-gray-900 dark:text-gray-300">Kích hoạt ngay</label>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-base font-medium text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto transform transition hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Tạo mã
                    </button>
                    <button type="button" onclick="closeCreateModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('createCouponForm').reset();
}

// Close on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeCreateModal();
});

async function submitCreate(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang tạo...';
    btn.disabled = true;
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/CouponController.php?action=create', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            // Success animation
            btn.innerHTML = '<svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Thành công!';
            setTimeout(() => location.reload(), 500);
        } else {
            alert(data.message);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    } catch (error) {
        console.error(error);
        alert('Có lỗi xảy ra!');
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }
}

async function deleteCoupon(id) {
    if (!confirm('Bạn có chắc muốn xóa mã này?')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/CouponController.php?action=delete', {
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

async function toggleStatus(id) {
    try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/CouponController.php?action=toggle_status', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Lỗi cập nhật');
        }
    } catch (error) {
        alert('Có lỗi xảy ra!');
    }
}
</script>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>
