document.addEventListener('DOMContentLoaded', function() {
    const bulkButtons = document.querySelectorAll('.bulk-action-btn');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const selectAllFeatured = document.getElementById('select-all-featured');
    const selectAllRegular = document.getElementById('select-all-regular');

    function getSelectedIds() {
        const ids = [];
        document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
            ids.push(cb.value);
        });
        return ids;
    }

    function updateBulkButtonsState() {
        const hasSelected = getSelectedIds().length > 0;
        bulkButtons.forEach(btn => {
            btn.disabled = !hasSelected;
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButtonsState);
    });

    if (selectAllFeatured) {
        selectAllFeatured.addEventListener('change', function() {
            document.querySelectorAll('#featured-table .product-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkButtonsState();
        });
    }

    if (selectAllRegular) {
        selectAllRegular.addEventListener('change', function() {
            document.querySelectorAll('#regular-table .product-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkButtonsState();
        });
    }

    bulkButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-bulk-action');
            const ids = getSelectedIds();
            if (!ids.length) {
                showToast('error', 'Lỗi', 'Vui lòng chọn ít nhất một sản phẩm');
                return;
            }

            let title = 'Xác nhận thao tác';
            let message = `Bạn chắc chắn muốn thực hiện thao tác này với ${ids.length} sản phẩm?`;
            if (action === 'delete') {
                title = 'Xác nhận xóa';
                message = `Bạn chắc chắn muốn xóa ${ids.length} sản phẩm? Thao tác này không thể hoàn tác.`;
            } else if (action === 'hide') {
                title = 'Ẩn sản phẩm';
                message = `Ẩn ${ids.length} sản phẩm khỏi danh sách hiển thị?`;
            } else if (action === 'feature') {
                title = 'Gắn nổi bật';
                message = `Đặt ${ids.length} sản phẩm thành nổi bật?`;
            } else if (action === 'unfeature') {
                title = 'Bỏ nổi bật';
                message = `Bỏ nổi bật ${ids.length} sản phẩm đã chọn?`;
            }

            const doBulk = async () => {
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.classList.add('disabled');

                try {
                    const formData = new URLSearchParams();
                    formData.append('bulk_action', action);
                    ids.forEach(id => formData.append('ids[]', id));

                    const response = await fetch('../../Models/admin/AdminModelAPI.php?action=bulk', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: formData.toString(),
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const text = await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Bulk response text:', text);
                        throw new Error('Server trả về dữ liệu không hợp lệ');
                    }

                    if (!data.success) {
                        showToast('error', 'Lỗi', data.message || 'Không thể thực hiện thao tác');
                        return;
                    }

                    showToast('success', 'Thành công', data.message);

                    ids.forEach(id => {
                        const row = document.querySelector(`tr[data-product-id="${id}"]`);
                        if (!row) return;

                        if (action === 'delete') {
                            row.style.transition = 'opacity 0.4s ease';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 400);
                        } else if (action === 'hide') {
                            const statusCell = row.querySelector('td:nth-child(7)');
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">Ẩn</span>';
                            }
                        } else if (action === 'feature' || action === 'unfeature') {
                            if (typeof updateFeaturedStatus === 'function') {
                                const isFeatured = action === 'feature';
                                updateFeaturedStatus(row, isFeatured);
                            }
                        }
                    });

                    setTimeout(() => {
                        if (!document.querySelector('#featured-table tbody tr') && !document.querySelector('#regular-table tbody tr')) {
                            const container = document.querySelector('main') || document.body;
                            container.insertAdjacentHTML(
                                'beforeend',
                                '<p class="mt-3 text-sm text-center text-gray-500 dark:text-gray-400">Không còn sản phẩm nào.</p>'
                            );
                        }
                        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
                        if (selectAllFeatured) selectAllFeatured.checked = false;
                        if (selectAllRegular) selectAllRegular.checked = false;
                        updateBulkButtonsState();
                    }, 450);
                } catch (error) {
                    console.error('Bulk error:', error);
                    showToast('error', 'Lỗi', error.message || 'Đã xảy ra lỗi khi thực hiện thao tác');
                } finally {
                    this.innerHTML = originalHtml;
                    this.classList.remove('disabled');
                }
            };

            showConfirmDialog(title, message, doBulk);
        });
    });

    updateBulkButtonsState();
});


