# Hướng dẫn sử dụng tính năng Đặt lại Mật khẩu

## 📋 Tổng quan

Tính năng đặt lại mật khẩu cho phép người dùng khôi phục tài khoản khi quên mật khẩu thông qua email xác thực.

## 🔧 Các thành phần đã triển khai

### 1. **Backend Logic**
- **`app/Models/user/Auth.php`**: Xử lý logic reset password
- **`app/Controllers/user/PasswordResetController.php`**: Controller xử lý requests
- **`app/Models/EmailService.php`**: Service gửi email

### 2. **Frontend Pages**
- **`app/View/user/forgot_password.php`**: Trang yêu cầu reset mật khẩu
- **`app/View/user/reset_password.php`**: Trang đặt lại mật khẩu với token

### 3. **Database Schema**
- **`password_resets`**: Bảng lưu trữ token reset password
- **`rate_limits`**: Bảng kiểm soát tần suất yêu cầu

### 4. **Styling**
- **`public/assets/css/auth.css`**: CSS chung cho các trang authentication

## 🚀 Cách sử dụng

### Bước 1: Yêu cầu đặt lại mật khẩu
1. Truy cập trang đăng nhập
2. Nhấn link "Quên mật khẩu?"
3. Nhập email đã đăng ký
4. Nhấn "Gửi yêu cầu đặt lại mật khẩu"

### Bước 2: Kiểm tra email
1. Mở email nhận được (kiểm tra cả thư mục spam)
2. Nhấn nút "Đặt lại mật khẩu" trong email
3. Hoặc copy/paste link vào trình duyệt

### Bước 3: Đặt mật khẩu mới
1. Nhập mật khẩu mới (tối thiểu 6 ký tự, có chữ hoa, chữ thường, số)
2. Xác nhận mật khẩu
3. Nhấn "Đặt lại mật khẩu"
4. Đăng nhập với mật khẩu mới

## 🔒 Tính năng bảo mật

### Rate Limiting
- Tối đa 3 yêu cầu reset password mỗi giờ per email
- Ngăn chặn spam và tấn công brute force

### Token Security
- Token có thời hạn 1 giờ
- Token được hash và lưu trữ an toàn
- Token tự động xóa sau khi sử dụng
- Xóa tất cả remember tokens khi reset password

### Password Validation
- Tối thiểu 6 ký tự
- Phải có ít nhất 1 chữ hoa
- Phải có ít nhất 1 chữ thường  
- Phải có ít nhất 1 số

### Activity Logging
- Ghi log tất cả hoạt động reset password
- Theo dõi IP address và user agent
- Hỗ trợ audit và troubleshooting

## 📧 Cấu hình Email

### Environment Variables (config/env.example)
```env
# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
```

### Development Mode
- Email được log vào error log thay vì gửi thật
- Link reset password hiển thị trực tiếp trên trang để test

### Production Mode
- Email được gửi qua SMTP
- Cần cấu hình SMTP server hợp lệ

## 🎨 Giao diện

### Design Features
- Responsive design cho mọi thiết bị
- Gradient background hiện đại
- Form validation real-time
- Password strength indicator
- Loading states và feedback messages
- Dark mode và high contrast support

### User Experience
- Clear error messages
- Progressive form validation
- Password visibility toggle
- Breadcrumb navigation
- Success confirmations

## 🔧 Cấu hình Database

### Bảng password_resets
```sql
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_email (email),
    KEY idx_token (token),
    KEY idx_expires_at (expires_at)
);
```

### Bảng rate_limits
```sql
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_action_identifier (action, identifier),
    KEY idx_created_at (created_at)
);
```

## 🔍 Testing

### Manual Testing
1. **Forgot Password Flow**:
   - Test với email hợp lệ
   - Test với email không tồn tại
   - Test rate limiting (3 requests/hour)

2. **Reset Password Flow**:
   - Test với token hợp lệ
   - Test với token đã hết hạn
   - Test với token không tồn tại
   - Test password validation

3. **Email Testing**:
   - Kiểm tra email template
   - Test link trong email
   - Verify email headers

### Security Testing
1. **Token Security**:
   - Verify token expiration (1 hour)
   - Test token uniqueness
   - Test token cleanup

2. **Rate Limiting**:
   - Test 3 requests per hour limit
   - Test different IP addresses
   - Test different email addresses

3. **Input Validation**:
   - Test XSS prevention
   - Test SQL injection prevention
   - Test CSRF protection

## 🐛 Troubleshooting

### Common Issues

#### Email không được gửi
- Kiểm tra cấu hình SMTP
- Verify credentials
- Check firewall/network restrictions
- Review error logs

#### Token không hợp lệ
- Kiểm tra token đã hết hạn chưa
- Verify token format
- Check database connection
- Review token generation logic

#### Rate limiting quá strict
- Adjust limits in PasswordResetController
- Clear rate_limits table
- Check IP detection logic

### Debug Mode
```php
// Trong development, enable debug logging
error_log("Password reset debug info: " . print_r($data, true));
```

## 📊 Monitoring

### Metrics to Track
- Number of password reset requests
- Success rate of password resets
- Email delivery rates
- Failed attempts and errors
- Rate limiting triggers

### Log Analysis
```bash
# Check password reset logs
grep "password_reset" /path/to/error.log

# Monitor rate limiting
grep "rate_limit" /path/to/error.log

# Email delivery status
grep "EMAIL LOG" /path/to/error.log
```

## 🔄 Future Enhancements

### Planned Features
1. **SMS Reset Option**: Alternative to email
2. **Security Questions**: Additional verification
3. **Account Lockout**: After multiple failed attempts
4. **Password History**: Prevent reusing recent passwords
5. **Multi-factor Authentication**: Enhanced security
6. **Admin Dashboard**: Monitor reset activities

### Performance Optimizations
1. **Token Cleanup Job**: Automated cleanup of expired tokens
2. **Email Queue**: Asynchronous email sending
3. **Redis Cache**: Rate limiting with Redis
4. **Database Indexing**: Optimize query performance

## 📞 Support

### Contact Information
- **Developer**: Team HIHand Shop
- **Email**: support@muabandocu.com
- **Documentation**: README.md

### Reporting Issues
1. Describe the issue clearly
2. Include steps to reproduce
3. Provide error logs
4. Specify browser/device information

---

**Lưu ý**: Tính năng này đã được test kỹ lưỡng và tuân thủ các best practices về bảo mật. Tuy nhiên, trong môi trường production, nên review và audit thêm để đảm bảo an toàn tuyệt đối. 