document.addEventListener('DOMContentLoaded', function () {
    const cartContainer = document.querySelector('.shopping-cart-container');
    if (!cartContainer) return;

    // --- Debounce function to prevent rapid firing of events ---
    function debounce(func, delay = 300) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    }

    // --- Toast notification function ---
    function showToast(type, title, message) {
    // Tạo container nếu chưa có
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Tạo toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast show align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.style.minWidth = '300px';
    
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastEl);

    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        toastEl.classList.remove('show');
        setTimeout(() => toastEl.remove(), 300);
    }, 3000);
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
    


    // --- API Call Functions ---
    async function callCartApi(action, productId, quantity) {
        const url = '/WebMuaBanDoCu/app/Controllers/cart/CartController.php';
        const body = `action=${action}&product_id=${productId}` + (quantity !== undefined ? `&quantity=${quantity}` : '');

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Có lỗi xảy ra từ máy chủ.');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            showToast('error', 'Lỗi!', error.message || 'Không thể kết nối đến máy chủ.');
            return null;
        }
    }

    // --- DOM Update Functions ---
    function updateCartSummary(newTotal) {
        // Update tạm tính
        const subtotalElements = document.querySelectorAll('.order-summary-container .d-flex span:last-child');
        if (subtotalElements.length > 0) {
            subtotalElements[0].textContent = formatPrice(newTotal);
        }
        
        // Update tổng cộng
        const totalElements = document.querySelectorAll('.order-summary-container .fw-bold.fs-5 span:last-child');
        if (totalElements.length > 0) {
            totalElements[0].textContent = formatPrice(newTotal);
        }
    }
    
    function updateCartIconCount(newCount) {
        // Update badge trong title của trang cart
        const cartBadge = document.querySelector('h1 .badge.bg-primary');
        if (cartBadge) {
            cartBadge.textContent = newCount;
        }
        
        // Update tất cả các cart icon count trong header/navigation
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            if (newCount > 0) {
                // Cập nhật số lượng (hiển thị tối đa 9+)
                if (newCount <= 9) {
                    element.textContent = newCount;
                } else {
                    element.textContent = '9+';
                }
                element.style.display = 'flex';
            } else {
                // Ẩn badge nếu giỏ hàng trống
                element.style.display = 'none';
            }
        });
        
        // Update cart icon count trong header - tìm kiếm theo nhiều cách
        const cartIconSelectors = [
            'a[href*="cart/index.php"][title="Giỏ hàng"]',
            'a[href*="cart"]',
            '.cart-icon'
        ];
        
        for (const selector of cartIconSelectors) {
            const cartIconLink = document.querySelector(selector);
            if (cartIconLink) {
                let cartCountBadge = cartIconLink.querySelector('.cart-count');
                
                if (newCount > 0) {
                    if (!cartCountBadge) {
                        // Tạo badge mới nếu chưa có
                        cartCountBadge = document.createElement('span');
                        cartCountBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count';
                        cartCountBadge.style.cssText = 'font-size: 12px; padding: 0.2em 0.4em;';
                        cartIconLink.appendChild(cartCountBadge);
                    }
                    
                    // Cập nhật số lượng (hiển thị tối đa 9+)
                    if (newCount <= 9) {
                        cartCountBadge.textContent = newCount;
                    } else {
                        cartCountBadge.textContent = '9+';
                    }
                    cartCountBadge.style.display = '';
                } else {
                    // Ẩn badge nếu giỏ hàng trống
                    if (cartCountBadge) {
                        cartCountBadge.style.display = 'none';
                    }
                }
                break; // Tìm thấy thì dừng
            }
        }
        
        console.log('Updated cart count to:', newCount);
    }

    // --- New updateCartCount function using modern fetch API ---
    async function updateCartCount() {
        try {
            const response = await fetch('/WebMuaBanDoCu/app/Controllers/cart/CartController.php?action=count', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                updateCartIconCount(data.count);
            } else {
                console.error('Failed to get cart count:', data.message);
            }
        } catch (error) {
            console.error('Error updating cart count:', error);
        }
    }

    // --- New updateCartCount function using modern fetch API ---
    async function updateCartCount() {
        try {
            const response = await fetch('/WebMuaBanDoCu/app/Controllers/cart/CartController.php?action=count', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                updateCartIconCount(data.count);
            } else {
                console.error('Failed to get cart count:', data.message);
            }
        } catch (error) {
            console.error('Error updating cart count:', error);
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }

    // --- Event Handlers ---
    async function handleRemoveItem(productId) {
        // Tìm cart-item chứa button remove có data-product-id này
        const removeButton = document.querySelector(`.remove-item[data-product-id="${productId}"]`);
        const itemElement = removeButton ? removeButton.closest('.cart-item') : null;
        
        if (!itemElement) {
            console.log('Không tìm thấy item element với productId:', productId);
            return;
        }

        console.log('Đang xóa item với productId:', productId);

        // Hiển thị loading state
        itemElement.style.opacity = '0.7';
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'item-loading-overlay';
        loadingOverlay.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        loadingOverlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.7); z-index: 10;';
        itemElement.style.position = 'relative';
        itemElement.appendChild(loadingOverlay);

        const response = await callCartApi('remove', productId);
        console.log('API Response:', response);

        if (response && response.success) {
            showToast('success', 'Thành công!', response.message);
            console.log('New cart count:', response.cart_count);
            console.log('New total:', response.total);
            
            // Animate removal
            itemElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            itemElement.style.opacity = '0';
            itemElement.style.transform = 'translateX(30px)';

            setTimeout(() => {
                const height = itemElement.offsetHeight;
                itemElement.style.height = height + 'px';
                itemElement.style.overflow = 'hidden';
                setTimeout(() => {
                    itemElement.style.transition = 'height 0.3s ease';
                    itemElement.style.height = '0';
                    
                    setTimeout(() => {
                        itemElement.remove();
                        updateCartSummary(response.total || 0);
                        updateCartIconCount(response.cart_count || 0);
                        
                        // Cập nhật thêm cart icon ở header để đảm bảo đồng bộ
                        updateCartCount();
                        
                        // Kiểm tra nếu giỏ hàng trống
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            location.reload(); // Reload để hiển thị trang giỏ hàng trống
                        }
                    }, 300);
                }, 10);
            }, 300);
        } else {
            // Remove loading state and restore original appearance
            itemElement.style.opacity = '1';
            loadingOverlay.remove();
            showToast('error', 'Lỗi!', response ? response.message : 'Không thể xóa sản phẩm.');
        }
    }

    // --- Event Listeners ---
    cartContainer.addEventListener('click', function(e) {
        console.log('Click event fired on:', e.target);
        
        // Tìm button remove được click
        const removeButton = e.target.closest('.remove-item');
        if (removeButton) {
            console.log('Remove button clicked');
            e.preventDefault();
            e.stopPropagation();
            
            const productId = removeButton.getAttribute('data-product-id');
            console.log('Product ID from button:', productId);
            
            if (productId) {
                showConfirmDialog(
                    'Xác nhận xóa',
                    'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?',
                    function() {
                        handleRemoveItem(productId);
                    }
                );
            } else {
                console.error('Không tìm thấy product ID');
            }
            return;
        }

        // Xử lý các sự kiện khác nếu cần
        const target = e.target;
        const itemElement = target.closest('.cart-item');
        if (!itemElement) return;

        const productId = itemElement.dataset.productId;
        if (!productId) return;

        const quantityInput = itemElement.querySelector('.quantity-input');
        if (!quantityInput) return;

        let quantity = parseInt(quantityInput.value);

        if (target.matches('.quantity-increase')) {
            quantity++;
            quantityInput.value = quantity;
            // debouncedUpdate(productId, quantity);
        }

        if (target.matches('.quantity-decrease')) {
            if (quantity > 1) {
                quantity--;
                quantityInput.value = quantity;
                // debouncedUpdate(productId, quantity);
            }
        }
    });

    // --- Make functions available globally ---
    window.updateCartCount = updateCartCount;
    window.updateCartIconCount = updateCartIconCount;

    console.log('Cart script loaded successfully');
});