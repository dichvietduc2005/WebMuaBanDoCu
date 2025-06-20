<?php
function footer() {
    ?>
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">Web Mua Bán Đồ Cũ</h5>
                    <p>Nền tảng mua bán đồ cũ uy tín, chất lượng hàng đầu Việt Nam</p>
                </div>
                
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/" class="text-white">Trang chủ</a></li>
                        <li class="mb-2"><a href="/gioi-thieu" class="text-white">Giới thiệu</a></li>
                        <li class="mb-2"><a href="/dieu-khoan" class="text-white">Điều khoản</a></li>
                        <li class="mb-2"><a href="/lien-he" class="text-white">Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h5 class="mb-3">Kết nối với chúng tôi</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-tiktok"></i></a>
                    </div>
                    <p class="mt-3 mb-0">© <?= date('Y') ?> Web Mua Bán Đồ Cũ. Bảo lưu mọi quyền.</p>
                </div>
            </div>
        </div>
    </footer>
    <?php
}