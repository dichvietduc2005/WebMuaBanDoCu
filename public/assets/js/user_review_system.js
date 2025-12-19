// Toast notification function
function showToast(type, title, message) {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        // Thêm các thuộc tính để tránh adblock
        toastContainer.style.pointerEvents = 'auto';
        toastContainer.style.visibility = 'visible';
        toastContainer.style.opacity = '1';
        toastContainer.style.display = 'block';
        document.body.appendChild(toastContainer);
    }

    const toastEl = document.createElement('div');
    toastEl.className = `toast show align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.style.minWidth = '300px';
    // Đảm bảo toast hiển thị đúng
    toastEl.style.position = 'relative';
    toastEl.style.transform = 'none';
    toastEl.style.transition = 'all 0.3s ease';
    
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastEl);

    setTimeout(() => {
        toastEl.classList.remove('show');
        setTimeout(() => toastEl.remove(), 300);
    }, 3000);
}

// Hàm kiểm tra và khắc phục adblock
function checkAndFixAdblockIssues() {
    // Kiểm tra các phần tử có thể bị adblock ẩn
    const reviewElements = [
        '.customer-reviews',
        '.review-form',
        '.reviews-container',
        '.reviews-footer'
    ];
    
    reviewElements.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            const computedStyle = window.getComputedStyle(element);
            
            // Kiểm tra nếu phần tử bị ẩn
            if (computedStyle.display === 'none' || 
                computedStyle.visibility === 'hidden' || 
                computedStyle.opacity === '0') {
                
                console.log(`Adblock detected on ${selector}, fixing...`);
                
                // Khôi phục hiển thị
                element.style.display = element.classList.contains('customer-reviews') ? 'flex' : 'block';
                element.style.visibility = 'visible';
                element.style.opacity = '1';
                element.style.position = 'relative';
                
                // Thêm class để đánh dấu đã sửa
                element.classList.add('adblock-fixed');
            }
        });
    });
    
    // Kiểm tra toast container
    const toastContainer = document.getElementById('toast-container');
    if (toastContainer) {
        const computedStyle = window.getComputedStyle(toastContainer);
        if (computedStyle.display === 'none' || computedStyle.visibility === 'hidden') {
            toastContainer.style.display = 'block';
            toastContainer.style.visibility = 'visible';
            toastContainer.style.opacity = '1';
            toastContainer.style.pointerEvents = 'auto';
        }
    }
}

function create_review_form(review) {
    return `
    <div class="review-item">
        <div class="reviewer-info">
            <div class="reviewer-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="reviewer-details">
                <div class="reviewer-name">${review.username}</div>
                <div class="review-date">${review.sent_at}</div>
            </div>
        </div>
        <div class="review-text">
            ${review.content}
        </div>
    </div>`;
}

function add_event_button_send() {
    let sendButton = document.getElementById('sendButton');
    let inputReview = document.getElementById('contentReview');
    
    if (!sendButton || !inputReview) return;
    
    sendButton.addEventListener('click', function () {
        send_review();
    });
    
    // Allow Enter key to submit (but allow Shift+Enter for new line)
    inputReview.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send_review();
        }
    });
}

function send_review() {
    let sendButton = document.getElementById('sendButton');
    let inputReview = document.getElementById('contentReview');
    
    if (!sendButton || !inputReview) return;
    
    const content = inputReview.value.trim();
    
    // Validation
    if (content.length < 5) {
        showToast('error', 'Lỗi', 'Đánh giá phải có ít nhất 5 ký tự');
        return;
    }
    
    if (content.length > 500) {
        showToast('error', 'Lỗi', 'Đánh giá không được quá 500 ký tự');
        return;
    }
    
    // Disable button and show loading
    const originalText = sendButton.innerHTML;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    sendButton.disabled = true;
    
    fetch("/WebMuaBanDoCu/app/Controllers/review/SendReviewController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "content=" + encodeURIComponent(content) + "&product_id=" + product_id
    })
    .then(res => res.text())
    .then(data => {
        if (data === 'success') {
            showToast('success', 'Thành công', 'Đánh giá đã được gửi thành công!');
            inputReview.value = '';
            load_reviews();
            
            // Ẩn form và hiển thị thông báo đã đánh giá
            const reviewForm = document.querySelector('.review-form');
            const reviewsFooter = document.querySelector('.reviews-footer');
            if (reviewForm && reviewsFooter) {
                reviewsFooter.innerHTML = `
                    <div class="already-reviewed">
                        <i class="fas fa-check-circle text-success"></i>
                        <p>Bạn đã đánh giá sản phẩm này rồi</p>
                        <small>Cảm ơn bạn đã chia sẻ ý kiến!</small>
                    </div>
                `;
            }
        } else {
            // Kiểm tra nếu là lỗi duplicate review
            if (data.includes('đã đánh giá sản phẩm này rồi')) {
                showToast('warning', 'Thông báo', data);
            } else {
                showToast('error', 'Lỗi', data || 'Có lỗi xảy ra khi gửi đánh giá');
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showToast('error', 'Lỗi', 'Không thể kết nối đến server');
    })
    .finally(() => {
        // Restore button
        sendButton.innerHTML = originalText;
        sendButton.disabled = false;
    });
}

function load_reviews() {
    fetch("/WebMuaBanDoCu/app/Controllers/review/LoadReviewsController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "product_id=" + product_id
    })
    .then(res => res.json())
    .then(data => {
        const reviewsBox = document.getElementById("reviewsContainer");
        if (!reviewsBox) return;
        
        reviewsBox.innerHTML = ''; // Clear previous reviews
        
        if (data && data.length > 0) {
            data.forEach(review => {
                reviewsBox.innerHTML += create_review_form(review);
            });
        } else {
            reviewsBox.innerHTML = `
                <div class="no-reviews">
                    <i class="fas fa-comment-slash"></i>
                    <p>Chưa có đánh giá nào cho sản phẩm này</p>
                    <small>Hãy là người đầu tiên đánh giá sản phẩm này!</small>
                </div>
            `;
        }
    })
    .catch(err => {
        console.error('Error loading reviews:', err);
        const reviewsBox = document.getElementById("reviewsContainer");
        if (reviewsBox) {
            reviewsBox.innerHTML = `
                <div class="no-reviews">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Không thể tải đánh giá</p>
                    <small>Vui lòng thử lại sau</small>
                </div>
            `;
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    add_event_button_send();
    load_reviews();
    
    // Kiểm tra và khắc phục adblock issues
    checkAndFixAdblockIssues();
    
    // Kiểm tra lại sau khi load reviews
    setTimeout(checkAndFixAdblockIssues, 1000);
    
    // Theo dõi thay đổi DOM để phát hiện adblock
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && 
                (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                checkAndFixAdblockIssues();
            }
        });
    });
    
    // Quan sát các phần tử review
    const reviewElements = document.querySelectorAll('.customer-reviews, .review-form, .reviews-container');
    reviewElements.forEach(element => {
        observer.observe(element, {
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    });
});