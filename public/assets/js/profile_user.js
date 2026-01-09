function add_event_btn_edit() {
    const editBtn = document.getElementById('btn-edit');
    if (editBtn) {
        editBtn.addEventListener('click', function () {
            const inputs = document.querySelectorAll('#profile-form input');
            inputs.forEach(input => {
                // Prevent editing of username if desired, otherwise allow all
                // For now allow all as per original logic, but usually username is fixed
                if (input.id !== 'user_name') { 
                    input.disabled = false;
                }
            });
            this.classList.add('d-none');
            document.getElementById('btn-save').classList.remove('d-none');
            
            // Focus first editable input
            document.getElementById('user_full_name').focus();
        });
    }
}

function add_event_btn_save() {
    const saveBtn = document.getElementById('btn-save');
    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            let phone_input = document.getElementById('user_phone');
            
            // Basic validation
            if (phone_input.value.length != 10) {
                alert('Số điện thoại không hợp lệ. Vui lòng nhập lại (10 số).');
                return;
            }
            // Check numeric
            if (!/^\d+$/.test(phone_input.value)) {
                 alert('Số điện thoại chỉ được chứa số.');
                 return;
            }

            const data = {};
            // Collect data from inputs
            const inputs = document.querySelectorAll('#profile-form input');
            inputs.forEach(input => {
                data[input.id] = input.value;
                input.disabled = true; // Lock inputs immediately for better UX
            });
            data['user_id'] = userId;

            // UI Toggle back
            this.classList.add('d-none');
            document.getElementById('btn-edit').classList.remove('d-none');

            // Send Request
            fetch('/WebMuaBanDoCu/app/Controllers/user/ProfileUserController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(res => res.text())
                .then(responseData => {
                    if (responseData.trim() === "success") {
                        // Success Feedback
                        alert('Cập nhật thông tin thành công!');
                    } else {
                        // Error Feedback
                        alert('Cập nhật thông tin thất bại: ' + responseData);
                        // Re-enable edit mode on failure?
                    }
                })
                .catch(err => {
                    alert('Lỗi kết nối: ' + err);
                });
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    add_event_btn_edit();
    add_event_btn_save();
});