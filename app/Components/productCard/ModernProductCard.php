<?php
/**
 * Modern ProductCard Component
 * Hiển thị card sản phẩm với design hiện đại
 */

function renderProductCard($product, $showAddToCart = true) {
    $imageUrl = htmlspecialchars($product['image_path'] ?? '/assets/images/default_product_image.png');
    $title = htmlspecialchars($product['title']);
    $price = number_format($product['price']);
    $productId = $product['id'];
    $stockQuantity = $product['stock_quantity'] ?? 0;
    $condition = $product['condition_status'] ?? 'good';
    $categoryName = htmlspecialchars($product['category_name'] ?? '');
    
    // Condition labels
    $conditionLabels = [
        'new' => ['label' => 'Mới', 'class' => 'bg-success'],
        'like_new' => ['label' => 'Như mới', 'class' => 'bg-info'],
        'good' => ['label' => 'Tốt', 'class' => 'bg-primary'],
        'fair' => ['label' => 'Khá tốt', 'class' => 'bg-warning'],
        'poor' => ['label' => 'Cần sửa chữa', 'class' => 'bg-danger']
    ];
    
    $conditionInfo = $conditionLabels[$condition] ?? $conditionLabels['good'];
    ?>
    
    <div class="product-card h-100">
        <div class="position-relative">
            <img src="<?php echo $imageUrl; ?>" 
                 alt="<?php echo $title; ?>" 
                 class="product-image">
            
            <?php if (isset($product['featured']) && $product['featured']): ?>
            <span class="product-badge">Nổi bật</span>
            <?php endif; ?>
            
            <div class="position-absolute top-0 end-0 p-2">
                <button class="btn btn-sm btn-light rounded-circle" title="Yêu thích">
                    <i class="bi bi-heart"></i>
                </button>
            </div>
        </div>
        
        <div class="p-3">
            <div class="mb-2">
                <?php if ($categoryName): ?>
                <small class="text-muted"><?php echo $categoryName; ?></small>
                <?php endif; ?>
            </div>
            
            <h6 class="card-title mb-2">
                <a href="/products/<?php echo $productId; ?>" class="text-decoration-none text-dark">
                    <?php echo $title; ?>
                </a>
            </h6>
            
            <p class="product-price mb-2"><?php echo $price; ?> đ</p>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <small class="badge <?php echo $conditionInfo['class']; ?> text-white">
                    <i class="bi bi-star-fill"></i> <?php echo $conditionInfo['label']; ?>
                </small>
                <small class="text-muted">
                    <i class="bi bi-box"></i> Còn <?php echo $stockQuantity; ?>
                </small>
            </div>
            
            <?php if ($showAddToCart && $stockQuantity > 0): ?>
            <div class="d-flex gap-2">
                <div class="flex-fill">
                    <input type="number" class="form-control form-control-sm" 
                           value="1" min="1" max="<?php echo $stockQuantity; ?>"
                           data-product-id="<?php echo $productId; ?>">
                </div>
                <button class="btn btn-primary-custom btn-sm flex-shrink-0" 
                        onclick="addToCart(<?php echo $productId; ?>)">
                    <i class="bi bi-cart-plus"></i>
                </button>
            </div>
            <?php elseif ($stockQuantity <= 0): ?>
            <button class="btn btn-secondary btn-sm w-100" disabled>
                <i class="bi bi-x-circle"></i> Hết hàng
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
}

/**
 * Render product grid
 */
function renderProductGrid($products, $columns = 4, $showAddToCart = true) {
    $colClass = 'col-lg-' . (12 / $columns) . ' col-md-6 col-sm-6';
    ?>
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
        <div class="<?php echo $colClass; ?>">
            <?php renderProductCard($product, $showAddToCart); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Add to cart JavaScript function
 */
function addToCartScript() {
    ?>
    <script>
    function addToCart(productId) {
        const quantityInput = document.querySelector(`input[data-product-id="${productId}"]`);
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
        
        // Show loading state
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        button.disabled = true;
        
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count in header
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count || 0;
                }
                
                // Show success message
                showToast('Đã thêm vào giỏ hàng!', 'success');
                
                // Reset button
                button.innerHTML = '<i class="bi bi-check"></i> Đã thêm';
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            showToast(error.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
            button.innerHTML = originalContent;
            button.disabled = false;
        });
    }
    
    function showToast(message, type = 'info') {
        // Create toast element
        const toastHTML = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Add to toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remove after hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
    </script>
    <?php
}
?>
