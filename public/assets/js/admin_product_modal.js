// Admin Product Detail Modal Handler

let currentProductId = null;
let currentImageIndex = 0;
let productImages = [];

// Mở modal và load chi tiết sản phẩm
function openProductDetailModal(productId) {
    currentProductId = productId;
    const modal = document.getElementById('productDetailModal');
    
    if (!modal) {
        console.error('Modal not found');
        return;
    }
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Load product data
    loadProductDetail(productId);
}

// Đóng modal
function closeProductDetailModal() {
    const modal = document.getElementById('productDetailModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentProductId = null;
        currentImageIndex = 0;
        productImages = [];
    }
}

// Load product detail via AJAX
async function loadProductDetail(productId) {
    const modalBody = document.getElementById('modalProductBody');
    
    if (!modalBody) return;
    
    // Show loading
    modalBody.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-3 text-gray-600">Đang tải...</span>
        </div>
    `;
    
    try {
        const response = await fetch(`${window.baseUrl || '/'}app/Models/admin/GetProductDetailAPI.php?id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            renderProductDetail(data.product);
        } else {
            modalBody.innerHTML = `
                <div class="text-center py-12">
                    <p class="text-red-600">${data.message || 'Không thể tải thông tin sản phẩm'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading product:', error);
        modalBody.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600">Lỗi kết nối. Vui lòng thử lại.</p>
            </div>
        `;
    }
}

// Render product detail
function renderProductDetail(product) {
    const modalBody = document.getElementById('modalProductBody');
    productImages = product.images || [];
    currentImageIndex = 0;
    
    const baseUrl = window.baseUrl || '/';
    
    modalBody.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Image Slider -->
            <div class="space-y-4">
                ${renderImageSlider(productImages, baseUrl)}
            </div>
            
            <!-- Product Info -->
            <div class="space-y-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    ${escapeHtml(product.title)}
                </h2>
                
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    ${formatPrice(product.price)} ₫
                </div>
                
                <div class="flex flex-wrap gap-2">
                    ${renderConditionBadge(product.condition_status)}
                    ${renderStatusBadge(product.status)}
                    ${product.featured ? '<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-white rounded-full bg-gradient-to-r from-amber-500 to-orange-500"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>Nổi bật</span>' : ''}
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Mô tả</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">
                        ${escapeHtml(product.description || 'Không có mô tả')}
                    </p>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                    <div class="flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Người bán: <strong>${escapeHtml(product.seller_name)}</strong></span>
                    </div>
                    <div class="flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Danh mục: <strong>${escapeHtml(product.category_name || 'Không rõ')}</strong></span>
                    </div>
                    <div class="flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Ngày đăng: <strong>${formatDate(product.created_at)}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex gap-3 justify-end">
            <button
                onclick="closeProductDetailModal()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
                Đóng
            </button>
            <button
                onclick="rejectFromModal(${product.id})"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
            >
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Từ chối
            </button>
            <button
                onclick="approveFromModal(${product.id})"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
            >
                <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Duyệt
            </button>
        </div>
    `;
}

// Render image slider
function renderImageSlider(images, baseUrl) {
    if (!images || images.length === 0) {
        return `
            <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
            </div>
        `;
    }
    
    const mainImage = images[currentImageIndex];
    
    return `
        <div class="relative">
            <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-800 rounded-xl overflow-hidden">
                <img
                    src="${baseUrl}public/${escapeHtml(mainImage.image_path)}"
                    alt="Product image"
                    class="w-full h-full object-cover"
                    onerror="this.src='${baseUrl}public/assets/images/product-placeholder.png'"
                >
            </div>
            
            ${images.length > 1 ? `
                <button
                    onclick="changeModalImage(-1)"
                    class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 dark:bg-gray-800/90 rounded-full flex items-center justify-center hover:bg-white dark:hover:bg-gray-700 transition-colors shadow-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button
                    onclick="changeModalImage(1)"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 dark:bg-gray-800/90 rounded-full flex items-center justify-center hover:bg-white dark:hover:bg-gray-700 transition-colors shadow-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/50 text-white text-xs px-2 py-1 rounded-full">
                    ${currentImageIndex + 1} / ${images.length}
                </div>
            ` : ''}
        </div>
        
        ${images.length > 1 ? `
            <div class="grid grid-cols-4 gap-2 mt-3">
                ${images.map((img, index) => `
                    <div
                        onclick="setModalImage(${index})"
                        class="aspect-square rounded-lg overflow-hidden cursor-pointer border-2 ${index === currentImageIndex ? 'border-blue-500' : 'border-transparent'} hover:border-blue-300 transition-colors"
                    >
                        <img
                            src="${baseUrl}public/${escapeHtml(img.image_path)}"
                            alt="Thumbnail ${index + 1}"
                            class="w-full h-full object-cover"
                        >
                    </div>
                `).join('')}
            </div>
        ` : ''}
    `;
}

// Change modal image
function changeModalImage(direction) {
    if (productImages.length <= 1) return;
    
    currentImageIndex += direction;
    if (currentImageIndex < 0) currentImageIndex = productImages.length - 1;
    if (currentImageIndex >= productImages.length) currentImageIndex = 0;
    
    // Re-render slider
    const baseUrl = window.baseUrl || '/';
    const sliderContainer = document.querySelector('#modalProductBody .space-y-4');
    if (sliderContainer) {
        sliderContainer.innerHTML = renderImageSlider(productImages, baseUrl);
    }
}

// Set modal image by index
function setModalImage(index) {
    currentImageIndex = index;
    changeModalImage(0);
}

// Approve from modal
function approveFromModal(productId) {
    closeProductDetailModal();
    
    // Trigger click on approve button in the card
    const approveBtn = document.querySelector(`[data-product-id="${productId}"] a[href*="action=approve"]`);
    if (approveBtn) {
        approveBtn.click();
    }
}

// Reject from modal
function rejectFromModal(productId) {
    closeProductDetailModal();
    
    // Trigger click on reject button in the card
    const rejectBtn = document.querySelector(`[data-product-id="${productId}"] a[href*="action=reject"]`);
    if (rejectBtn) {
        rejectBtn.click();
    }
}

// Helper: Format price
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

// Helper: Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

// Helper: Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper: Render condition badge (reuse from products.php)
function renderConditionBadge(condition) {
    const map = {
        'Mới': ['Mới', 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
        'Đã qua sử dụng': ['Đã qua sử dụng', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        'Kém': ['Kém', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300']
    };
    
    const [label, classes] = map[condition] || [condition || 'Không rõ', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];
    
    return `<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full ${classes}">${escapeHtml(label)}</span>`;
}

// Helper: Render status badge
function renderStatusBadge(status) {
    const map = {
        'pending': ['Chờ duyệt', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        'active': ['Đang bán', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'reject': ['Đã từ chối', 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300']
    };
    
    const [label, classes] = map[status] || ['Không rõ', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];
    
    return `<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full ${classes}">${escapeHtml(label)}</span>`;
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProductDetailModal();
    }
});
