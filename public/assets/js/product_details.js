
document.addEventListener('DOMContentLoaded', function() {
    // Function to create and show a toast notification
    function showToast(message, type = 'success') {
        // Create container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            info: 'fa-info-circle'
        };
        const icon = icons[type] || 'fa-info-circle';

        toast.innerHTML = `
            <div class="toast-icon"><i class="fas ${icon}"></i></div>
            <div class="toast-message">${message}</div>
            <button class="toast-close">&times;</button>
        `;

        toastContainer.appendChild(toast);

        // Show the toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Hide and remove the toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => toast.remove());
        }, 3000);

        // Close button functionality
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => toast.remove());
        });
    }

    // --- Existing logic from Product_detail.php will be moved here ---

    // Example: Hook into the "Add to Cart" button
    const addToCartBtn = document.querySelector('.btn-add-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            // Assuming addToCart function exists and handles the AJAX call
            // addToCart(productId); 
            
            // For demonstration, we'll just show the toast.
            // In a real scenario, this would be in the success callback of the AJAX call.
            showToast('Sản phẩm đã được thêm vào giỏ hàng!', 'success');
        });
    }
});
