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
    toastEl.addEventListener('hidden.bs.toast', function() {
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

    document.getElementById('confirm-yes').onclick = function() {
        modal.remove();
        if (typeof onConfirm === 'function') onConfirm();
    };
    document.getElementById('confirm-no').onclick = function() {
        modal.remove();
        if (typeof onCancel === 'function') onCancel();
    };
}



// Xóa sản phẩm bằng AJAX
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.onclick = function (e) {
        e.preventDefault();
        showConfirmDialog(
            'Xác nhận xóa',
            'Bạn chắc chắn muốn xóa?',
            function onConfirm() {
                fetch('../../Models/product/ProductUserModel.php?action=delete_ajax&id=' + btn.dataset.id)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('row-' + btn.dataset.id).remove();
                            showToast('success', 'Thành công!', 'Đã xóa sản phẩm!');
                        } else {
                            showToast('danger', 'Lỗi!', data.message || 'Xóa thất bại!');
                        }
                    });
            },
            function onCancel() {
                // Không làm gì khi hủy
            }
        );
    }
});