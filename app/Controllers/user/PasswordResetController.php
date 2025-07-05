<?php
/**
 * PasswordResetController - Xử lý reset mật khẩu
 */

require_once(__DIR__ . '/../../Models/user/Auth.php');

class PasswordResetController 
{
    private $auth;
    
    public function __construct($pdo) 
    {
        $this->auth = new Auth($pdo);
    }
    
    /**
     * Xử lý yêu cầu reset mật khẩu
     */
    public function requestReset($data) 
    {
        // Validate input
        $email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        
        if (empty($email)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập email.'
            ];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Email không hợp lệ.'
            ];
        }
        
        // Rate limiting - chỉ cho phép 3 yêu cầu reset mỗi giờ
        if (!$this->checkRateLimit('password_reset', $email)) {
            return [
                'success' => false,
                'message' => 'Bạn đã yêu cầu reset mật khẩu quá nhiều lần. Vui lòng thử lại sau 1 giờ.'
            ];
        }
        
        return $this->auth->requestPasswordReset($email);
    }
    
    /**
     * Xử lý reset mật khẩu với token
     */
    public function resetPassword($data) 
    {
        $token = trim($data['token'] ?? '');
        $new_password = $data['new_password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';
        
        // Validate input
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token không hợp lệ.'
            ];
        }
        
        if (empty($new_password)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập mật khẩu mới.'
            ];
        }
        
        if ($new_password !== $confirm_password) {
            return [
                'success' => false,
                'message' => 'Mật khẩu xác nhận không khớp.'
            ];
        }
        
        if (strlen($new_password) < 6) {
            return [
                'success' => false,
                'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'
            ];
        }
        
        // Validate password strength
        if (!$this->validatePasswordStrength($new_password)) {
            return [
                'success' => false,
                'message' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường và 1 số.'
            ];
        }
        
        return $this->auth->resetPassword($token, $new_password);
    }
    
    /**
     * Kiểm tra token có hợp lệ không
     */
    public function validateToken($token) 
    {
        if (empty($token)) {
            return ['success' => false, 'message' => 'Token không hợp lệ.'];
        }
        
        return $this->auth->validateResetToken($token);
    }
    
    /**
     * Rate limiting cho password reset
     */
    private function checkRateLimit($action, $identifier) 
    {
        try {
            global $pdo;
            
            // Kiểm tra số lần yêu cầu trong 1 giờ qua
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM rate_limits 
                WHERE action = ? AND identifier = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$action, $identifier]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] >= 3) {
                return false;
            }
            
            // Ghi log yêu cầu
            $stmt = $pdo->prepare("
                INSERT INTO rate_limits (action, identifier, ip_address, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$action, $identifier, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return true; // Cho phép nếu có lỗi
        }
    }
    
    /**
     * Validate password strength
     */
    private function validatePasswordStrength($password) 
    {
        return strlen($password) >= 6 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
}
?> 