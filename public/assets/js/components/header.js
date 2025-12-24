// Variables expected to be defined by global scope or inline script:
// let userId = ...;
// let BASE_URL = ...;

document.addEventListener('DOMContentLoaded', function () {
    const toasts = document.querySelectorAll('.custom-toast');
    toasts.forEach(toast => {
        // Hiện ra
        setTimeout(() => {
            toast.classList.add('show');
        }, 300);

        // Tự động biến mất
        setTimeout(() => {
            toast.classList.remove('show');
            // Xóa khỏi cây thư mục sau khi ẩn hẳn
            setTimeout(() => toast.remove(), 700);
        }, 4000);
    });
});
