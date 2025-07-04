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
    sendButton.addEventListener('click', function () {
        if (inputReview.value.length < 1 || inputReview.value.trim().length < 1) return;
        send_review(inputReview.value)
    })
}

function send_review(content) {
    fetch("/WebMuaBanDoCu/app/Controllers/review/SendReviewController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "content=" + content + "&product_id=" + product_id
            }).then(res => res.text())
    .then(data => {
        if (data === 'success') {
            load_reviews()
        } else {
            alert("Error sending message: " + data);
        }
    }).catch(err => {
        alert("Error: " + err);
    });
        }


function load_reviews() {
    fetch("/WebMuaBanDoCu/app/Controllers/review/LoadReviewsController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "product_id=" + product_id
            }).then(res => res.json())
    .then(data => {
        const reviewsBox = document.getElementById("reviewsContainer");
        reviewsBox.innerHTML = ''; // Clear previous messages
        data.forEach(review => {
            reviewsBox.innerHTML += create_review_form(review);
        });
    }).catch(err => {
        alert("Error: " + err);
    });
        }
add_event_button_send()