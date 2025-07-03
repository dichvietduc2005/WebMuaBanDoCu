// Add to cart function
function addToCart(event, productId) {
    event.preventDefault();
    const form = event.target;
    const quantityInput = form.querySelector('.quantity-input');
    const quantity = quantityInput ? quantityInput.value : 1; // Default to 1 if not found
    const button = form.querySelector('.btn-add-to-cart');

    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
    }

    fetch('/WebMuaBanDoCu/app/Controllers/cart/CartController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        console.log("Response data:", data);
        if (data.success) {
            showToast('success', 'Thành công!', 'Sản phẩm đã được thêm vào giỏ hàng.');
            updateCartIcon(data.cart_count);
        } else {
            // Kiểm tra xem có phải lỗi yêu cầu đăng nhập không
            if (data.message && data.message.includes("Bạn cần đăng nhập")) {
                 showLoginPromptToast();
            } else {
                showToast('error', 'Lỗi!', data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
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
            button.innerHTML = '<i class="fas fa-cart-plus"></i> Thêm vào giỏ';
        }
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
    const loginPath = window.location.pathname.includes('/app/View/') ? '../auth/login.php' : 'app/View/auth/login.php';

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
    toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.style.minWidth = '320px';
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body text-white">
                <strong>${title}</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toastEl);
    // Sử dụng Bootstrap 5 Toast
    if (window.bootstrap && window.bootstrap.Toast) {
        const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastEl.remove();
        });
    } else {
        // Fallback: tự ẩn sau 3.5s nếu không có Bootstrap JS
        toastEl.style.display = 'block';
        setTimeout(() => toastEl.remove(), 3500);
    }
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
    showConfirmDialog('Xác nhận hủy đơn', 'Bạn có chắc muốn hủy đơn hàng này?', function() {
        fetch('/WebMuaBanDoCu/modules/order/cancel_order.php', {
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
    showConfirmDialog('Xác nhận mua lại', 'Bạn có muốn mua lại các sản phẩm trong đơn hàng này?', function() {
        fetch('/WebMuaBanDoCu/modules/order/reorder.php', {
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
    confirmBtn.addEventListener('click', function() {
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
document.addEventListener('DOMContentLoaded', function() {
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    // Initialize toast container on load
    createToastContainer();
});
