<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Controllers/admin/AdminController.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login.php');
    exit;
}

$currentAdminPage = 'products_pending';
$pageTitle = 'Sản phẩm chờ duyệt';

$pending_products = getPendingProducts($pdo);

// Helper functions
function renderStatusBadge(?string $status): string {
    $status = strtolower((string)$status);
    $map = [
        'pending' => ['Chờ duyệt', 'bg-yellow-100 text-yellow-800'],
        'active' => ['Đang bán', 'bg-green-100 text-green-800'],
        'reject' => ['Đã từ chối', 'bg-red-100 text-red-800'],
    ];
    [$label, $classes] = $map[$status] ?? ['Không rõ', 'bg-gray-100 text-gray-800'];
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $classes . '">' . htmlspecialchars($label) . '</span>';
}

function renderConditionBadge(?string $condition): string {
    $condition = trim((string)$condition);
    $normalized = mb_strtolower($condition, 'UTF-8');
    
    // Map từ giá trị database (tiếng Anh) sang tiếng Việt
    $map = [
        'new' => ['Mới', 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
        'like_new' => ['Như mới', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        'good' => ['Tốt', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'fair' => ['Khá tốt', 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-300'],
        'poor' => ['Cũ', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'],
        // Fallback cho các giá trị tiếng Việt cũ (nếu có)
        'mới' => ['Mới', 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
        'đã qua sử dụng' => ['Đã qua sử dụng', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        'kém' => ['Kém', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'],
    ];
    
    [$label, $classes] = $map[$normalized] ?? [$condition ?: 'Không rõ', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];
    
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $classes . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
}

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

  <div id="toast-container" class="fixed z-50 p-3 space-y-2 top-4 right-4"></div>

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Sản phẩm chờ duyệt</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Click vào ảnh sản phẩm để xem chi tiết và duyệt.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
             <?php if (count($pending_products) > 0): ?>
                <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold leading-none text-indigo-100 bg-indigo-600 rounded-full">
                    <?= count($pending_products) ?> sản phẩm
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($pending_products)): ?>
        <div class="rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Tuyệt vời!</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Không có sản phẩm nào chờ duyệt.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hình ảnh</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tiêu đề</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Người đăng</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Giá</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tình trạng</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Trạng thái</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ngày đăng</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($pending_products as $product): ?>
                            <?php $isFeatured = (bool)($product['featured'] ?? false); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors <?= $isFeatured ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="relative h-16 w-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 group cursor-pointer"
                                         onclick="openProductDetailModal(<?= (int)$product['id'] ?>)">
                                        <?php if (!empty($product['image_path'])): ?>
                                            <?php 
                                              $imagePath = ltrim($product['image_path'] ?? '', '/');
                                              $imageUrl = rtrim(BASE_URL, '/') . '/public/' . $imagePath;
                                              $placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Crect fill='%23e5e7eb' width='80' height='80'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%239ca3af' font-family='sans-serif' font-size='12'%3ENo Image%3C/text%3E%3C/svg%3E";
                                            ?>
                                            <img class="h-full w-full object-cover transform transition-transform duration-300 group-hover:scale-110"
                                                 src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="<?= htmlspecialchars($product['title']) ?>"
                                                 onerror="this.onerror=null;this.src='<?= $placeholderSvg ?>';">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </div>
                                        <?php else: ?>
                                            <div class="h-full w-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs" title="<?= htmlspecialchars($product['title']) ?>">
                                        <?= htmlspecialchars($product['title']) ?>
                                    </div>
                                    <?php if ($isFeatured): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                            <svg class="mr-1 h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            Nổi bật
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                            <?= strtoupper(substr($product['username'], 0, 1)) ?>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($product['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                    <?= number_format($product['price']) ?> ₫
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= renderConditionBadge($product['condition_status'] ?? null) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= renderStatusBadge($product['status'] ?? 'pending') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?= date('d/m/Y', strtotime($product['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="<?= BASE_URL ?>app/Models/admin/AdminModelAPI.php?action=approve&id=<?= $product['id'] ?>"
                                           class="action-btn text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 p-2 rounded-lg transition-colors"
                                           title="Duyệt">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </a>
                                        <a href="<?= BASE_URL ?>app/Models/admin/AdminModelAPI.php?action=reject&id=<?= $product['id'] ?>"
                                           class="action-btn text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors"
                                           title="Từ chối">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </a>
                                        <?php if ($isFeatured): ?>
                                            <a href="<?= BASE_URL ?>app/Models/admin/AdminModelAPI.php?action=toggle_featured&id=<?= $product['id'] ?>"
                                               class="action-btn text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-lg transition-colors"
                                               title="Bỏ nổi bật">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>app/Models/admin/AdminModelAPI.php?action=toggle_featured&id=<?= $product['id'] ?>"
                                               class="action-btn text-gray-400 hover:text-yellow-600 bg-gray-50 hover:bg-yellow-50 p-2 rounded-lg transition-colors"
                                               title="Đặt nổi bật">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>app/Models/admin/AdminModelAPI.php?action=delete&id=<?= $product['id'] ?>"
                                           class="delete text-gray-400 hover:text-red-600 bg-gray-50 hover:bg-red-50 p-2 rounded-lg transition-colors"
                                           title="Xóa">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Tailwind Modal -->
<div id="productDetailModal" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-gray-900/75 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeProductDetailModal()"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-200 dark:border-gray-700">
                
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white flex items-center" id="modal-title">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Chi tiết sản phẩm
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeProductDetailModal()">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="px-6 py-6" id="modalProductBody">
                    <div class="flex justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>
                </div>

                <!-- Footer (Actions will be injected via JS) -->
            </div>
        </div>
    </div>
</div>

<script>
window.BASE_URL = '<?= BASE_URL ?>';

function openProductDetailModal(productId) {
    const modal = document.getElementById('productDetailModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    loadProductDetail(productId);
}

function closeProductDetailModal() {
    const modal = document.getElementById('productDetailModal');
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Restore scrolling
    // Reset content to spinner for next time
    document.getElementById('modalProductBody').innerHTML = `
        <div class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>
    `;
}

// Close on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeProductDetailModal();
    }
});

async function loadProductDetail(productId) {
    const modalBody = document.getElementById('modalProductBody');
    
    try {
        const response = await fetch(`${window.BASE_URL}app/Models/admin/GetProductDetailAPI.php?id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            renderProductDetail(data.product);
        } else {
            showError(data.message || 'Không thể tải thông tin sản phẩm');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Lỗi kết nối. Vui lòng thử lại.');
    }
}

function showError(message) {
    const modalBody = document.getElementById('modalProductBody');
    modalBody.innerHTML = `
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">${message}</p>
                </div>
            </div>
        </div>
    `;
}

function renderProductDetail(product) {
    const modalBody = document.getElementById('modalProductBody');
    const images = product.images || [];
    
    // Simple image gallery
    let imagesHTML = '';
    if (images.length > 0) {
        imagesHTML = `
            <div class="mb-6 space-y-2">
                <div class="aspect-w-16 aspect-h-9 bg-gray-100 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <img src="${window.BASE_URL}public/${images[0].image_path}" 
                         class="object-contain w-full h-96" 
                         id="mainImage"
                         alt="Main product image">
                </div>
                ${images.length > 1 ? `
                    <div class="flex space-x-2 overflow-x-auto pb-2">
                        ${images.map((img, index) => `
                            <button onclick="document.getElementById('mainImage').src='${window.BASE_URL}public/${img.image_path}'" 
                                    class="flex-shrink-0 w-20 h-20 rounded-md overflow-hidden border-2 border-transparent hover:border-indigo-500 focus:outline-none focus:border-indigo-500 transition-colors">
                                <img src="${window.BASE_URL}public/${img.image_path}" class="w-full h-full object-cover" alt="Thumbnail">
                            </button>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
        `;
    } else {
        imagesHTML = `
            <div class="bg-gray-100 rounded-lg flex items-center justify-center h-64 mb-6">
                <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        `;
    }
    
    modalBody.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Images -->
            <div>
                ${imagesHTML}
            </div>
            
            <!-- Right: Info -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">${escapeHtml(product.title)}</h2>
                    <div class="mt-2 flex items-center space-x-4">
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">${formatPrice(product.price)} ₫</span>
                        ${getConditionBadge(product.condition_status)}
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Người bán</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                                <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs mr-2">
                                    ${escapeHtml(product.seller_name).charAt(0).toUpperCase()}
                                </div>
                                ${escapeHtml(product.seller_name)}
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ngày đăng</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">${formatDate(product.created_at)}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mô tả</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-line max-h-60 overflow-y-auto rounded-md bg-gray-50 dark:bg-gray-900/50 p-3">
                                ${escapeHtml(product.description || 'Không có mô tả')}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="${window.BASE_URL}app/Models/admin/AdminModelAPI.php?action=approve&id=${product.id}" 
                       class="action-btn flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Duyệt sản phẩm
                    </a>
                    <a href="${window.BASE_URL}app/Models/admin/AdminModelAPI.php?action=reject&id=${product.id}" 
                       class="action-btn flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Từ chối
                    </a>
                </div>
            </div>
        </div>
    `;
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('vi-VN');
}

function getConditionBadge(condition) {
    if (!condition) return '';
    const conditionMap = {
        'new': ['Mới', 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
        'like_new': ['Như mới', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        'good': ['Tốt', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'fair': ['Khá tốt', 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-300'],
        'poor': ['Cũ', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300']
    };
    const normalized = condition.toLowerCase();
    const [label, classes] = conditionMap[normalized] || [condition, 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${classes}">${escapeHtml(label)}</span>`;
}
</script>
<script src="<?= BASE_URL ?>public/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>public/assets/js/admin_Product.js"></script>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>