/**
 * Search Placeholder Typing Animation (Shopee Style)
 * Changes the placeholder text of the search input with a typing effect.
 */
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('search-input-desktop');
    if (!searchInput) return;

    const textArray = [
        "Bạn tìm gì hôm nay?",
        "Xe máy giá rẻ", 
        "iPhone 15 Pro Max", 
        "Ghế tập ngồi cho bé", 
        "Laptop Dell Inspiron", 
        "Đồ gia dụng thanh lý",
        "Sách cũ giá tốt",
        "Dream Thái kiểng"
    ];
    
    let arrayIndex = 0;
    let charIndex = 0;
    const typingSpeed = 100;
    const erasingSpeed = 50;
    const newTextDelay = 2500; 
    let typingTimeout;

    function type() {
        if (charIndex < textArray[arrayIndex].length) {
            searchInput.placeholder += textArray[arrayIndex].charAt(charIndex);
            charIndex++;
            typingTimeout = setTimeout(type, typingSpeed);
        } else {
            typingTimeout = setTimeout(erase, newTextDelay);
        }
    }

    function erase() {
        if (charIndex > 0) {
            searchInput.placeholder = textArray[arrayIndex].substring(0, charIndex - 1);
            charIndex--;
            typingTimeout = setTimeout(erase, erasingSpeed);
        } else {
            arrayIndex = (arrayIndex + 1) % textArray.length;
            typingTimeout = setTimeout(type, typingSpeed);
        }
    }

    // Reset loop when user interacts
    searchInput.addEventListener('focus', () => {
        clearTimeout(typingTimeout);
        searchInput.placeholder = "Bạn tìm gì hôm nay?";
    });

    searchInput.addEventListener('blur', () => {
        if (searchInput.value === "") {
            searchInput.placeholder = "";
            charIndex = 0;
            type();
        }
    });

    // Start the animation
    searchInput.placeholder = "";
    setTimeout(type, 1000);
});
