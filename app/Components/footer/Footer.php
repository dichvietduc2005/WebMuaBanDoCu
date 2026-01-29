<?php
// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../config/config.php';
}

function footer()
{
    // Prevent duplicate rendering
    static $footerRendered = false;
    if ($footerRendered) {
        return; // Already rendered, skip
    }
    $footerRendered = true;
    ?>
    <footer class="footer"
        style="width: 100vw !important; max-width: 100vw !important; margin: 0 !important; margin-left: calc(-50vw + 50%) !important; margin-right: calc(-50vw + 50%) !important; padding-left: 0 !important; padding-right: 0 !important; left: 0 !important; right: 0 !important; position: relative !important; z-index: 100 !important; display: block !important;">
        <div class="container-fluid px-3 px-md-4" style="max-width: 1400px; margin: 0 auto; width: 100%;">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Về chúng tôi</h3>
                    <p>Mua Bán Đồ Cũ là nền tảng kết nối người mua và người bán đồ đã qua sử dụng uy tín, chất lượng hàng
                        đầu Việt Nam.</p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/Duckerrrrrrr" class="social-link" aria-label="Facebook"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="https://dichvietduc2005.github.io/NhomTamAt/" class="social-link" aria-label="GitHub"><i
                                class="fab fa-github"></i></a>
                        <a href="https://www.youtube.com/@nguyenthinh7643" class="social-link" aria-label="Youtube"><i
                                class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Liên kết nhanh</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Giới thiệu</a></li>
                        <li><a href="<?php echo BASE_URL; ?>app/View/product/products.php"><i
                                    class="fas fa-chevron-right"></i> Sản phẩm</a></li>
                        <li><a href="<?php echo BASE_URL; ?>app/View/product/sell.php"><i class="fas fa-chevron-right"></i>
                                Đăng bán</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Liên hệ</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Danh mục</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Điện thoại</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Laptop</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Máy tính bảng</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Máy ảnh</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Phụ kiện</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Liên hệ</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Phường 12, Hồ Chí Minh</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span><a href="tel:0945554902" class="text-decoration-none"
                                    style="color: inherit;">0945554902</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><a href="mailto:nguyenthinhk52005@gmail.com" class="text-decoration-none"
                                    style="color: inherit;">nguyenthinhk52005@gmail.com</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Thứ 2 - Chủ nhật: 8:00 - 22:00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="copyright">
                &copy; 2025 Mua Bán Đồ Cũ. Tất cả quyền được bảo lưu.
            </div>
        </div>
    </footer>

    <!-- Chat Widget Global Integration -->
    <?php
    // Ensure BASE_URL is defined
    $baseUrl = defined('BASE_URL') ? BASE_URL : '/WebMuaBanDoCu/'; // Fallback path if needed
    ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>public/assets/css/chat_widget_modern.css">

    <?php
    // Include Chat HTML
    // Allow for flexible path resolution
    $chatViewPath = __DIR__ . '/../../View/user/ChatView.php';
    if (file_exists($chatViewPath)) {
        require_once $chatViewPath;
    }
    ?>

    <!-- Chat Widget Logic -->
    <script>
        window.userId = <?php echo isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 'null'; ?>;
    </script>
    <script src="<?php echo $baseUrl; ?>public/assets/js/user_chat_system.js"></script>

    <?php
}

// Thêm hàm renderFooter để tương thích với Home.php
function renderFooter()
{
    // Gọi hàm footer gốc
    footer();
}