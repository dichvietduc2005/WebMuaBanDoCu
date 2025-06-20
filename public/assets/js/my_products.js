// Hiện toast
function showToast(msg, success=true) {
    var toast = document.getElementById('toast');
    toast.innerText = msg;
    toast.style.background = success ? '#3a86ff' : '#e63946';
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2000);
}

// Hiện modal sửa
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.onclick = function(e) {
        e.preventDefault();
        let row = document.getElementById('row-' + btn.dataset.id);
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_title').value = row.querySelector('.title').innerText;
        document.getElementById('edit_price').value = row.querySelector('.price').innerText.replace(/\D/g,'');
        document.getElementById('edit_category_id').value = row.dataset.category_id || 1;
        document.getElementById('edit_condition_status').value = row.dataset.condition_status || 'new';
        document.getElementById('edit_location').value = row.dataset.location || '';
        document.getElementById('edit_description').value = row.dataset.description || '';
        document.getElementById('editModal').style.display = 'block';
    }
});
document.getElementById('closeModal').onclick = function() {
    document.getElementById('editModal').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
}

// Gửi AJAX cập nhật sản phẩm
document.getElementById('editForm').onsubmit = function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch('../modules/my_products/handler.php?action=edit_ajax', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            let row = document.getElementById('row-' + formData.get('id'));
            row.querySelector('.title').innerText = formData.get('title');
            row.querySelector('.price').innerText = Number(formData.get('price')).toLocaleString() + ' VNĐ';
            row.querySelector('.status').innerText = data.status;
            showToast('Cập nhật thành công!');
            document.getElementById('editModal').style.display = 'none';
        } else {
            showToast(data.message || 'Có lỗi xảy ra!', false);
        }
    });
};

// Xóa sản phẩm bằng AJAX
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.onclick = function(e) {
        e.preventDefault();
        if (!confirm('Bạn chắc chắn muốn xóa?')) return;
        fetch('../modules/my_products/handler.php?action=delete_ajax&id=' + btn.dataset.id)
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