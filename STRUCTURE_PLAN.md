# CẤU TRÚC THỦ MỤC ĐỀ XUẤT CHO WEB MUA BÁN ĐỒ CŨ

## 📁 Cấu trúc thư mục mới:

```
Web_MuaBanDoCu/
├── assets/                     # Tài nguyên tĩnh
│   ├── css/                    # File CSS
│   │   ├── bootstrap.min.css
│   │   ├── jumbotron-narrow.css
│   │   └── style/
│   │       └── index.css
│   ├── js/                     # File JavaScript
│   │   └── jquery-1.11.3.min.js
│   ├── images/                 # Hình ảnh
│   │   └── default_product_image.svg
│   └── uploads/                # File upload từ user
│
├── config/                     # File cấu hình
│   └── config.php             # Cấu hình database, session
│
├── includes/                   # File include/require chung
│   ├── header.php             # Header chung
│   ├── footer.php             # Footer chung
│   └── functions.php          # Các hàm tiện ích chung
│
├── modules/                    # Các module chính
│   ├── user/                   # Quản lý user
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── profile.php
│   │   └── functions.php
│   │
│   ├── product/                # Quản lý sản phẩm
│   │   ├── list.php
│   │   ├── detail.php
│   │   ├── add.php
│   │   └── functions.php
│   │
│   ├── cart/                   # Giỏ hàng
│   │   ├── view.php
│   │   ├── handler.php
│   │   ├── functions.php
│   │   └── checkout.php
│   │
│   └── payment/                # Thanh toán
│       ├── vnpay/              # VNPAY gateway
│       │   ├── create_payment.php
│       │   ├── return.php
│       │   ├── ipn.php
│       │   ├── query.php
│       │   └── refund.php
│       ├── history.php         # Lịch sử thanh toán
│       └── functions.php
│
├── admin/                      # Quản trị
│   ├── dashboard.php
│   ├── products/
│   ├── users/
│   └── orders/
│
├── data/                       # Scripts database
│   ├── database.sql           # Script tạo database
│   ├── sample_data.sql        # Dữ liệu mẫu
│   └── migrations/            # Scripts migration
│
├── uploads/                    # File upload
│   ├── products/              # Hình ảnh sản phẩm
│   └── users/                 # Avatar user
│
├── logs/                       # File log
│   ├── error.log
│   └── payment.log
│
└── public/                     # File public
    ├── index.php              # Trang chủ
    ├── about.php
    └── contact.php
```

## 🎯 Ưu điểm của cấu trúc mới:

1. **Phân loại rõ ràng**: Mỗi module có thư mục riêng
2. **Dễ bảo trì**: Tìm file nhanh theo chức năng  
3. **Scalable**: Dễ mở rộng thêm tính năng
4. **Security**: Tách biệt file public và private
5. **MVC pattern**: Tách view, controller, model

## 📝 Kế hoạch migration:

### Phase 1: Tạo cấu trúc mới
- Tạo các thư mục mới
- Di chuyển file theo từng module

### Phase 2: Cập nhật đường dẫn
- Sửa include/require paths
- Cập nhật URL trong form action

### Phase 3: Testing
- Test từng chức năng
- Đảm bảo không có link bị broken
