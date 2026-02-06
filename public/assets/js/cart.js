document.addEventListener('DOMContentLoaded', function () {
    const cartContainer = document.querySelector('.shopping-cart-container');

    // Nếu không tìm thấy class trong HTML, log lỗi để biết
    if (!cartContainer) {
        console.error("Lỗi: Không tìm thấy class .shopping-cart-container trong HTML");
        return;
    }

    // --- 1. CẤU HÌNH MODAL XÓA (PHẦN MỚI SỬA) ---
    let pendingDeleteCallback = null;
    let deleteModalInstance = null;

    // Tìm Modal theo ID (ID này nằm trong file Component PHP bạn đã include)
    const modalElement = document.getElementById('deleteConfirmModal');

    if (modalElement) {
        // Khởi tạo Bootstrap Modal
        deleteModalInstance = new bootstrap.Modal(modalElement);

        // Bắt sự kiện click nút "Xác nhận xóa" màu đỏ trong Modal
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (pendingDeleteCallback) {
                    pendingDeleteCallback(); // Thực hiện hành động xóa
                }
                deleteModalInstance.hide(); // Đóng modal sau khi xóa
            });
        }
    } else {
        console.warn("Cảnh báo: Không tìm thấy ID 'deleteConfirmModal'. Kiểm tra lại file include.");
    }

    // --- Helper: Show Confirm Dialog (Đã sửa để dùng Modal) ---
    function showConfirmDialog(title, message, callback) {
        pendingDeleteCallback = callback;

        if (deleteModalInstance) {
            // Nếu tìm thấy Modal đẹp -> Hiện Modal đẹp
            deleteModalInstance.show();
        } else {
            // Fallback: Nếu lỗi không thấy Modal -> Dùng tạm popup trình duyệt
            if (window.confirm(message)) {
                callback();
            }
        }
    }

    // --- Debounce function ---
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
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toastEl = document.createElement('div');
        toastEl.className = `toast show align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body"><strong>${title}</strong> ${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);
        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 300);
        }, 3000);
    }

    // --- API Call Functions ---
    async function callCartApi(action, productId = null, quantity = null, extraData = {}) {
        const url = (window.baseUrl || '') + 'app/Controllers/cart/CartController.php';
        let body = `action=${action}`;
        if (productId) body += `&product_id=${productId}`;
        if (quantity !== null) body += `&quantity=${quantity}`;

        // Append extra data
        for (const key in extraData) {
            body += `&${key}=${encodeURIComponent(extraData[key])}`;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
            });

            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (!response.ok) throw new Error(data.message || 'Lỗi server.');
                return data;
            } catch (e) {
                console.error("Server trả về dữ liệu không phải JSON:", text);
                throw new Error("Lỗi phản hồi từ máy chủ (không phải JSON).");
            }
        } catch (error) {
            console.error('API Error:', error);
            showToast('error', 'Lỗi!', error.message);
            return null;
        }
    }

    // --- DOM Update Functions ---
    function updateCartSummary(newTotal) {
        const totalElements = document.querySelectorAll('.order-summary-container .fw-bold.text-danger');
        totalElements.forEach(el => {
            el.textContent = formatPrice(newTotal);
        });
    }

    function updateCartIconCount(newCount) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            if (newCount > 0) {
                element.textContent = newCount > 9 ? '9+' : newCount;
                element.style.display = 'inline-block';
            } else {
                element.style.display = 'none';
            }
        });
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }

    // --- Event Handlers: REMOVE ---
    async function handleRemoveItem(productId) {
        const removeButton = document.querySelector(`.remove-item[data-product-id="${productId}"]`);
        const itemElement = removeButton ? removeButton.closest('.cart-item') : null;

        if (!itemElement) return;

        // UI Loading
        itemElement.style.opacity = '0.5';

        const response = await callCartApi('remove', productId);

        if (response && response.success) {
            showToast('success', 'Đã xóa!', response.message);

            // UI Remove Animation
            itemElement.style.transition = 'all 0.3s ease';
            itemElement.style.transform = 'translateX(100%)';
            setTimeout(() => {
                itemElement.remove();
                updateCartIconCount(response.cart_count || 0);

                recalculateTotal();

                // Nếu hết sản phẩm thì reload để hiện trang trống
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload();
                }
            }, 300);
        } else {
            itemElement.style.opacity = '1'; // Revert loading
        }
    }

    // --- Event Handlers: COUPON ---
    const applyCouponBtn = document.getElementById('apply-coupon-btn');
    const removeCouponBtn = document.getElementById('remove-coupon-btn');
    const couponInput = document.getElementById('coupon-code-input');

    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', async function () {
            const code = couponInput.value.trim();
            if (!code) {
                showToast('error', 'Lỗi', 'Vui lòng nhập mã giảm giá.');
                return;
            }

            // Disable button loading
            const originalText = applyCouponBtn.textContent;
            applyCouponBtn.disabled = true;
            applyCouponBtn.textContent = 'Đang áp dụng...';

            const url = (window.baseUrl || '') + 'app/Controllers/cart/CartController.php';
            const body = `action=apply_coupon&code=${encodeURIComponent(code)}`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: body
                });
                const data = await response.json();

                if (data.success) {
                    showToast('success', 'Thành công', data.message);
                    updateCouponUI(true, code, data);
                } else {
                    showToast('error', 'Lỗi', data.message || 'Mã giảm giá không hợp lệ.');
                }
            } catch (error) {
                console.error('Coupon Error:', error);
                showToast('error', 'Lỗi hệ thống', 'Không thể áp dụng mã giảm giá.');
            } finally {
                applyCouponBtn.disabled = false;
                applyCouponBtn.textContent = originalText;
            }
        });
    }

    if (removeCouponBtn) {
        removeCouponBtn.addEventListener('click', async function () {
            const url = (window.baseUrl || '') + 'app/Controllers/cart/CartController.php';
            const body = `action=remove_coupon`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: body
                });
                const data = await response.json();

                if (data.success) {
                    showToast('success', 'Thành công', data.message);
                    updateCouponUI(false, null, data);
                    couponInput.value = '';
                }
            } catch (error) {
                console.error('Remove Coupon Error:', error);
            }
        });
    }

    function updateCouponUI(isApplied, code, data) {
        const inputGroup = document.getElementById('coupon-input-group');
        const appliedInfo = document.getElementById('applied-coupon-info');
        const appliedCodeText = document.getElementById('applied-code-text');

        const discountRow = document.getElementById('discount-row');
        const discountAmountEl = document.getElementById('discount-amount');
        const finalTotalEl = document.getElementById('cart-final-total');

        if (isApplied) {
            inputGroup.style.display = 'none';
            appliedInfo.style.display = 'flex';
            appliedCodeText.textContent = code;

            if (discountRow) discountRow.style.display = 'flex';
            if (discountAmountEl) discountAmountEl.textContent = formatPrice(data.discount_amount);
        } else {
            inputGroup.style.display = 'flex';
            appliedInfo.style.display = 'none';
            appliedCodeText.textContent = '';

            if (discountRow) discountRow.style.display = 'none';
            if (discountAmountEl) discountAmountEl.textContent = '0đ';
        }

        if (finalTotalEl && data.final_total !== undefined) {
            finalTotalEl.textContent = formatPrice(data.final_total);
        }
    }

    // --- Main Event Listener (Delegation) ---
    cartContainer.addEventListener('click', function (e) {
        const target = e.target;

        // 1. Xử lý nút XÓA
        const removeButton = target.closest('.remove-item');
        if (removeButton) {
            e.preventDefault();
            const productId = removeButton.getAttribute('data-product-id');
            if (productId) {
                // GỌI HÀM SHOW DIALOG MỚI
                showConfirmDialog('Xóa sản phẩm', 'Bạn chắc chắn muốn xóa?', () => {
                    handleRemoveItem(productId);
                });
            }
            return;
        }

        // 2. Xử lý nút TĂNG/GIẢM số lượng
        const itemElement = target.closest('.cart-item');
        if (!itemElement) return;

        const productId = itemElement.dataset.productId;
        const quantityInput = itemElement.querySelector('.quantity-input');

        if (target.matches('.quantity-increase') || target.closest('.quantity-increase')) {
            let qty = parseInt(quantityInput.value) || 0;
            qty++;
            quantityInput.value = qty;
            debouncedUpdate(productId, qty);
        }

        if (target.matches('.quantity-decrease') || target.closest('.quantity-decrease')) {
            let qty = parseInt(quantityInput.value) || 0;
            if (qty > 1) {
                qty--;
                quantityInput.value = qty;
                debouncedUpdate(productId, qty);
            }
        }
    });

    // --- Selection Logic ---
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const selectedCountSpan = document.getElementById('selected-count');
    const selectedCountMobileSpan = document.querySelector('.selected-count-mobile');
    const selectedProductsInput = document.getElementById('selected-products-input');

    function recalculateTotal() {
        let total = 0;
        let count = 0;
        let selectedIds = [];
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');

        document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
            const price = parseFloat(checkbox.dataset.price) || 0;
            total += price;
            count++;
            selectedIds.push(checkbox.value);
        });

        // Update Total Price Text
        updateCartSummary(total);

        // Update Counts
        if (selectedCountSpan) selectedCountSpan.textContent = count;
        if (selectedCountMobileSpan) selectedCountMobileSpan.textContent = count;

        // Update Hidden Input for Checkout
        if (selectedProductsInput) selectedProductsInput.value = selectedIds.join(',');

        // Update Select All Checkbox State
        if (selectAllCheckbox) {
            const allChecked = (itemCheckboxes.length > 0) && (document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length);
            selectAllCheckbox.checked = allChecked;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.checked = isChecked;
            });
            recalculateTotal();
        });
    }

    // Delegation for checkboxes (in case dynamic)
    cartContainer.addEventListener('change', function (e) {
        if (e.target.classList.contains('item-checkbox')) {
            recalculateTotal();
        }
    });

    // Initial calculation
    recalculateTotal();

    // Hàm update số lượng (debounce)
    const debouncedUpdate = debounce(async (productId, quantity) => {
        console.log(`Updating product ${productId} to ${quantity}`);
        const response = await callCartApi('update', productId, quantity);
        if (response && response.success) {
            // Update data-price based on new quantity
            const checkbox = document.querySelector(`.item-checkbox[value="${productId}"]`);
            if (checkbox) {
                const unitPrice = parseFloat(checkbox.dataset.unitPrice) || 0;
                checkbox.dataset.price = unitPrice * quantity;
            }
            recalculateTotal();
        }
    }, 500);


    console.log('Cart script loaded successfully');
});
