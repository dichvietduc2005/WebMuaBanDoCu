function add_event_btn_edit() {
    document.getElementById('#btn-edit').addEventListener('click', function () {
        const inputs = document.querySelectorAll('.info-group input');
        inputs.forEach(input => {
            input.disabled = false;
        });
        this.style.display = 'none';
        document.getElementById('#btn-save').style.display = 'inline-block';
    });
}

function add_event_btn_save() {
    document.getElementById('#btn-save').addEventListener('click', function () {
        let phone_input = document.getElementById('user_phone');
        if (phone_input.value.length != 10) {
            alert('Số điện thoại không hợp lệ. Vui lòng nhập lại.');
            return;
        }
        for (let i = 0; i < phone_input.value.length; i++) {
            if (isNaN(phone_input.value[i]) || phone_input.value[i] < '0' || phone_input.value[i] > '9') {
                alert('Số điện thoại không hợp lệ. Vui lòng nhập lại.');
                return;
            }
        }

        const data = {};
        const inputs = document.querySelectorAll('.info-group input');
        inputs.forEach(input => {
            data[input.id] = input.value;
            input.disabled = true;
        });
        data['user_id'] = userId;

        this.style.display = 'none';
        document.getElementById('#btn-edit').style.display = 'inline-block';


        fetch('/WebMuaBanDoCu/app/Controllers/user/ProfileUserController.php', { //
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(res => res.text())
            .then(data => {
                if (data == "success") {
                    alert('Cập nhật thông tin thành công!');
                } else {
                    alert('Cập nhật thông tin thất bại: ' + data);
                }
            })


    });
}

document.getElementById('#btn-save').style.display = 'none';
add_event_btn_edit();
add_event_btn_save();