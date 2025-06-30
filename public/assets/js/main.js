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

// Show toast notification using Bootstrap 5
function showToast(type, title, message) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`; // text-white is on the parent
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body text-white"> <!-- Added text-white here for better contrast -->
                <strong>${title}</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toastEl);

    const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', function () {
        toastEl.remove();
    });
}

function createToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '1090'; // Ensure it's above other elements like navbar
        document.body.appendChild(container);
    }
    return container;
}

// Cancel order function
function cancelOrder(orderId) {
    if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
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
    }
}

// Reorder function
function reorder(orderId) {
    if (confirm('Bạn có muốn mua lại các sản phẩm trong đơn hàng này?')) {
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
    }
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
