// ... [phần code hiện tại] ...

// Thêm sự kiện đóng modal
document.getElementById('close-auth-modal')?.addEventListener('click', function() {
    document.getElementById('clerk-auth-container').style.display = 'none';
});

// Cập nhật hàm updatePhpSession
async function updatePhpSession(clerkUserId, clerkUserEmail = null) {
    try {
        const response = await fetch(
            `${APP_ROOT}/app/Controllers/Auth/sync_clerk_session.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    clerk_user_id: clerkUserId,
                    clerk_user_email: clerkUserEmail,
                }),
            }
        );

        const data = await response.json();
        if (data.success) {
            console.log("Session PHP đã được đồng bộ thành công.", data.message);
            
            // Đóng modal xác thực
            document.getElementById('clerk-auth-container').style.display = 'none';
            
            // Cập nhật giao diện người dùng ngay lập tức
            updateUIAfterLogin();
        } else {
            console.error("Lỗi khi đồng bộ session PHP:", data.message);
        }
    } catch (error) {
        console.error("Lỗi kết nối đến server để đồng bộ session:", error);
    }
}

// Hàm cập nhật giao diện sau khi đăng nhập
function updateUIAfterLogin() {
    // Ẩn nút đăng nhập, hiển thị nút đăng bán
    const openAuthBtn = document.getElementById('openClerkAuth');
    if (openAuthBtn) {
        openAuthBtn.parentElement.innerHTML = `
            <a href="product/sell.php" class="hero-btn btn-transparent">
                <i class="fas fa-store"></i> Đăng bán đồ
            </a>
        `;
    }
    
    // Cập nhật header
    document.querySelectorAll('.nav-auth').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll('.nav-user').forEach(el => {
        el.style.display = 'flex';
    });
}

// Khởi tạo Clerk
window.addEventListener("load", async function () {
    // ... [phần code hiện tại] ...

    // Nếu đã đăng nhập, cập nhật UI ngay
    if (IS_LOGGED_IN) {
        updateUIAfterLogin();
    }
});