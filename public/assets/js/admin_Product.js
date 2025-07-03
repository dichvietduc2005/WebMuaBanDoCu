// Xử lý tất cả action buttons bằng AJAX
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
            const response = await fetch(this.href);
            
            // Kiểm tra Content-Type trước
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Invalid response format');
            }
            
            const data = await response.json();
            
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
            if (document.querySelectorAll('tbody tr').length === 0) {
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

// Xử lý confirm cho action delete (dùng modal đẹp hơn)
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
                    const response = await fetch(this.href);
                    const data = await response.json();
                    
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
                    showToast('error', 'Lỗi', 'Có lỗi xảy ra');
                } finally {
                    this.innerHTML = originalHtml;
                    this.classList.remove('disabled');
                }
            }
        );
    });
});

