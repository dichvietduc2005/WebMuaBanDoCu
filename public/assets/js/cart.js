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
            confirmBtn.addEventListener('click', function() {
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
        const url = '/WebMuaBanDoCu/app/Controllers/cart/CartController.php'; 
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
                updateCartSummary(response.total || 0);
                updateCartIconCount(response.cart_count || 0);
                
                // Nếu hết sản phẩm thì reload để hiện trang trống
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload();
                }
            }, 300);
        } else {
            itemElement.style.opacity = '1'; // Revert loading
        }
    }

    // --- Main Event Listener (Delegation) ---
    cartContainer.addEventListener('click', function(e) {
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
    
    // Hàm update số lượng (debounce)
    const debouncedUpdate = debounce(async (productId, quantity) => {
        console.log(`Updating product ${productId} to ${quantity}`);
        const response = await callCartApi('update', productId, quantity);
        if (response && response.success) {
            updateCartSummary(response.total);
        }
    }, 500);


    console.log('Cart script loaded successfully');
});
