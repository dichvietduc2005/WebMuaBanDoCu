<?php
/**
 * EmailService - Dịch vụ gửi email
 */

class EmailService 
{
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    
    public function __construct() 
    {
        // Load cấu hình từ environment hoặc config
        $this->smtp_host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->smtp_port = $_ENV['SMTP_PORT'] ?? 587;
        $this->smtp_username = $_ENV['SMTP_USERNAME'] ?? '';
        $this->smtp_password = $_ENV['SMTP_PASSWORD'] ?? '';
        $this->from_email = $this->smtp_username ?: 'noreply@muabandocu.com';
        $this->from_name = 'Cửa Hàng Đồ Cũ';
    }
    
    /**
     * Gửi email reset password
     */
    public function sendPasswordResetEmail($to_email, $user_name, $reset_token) 
    {
        $reset_link = BASE_URL . "app/View/user/reset_password.php?token=" . $reset_token;
        
        $subject = 'Đặt lại mật khẩu - Cửa Hàng Đồ Cũ';
        
        $html_body = $this->getPasswordResetTemplate($user_name, $reset_link);
        $text_body = $this->getPasswordResetTextTemplate($user_name, $reset_link);
        
        return $this->sendEmail($to_email, $subject, $html_body, $text_body);
    }
    
    /**
     * Gửi email đơn giản
     */
    public function sendEmail($to_email, $subject, $html_body, $text_body = null) 
    {
        try {
            // Sử dụng PHPMailer nếu có, hoặc fallback về mail() function
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendWithPHPMailer($to_email, $subject, $html_body, $text_body);
            } else {
                return $this->sendWithMailFunction($to_email, $subject, $html_body, $text_body);
            }
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gửi email bằng PHPMailer (nếu có)
     */
    private function sendWithPHPMailer($to_email, $subject, $html_body, $text_body = null) 
    {
        // TODO: Implement PHPMailer integration
        // Tạm thời log để test
        error_log("PHPMailer not available, using fallback method");
        return $this->sendWithMailFunction($to_email, $subject, $html_body, $text_body);
    }
    
    /**
     * Gửi email bằng mail() function của PHP
     */
    private function sendWithMailFunction($to_email, $subject, $html_body, $text_body = null) 
    {
        // Headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headers_string = implode("\r\n", $headers);
        
        // Trong môi trường development, chỉ log email thay vì gửi thật
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log("=== EMAIL LOG (Development Mode) ===");
            error_log("To: $to_email");
            error_log("Subject: $subject");
            error_log("Body: $html_body");
            error_log("=====================================");
            return true;
        }
        
        // Gửi email thật
        return mail($to_email, $subject, $html_body, $headers_string);
    }
    
    /**
     * Template HTML cho email reset password
     */
    private function getPasswordResetTemplate($user_name, $reset_link) 
    {
        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Đặt lại mật khẩu</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Đặt lại mật khẩu</h1>
                    <p>Cửa Hàng Đồ Cũ</p>
                </div>
                <div class='content'>
                    <h2>Xin chào " . htmlspecialchars($user_name) . ",</h2>
                    <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                    <p>Nhấn vào nút bên dưới để đặt lại mật khẩu:</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . htmlspecialchars($reset_link) . "' class='button'>Đặt lại mật khẩu</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Lưu ý quan trọng:</strong>
                        <ul>
                            <li>Link này chỉ có hiệu lực trong <strong>1 giờ</strong></li>
                            <li>Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này</li>
                            <li>Không chia sẻ link này với bất kỳ ai</li>
                        </ul>
                    </div>
                    
                    <p>Nếu nút không hoạt động, bạn có thể copy và paste link sau vào trình duyệt:</p>
                    <p style='word-break: break-all; background: #f1f1f1; padding: 10px; border-radius: 5px;'>
                        " . htmlspecialchars($reset_link) . "
                    </p>
                    
                    <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
                    <p><strong>Đội ngũ Cửa Hàng Đồ Cũ</strong></p>
                </div>
                <div class='footer'>
                    <p>Email này được gửi tự động, vui lòng không reply.</p>
                    <p>&copy; " . date('Y') . " Cửa Hàng Đồ Cũ. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Template text cho email reset password
     */
    private function getPasswordResetTextTemplate($user_name, $reset_link) 
    {
        return "
Xin chào " . $user_name . ",

Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.

Vui lòng truy cập link sau để đặt lại mật khẩu:
" . $reset_link . "

LƯU Ý QUAN TRỌNG:
- Link này chỉ có hiệu lực trong 1 giờ
- Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này
- Không chia sẻ link này với bất kỳ ai

Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!

Đội ngũ Cửa Hàng Đồ Cũ
        ";
    }
    
    /**
     * Gửi email thông báo đăng ký thành công
     */
    public function sendWelcomeEmail($to_email, $user_name) 
    {
        $subject = 'Chào mừng đến với Cửa Hàng Đồ Cũ!';
        
        $html_body = "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Chào mừng!</h1>
                    <p>Cửa Hàng Đồ Cũ</p>
                </div>
                <div class='content'>
                    <h2>Xin chào " . htmlspecialchars($user_name) . ",</h2>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại Cửa Hàng Đồ Cũ!</p>
                    <p>Bạn có thể bắt đầu mua bán các sản phẩm đồ cũ chất lượng ngay bây giờ.</p>
                    <p>Chúc bạn có những trải nghiệm tuyệt vời!</p>
                    <p><strong>Đội ngũ Cửa Hàng Đồ Cũ</strong></p>
                </div>
            </div>
        </body>
        </html>";
        
        return $this->sendEmail($to_email, $subject, $html_body);
    }
}
?> 