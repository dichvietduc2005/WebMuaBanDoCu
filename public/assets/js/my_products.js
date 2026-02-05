// Toast helper giống sell.js
function showToast(type, title, message) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body text-white">
                <strong>${title}</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', function () {
        toastEl.remove();
    });
}

function createToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
    }
    return container;
}

// Hiển thị dialog xác nhận (kiểu hỏi lại)
function showConfirmDialog(title, message, onConfirm, onCancel) {
    // Nếu đã có modal thì xóa trước
    let oldModal = document.getElementById('custom-confirm-modal');
    if (oldModal) oldModal.remove();

    const modal = document.createElement('div');
    modal.id = 'custom-confirm-modal';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100vw';
    modal.style.height = '100vh';
    modal.style.background = 'rgba(0,0,0,0.3)';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.zIndex = '2000';

    modal.innerHTML = `
        <div style="background: #fff; border-radius: 10px; max-width: 350px; width: 100%; box-shadow: 0 2px 16px rgba(0,0,0,0.15); padding: 24px 20px; text-align: center;">
            <h5 style="margin-bottom: 12px;">${title}</h5>
            <div style="margin-bottom: 18px;">${message}</div>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button id="confirm-yes" class="btn btn-danger">Đồng ý</button>
                <button id="confirm-no" class="btn btn-secondary">Hủy</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    document.getElementById('confirm-yes').onclick = function () {
        modal.remove();
        if (typeof onConfirm === 'function') onConfirm();
    };
    document.getElementById('confirm-no').onclick = function () {
        modal.remove();
        if (typeof onCancel === 'function') onCancel();
    };
}



// Xóa sản phẩm bằng AJAX
// Xóa sản phẩm bằng AJAX
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.onclick = function (e) {
        e.preventDefault();
        const id = btn.dataset.id; // Corrected to get ID from current button
        showConfirmDialog(
            'Xác nhận xóa',
            'Bạn chắc chắn muốn xóa?',
            function onConfirm() {
                fetch((window.baseUrl || '') + 'app/Models/product/ProductUserModel.php?action=delete_ajax&id=' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Remove both row (desktop) and card (mobile)
                            const row = document.getElementById('row-' + id);
                            if (row) row.remove();

                            const card = document.getElementById('card-' + id);
                            if (card) card.remove();

                            showToast('success', 'Thành công!', 'Đã xóa sản phẩm!');

                            // Check if empty and show empty state if needed? (Optional)
                        } else {
                            showToast('danger', 'Lỗi!', data.message || 'Xóa thất bại!');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('danger', 'Lỗi!', 'Có lỗi xảy ra khi xóa.');
                    });
            },
            function onCancel() {
                // Không làm gì khi hủy
            }
        );
    }
});

// Helper to format currency input like 10.000.000
function formatCurrencyInput(value) {
    if (!value) return '';
    value = value.toString().replace(/\D/g, '');
    return value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Bind formatter to inputs
const editPriceInput = document.getElementById('edit_price');
if (editPriceInput) {
    editPriceInput.addEventListener('input', function (e) {
        let original = this.value;
        let formatted = formatCurrencyInput(original);
        if (original !== formatted) {
            this.value = formatted;
        }
    });
}

// Handle Edit Form Submit
document.getElementById('editForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'edit_ajax');

    // Clean price (remove dots) before sending
    let rawPrice = formData.get('price').toString().replace(/\./g, '');
    formData.set('price', rawPrice);

    fetch((window.baseUrl || '') + 'app/Models/product/ProductUserModel.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Thành công!', 'Cập nhật sản phẩm thành công!');

                const modalEl = document.getElementById('editModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Reload to reflect changes clean
                setTimeout(() => location.reload(), 1000);

            } else {
                showToast('danger', 'Lỗi!', data.message || 'Cập nhật thất bại');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('danger', 'Lỗi!', 'Có lỗi kết nối server');
        });
});

// Lightbox & Edit Logic
$(document).ready(function () {
    // Open Lightbox
    $(document).on('click', '.product-img, .card-img', function () {
        let src = $(this).attr('src');
        if (src) {
            $('#lightbox-img').attr('src', src);
            $('#lightbox-overlay').css('display', 'flex').fadeIn(200);
        }
    });

    // Close Lightbox
    $('.lightbox-close, #lightbox-overlay').click(function (e) {
        if (e.target !== document.getElementById('lightbox-img')) {
            $('#lightbox-overlay').fadeOut(200);
        }
    });

    // Handle Edit Click - Fetch Details via AJAX
    $('.btn-edit, .edit-btn-mobile').off('click').on('click', function (e) {
        e.preventDefault();
        const btn = $(this);
        const id = btn.data('id');

        // Show Modal & Loader
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();

        // Reset/Loading State
        $('#editForm')[0].reset();
        $('#edit_images_preview').html('<div class="text-muted small fst-italic">Đang tải dữ liệu...</div>');

        // Fetch Details
        fetch((window.baseUrl || '') + `app/Models/product/ProductUserModel.php?action=get_details_ajax&id=${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;

                    $('#edit_id').val(data.id);
                    $('#edit_title').val(data.title);
                    $('#edit_price').val(formatCurrencyInput(data.price));
                    $('#edit_category_id').val(data.category_id);
                    $('#edit_condition_status').val(data.condition_status);
                    $('#edit_location').val(data.location || '');
                    $('#edit_description').val(data.description);

                    // Render Images
                    let imgHtml = '';
                    if (data.images && data.images.length > 0) {
                        data.images.forEach(img => {
                            let fullPath = (window.baseUrl || '') + `public/${img.image_path}`;
                            // Only show secondary images or all? User asked for "detail images".
                            // Usually "is_primary=1" is the main one. We can show all.
                            imgHtml += `
                                <div style="position:relative; width:80px; height:80px; cursor:pointer;" class="detail-img-thumb">
                                    <img src="${fullPath}" style="width:100%; height:100%; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                                </div>
                             `;
                        });
                    } else {
                        imgHtml = '<div class="text-muted small">Không có ảnh chi tiết.</div>';
                    }
                    $('#edit_images_preview').html(imgHtml);

                    // Allow clicking detail images to zoom too
                    $('.detail-img-thumb img').on('click', function () {
                        let src = $(this).attr('src');
                        $('#lightbox-img').attr('src', src);
                        $('#lightbox-overlay').fadeIn(200).css('display', 'flex');
                    });

                } else {
                    showToast('danger', 'Lỗi', 'Không thể lấy thông tin sản phẩm');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('danger', 'Lỗi', 'Lỗi kết nối khi lấy thông tin');
            });
    });
});