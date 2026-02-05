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
    const blockBtn = document.getElementById("block-account-button");
    if (!blockBtn) return;

    blockBtn.addEventListener("click", function () {
        const board = document.getElementById("board-skill");
        const userId = board.getAttribute("user-id");
        if (!userId) return;

        this.disabled = true;
        const originalText = this.innerText;
        this.innerText = 'Đang xử lý...';

        const base = window.baseUrl || '/';
        fetch(`${base}app/Controllers/admin/QuanLyTaiKhoanController.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=block&user_id=${encodeURIComponent(userId)}`
        }).then(respone => respone.text())
            .then(data => {
                if (data === "success") {
                    showToast("Tài khoản đã bị khóa thành công.", 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast("Lỗi: " + data, 'error');
                }
            }).catch(error => {
                console.error('Error:', error);
                showToast("Đã xảy ra lỗi khi xử lý.", 'error');
            }).finally(() => {
                this.disabled = false;
                this.innerText = originalText;
            });
    });
}

function add_event_unlock_account() {
    const unlockBtn = document.getElementById("unlock-account-button");
    if (!unlockBtn) return;

    unlockBtn.addEventListener("click", function () {
        const board = document.getElementById("board-skill");
        const userId = board.getAttribute("user-id");
        if (!userId) return;

        this.disabled = true;
        const originalText = this.innerText;
        this.innerText = 'Đang xử lý...';

        const base = window.baseUrl || '/';
        fetch(`${base}app/Controllers/admin/QuanLyTaiKhoanController.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=unlock&user_id=${encodeURIComponent(userId)}`
        }).then(respone => respone.text())
            .then(data => {
                if (data === "success") {
                    showToast("Tài khoản đã được mở khóa thành công.", 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data, 'error');
                }
            }).catch(error => {
                console.error('Error:', error);
                showToast("Đã xảy ra lỗi khi xử lý.", 'error');
            }).finally(() => {
                this.disabled = false;
                this.innerText = originalText;
            });
    });
}

function add_event_user_actions() {
    document.querySelectorAll('.js-user-action').forEach(btn => {
        btn.addEventListener('click', function () {
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            const action = this.getAttribute('data-action');
            show_behavior(userId, username, action);
        });
    });
}

function show_behavior(userid, username, action) {
    const usernameDisplay = document.getElementById("username-display");
    const titleEl = document.getElementById("account-modal-title");
    const descEl = document.getElementById("account-modal-description");
    const blockBtn = document.getElementById("block-account-button");
    const unlockBtn = document.getElementById("unlock-account-button");

    if (!userid || !usernameDisplay || !titleEl || !descEl || !blockBtn || !unlockBtn) return;

    const board = document.getElementById("board-skill");
    const overlay = document.getElementById("modal-overlay");

    usernameDisplay.innerText = `Tài khoản #${userid} - ${username}`;
    board.setAttribute("user-id", userid);
    board.setAttribute("data-action", action || '');

    if (action === 'block') {
        titleEl.innerText = 'Khóa tài khoản';
        descEl.innerText = 'Bạn có chắc chắn muốn khóa tài khoản này? Người dùng sẽ không thể đăng nhập cho đến khi được mở khóa.';
        blockBtn.classList.remove('hidden');
        unlockBtn.classList.add('hidden');
    } else {
        titleEl.innerText = 'Mở khóa tài khoản';
        descEl.innerText = 'Bạn có chắc chắn muốn mở khóa tài khoản này? Người dùng sẽ có thể đăng nhập và sử dụng hệ thống.';
        unlockBtn.classList.remove('hidden');
        blockBtn.classList.add('hidden');
    }

    board.style.display = "block";
    overlay.style.display = "block";
}

window.onload = function () {
    add_event_user_actions();
    add_event_block_account();
    add_event_unlock_account();

    const overlay = document.getElementById("modal-overlay");
    const board = document.getElementById("board-skill");
    const cancelBtn = document.getElementById("account-modal-cancel");

    function closeModal() {
        overlay.style.display = "none";
        board.style.display = "none";
        board.removeAttribute("user-id");
        board.removeAttribute("data-action");
    }

    if (overlay && board) {
        overlay.addEventListener("click", closeModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", closeModal);
    }
}
