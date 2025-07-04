# Hướng dẫn quản lý sản phẩm nổi bật

## Cách để sản phẩm trở thành sản phẩm nổi bật

### 1. **Qua Admin Panel (Quản trị viên)**

#### Bước 1: Đăng nhập với tài khoản Admin
- Truy cập trang đăng nhập admin
- Đăng nhập với tài khoản có role `admin`

#### Bước 2: Quản lý sản phẩm chờ duyệt
- Truy cập: `/app/View/admin/products.php`
- Duyệt sản phẩm từ trạng thái `pending` → `active`
- Sau khi duyệt, sản phẩm sẽ hiển thị công khai

#### Bước 3: Đặt sản phẩm làm nổi bật
- Truy cập: `/app/View/admin/manage_products.php` (trang mới được tạo)
- Tìm sản phẩm muốn đặt nổi bật
- Click nút **"Đặt nổi bật"** (⭐)
- Sản phẩm sẽ được đánh dấu `featured = 1`

### 2. **Cách hoạt động trong code**

#### Database Structure:
```sql
-- Cột featured trong bảng products
featured TINYINT(1) DEFAULT 0
-- 0 = sản phẩm thường
-- 1 = sản phẩm nổi bật
```

#### Query hiển thị sản phẩm nổi bật:
```sql
SELECT p.*, pi.image_path, c.name as category_name 
FROM products p 
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active' AND p.featured = 1 AND p.stock_quantity > 0
ORDER BY p.created_at DESC 
LIMIT 12
```

### 3. **Các trang hiển thị sản phẩm nổi bật**

1. **Trang chủ (`/app/View/Home.php`)**:
   - Section "Sản phẩm nổi bật" 
   - Hiển thị tối đa 12 sản phẩm
   - Có badge "Nổi bật" trên ảnh sản phẩm

2. **Trang danh sách sản phẩm**:
   - Sản phẩm nổi bật được ưu tiên hiển thị trước

### 4. **Quy trình đầy đủ**

```
User đăng sản phẩm 
→ Trạng thái: pending
→ Admin duyệt 
→ Trạng thái: active
→ Admin đặt featured
→ featured = 1
→ Hiển thị trong "Sản phẩm nổi bật"
```

### 5. **Tính năng mới được thêm**

1. **Toggle Featured Status**: 
   - Admin có thể bật/tắt trạng thái nổi bật
   - Validation chỉ cho phép sản phẩm `active`

2. **Trang quản lý mới**: `/app/View/admin/manage_products.php`
   - Hiển thị tất cả sản phẩm active
   - Sản phẩm nổi bật có highlight đặc biệt
   - Nút toggle featured cho từng sản phẩm

3. **Visual indicators**:
   - Badge "Nổi bật" trên sản phẩm
   - Highlight màu vàng cho dòng sản phẩm nổi bật
   - Icon star khác nhau cho trạng thái on/off

### 6. **Cách sử dụng**

1. **Admin muốn đặt sản phẩm nổi bật**:
   ```
   Truy cập: /app/View/admin/manage_products.php
   → Click "Đặt nổi bật" 
   → Sản phẩm xuất hiện trong section "Sản phẩm nổi bật"
   ```

2. **Admin muốn bỏ sản phẩm khỏi nổi bật**:
   ```
   Truy cập: /app/View/admin/manage_products.php
   → Click "Bỏ nổi bật"
   → Sản phẩm chỉ hiển thị trong section sản phẩm thường
   ```

### 7. **URL truy cập**

- **Quản lý sản phẩm chờ duyệt**: `http://localhost/WebMuaBanDoCu/app/View/admin/products.php`
- **Quản lý tất cả sản phẩm**: `http://localhost/WebMuaBanDoCu/app/View/admin/manage_products.php`

Bây giờ admin có thể dễ dàng quản lý sản phẩm nổi bật thông qua giao diện web!
