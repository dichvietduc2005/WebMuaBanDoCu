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

            /* Phone validation removed */

            // Use FormData for file upload
            const formData = new FormData();

            const inputs = document.querySelectorAll('#profile-form input');
            inputs.forEach(input => {
                formData.append(input.id, input.value);
                input.disabled = true;
            });
            formData.append('user_id', userId);

            // Add avatar if selected
            const fileInput = document.getElementById('avatar-upload');
            if (fileInput && fileInput.files[0]) {
                formData.append('avatar', fileInput.files[0]);
            }

            // UI Toggle back
            this.classList.add('d-none');
            document.getElementById('btn-edit').classList.remove('d-none');

            // Send Request
            fetch('/WebMuaBanDoCu/app/Controllers/user/ProfileUserController.php', {
                method: 'POST',
                // Content-Type header MUST be removed for FormData to set boundary
                body: formData
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

// Avatar Upload Logic
function add_event_avatar_upload() {
    const avatarFrame = document.querySelector('.promax-avatar-frame');
    const fileInput = document.getElementById('avatar-upload');
    const avatarImg = document.querySelector('.promax-avatar-img');

    if (avatarFrame && fileInput) {
        // Trigger file input on frame click
        avatarFrame.addEventListener('click', () => {
            fileInput.click();
        });

        // Handle file selection
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate size/type if needed
                if (file.size > 5 * 1024 * 1024) { // 5MB
                    alert('File quá lớn! Vui lòng chọn ảnh dưới 5MB.');
                    return;
                }

                // Preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (avatarImg.tagName === 'IMG') {
                        avatarImg.src = e.target.result;
                    } else {
                        // Replace div placeholder with img
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.className = 'promax-avatar-img';
                        newImg.id = 'avatar-preview';
                        avatarImg.parentNode.replaceChild(newImg, avatarImg);
                    }
                }
                reader.readAsDataURL(file);

                // Show Save Button automatically when image changes
                document.getElementById('btn-save').classList.remove('d-none');
                document.getElementById('btn-edit').classList.add('d-none');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    add_event_btn_edit();
    add_event_btn_save();
    add_event_avatar_upload();
});