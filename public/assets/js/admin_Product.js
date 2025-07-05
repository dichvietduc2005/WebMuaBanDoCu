// Xử lý tất cả action buttons bằng AJAX
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.actions a.action-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            // Bỏ qua xử lý nếu là nút xóa (đã có confirm riêng)
            if (this.classList.contains('delete')) return;
            
            e.preventDefault();
            
            // Hiệu ứng loading
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.classList.add('disabled');
            
            try {
                const response = await fetch(this.href, {credentials: 'same-origin'});
                
                // Kiểm tra response status
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Lấy text trước để debug
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                // Thử parse JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Response text:', responseText);
                    throw new Error('Server returned invalid JSON format');
                }
                
                if (!data.success) {
                    showToast('error', 'Lỗi', data.message);
                    return;
                }
                
                showToast('success', 'Thành công', data.message);
                
                // Xử lý UI động
                const productRow = this.closest('tr');
                
                if (data.action === 'delete') {
                    productRow.style.transition = 'opacity 0.5s ease';
                    productRow.style.opacity = '0';
                    setTimeout(() => productRow.remove(), 500);
                } else if (data.action === 'toggle_featured') {
                    // Cập nhật trạng thái featured
                    updateFeaturedStatus(productRow, data.isFeatured);
                    
                    // Nếu server tự động bỏ nổi bật sản phẩm khác để giữ giới hạn, cập nhật UI cho sản phẩm đó
                    if (data.autoUnfeaturedId) {
                        const oldRow = document.querySelector(`tr[data-product-id="${data.autoUnfeaturedId}"]`);
                        if (oldRow) {
                            updateFeaturedStatus(oldRow, false);
                        }
                    }
                } else {
                    // Tạo status cell nếu chưa có
                    let statusCell = productRow.querySelector('.status-cell');
                    if (!statusCell) {
                        statusCell = document.createElement('td');
                        statusCell.className = 'status-cell';
                        productRow.appendChild(statusCell);
                    }
                    
                    statusCell.textContent = data.action === 'approve' ? 'Đã duyệt' : 'Đã từ chối';
                    statusCell.className = 'status-cell ' + 
                        (data.action === 'approve' ? 'text-success' : 'text-warning');
                    
                    // Ẩn các nút hành động
                    productRow.querySelectorAll('.action-btn').forEach(btn => {
                        btn.style.display = 'none';
                    });
                }
                
                // Kiểm tra nếu không còn sản phẩm nào
                if (document.querySelectorAll('table tr').length === 1) { // chỉ còn hàng header
                    document.querySelector('table').insertAdjacentHTML('afterend', 
                        '<p class="mt-3">Không còn sản phẩm nào chờ duyệt</p>');
                }
                
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'Lỗi', error.message || 'Có lỗi xảy ra khi xử lý');
            } finally {
                this.innerHTML = originalHtml;
                this.classList.remove('disabled');
            }
        });
    });
});

// Xử lý confirm cho action delete
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.actions a.delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            showConfirmDialog(
                'Xác nhận xóa',
                'Bạn chắc chắn muốn xóa sản phẩm này?',
                async () => {
                    // Hiệu ứng loading
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.classList.add('disabled');
                    
                    try {
                        const response = await fetch(this.href, {credentials: 'same-origin'});
                        
                        // Kiểm tra response status
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        // Lấy text trước để debug
                        const responseText = await response.text();
                        console.log('Delete response:', responseText);
                        
                        // Thử parse JSON
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('Response text:', responseText);
                            throw new Error('Server returned invalid JSON format');
                        }
                        
                        if (data.success) {
                            showToast('success', 'Thành công', data.message);
                            const productRow = this.closest('tr');
                            productRow.style.transition = 'opacity 0.5s ease';
                            productRow.style.opacity = '0';
                            setTimeout(() => productRow.remove(), 500);
                        } else {
                            showToast('error', 'Lỗi', data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('error', 'Lỗi', error.message || 'Có lỗi xảy ra');
                    } finally {
                        this.innerHTML = originalHtml;
                        this.classList.remove('disabled');
                    }
                }
            );
        });
    });
});

// Function to update featured status UI
function updateFeaturedStatus(productRow, isFeatured) {
    const toggleBtn = productRow.querySelector('a[href*="toggle_featured"]');
    const badge = productRow.querySelector('.featured-badge');
    
    // Di chuyển dòng sang bảng thích hợp
    const featuredTbody = document.querySelector('#featured-table tbody');
    const regularTbody = document.querySelector('#regular-table tbody');
    if (isFeatured && featuredTbody) {
        featuredTbody.appendChild(productRow);
    } else if (!isFeatured && regularTbody) {
        regularTbody.appendChild(productRow);
    }

    if (isFeatured) {
        // Product is now featured
        toggleBtn.innerHTML = '<i class="fas fa-star-half-alt"></i> Bỏ nổi bật';
        toggleBtn.className = 'btn btn-warning action-btn';
        productRow.classList.add('featured-row');
        
        // Add badge if not exists
        if (!badge) {
            const titleCell = productRow.querySelector('td:nth-child(2)');
            if (titleCell) {
                titleCell.innerHTML += ' <span class="featured-badge">✨ Nổi bật</span>';
            }
        }
    } else {
        // Product is no longer featured
        toggleBtn.innerHTML = '<i class="fas fa-star"></i> Đặt nổi bật';
        toggleBtn.className = 'btn btn-info action-btn';
        productRow.classList.remove('featured-row');
        
        // Remove badge if exists
        if (badge) {
            badge.remove();
        }
    }
}

