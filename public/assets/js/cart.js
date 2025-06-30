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
        const subtotalElement = document.querySelector('.order-summary .summary-row:first-child span:last-child');
        const totalElement = document.getElementById('total-amount');
        const formattedTotal = '$' + (newTotal / 1000).toFixed(2);
        
        if (subtotalElement) subtotalElement.textContent = formattedTotal;
        if (totalElement) totalElement.textContent = formattedTotal;
    }
    
    function updateCartIconCount(newCount) {
        const cartIconLink = document.querySelector('a[href="../cart/index.php"][title="Giỏ hàng"], a[href="cart/index.php"][title="Giỏ hàng"]');
        if (cartIconLink) {
            let cartCountBadge = cartIconLink.querySelector('.cart-count');
            if (newCount > 0) {
                 if (!cartCountBadge) {
                    cartCountBadge = document.createElement('span');
                    cartCountBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count';
                    cartIconLink.appendChild(cartCountBadge);
                    if (!cartIconLink.classList.contains('position-relative')) {
                        cartIconLink.classList.add('position-relative');
                    }
                }
                cartCountBadge.textContent = newCount;
                cartCountBadge.style.display = '';
            } else {
                 if (cartCountBadge) {
                    cartCountBadge.style.display = 'none';
                }
            }
        }
    }

    function updateItemTotal(itemElement, quantity) {
        const priceElement = itemElement.querySelector('.item-price');
        const totalElement = itemElement.querySelector('.item-total');
        if (!priceElement || !totalElement) return;

        // Extract price from '$123.45' format
        const price = parseFloat(priceElement.textContent.replace(/[^0-9.]/g, '')) * 1000;
        const newSubtotal = (price * quantity) / 1000;

        totalElement.textContent = '$' + newSubtotal.toFixed(2);
    }
    
    // --- Event Handlers ---
    async function handleUpdateQuantity(productId, newQuantity) {
        const itemElement = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
        if (!itemElement) return;
        
        const input = itemElement.querySelector('.quantity-input');
        input.disabled = true; // Disable input during API call

        const response = await callCartApi('update', productId, newQuantity);

        if (response && response.success) {
            showToast('success', 'Thành công!', response.message);
            updateItemTotal(itemElement, newQuantity);
            updateCartSummary(response.total);
            updateCartIconCount(response.cart_count);
        } else {
            // Revert quantity if update failed
            // You might need to fetch the old quantity or reload
            location.reload(); 
        }
        input.disabled = false;
    }
    
    async function handleRemoveItem(productId) {
        const itemElement = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
        if (!itemElement) return;

        const response = await callCartApi('remove', productId);

        if (response && response.success) {
            showToast('success', 'Thành công!', response.message);
            
            itemElement.style.transition = 'opacity 0.5s ease';
            itemElement.style.opacity = '0';

            setTimeout(() => {
                itemElement.remove();
                updateCartSummary(response.total);
                updateCartIconCount(response.cart_count);
                
                // Check if cart is now empty
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload(); // Reload to show the "empty cart" message
                }
            }, 500);
        } else {
            showToast('error', 'Lỗi!', response ? response.message : 'Không thể xóa sản phẩm.');
        }
    }
    
    const debouncedUpdate = debounce((productId, quantity) => {
        handleUpdateQuantity(productId, quantity);
    }, 500);

    // --- Event Listeners ---
    cartContainer.addEventListener('click', function(e) {
        const target = e.target;
        const itemElement = target.closest('.cart-item');
        if (!itemElement) return;

        const productId = itemElement.dataset.productId;
        const quantityInput = itemElement.querySelector('.quantity-input');
        let quantity = parseInt(quantityInput.value);

        if (target.matches('.quantity-increase')) {
            quantity++;
            quantityInput.value = quantity;
            debouncedUpdate(productId, quantity);
        }

        if (target.matches('.quantity-decrease')) {
            if (quantity > 1) {
                quantity--;
                quantityInput.value = quantity;
                debouncedUpdate(productId, quantity);
            }
        }

        if (target.matches('.remove-item')) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                handleRemoveItem(productId);
            }
        }
    });

    cartContainer.addEventListener('change', function(e) {
        const target = e.target;
        if (target.matches('.quantity-input')) {
            const productId = target.dataset.productId;
            const quantity = parseInt(target.value);
            if (quantity > 0) {
                handleUpdateQuantity(productId, quantity);
            } else {
                // Reset to 1 if invalid value is entered
                target.value = 1;
                handleUpdateQuantity(productId, 1);
            }
        }
    });
});
  