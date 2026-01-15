# Hướng Dẫn Vẽ Activity Diagram - Dự Án Web Mua Bán Đồ Cũ

## 📊 Tổng Quan Database

Dự án **Web Mua Bán Đồ Cũ** sử dụng **27 bảng dữ liệu** trong hệ thống MySQL, được tổ chức theo các nhóm chức năng chính.

## 🗄️ Danh Sách Đầy Đủ Các Bảng (27 bảng)

### 1. **Nhóm Quản Trị (Admin) - 5 bảng**
- `admin_action_logs` - Ghi log các hành động của admin
- `admin_banner_images` - Quản lý banner quảng cáo
- `admin_theme_events` - Quản lý sự kiện theme
- `admin_theme_settings` - Cài đặt giao diện hệ thống
- `theme_presets` - Các preset theme có sẵn

### 2. **Nhóm Người Dùng (Users) - 3 bảng**
- `users` - Thông tin người dùng (user/admin)
- `user_logs` - Log hoạt động người dùng
- `remember_tokens` - Token đăng nhập lâu dài

### 3. **Nhóm Sản Phẩm (Products) - 4 bảng**
- `products` - Thông tin sản phẩm
- `product_images` - Hình ảnh sản phẩm
- `product_status_logs` - Log thay đổi trạng thái sản phẩm
- `categories` - Danh mục sản phẩm

### 4. **Nhóm Giỏ Hàng (Cart) - 2 bảng**
- `carts` - Giỏ hàng của người dùng
- `cart_items` - Chi tiết sản phẩm trong giỏ hàng

### 5. **Nhóm Đơn Hàng (Orders) - 2 bảng**
- `orders` - Thông tin đơn hàng
- `order_items` - Chi tiết sản phẩm trong đơn hàng

### 6. **Nhóm Thanh Toán & Bảo Mật - 2 bảng**
- `password_resets` - Token đặt lại mật khẩu
- `rate_limits` - Giới hạn tần suất thao tác

### 7. **Nhóm Thông Báo (Notifications) - 4 bảng**
- `notifications` - Thông báo cho người dùng
- `notification_queue` - Hàng đợi thông báo
- `notification_settings` - Cài đặt thông báo
- `notification_templates` - Template thông báo
- `system_notifications` - Thông báo hệ thống

### 8. **Nhóm Chat & Tin Nhắn - 2 bảng**
- `box_chat` - Hộp chat của người dùng
- `messages` - Tin nhắn trong chat

### 9. **Nhóm Đánh Giá & Khuyến Mãi - 2 bảng**
- `review_products` - Đánh giá sản phẩm
- `coupons` - Mã giảm giá

## ⚠️ Các Điểm Cần Lưu Ý Khi Vẽ Activity Diagram

### 1. **Quan Hệ Foreign Key (Ràng Buộc Tham Chiếu)**

Khi vẽ Activity Diagram, cần chú ý các quan hệ ràng buộc giữa các bảng:

#### **Quan Hệ 1-Nhiều (One-to-Many)**
- `users` → `products` (1 user có nhiều sản phẩm)
- `users` → `orders` (1 user có nhiều đơn hàng)
- `users` → `carts` (1 user có 1 giỏ hàng)
- `products` → `product_images` (1 sản phẩm có nhiều ảnh)
- `products` → `cart_items` (1 sản phẩm có thể trong nhiều giỏ hàng)
- `products` → `order_items` (1 sản phẩm có thể trong nhiều đơn hàng)
- `orders` → `order_items` (1 đơn hàng có nhiều sản phẩm)
- `carts` → `cart_items` (1 giỏ hàng có nhiều sản phẩm)
- `categories` → `products` (1 danh mục có nhiều sản phẩm)
- `box_chat` → `messages` (1 hộp chat có nhiều tin nhắn)
- `users` → `box_chat` (1 user có 1 hộp chat)
- `users` → `notifications` (1 user có nhiều thông báo)
- `users` → `review_products` (1 user có nhiều đánh giá)
- `products` → `review_products` (1 sản phẩm có nhiều đánh giá)

#### **Quan Hệ 1-1 (One-to-One)**
- `users` → `carts` (1 user = 1 giỏ hàng, UNIQUE constraint)
- `users` → `remember_tokens` (1 user = 1 token nhớ, UNIQUE constraint)

#### **Quan Hệ Nhiều-Nhiều (Many-to-Many)**
- `users` ↔ `products` (qua `review_products` với UNIQUE constraint `ux_user_product`)
- `users` ↔ `products` (qua `cart_items` và `order_items`)

### 2. **Cascade Actions (Hành Động Dây Chuyền)**

Khi xóa hoặc cập nhật dữ liệu, cần lưu ý các hành động cascade:

#### **ON DELETE CASCADE** (Xóa dây chuyền)
- Xóa `user` → Tự động xóa `products`, `carts`, `cart_items`, `box_chat`, `messages`, `notifications`, `remember_tokens`, `review_products`
- Xóa `product` → Tự động xóa `product_images`, `cart_items`, `product_status_logs`
- Xóa `order` → Tự động xóa `order_items`
- Xóa `cart` → Tự động xóa `cart_items`
- Xóa `box_chat` → Tự động xóa `messages`

#### **ON DELETE SET NULL** (Đặt NULL khi xóa)
- Xóa `product` → `order_items.product_id` = NULL (giữ lại lịch sử đơn hàng)
- Xóa `user` → `user_logs.user_id` = NULL (giữ lại log)
- Xóa `user` → `product_status_logs.user_id` = NULL (giữ lại log)

#### **ON DELETE RESTRICT** (Ngăn chặn xóa)
- Không thể xóa `user` nếu có `orders` đang tham chiếu (bảo vệ dữ liệu đơn hàng)

### 3. **Trạng Thái (Status) và Enum Values**

Các bảng có trường status/enum cần được xử lý đúng trong Activity Diagram:

#### **products.status**
- `pending` - Chờ duyệt
- `active` - Đã duyệt, đang bán
- `reject` - Bị từ chối
- `sold` - Đã bán

#### **products.condition_status**
- `new` - Mới
- `like_new` - Như mới
- `good` - Tốt
- `fair` - Khá
- `poor` - Kém

#### **orders.status**
- `pending` - Chờ xử lý
- `success` - Thành công
- `failed` - Thất bại
- `cancelled` - Đã hủy

#### **orders.payment_status**
- `pending` - Chờ thanh toán
- `paid` - Đã thanh toán
- `failed` - Thanh toán thất bại

#### **orders.payment_method**
- `vnpay` - Thanh toán VNPay
- `bank_transfer` - Chuyển khoản
- `cod` - Thanh toán khi nhận hàng

#### **users.role**
- `user` - Người dùng thường
- `admin` - Quản trị viên

#### **users.status**
- `active` - Hoạt động
- `inactive` - Không hoạt động

#### **cart_items.status**
- `active` - Đang hoạt động
- `sold` - Đã bán (ẩn khỏi giỏ hàng)

### 4. **Các Luồng Nghiệp Vụ Chính Cần Vẽ Activity Diagram**

#### **A. Luồng Đăng Ký & Đăng Nhập**
1. Đăng ký tài khoản → Tạo `users`
2. Xác thực email → Cập nhật `users.email_verified_at`
3. Đăng nhập → Tạo session, cập nhật `users.last_login`
4. Quên mật khẩu → Tạo `password_resets`, gửi email
5. Đặt lại mật khẩu → Xóa `password_resets`, cập nhật `users.password`

#### **B. Luồng Đăng Bán Sản Phẩm**
1. Người dùng đăng sản phẩm → Tạo `products` (status = `pending`)
2. Upload ảnh → Tạo `product_images`
3. Admin duyệt → Cập nhật `products.status` = `active`, tạo `admin_action_logs`, tạo `notifications`
4. Admin từ chối → Cập nhật `products.status` = `reject`, tạo `notifications`
5. Admin xóa → Xóa `products`, tạo `admin_action_logs`, tạo `notifications`

#### **C. Luồng Mua Hàng**
1. Xem sản phẩm → Tăng `products.views`
2. Thêm vào giỏ hàng → Tạo/cập nhật `carts` và `cart_items`
3. Cập nhật giỏ hàng → Cập nhật `cart_items.quantity`
4. Xóa khỏi giỏ hàng → Xóa `cart_items`
5. Thanh toán → Tạo `orders` và `order_items`, cập nhật `cart_items.status` = `sold`
6. Thanh toán VNPay → Redirect, xử lý callback, cập nhật `orders.payment_status`
7. Sau khi thanh toán thành công → Cập nhật `products.status` = `sold`, `products.stock_quantity` = 0

#### **D. Luồng Quản Trị**
1. Admin duyệt sản phẩm → Cập nhật `products`, tạo `admin_action_logs`, tạo `notifications`
2. Admin xóa sản phẩm → Xóa `products`, tạo `admin_action_logs`
3. Admin quản lý người dùng → Cập nhật `users.status`
4. Admin xem thống kê → Query từ `orders`, `products`, `users`

#### **E. Luồng Chat**
1. Người dùng mở chat → Tạo `box_chat` (nếu chưa có)
2. Gửi tin nhắn → Tạo `messages`
3. Đánh dấu đã đọc → Cập nhật `box_chat.is_read`

#### **F. Luồng Đánh Giá**
1. Người dùng đánh giá sản phẩm → Tạo `review_products` (UNIQUE constraint `ux_user_product`)
2. Cập nhật rating → Cập nhật `review_products.rating`

#### **G. Luồng Thông Báo**
1. Hệ thống tạo thông báo → Tạo `notifications`
2. Đánh dấu đã đọc → Cập nhật `notifications.is_read`
3. Thông báo giỏ hàng bỏ quên → Tạo `notification_queue`, xử lý cron, tạo `notifications`

### 5. **Các Điểm Kỹ Thuật Quan Trọng**

#### **A. Transaction & Data Integrity**
- Khi tạo đơn hàng: Phải dùng TRANSACTION để đảm bảo tạo `orders` và `order_items` cùng lúc
- Khi thanh toán: Cập nhật `orders.payment_status` và `products.status` trong cùng transaction
- Khi xóa sản phẩm: Kiểm tra xem có trong `cart_items` hoặc `order_items` không

#### **B. Unique Constraints**
- `users.username` - UNIQUE
- `users.email` - UNIQUE
- `products.slug` - UNIQUE
- `categories.slug` - UNIQUE
- `orders.order_number` - UNIQUE
- `coupons.code` - UNIQUE
- `carts.user_id` - UNIQUE (1 user = 1 cart)
- `carts.session_id` - UNIQUE (1 session = 1 cart)
- `cart_items.cart_id + product_id` - UNIQUE (không trùng sản phẩm trong giỏ)
- `review_products.user_id + product_id` - UNIQUE (1 user chỉ đánh giá 1 lần/sản phẩm)
- `remember_tokens.user_id` - UNIQUE

#### **C. Indexes (Chỉ Mục)**
Các bảng có indexes để tối ưu query, cần lưu ý khi vẽ luồng:
- `products`: `fk_products_user`, `fk_products_category`
- `orders`: `fk_orders_buyer`
- `cart_items`: `ux_cart_product`, `fk_cart_items_product`, `idx_cart_items_status_hidden`
- `order_items`: `fk_order_items_order`, `idx_order_items_product_id`
- `notifications`: `fk_notifications_user`
- `admin_action_logs`: `idx_admin`, `idx_product`, `idx_action_created`

#### **D. JSON Fields**
- `products.images` - Lưu JSON (nhưng thực tế dùng bảng `product_images`)
- `admin_theme_events.theme_config` - JSON
- `notification_queue.data` - JSON

#### **E. Timestamps**
Hầu hết bảng có:
- `created_at` - Thời gian tạo
- `updated_at` - Thời gian cập nhật (auto-update)

### 6. **Các Tình Huống Edge Case Cần Xử Lý**

#### **A. Sản Phẩm Đã Bán**
- Khi sản phẩm đã bán (`products.status` = `sold`), không thể thêm vào giỏ hàng
- `cart_items` có sản phẩm đã bán sẽ có `status` = `sold` và `is_hidden` = 1
- `order_items.product_id` có thể NULL nếu admin xóa sản phẩm (giữ lại lịch sử)

#### **B. Giỏ Hàng Guest vs User**
- User chưa đăng nhập: Dùng `carts.session_id`
- User đã đăng nhập: Dùng `carts.user_id`
- Khi user đăng nhập: Merge giỏ hàng session vào giỏ hàng user

#### **C. Rate Limiting**
- Bảng `rate_limits` theo dõi tần suất thao tác (ví dụ: đặt lại mật khẩu)
- Cần kiểm tra trước khi cho phép thao tác

#### **D. Product Status Flow**
```
pending → active (admin duyệt)
pending → reject (admin từ chối)
active → sold (đã bán)
active → pending (admin có thể đưa về chờ duyệt lại)
```

#### **E. Order Status Flow**
```
pending → success (thanh toán thành công)
pending → failed (thanh toán thất bại)
pending → cancelled (hủy đơn)
```

### 7. **Các Bảng Phụ Trợ (Supporting Tables)**

Các bảng này không trực tiếp tham gia luồng nghiệp vụ chính nhưng quan trọng:

- `user_logs` - Audit trail cho hoạt động user
- `admin_action_logs` - Audit trail cho hành động admin
- `product_status_logs` - Lịch sử thay đổi trạng thái sản phẩm
- `rate_limits` - Bảo mật, chống spam
- `password_resets` - Bảo mật, đặt lại mật khẩu
- `remember_tokens` - Trải nghiệm người dùng, đăng nhập lâu dài
- `notification_queue` - Hệ thống thông báo tự động
- `notification_settings` - Cấu hình thông báo
- `notification_templates` - Template thông báo
- `system_notifications` - Thông báo hệ thống công khai
- `admin_banner_images` - Quản lý banner
- `admin_theme_events` - Quản lý sự kiện theme
- `admin_theme_settings` - Cài đặt giao diện
- `theme_presets` - Preset theme
- `coupons` - Mã giảm giá

## 📝 Checklist Khi Vẽ Activity Diagram

### ✅ Trước Khi Vẽ
- [ ] Xác định actor (User, Admin, System)
- [ ] Xác định use case cụ thể
- [ ] Liệt kê các bảng liên quan
- [ ] Xác định các điều kiện (guards/conditions)
- [ ] Xác định các exception/error cases

### ✅ Khi Vẽ
- [ ] Bắt đầu với Initial Node (●)
- [ ] Kết thúc với Final Node (◉)
- [ ] Sử dụng Decision Node (◇) cho điều kiện
- [ ] Sử dụng Merge Node (◇) để gộp luồng
- [ ] Sử dụng Fork/Join cho parallel activities
- [ ] Ghi rõ tên activity (hành động)
- [ ] Ghi rõ điều kiện trên decision edges
- [ ] Ghi rõ bảng dữ liệu được thao tác

### ✅ Sau Khi Vẽ
- [ ] Kiểm tra tính logic của luồng
- [ ] Kiểm tra các trường hợp ngoại lệ
- [ ] Kiểm tra foreign key constraints
- [ ] Kiểm tra cascade actions
- [ ] Kiểm tra unique constraints
- [ ] Kiểm tra enum/status values
- [ ] Kiểm tra transaction boundaries

## 🎯 Ví Dụ Activity Diagram: Luồng Mua Hàng

```
[Start] → [User xem sản phẩm] → [Tăng products.views]
    ↓
[User thêm vào giỏ hàng]
    ↓
{User đã đăng nhập?}
    ├─ Yes → [Tìm carts theo user_id]
    └─ No → [Tìm carts theo session_id]
    ↓
{Cart đã tồn tại?}
    ├─ No → [Tạo carts mới]
    └─ Yes → [Sử dụng cart hiện có]
    ↓
{Sản phẩm đã có trong giỏ?}
    ├─ Yes → [Cập nhật cart_items.quantity]
    └─ No → [Tạo cart_items mới]
    ↓
[User vào trang giỏ hàng]
    ↓
[User click thanh toán]
    ↓
[Hiển thị form thông tin giao hàng]
    ↓
[User nhập thông tin và submit]
    ↓
[Kiểm tra stock_quantity]
    ├─ Hết hàng → [Thông báo lỗi] → [End]
    └─ Còn hàng → [Tiếp tục]
    ↓
[Bắt đầu TRANSACTION]
    ↓
[Tạo orders với status='pending']
    ↓
[Với mỗi sản phẩm trong giỏ]
    ├─ [Tạo order_items]
    ├─ [Cập nhật cart_items.status='sold']
    └─ [Cập nhật cart_items.is_hidden=1]
    ↓
[COMMIT TRANSACTION]
    ↓
[Redirect đến VNPay]
    ↓
[User thanh toán trên VNPay]
    ↓
[VNPay callback]
    ↓
[Kiểm tra hash validation]
    ├─ Invalid → [Cập nhật orders.status='failed'] → [End]
    └─ Valid → [Tiếp tục]
    ↓
[Kiểm tra payment_status từ VNPay]
    ├─ Success → [Cập nhật orders.payment_status='paid', orders.status='success']
    │              [Cập nhật products.status='sold', products.stock_quantity=0]
    │              [Tạo notifications cho seller]
    └─ Failed → [Cập nhật orders.payment_status='failed', orders.status='failed']
    ↓
[Redirect về website]
    ↓
[Hiển thị kết quả thanh toán]
    ↓
[End]
```

## 📚 Tài Liệu Tham Khảo

- File SQL schema: `data/muabandocu.sql`
- System Architecture: `docs/SYSTEM_ARCHITECTURE_DIAGRAMS.md`
- README: `README.md`

---

**Lưu ý cuối cùng**: Khi vẽ Activity Diagram, luôn đảm bảo tuân thủ các ràng buộc database và xử lý đầy đủ các trường hợp ngoại lệ để diagram phản ánh chính xác luồng nghiệp vụ thực tế của hệ thống.
