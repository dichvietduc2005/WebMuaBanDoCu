# Phân tích các file không còn sử dụng - Đề xuất xóa

## Các file có thể xóa an toàn

### 1. **TrangChu.php** (Legacy redirect files)
**Files:**
- `app/View/TrangChu.php` - Chỉ redirect về index.php
- `public/TrangChu.php` - Chỉ redirect về index.php

**Lý do:**
- Chỉ là redirect files, không có logic
- Không được reference trong code
- Đã được thay thế bởi Home.php và routing system

**Hành động:** ✅ **CÓ THỂ XÓA**

---

### 2. **search.php** (Legacy search page)
**File:** `app/View/extra/search.php`

**Lý do:**
- Có vẻ là legacy version của search_advanced.php
- Không được reference trong router (public/index.php)
- Chỉ được mention trong CSS comments

**Kiểm tra cần thiết:**
- Xem có link nào trong code trỏ đến file này không
- Nếu không có, có thể xóa

**Hành động:** ⚠️ **CẦN KIỂM TRA TRƯỚC KHI XÓA**

---

### 3. **products_index.php** (Duplicate entry point)
**File:** `app/View/product/products_index.php`

**Lý do:**
- Có vẻ là entry point cũ cho products listing
- Hiện tại đã có `products.php` và routing qua `FrontendProductController`
- Không được reference trong router

**Kiểm tra cần thiết:**
- Xem có direct access nào đến file này không
- Nếu không có, có thể xóa

**Hành động:** ⚠️ **CẦN KIỂM TRA TRƯỚC KHI XÓA**

---

## Các section đã bị comment out (có thể xóa code)

### 1. **"Xem tất cả" links trong Home.php**
**Lines:** 109-110, 181-182, 249-250

**Code đã comment:**
```php
<!-- <a href="<?php echo BASE_URL; ?>public/index.php?page=products" class="view-all">Xem tất cả <i
        class="fas fa-arrow-right"></i></a> -->
```

**Lý do:**
- Đã bị comment out
- Không còn hiển thị trên UI
- Có thể xóa code này để clean up

**Hành động:** ✅ **CÓ THỂ XÓA CODE COMMENT**

---

### 2. **"Đơn hàng gần đây" section trong Home.php**
**Lines:** 285-348

**Code đã comment:**
```php
<!-- <?php if (isset($_SESSION['user_id']) && !empty($recent_orders)): ?>
    <section class="section">
        <div class="section-header">
            <h2 class="section-title">Đơn hàng gần đây</h2>
            ...
        </div>
    </section>
<?php endif; ?> -->
```

**Lý do:**
- Toàn bộ section đã bị comment out
- Không còn hiển thị
- Có thể xóa để giảm code clutter

**Hành động:** ✅ **CÓ THỂ XÓA CODE COMMENT**

---

## Các file cần refactor (không xóa)

### Trang quan trọng (Priority 1):
1. ✅ `app/View/Home.php` - **ĐÃ REFACTOR**
2. ⏳ `app/View/product/Product_detail.php` - Trang chi tiết sản phẩm
3. ⏳ `app/View/user/ProfileUserView.php` - Trang profile user
4. ⏳ `app/View/checkout/index.php` - Trang checkout
5. ⏳ `app/View/cart/index.php` - Trang giỏ hàng

### Trang quan trọng (Priority 2):
6. ⏳ `app/View/product/products.php` - Danh sách sản phẩm
7. ⏳ `app/View/product/category.php` - Danh sách theo category
8. ⏳ `app/View/product/sell.php` - Trang đăng bán
9. ⏳ `app/View/order/order_history.php` - Lịch sử đơn hàng
10. ⏳ `app/View/extra/search_advanced.php` - Trang tìm kiếm

### Trang auth (Priority 3):
11. ⏳ `app/View/user/login.php`
12. ⏳ `app/View/user/register.php`
13. ⏳ `app/View/user/forgot_password.php`
14. ⏳ `app/View/user/reset_password.php`

---

## Kế hoạch hành động

### Phase 1: Cleanup (Ngay lập tức)
1. Xóa `app/View/TrangChu.php`
2. Xóa `public/TrangChu.php`
3. Xóa commented code trong Home.php (view-all links và recent orders section)

### Phase 2: Kiểm tra và xóa (Sau khi verify)
1. Kiểm tra `search.php` có được sử dụng không
2. Kiểm tra `products_index.php` có được sử dụng không
3. Nếu không, xóa chúng

### Phase 3: Refactor các trang quan trọng
1. Product_detail.php
2. ProfileUserView.php
3. checkout/index.php
4. cart/index.php

---

## Lưu ý

- **Backup trước khi xóa:** Tạo backup của các file trước khi xóa
- **Test sau khi xóa:** Đảm bảo không có broken links
- **Update documentation:** Cập nhật docs nếu có reference đến các file đã xóa
