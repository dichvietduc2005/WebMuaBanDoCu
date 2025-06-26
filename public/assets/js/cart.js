        $(document).ready(function () {
            // Xử lý tăng giảm số lượng
            $('.quantity-increase').click(function () {
                var productId = $(this).data('product-id');
                var input = $('.quantity-input[data-product-id="' + productId + '"]');
                var newQuantity = parseInt(input.val()) + 1;
                updateQuantity(productId, newQuantity);
            });

            $('.quantity-decrease').click(function () {
                var productId = $(this).data('product-id');
                var input = $('.quantity-input[data-product-id="' + productId + '"]');
                var newQuantity = parseInt(input.val()) - 1;
                if (newQuantity > 0) {
                    updateQuantity(productId, newQuantity);
                }
            });

            $('.quantity-input').change(function () {
                var productId = $(this).data('product-id');
                var newQuantity = parseInt($(this).val());
                if (newQuantity > 0) {
                    updateQuantity(productId, newQuantity);
                }
            });

            // Xử lý xóa sản phẩm
            $('.remove-item').click(function () {
                var productId = $(this).data('product-id');
                if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                    removeItem(productId);
                }
            });

            function updateQuantity(productId, quantity) {
                $.ajax({
                    url: '../../modules/cart/handler.php',
                    method: 'POST',
                    data: {
                        action: 'update',
                        product_id: productId,
                        quantity: quantity
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('Có lỗi xảy ra khi cập nhật giỏ hàng.');
                    }
                });
            }

            function removeItem(productId) {
                $.ajax({
                    url: '../../modules/cart/handler.php',
                    method: 'POST',
                    data: {
                        action: 'remove',
                        product_id: productId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('Có lỗi xảy ra khi xóa sản phẩm.');
                    }
                });
            }
        });
