// Hiện toast
function showToast(msg, success = true) {
    var toast = document.getElementById('toast');
    toast.innerText = msg;
    toast.style.background = success ? '#3a86ff' : '#e63946';
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2000);
}

// Hiện modal sửa
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.onclick = function (e) {
        e.preventDefault();
        let row = document.getElementById('row-' + btn.dataset.id);
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_title').value = row.querySelector('.title').innerText;
        document.getElementById('edit_price').value = row.querySelector('.price').innerText.replace(/\D/g, '');
        document.getElementById('edit_category_id').value = row.dataset.category_id || 1;
        document.getElementById('edit_condition_status').value = row.dataset.condition_status || 'new';
        document.getElementById('edit_location').value = row.dataset.location || '';
        document.getElementById('edit_description').value = row.dataset.description || '';
        document.getElementById('editModal').style.display = 'block';
    }
});
document.getElementById('closeModal').onclick = function () {
    document.getElementById('editModal').style.display = 'none';
}
window.onclick = function (event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
}

// Gửi AJAX cập nhật sản phẩm
document.getElementById('editForm').onsubmit = function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch('../../Models/product/ProductUserModel.php?action=edit_ajax', {
        method: 'POST',
        body: formData
    })
        .then(res => res.text())
        .then(text => {
            try {
                var data = JSON.parse(text);
                if (data.success) {
                    let row = document.getElementById('row-' + formData.get('id'));
                    row.querySelector('.title').innerText = formData.get('title');
                    row.querySelector('.price').innerText = Number(formData.get('price')).toLocaleString() + ' VNĐ';
                    row.querySelector('.category').innerText = formData.get('category_id');
                    row.querySelector('.condition').innerText = formData.get('condition_status');
                    row.querySelector('.location').innerText = formData.get('location');
                    row.querySelector('.description').innerText = formData.get('description');
                    row.querySelector('.status').innerText = data.status;
                    // Cập nhật lại data-*
                    row.dataset.category_id = formData.get('category_id');
                    row.dataset.condition_status = formData.get('condition_status');
                    row.dataset.location = formData.get('location');
                    row.dataset.description = formData.get('description');
                    showToast('Cập nhật thành công!');
                    document.getElementById('editModal').style.display = 'none';
                } else {
                    showToast(data.message || 'Có lỗi xảy ra!', false);
                }
            } catch (e) {
                // Nếu không phải JSON, báo lỗi hệ thống
                showToast('Lỗi hệ thống hoặc phiên đăng nhập đã hết hạn!', false);
                // Có thể log text ra console để debug
                console.error('Server response:', text);
            }
        });
};

// Xóa sản phẩm bằng AJAX
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.onclick = function (e) {
        e.preventDefault();
        if (!confirm('Bạn chắc chắn muốn xóa?')) return;
        fetch('../../Models/product/ProductUserModel.php?action=delete_ajax&id=' + btn.dataset.id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('row-' + btn.dataset.id).remove();
                    showToast('Đã xóa sản phẩm!');
                } else {
                    showToast(data.message || 'Xóa thất bại!', false);
                }
            });
    }
});