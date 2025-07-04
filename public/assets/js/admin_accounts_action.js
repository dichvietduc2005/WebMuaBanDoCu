function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notify ${type}`;
    toast.innerText = message;
    document.body.appendChild(toast);
    // allow DOM paint
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    // auto remove after 3s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function add_event_block_account() {
    document.getElementById("block-account-button").addEventListener("click", function () {
        const userId = document.getElementById("board-skill").getAttribute("user-id");
        if (userId != "") {

            fetch(`/WebMuaBanDoCu/app/Controllers/admin/QuanLyTaiKhoanController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=block&user_id=${userId}`
            }).then(respone => respone.text())
                .then(data => {
                    if (data === "success") {
                        showToast("Tài khoản đã bị khóa thành công.", 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showToast("Lỗi: " + data, 'error');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    showToast("Đã xảy ra lỗi khi xử lý.", 'error');
                });
        } else {
            alert("Vui lòng chọn một tài khoản để xóa.");
        }
    });
}

function add_event_unlock_account() {
    document.getElementById("unlock-account-button").addEventListener("click", function () {
        const userId = document.getElementById("board-skill").getAttribute("user-id");
        if (userId != "") {

            fetch(`/WebMuaBanDoCu/app/Controllers/admin/QuanLyTaiKhoanController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=unlock&user_id=${userId}`
            }).then(respone => respone.text())
                .then(data => {
                    if (data === "success") {
                        showToast("Tài khoản đã được mở khóa thành công.", 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showToast(data, 'error');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    showToast("Đã xảy ra lỗi khi xử lý.", 'error');
                });


        } else {
            alert("Vui lòng chọn một tài khoản để xóa.");
        }
    });
}

function add_event_click() {
    document.querySelectorAll('.user-row').forEach(row => {
        row.addEventListener('click', function () {
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            show_behavior(userId, username);
        });
    });
}

function show_behavior(userid, username) {
    const usernameDisplay = document.getElementById("username-display");
    usernameDisplay.innerText = `${userid} - ${username}`;

    const board = document.getElementById("board-skill");
    const overlay = document.getElementById("modal-overlay");

    board.setAttribute("user-id", userid);
    board.style.display = "flex";
    overlay.style.display = "block";
}

window.onload = function () {
    add_event_click();
    add_event_block_account();
    add_event_unlock_account();
    const overlay = document.getElementById("modal-overlay");
    const board = document.getElementById("board-skill");

    overlay.addEventListener("click", () => {
        overlay.style.display = "none";
        board.style.display = "none";
        board.removeAttribute("user-id");
    });
}
