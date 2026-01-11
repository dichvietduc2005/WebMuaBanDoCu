/**
 * Hero Carousel - Tự động chuyển đổi ảnh nền
 */

class HeroCarousel {
    constructor(options = {}) {
        this.slider = document.getElementById('hero-slider');
        this.slides = document.querySelectorAll('.hero-slide');
        this.indicators = document.querySelectorAll('.indicator');
        
        console.log('HeroCarousel init:', {
            slider: this.slider,
            slides: this.slides.length,
            indicators: this.indicators.length,
            slideElements: Array.from(this.slides).map((s, i) => ({
                index: i,
                classes: s.className,
                backgroundImage: s.style.backgroundImage
            }))
        });
        
        // Cấu hình
        this.currentSlide = 0;
        this.autoPlayInterval = options.interval || 5000; // 5 giây
        this.transitionSpeed = options.speed || 1000; // 1 giây
        this.autoPlay = options.autoPlay !== false; // Mặc định true
        
        if (!this.slider || this.slides.length === 0) {
            console.error('HeroCarousel: slider hoặc slides không tồn tại');
            return;
        }
        
        this.init();
    }
    
    init() {
        console.log('HeroCarousel: init started');
        
        // Đảm bảo slide đầu tiên có active class
        if (this.slides.length > 0) {
            this.slides[0].classList.add('active');
            console.log('Added active class to slide 0');
        }
        
        // Bind events
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                console.log('Indicator clicked:', index);
                this.goToSlide(index);
            });
        });
        
        // Pause on hover
        this.slider.addEventListener('mouseenter', () => {
            console.log('Mouse enter, pausing');
            this.pause();
        });
        this.slider.addEventListener('mouseleave', () => {
            console.log('Mouse leave, resuming');
            this.play();
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                console.log('Left arrow pressed');
                this.prevSlide();
            }
            if (e.key === 'ArrowRight') {
                console.log('Right arrow pressed');
                this.nextSlide();
            }
        });
        
        // Start auto play
        if (this.autoPlay) {
            console.log('Starting autoplay with interval:', this.autoPlayInterval);
            this.play();
        }
    }
    
goToSlide(index) {
        // Nếu đang ở slide đó rồi thì không làm gì cả
        if (index === this.currentSlide) return;
        
        // Xác định slide hiện tại và slide sắp tới
        const currentSlideEl = this.slides[this.currentSlide];
        const nextSlideEl = this.slides[index];
        
        // BƯỚC 1: Dọn dẹp các slide không liên quan
        // (Đưa chúng về trạng thái chờ bên phải, bỏ class prev/active)
        this.slides.forEach((slide, i) => {
            if (i !== this.currentSlide && i !== index) {
                slide.classList.remove('active', 'prev');
                // Ngắt transition để nó về vị trí chờ ngay lập tức mà không trượt qua màn hình
                slide.style.transition = 'none'; 
                // Force Reflow
                void slide.offsetWidth; 
                // Trả lại transition cho lần dùng sau
                slide.style.transition = ''; 
            }
        });

        // BƯỚC 2: Xử lý Slide HIỆN TẠI -> Biến thành Slide CŨ (Trượt sang trái)
        if (currentSlideEl) {
            currentSlideEl.classList.remove('active');
            currentSlideEl.classList.add('prev');
        }

        // BƯỚC 3: Xử lý Indicators
        this.indicators.forEach(ind => ind.classList.remove('active'));
        if (this.indicators[index]) {
            this.indicators[index].classList.add('active');
        }

        // BƯỚC 4: Xử lý Slide MỚI (Trượt từ phải vào giữa)
        // Đảm bảo slide mới đang ở đúng vị trí bên phải trước khi trượt
        nextSlideEl.classList.remove('prev'); 
        
        // Force Reflow: Bắt trình duyệt tính toán lại vị trí trước khi add class active
        // Đây là bí quyết để animation luôn chạy đúng hướng
        void nextSlideEl.offsetWidth; 
        
        nextSlideEl.classList.add('active');

        // Cập nhật index
        this.currentSlide = index;
    }   
    
    nextSlide() {
        let next = (this.currentSlide + 1) % this.slides.length;
        console.log('Next slide:', next);
        this.goToSlide(next);
    }
    
    prevSlide() {
        let prev = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        console.log('Prev slide:', prev);
        this.goToSlide(prev);
    }
    
    play() {
        if (this.autoPlayTimer) clearInterval(this.autoPlayTimer);
        console.log('Play: setting interval');
        this.autoPlayTimer = setInterval(() => this.nextSlide(), this.autoPlayInterval);
    }
    
    pause() {
        if (this.autoPlayTimer) {
            clearInterval(this.autoPlayTimer);
            console.log('Paused');
        }
    }
    
    destroy() {
        this.pause();
    }
}

// Khởi tạo khi DOM sẵn sàng
console.log('hero-carousel.js loaded');

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded event fired');
    new HeroCarousel({
        interval: 5000, // Chuyển ảnh mỗi 5 giây
        speed: 1000,    // Transition 1 giây
        autoPlay: true  // Tự động phát
    });
});

// Fallback nếu DOM đã sẵn sàng
if (document.readyState === 'loading') {
    console.log('Document still loading');
} else {
    console.log('Document already loaded, initializing carousel');
    new HeroCarousel({
        interval: 4000,
        speed: 1000,
        autoPlay: true
    });
}
