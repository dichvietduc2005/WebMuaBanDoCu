// Add to cart function
function addToCart(arg1, arg2) {
    let productId;
    let quantity = 1;
    let button = null;

    // Detect if called as addToCart(event, productId) or addToCart(productId)
    if (arg1 && arg1.preventDefault) {
        // It's an event
        arg1.preventDefault();
        productId = arg2;
        const form = arg1.target.closest('form'); // Use closest form to be safe
        if (form) {
            const quantityInput = form.querySelector('.quantity-input');
            if (quantityInput) quantity = quantityInput.value;
            button = form.querySelector('.btn-add-to-cart') || arg1.target.closest('button');
        } else {
            button = arg1.target.closest('button');
        }
    } else {
        // It's a direct ID call (e.g. suggestion list)
        productId = arg1;
        // Try to find button using current event if possible, or leave null
        if (window.event && window.event.target) {
            button = window.event.target.closest('button');
        }
    }

    if (button) {
        button.disabled = true;
        const originalHtml = button.innerHTML;
        button.setAttribute('data-original-html', originalHtml);
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    const base = window.baseUrl || '/';
    fetch(base + 'app/Controllers/cart/CartController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
        .then(response => {
            return response.json().then(data => {
                return { status: response.status, ok: response.ok, data: data };
            });
        })
        .then(result => {
            console.log("Response data:", result.data);
            if (result.ok && result.data.success) {
                showToast('success', 'Thành công!', 'Sản phẩm đã được thêm vào giỏ hàng.');
                updateCartIcon(result.data.cart_count);
            } else {
                // Kiểm tra xem có phải lỗi yêu cầu đăng nhập không
                if (result.data.message && result.data.message.includes("Bạn cần đăng nhập")) {
                    showLoginPromptToast();
                } else {
                    // Hiển thị message từ server (có thể là lỗi tồn kho hoặc lỗi khác)
                    showToast('error', 'Lỗi!', result.data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi!', 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
        })
        .finally(() => {
            if (button) {
                button.disabled = false;
                const originalHtml = button.getAttribute('data-original-html');
                if (originalHtml) {
                    button.innerHTML = originalHtml;
                } else {
                    button.innerHTML = '<i class="fas fa-cart-plus"></i> Thêm vào giỏ';
                }
            }
        });
}

// Buy now function - Mua ngay và chuyển đến trang checkout
function buyNow(event, productId) {
    event.preventDefault();
    const form = event.target.closest('form');
    const quantityInput = form.querySelector('.quantity-input');
    const quantity = quantityInput ? quantityInput.value : 1; // Default to 1 if not found
    const button = event.currentTarget;

    // Hiển thị trạng thái loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    const base = window.baseUrl || '/';
    // Thêm sản phẩm vào giỏ hàng và chuyển đến trang checkout ngay lập tức
    fetch(base + 'app/Controllers/cart/CartController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}&checkout=1`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Chuyển hướng đến trang checkout
                window.location.href = base + 'app/View/checkout/index.php';
            } else {
                // Kiểm tra xem có phải lỗi yêu cầu đăng nhập không
                if (data.message && data.message.includes("Bạn cần đăng nhập")) {
                    showLoginPromptToast();
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-bolt"></i> Mua ngay';
                } else {
                    showToast('error', 'Lỗi!', data.message || 'Không thể mua sản phẩm.');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-bolt"></i> Mua ngay';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi!', 'Đã xảy ra lỗi khi xử lý.');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-bolt"></i> Mua ngay';
        });
}

/**
 * Cập nhật số lượng trên icon giỏ hàng
 * @param {number} cart_count 
 */
function updateCartIcon(cart_count) {
    const cartIconLink = document.querySelector('a[href*="cart/index.php"][title="Giỏ hàng"]');
    if (cartIconLink) {
        let cartCountBadge = cartIconLink.querySelector('.cart-count');
        if (cart_count > 0) {
            if (!cartCountBadge) {
                cartCountBadge = document.createElement('span');
                cartCountBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count';
                cartIconLink.appendChild(cartCountBadge);
                if (!cartIconLink.classList.contains('position-relative')) {
                    cartIconLink.classList.add('position-relative');
                }
            }
            cartCountBadge.textContent = cart_count;
            cartCountBadge.style.display = '';
        } else {
            if (cartCountBadge) {
                cartCountBadge.style.display = 'none';
            }
        }
    }
}

/**
 * Hiển thị thông báo yêu cầu đăng nhập
 */
function showLoginPromptToast() {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-warning border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    // Xác định đường dẫn đến trang đăng nhập một cách linh hoạt
    const base = window.baseUrl || '/';
    const loginPath = base + 'public/index.php?page=login';

    toastEl.innerHTML = `
        <div class="toast-body">
            <div class="d-flex justify-content-between align-items-center">
                <span>Bạn cần đăng nhập để tiếp tục.</span>
                <a href="${loginPath}" class="btn btn-light btn-sm ms-2">Đăng nhập</a>
            </div>
        </div>
    `;
    toastContainer.appendChild(toastEl);

    const toast = new bootstrap.Toast(toastEl, { delay: 7000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', function () {
        toastEl.remove();
    });
}

/**
 * Hiển thị toast notification góc phải trên
 */
function showToast(type, title, message) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastEl = document.createElement('div');

    // Style chuẩn 'Clean & Minimalist'
    const iconBg = type === 'success' ? 'bg-green-100 dark:bg-green-800' : 'bg-red-100 dark:bg-red-800';
    const iconColor = type === 'success' ? 'text-green-500 dark:text-green-200' : 'text-red-500 dark:text-red-200';
    const iconSvg = type === 'success'
        ? '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>'
        : '<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>';

    toastEl.className = `flex items-center w-full max-w-sm p-4 mb-3 text-gray-500 bg-white rounded-xl shadow-lg dark:text-gray-400 dark:bg-gray-800 transform transition-all duration-500 translate-x-full opacity-0 filter blur-sm`;
    toastEl.style.minWidth = '300px';

    toastEl.innerHTML = `
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 ${iconColor} ${iconBg} rounded-lg">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                ${iconSvg}
            </svg>
        </div>
        <div class="ml-3 text-sm font-normal">
            <span class="mb-1 text-sm font-semibold text-gray-900 dark:text-white block">${title}</span>
            <div class="text-xs font-normal text-gray-500 dark:text-gray-400">${message}</div>
        </div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700 focus:outline-none" onclick="removeToast(this.parentElement)">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    `;

    toastContainer.appendChild(toastEl);

    // Animation loop
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toastEl.classList.remove('translate-x-full', 'opacity-0', 'blur-sm');
        });
    });

    const timeout = setTimeout(() => {
        removeToast(toastEl);
    }, 4000);

    // Pause on hover
    toastEl.addEventListener('mouseenter', () => clearTimeout(timeout));
    toastEl.addEventListener('mouseleave', () => setTimeout(() => removeToast(toastEl), 2000));
}

function removeToast(element) {
    if (!element) return;
    element.classList.add('opacity-0', 'translate-x-full', 'blur-sm');
    setTimeout(() => {
        if (element.parentElement) {
            element.parentElement.removeChild(element);
        }
    }, 500);
}

function createToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = 10800;
        document.body.appendChild(container);
    }
    return container;
}

// Cancel order function
function cancelOrder(orderId) {
    const base = window.baseUrl || '/';
    showConfirmDialog('Xác nhận hủy đơn', 'Bạn có chắc muốn hủy đơn hàng này?', function () {
        fetch(base + 'modules/order/cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `order_id=${orderId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', 'Thành công!', 'Đơn hàng đã được hủy.');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', 'Lỗi!', data.message || 'Không thể hủy đơn hàng.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Lỗi!', 'Đã xảy ra lỗi khi hủy đơn hàng.');
            });
    });
}

// Reorder function
function reorder(orderId) {
    const base = window.baseUrl || '/';
    showConfirmDialog('Xác nhận mua lại', 'Bạn có muốn mua lại các sản phẩm trong đơn hàng này?', function () {
        fetch(base + 'modules/order/reorder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `order_id=${orderId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', 'Thành công!', 'Sản phẩm đã được thêm vào giỏ hàng.');
                    // Update cart count
                    const cartIconLink = document.querySelector('a[href="cart/index.php"][title="Giỏ hàng"]');
                    if (cartIconLink) {
                        let cartCountBadge = cartIconLink.querySelector('.cart-count');
                        if (data.cart_count > 0) {
                            if (!cartCountBadge) {
                                cartCountBadge = document.createElement('span');
                                cartCountBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count';
                                cartIconLink.appendChild(cartCountBadge);
                                if (!cartIconLink.classList.contains('position-relative')) {
                                    cartIconLink.classList.add('position-relative');
                                }
                            }
                            cartCountBadge.textContent = data.cart_count;
                            cartCountBadge.style.display = '';
                        } else {
                            if (cartCountBadge) {
                                cartCountBadge.style.display = 'none';
                            }
                        }
                    }
                } else {
                    showToast('error', 'Lỗi!', data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Lỗi!', 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
            });
    });
}

/**
 * Hiển thị dialog xác nhận thay thế cho confirm của trình duyệt
 * @param {string} title - Tiêu đề dialog
 * @param {string} message - Nội dung thông báo
 * @param {function} confirmCallback - Hàm callback khi người dùng xác nhận
 */
function showConfirmDialog(title, message, confirmCallback) {
    // Tạo container nếu chưa tồn tại
    let modalContainer = document.getElementById('confirm-dialog-container');
    if (!modalContainer) {
        modalContainer = document.createElement('div');
        modalContainer.id = 'confirm-dialog-container';
        document.body.appendChild(modalContainer);
    }

    // Tạo ID duy nhất cho modal
    const modalId = 'confirmModal-' + Date.now();

    // Tạo HTML cho modal
    const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="${modalId}-label">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary confirm-btn">Xác nhận</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Thêm modal vào container
    modalContainer.innerHTML = modalHTML;

    // Lấy reference đến modal
    const modalElement = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalElement);

    // Thêm sự kiện cho nút xác nhận
    const confirmBtn = modalElement.querySelector('.confirm-btn');
    confirmBtn.addEventListener('click', function () {
        modal.hide();
        if (typeof confirmCallback === 'function') {
            confirmCallback();
        }
    });

    // Xóa modal sau khi đóng để tránh tràn DOM
    modalElement.addEventListener('hidden.bs.modal', function () {
        modalElement.remove();
    });

    // Hiển thị modal
    modal.show();
}

// Category cards animation
document.addEventListener('DOMContentLoaded', function () {
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });
    // Initialize toast container on load
    createToastContainer();
});
