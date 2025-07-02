function add_event_block_account() {
    document.getElementById("block-account-button").addEventListener("click", function () {
        const userId = document.getElementById("#board-skill").getAttribute("user-id");
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
                        alert("Tài khoản đã bị khóa thành công.");
                        window.location.reload();
                    } else {
                        alert("error: " + data);
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert("Đã xảy ra lỗi khi xóa tài khoản.");
                });
        } else {
            alert("Vui lòng chọn một tài khoản để xóa.");
        }
    });
}

function add_event_unlock_account() {
    document.getElementById("unlock-account-button").addEventListener("click", function () {
        const userId = document.getElementById("#board-skill").getAttribute("user-id");
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
                        alert("Tài khoản đã được mở khóa thành công.");
                        window.location.reload();
                    } else {
                        alert(data);
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert("Đã xảy ra lỗi khi xóa tài khoản.");
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
    let usernameDisplay = document.getElementById("#username-display")
    usernameDisplay.innerText = `${userid} - ${username}`;

    let board = document.getElementById("#board-skill");
    board.setAttribute("user-id", userid);
    board.style.visibility = "visible";
}

window.onload = function () {
    add_event_click();
    add_event_block_account();
    add_event_unlock_account();
}