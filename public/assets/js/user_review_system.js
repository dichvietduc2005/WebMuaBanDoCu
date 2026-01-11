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
    // Generate stars HTML
    const rating = parseInt(review.rating) || 5;
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            starsHtml += '<i class="fas fa-star"></i>';
        } else {
            starsHtml += '<i class="far fa-star"></i>';
        }
    }

    const isRecommended = review.is_recommended == 1;
    const sent_at = review.sent_at ? new Date(review.sent_at) : new Date();
    const formattedDate = sent_at.toLocaleDateString('vi-VN');
    const firstLetter = review.username ? review.username.charAt(0).toUpperCase() : '?';

    return `
    <div class="review-item border-bottom pb-4 mb-4">
        <div class="d-flex justify-content-between mb-2">
            <div class="reviewer-info d-flex align-items-center">
                <div class="reviewer-avatar fw-bold bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:28px; height:28px; font-size:14px;">
                    ${firstLetter}
                </div>
                <div class="reviewer-name fw-bold text-dark">${review.username}</div>
            </div>
            ${isRecommended ? '<div class="text-success small fw-medium"><i class="fas fa-check-circle me-1"></i>Sẽ giới thiệu</div>' : ''}
        </div>
        
        <div class="review-rating-row mb-3 d-flex align-items-center">
            <div class="text-warning small me-3">
                ${starsHtml}
            </div>
            <div class="verified-badge text-success small">
                <i class="fas fa-check-circle me-1"></i>Đã mua tại WebMuaBanDoCu
            </div>
        </div>

        <div class="review-text text-dark mb-3" style="font-size: 15px; line-height: 1.5;">${review.content}</div>
        
        <div class="review-footer d-flex justify-content-between align-items-center">
            <div class="review-actions small">
                <a href="#" class="text-secondary text-decoration-none me-4"><i class="far fa-thumbs-up me-1"></i> Hữu ích (0)</a>
                <a href="#" class="text-secondary text-decoration-none"><i class="far fa-comment-dots me-1"></i> Thảo luận</a>
            </div>
            <div class="review-date small text-muted">${formattedDate}</div>
         </div>
    </div>`;
}

function add_event_button_send() {
    let sendButton = document.getElementById('sendButton');
    let inputReview = document.getElementById('contentReview');
    
    if (!sendButton || !inputReview) return;
    

    // Star Rating Logic (Modal)
    const stars = document.querySelectorAll('.review-stars-group i');
    const ratingInput = document.getElementById('reviewRating');
    const ratingLabel = document.getElementById('ratingLabel');
    
    if (stars.length > 0 && ratingInput) {
        stars.forEach(star => {
            // Click
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                const label = this.getAttribute('data-label');
                
                ratingInput.value = rating;
                if(ratingLabel) ratingLabel.textContent = label;
                
                // Update visual active state
                stars.forEach(s => {
                    if (s.getAttribute('data-rating') <= rating) {
                        s.classList.add('active');
                        // Ensure fas/far consistency if we were swapping classes
                        // But CSS uses .active to color, so just keeping .active is enough 
                        // if base class has fa-star. 
                        // In HTML we have <i class="fas fa-star ..."> so just toggling active is key.
                        // However, previous code might have swapped fas/far. Let's stick to simple active toggle.
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
            
            // Hover effect
            star.addEventListener('mouseenter', function() {
                const rating = this.getAttribute('data-rating');
                const label = this.getAttribute('data-label');
                
                if(ratingLabel) ratingLabel.textContent = label;

                stars.forEach(s => {
                     if (s.getAttribute('data-rating') <= rating) {
                        s.style.transform = 'scale(1.2)';
                        s.style.color = '#f59e0b'; // Force color on hover
                    } else {
                        s.style.transform = 'scale(1)';
                        s.style.color = '#ddd';
                    }
                });
            });
            
            // Mouse leave - restore selection
            star.parentElement.addEventListener('mouseleave', function() {
                const currentRating = ratingInput.value;
                const activeStar = document.querySelector(`.review-stars-group i[data-rating="${currentRating}"]`);
                if(activeStar && ratingLabel) ratingLabel.textContent = activeStar.getAttribute('data-label');

                stars.forEach(s => {
                    s.style.transform = 'scale(1)';
                    s.style.color = ''; // Remove inline style to revert to CSS class
                    
                    if (s.getAttribute('data-rating') <= currentRating) {
                         s.classList.add('active');
                    } else {
                         s.classList.remove('active');
                    }
                });
            });
        });
    }

    sendButton.addEventListener('click', function () {
        send_review();
    });
    
    // Allow Enter key to submit (but allow Shift+Enter for new line) - Modal textarea
    if(inputReview) {
        inputReview.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                send_review();
            }
        });
    }

}

function send_review() {
    let sendButton = document.getElementById('sendButton');
    let inputReview = document.getElementById('contentReview');
    let ratingInput = document.getElementById('reviewRating'); // Get rating
    
    if (!sendButton || !inputReview) return;
    
    const content = inputReview.value.trim();
    const rating = ratingInput ? ratingInput.value : 5; // Default to 5
    const recommendCheck = document.getElementById('recommendCheck');
    const isRecommended = recommendCheck && recommendCheck.checked ? 1 : 0;
    
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
        body: "content=" + encodeURIComponent(content) + "&product_id=" + product_id + "&rating=" + rating + "&is_recommended=" + isRecommended
    })
    .then(res => res.text())
    .then(data => {
        if (data === 'success') {
            showToast('success', 'Thành công', 'Đánh giá đã được gửi thành công!');
            inputReview.value = '';
            load_reviews();
            
            // Close Modal
            const modalEl = document.getElementById('reviewModal');
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            }

            // Update UI to "Already Reviewed"
            const reviewTrigger = document.querySelector('.btn-write-review');
            if (reviewTrigger) {
                const footer = reviewTrigger.closest('.reviews-footer');
                if (footer) {
                    footer.innerHTML = `
                        <div class="already-reviewed">
                            <i class="fas fa-check-circle text-success"></i>
                            <p>Bạn đã đánh giá sản phẩm này rồi</p>
                            <small>Cảm ơn bạn đã chia sẻ ý kiến!</small>
                        </div>
                    `;
                }
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
    .then(res => {
        // Check if response is actually JSON
        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            // If not JSON, try to parse as text first to see what we got
            return res.text().then(text => {
                console.error('Expected JSON but got:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response');
            });
        }
        return res.json();
    })
    .then(data => {
        const reviewsBox = document.getElementById("reviewsList");
        if (!reviewsBox) return;
        
        // Handle both array response and object with success flag
        let reviews = [];
        if (Array.isArray(data)) {
            reviews = data;
        } else if (data && data.success === false) {
            // Error response
            console.error('API error:', data.message);
            reviews = [];
        } else if (data && Array.isArray(data.reviews)) {
            reviews = data.reviews;
        }
        
        // Don't clear if summary box is inside, but now it's outside
        reviewsBox.innerHTML = ''; // Clear previous reviews
        
        if (reviews && reviews.length > 0) {
            reviews.forEach(review => {
                reviewsBox.innerHTML += create_review_form(review);
            });
        } else {
            reviewsBox.innerHTML = `
                <div class="no-reviews text-center py-5">
                    <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này</p>
                </div>
            `;
        }
    })
    .catch(err => {
        console.error('Error loading reviews:', err);
        const reviewsBox = document.getElementById("reviewsList") || document.getElementById("reviewsContainer");
        if (reviewsBox) {
            reviewsBox.innerHTML = `
                <div class="no-reviews text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p class="text-muted">Không thể tải đánh giá</p>
                    <small class="text-muted">Vui lòng thử lại sau</small>
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