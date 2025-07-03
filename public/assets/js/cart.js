document.addEventListener('DOMContentLoaded', function() {
    const cartContainer = document.querySelector('.shopping-cart-container');
    if (!cartContainer) return;

    // --- START: Confirmation Modal Logic ---
    function createConfirmationModal() {
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'confirmation-modal-overlay';

        modalOverlay.innerHTML = `
            <div class="confirmation-modal">
                <div class="confirmation-modal-header">
                    <h5 class="modal-title">Xác nhận hành động</h5>
                </div>
                <div class="confirmation-modal-body">
                    <p>Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?</p>
                </div>
                <div class="confirmation-modal-footer">
                    <button class="confirmation-modal-btn btn-cancel">Hủy bỏ</button>
                    <button class="confirmation-modal-btn btn-confirm">Xác nhận</button>
                </div>
            </div>
        `;

        document.body.appendChild(modalOverlay);
        return modalOverlay;
    }

    function showConfirmationModal(onConfirm) {
        let modalOverlay = document.querySelector('.confirmation-modal-overlay');
        if (!modalOverlay) {
            modalOverlay = createConfirmationModal();
        }

        // Show modal with transition
        setTimeout(() => modalOverlay.classList.add('show'), 10);

        const btnConfirm = modalOverlay.querySelector('.btn-confirm');
        const btnCancel = modalOverlay.querySelector('.btn-cancel');
        const overlay = modalOverlay; // to use in click outside

        const closeModal = () => {
            modalOverlay.classList.remove('show');
            // Remove event listeners to prevent memory leaks
            btnConfirm.replaceWith(btnConfirm.cloneNode(true));
            btnCancel.replaceWith(btnCancel.cloneNode(true));
            overlay.replaceWith(overlay.cloneNode(true));
        };

        btnConfirm.addEventListener('click', () => {
            onConfirm();
            closeModal();
        }, { once: true });

        btnCancel.addEventListener('click', closeModal, { once: true });

        // Close when clicking outside the modal content
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        }, { once: true });
    }
    // --- END: Confirmation Modal Logic ---


    cartContainer.addEventListener('click', function(e) {
        const removeButton = e.target.closest('.remove-item');
        if (!removeButton) return;

        e.preventDefault();

        const productId = removeButton.dataset.productId;
        const cartItemElement = removeButton.closest('.cart-item');

        // Use the custom confirmation modal instead of the browser's default confirm
        showConfirmationModal(() => {
            removeItemFromCart(productId, cartItemElement);
        });
    });

    async function removeItemFromCart(productId, itemElement) {
        itemElement.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);

        try {
            const response = await fetch('/WebMuaBanDoCu/app/Controllers/cart/CartController.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                updateUICart(data, itemElement);
            } else {
                throw new Error(data.message || 'Lỗi không xác định');
            }
        } catch (error) {
            console.error('Lỗi khi xóa sản phẩm:', error);
            alert('Đã xảy ra lỗi: ' + error.message);
            itemElement.style.opacity = '1';
        }
    }

    function updateUICart(data, itemElement) {
        // Cập nhật số lượng trên header
        const headerCartCount = document.querySelector('.navbar .cart-count');
        if (headerCartCount) {
            headerCartCount.textContent = data.cartItemCount;
            headerCartCount.style.display = data.cartItemCount > 0 ? 'inline-block' : 'none';
        }
        
        // Cập nhật số lượng trên trang giỏ hàng
        const pageCartCount = document.querySelector('.shopping-cart-container .badge');
        if(pageCartCount) pageCartCount.textContent = data.cartItemCount;

        // Xóa item khỏi DOM
        itemElement.style.transition = 'all 0.3s ease';
        itemElement.style.transform = 'translateX(100px)';
        itemElement.style.opacity = '0';
        setTimeout(() => {
            itemElement.remove();
            // Kiểm tra nếu giỏ hàng trống
            const remainingItems = cartContainer.querySelectorAll('.cart-item').length;
            if (remainingItems === 0) {
                showEmptyCartView();
            } else {
                 // Cập nhật tổng tiền
                const totalElement = document.querySelector('.order-summary-container .text-primary');
                const subTotalElement = document.querySelector('.order-summary-container span:not(.text-primary):not(.text-muted):not(.text-success)');
                if(totalElement) totalElement.textContent = data.cartTotalFormatted;
                if(subTotalElement) subTotalElement.textContent = data.cartTotalFormatted;
            }
        }, 300);
    }

    function showEmptyCartView() {
        const cartRow = document.querySelector('.shopping-cart-container .row.g-4');
        if (cartRow) {
            cartRow.remove();
        }
        const emptyCartHTML = `
            <div class="cart-empty">
                <i class="fas fa-shopping-cart fa-4x mb-4 text-muted"></i>
                <h3 class="mb-3">Giỏ hàng đang trống</h3>
                <p class="text-muted mb-4">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                <a href="../TrangChu.php" class="btn btn-primary px-4">
                    <i class="fas fa-store me-2"></i>Mua sắm ngay
                </a>
            </div>
        `;
        // Chèn vào sau thẻ h1
        cartContainer.querySelector('.d-flex.justify-content-between').insertAdjacentHTML('afterend', emptyCartHTML);
    }
});