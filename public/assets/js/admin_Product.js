// Xử lý tất cả action buttons bằng AJAX với confirm + lý do từ chối
document.addEventListener('DOMContentLoaded', function() {
    // Hỗ trợ cả table layout (.actions a.action-btn) và card layout (a.action-btn)
    const actionButtons = document.querySelectorAll('a.action-btn:not(.delete)');
    actionButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Delete được xử lý riêng bên dưới
            if (this.classList.contains('delete')) return;

            e.preventDefault();

            const url = new URL(this.href, window.location.origin);
            const action = url.searchParams.get('action');
            // Hỗ trợ cả table row và product card
            const productRow = this.closest('tr') || this.closest('.product-card');

            const doRequest = async (extraParams = {}) => {
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.classList.add('disabled');

                try {
                    Object.entries(extraParams).forEach(([key, value]) => {
                        if (value !== undefined && value !== null) {
                            url.searchParams.set(key, value);
                        }
                    });

                    const response = await fetch(url.toString(), { credentials: 'same-origin' });
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const responseText = await response.text();
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

                    if (data.action === 'delete') {
                        productRow.style.transition = 'opacity 0.5s ease';
                        productRow.style.opacity = '0';
                        setTimeout(() => productRow.remove(), 500);
                    } else if (data.action === 'toggle_featured') {
                        updateFeaturedStatus(productRow, data.isFeatured);

                        if (data.autoUnfeaturedId) {
                            const oldRow = document.querySelector(`tr[data-product-id="${data.autoUnfeaturedId}"]`);
                            if (oldRow) {
                                updateFeaturedStatus(oldRow, false);
                            }
                        }
                    } else if (data.action === 'approve' || data.action === 'reject') {
                        // Ẩn các nút action, cập nhật badge trạng thái nếu cần
                        productRow.querySelectorAll('.action-btn').forEach(b => {
                            b.style.display = 'none';
                        });
                        const statusCell = productRow.querySelector('td:nth-child(6)');
                        if (statusCell) {
                            statusCell.innerHTML = data.action === 'approve'
                                ? '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Đang bán</span>'
                                : '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300">Đã từ chối</span>';
                        }
                    }

                    if (document.querySelectorAll('table tbody tr').length === 0) {
                        document.querySelector('table').insertAdjacentHTML(
                            'afterend',
                            '<p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Không còn sản phẩm nào chờ duyệt</p>'
                        );
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('error', 'Lỗi', error.message || 'Có lỗi xảy ra khi xử lý');
                } finally {
                    this.innerHTML = originalHtml;
                    this.classList.remove('disabled');
                }
            };

            if (action === 'approve') {
                showConfirmDialog(
                    'Xác nhận duyệt sản phẩm',
                    '<p class="text-xs text-gray-600 dark:text-gray-300">Bạn có chắc muốn duyệt sản phẩm này? Thao tác này sẽ hiển thị sản phẩm cho người dùng.</p>',
                    () => doRequest()
                );
            } else if (action === 'reject') {
                const titleCell = productRow.querySelector('td:nth-child(2) .text-sm');
                const productTitle = titleCell ? titleCell.textContent.trim() : 'sản phẩm này';
                const messageHtml = `
                    <p class="mb-2 text-xs text-gray-600 dark:text-gray-300">
                        Bạn có chắc muốn từ chối <span class="font-semibold">"${productTitle}"</span>? Thao tác này sẽ gửi thông báo đến người bán.
                    </p>
                    <label class="block mb-1 text-[11px] font-medium text-gray-500 dark:text-gray-400">Lý do từ chối</label>
                    <textarea
                        id="reject-reason-input"
                        class="w-full px-2 py-1.5 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-red-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                        rows="3"
                        placeholder="Nhập lý do từ chối tại đây..."></textarea>
                    <div class="flex flex-wrap items-center gap-1 mt-2 text-[11px]">
                        <span class="text-gray-400 mr-1">Gợi ý nhanh:</span>
                        <button type="button" class="px-2 py-0.5 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" data-reason-chip="Sai giá">Sai giá</button>
                        <button type="button" class="px-2 py-0.5 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" data-reason-chip="Hình ảnh mờ">Hình ảnh mờ</button>
                        <button type="button" class="px-2 py-0.5 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" data-reason-chip="Nội dung không phù hợp">Nội dung không phù hợp</button>
                    </div>
                `;
                showConfirmDialog(
                    'Từ chối sản phẩm',
                    messageHtml,
                    () => {
                        const reasonEl = document.getElementById('reject-reason-input');
                        const reason = reasonEl ? reasonEl.value.trim() : '';
                        doRequest({ reason });
                    },
                    { confirmLabel: 'Xác nhận từ chối', confirmVariant: 'danger' }
                );
            } else {
                // toggle_featured và các action khác
                doRequest();
            }
        });
    });
});

// Xử lý confirm cho action delete
document.addEventListener('DOMContentLoaded', function() {
    // Hỗ trợ cả table và card layout
    document.querySelectorAll('a.delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const url = this.href;

            showConfirmDialog(
                'Xác nhận xóa',
                'Bạn chắc chắn muốn xóa sản phẩm này?',
                async () => {
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.classList.add('disabled');

                    try {
                        const response = await fetch(url, { credentials: 'same-origin' });
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const responseText = await response.text();
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('Response text:', responseText);
                            throw new Error('Server returned invalid JSON format');
                        }

                        if (data.success) {
                            showToast('success', 'Thành công', data.message);
                            const productRow = this.closest('tr') || this.closest('.product-card');
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

// Confirm modal riêng cho khu vực admin (Tailwind), không phụ thuộc Bootstrap CSS
function showConfirmDialog(title, message, confirmCallback, options = {}) {
    const confirmLabel = options.confirmLabel || 'Xác nhận';
    const variant = options.confirmVariant || 'primary';
    const confirmClass =
        variant === 'danger'
            ? 'px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700'
            : 'px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700';

    const existing = document.getElementById('admin-confirm-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'admin-confirm-overlay';
    overlay.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm';

    overlay.innerHTML = `
      <div class="w-full max-w-lg px-6 py-5 bg-white rounded-2xl shadow-2xl border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
        <div class="flex items-start justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
            ${title}
          </h3>
        </div>
        <div class="text-xs text-gray-600 dark:text-gray-300 mb-4 space-y-2" id="admin-confirm-message">
          ${message}
        </div>
        <div class="flex justify-end gap-2 text-xs pt-2 border-t border-gray-100 dark:border-gray-800">
          <button type="button"
            class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
            data-role="cancel">
            Hủy
          </button>
          <button type="button"
            class="${confirmClass}"
            data-role="confirm">
            ${confirmLabel}
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);

    const cancelBtn = overlay.querySelector('[data-role="cancel"]');
    const confirmBtn = overlay.querySelector('[data-role="confirm"]');

    // Gắn sự kiện cho chips gợi ý lý do (nếu có)
    const reasonInput = overlay.querySelector('#reject-reason-input');
    if (reasonInput) {
      overlay.querySelectorAll('[data-reason-chip]').forEach(btn => {
        btn.addEventListener('click', () => {
          const val = btn.getAttribute('data-reason-chip') || '';
          if (!val) return;
          if (!reasonInput.value) {
            reasonInput.value = val;
          } else if (!reasonInput.value.includes(val)) {
            reasonInput.value = reasonInput.value.trim() + (reasonInput.value.trim().endsWith('.') ? ' ' : '; ') + val;
          }
          reasonInput.focus();
        });
      });
    }

    const close = () => {
      overlay.remove();
    };

    cancelBtn.addEventListener('click', close);
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) close();
    });

    confirmBtn.addEventListener('click', () => {
      close();
      if (typeof confirmCallback === 'function') {
        confirmCallback();
      }
    });
}


