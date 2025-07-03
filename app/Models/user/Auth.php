<?php
require_once(__DIR__ . '/../../../config/config.php');

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    
    /**
     * Đăng ký tài khoản mới
     */
    public function register($username, $email, $password, $full_name, $phone = null) {
        try {
            // Kiểm tra email và username đã tồn tại
            if ($this->isEmailExists($email)) {
                return ['success' => false, 'message' => 'Email đã được đăng ký.'];
            }
            
            if ($this->isUsernameExists($username)) {
                return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng.'];
            }
            
            // Validate dữ liệu
            $validation = $this->validateUserData($username, $email, $password, $full_name);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Tạo tài khoản
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, full_name, phone, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            $result = $stmt->execute([$username, $email, $hashed_password, $full_name, $phone]);
            
            if ($result) {
                $user_id = $this->pdo->lastInsertId();
                $this->logActivity($user_id, 'register', 'User registered successfully');
                
                return [
                    'success' => true, 
                    'message' => 'Đăng ký thành công!',
                    'user_id' => $user_id
                ];
            } else {
                return ['success' => false, 'message' => 'Có lỗi xảy ra khi tạo tài khoản.'];
            }
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi hệ thống. Vui lòng thử lại sau.'];
        }
    }
    
    /**
     * Đăng nhập
     */
    public function login($email, $password, $remember_me = false) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *
                FROM users WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->logActivity(null, 'login_failed', "Failed login attempt for email: $email");
                return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
            }
            
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Tài khoản của bạn đã bị vô hiệu hóa.'];
            }
            
            if (!password_verify($password, $user['password'])) {
                $this->logActivity($user['id'], 'login_failed', 'Wrong password');
                return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
            }
            
            // Đăng nhập thành công
            $this->startUserSession($user);
            $this->updateLastLogin($user['id']);
            
            // Xử lý Remember Me
            if ($remember_me) {
                $this->setRememberMeToken($user['id']);
            }
            
            $this->logActivity($user['id'], 'login', 'User logged in successfully');
            
            return [
                'success' => true, 
                'message' => 'Đăng nhập thành công!',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'username' => $user['username']
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi hệ thống. Vui lòng thử lại sau.'];
        }
    }
    
    /**
     * Đăng xuất
     */
    public function logout() {
        $user_id = $_SESSION['user_id'] ?? null;
        
        if ($user_id) {
            $this->logActivity($user_id, 'logout', 'User logged out');
            $this->clearRememberMeToken($user_id);
        }
        
        // Xóa tất cả session
        $_SESSION = array();
        
        // Xóa session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Xóa remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        session_destroy();
        
        return ['success' => true, 'message' => 'Đăng xuất thành công!'];
    }
    
    /**
     * Kiểm tra xem người dùng đã đăng nhập chưa
     */
    public function isLoggedIn() {
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            return true;
        }
        
        // Kiểm tra Remember Me token
        if (isset($_COOKIE['remember_token'])) {
            return $this->checkRememberMeToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * Lấy thông tin user hiện tại
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $user_id = $_SESSION['user_id'];
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, full_name, phone, status, created_at, last_login
                FROM users WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Đổi mật khẩu
     */
    public function changePassword($user_id, $old_password, $new_password) {
        try {
            // Lấy mật khẩu hiện tại
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($old_password, $user['password'])) {
                return ['success' => false, 'message' => 'Mật khẩu cũ không đúng.'];
            }
            
            // Validate mật khẩu mới
            if (strlen($new_password) < 6) {
                return ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'];
            }
            
            // Cập nhật mật khẩu
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$hashed_password, $user_id]);
            
            if ($result) {
                $this->logActivity($user_id, 'change_password', 'Password changed successfully');
                return ['success' => true, 'message' => 'Đổi mật khẩu thành công!'];
            } else {
                return ['success' => false, 'message' => 'Có lỗi xảy ra khi đổi mật khẩu.'];
            }
            
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi hệ thống. Vui lòng thử lại sau.'];
        }
    }
    
    /**
     * Cập nhật thông tin profile
     */
    public function updateProfile($user_id, $full_name, $phone = null, $address = null) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE users SET full_name = ?, phone = ?, address = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$full_name, $phone, $address, $user_id]);
            
            if ($result) {
                // Cập nhật session
                $_SESSION['user_name'] = $full_name;
                $this->logActivity($user_id, 'update_profile', 'Profile updated successfully');
                return ['success' => true, 'message' => 'Cập nhật thông tin thành công!'];
            } else {
                return ['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật thông tin.'];
            }
            
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi hệ thống. Vui lòng thử lại sau.'];
        }
    }
    
    /**
     * Yêu cầu reset mật khẩu
     */
    public function requestPasswordReset($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, full_name FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Email không tồn tại trong hệ thống.'];
            }
            
            // Tạo reset token
            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $this->pdo->prepare("
                INSERT INTO password_resets (user_id, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                token = VALUES(token), 
                expires_at = VALUES(expires_at),
                created_at = NOW()
            ");
            $stmt->execute([$user['id'], $reset_token, $expires_at]);
            
            $this->logActivity($user['id'], 'password_reset_request', 'Password reset requested');
            
            // TODO: Gửi email reset password
            // $this->sendPasswordResetEmail($email, $reset_token, $user['full_name']);
            
            return [
                'success' => true, 
                'message' => 'Đã gửi email hướng dẫn reset mật khẩu.',
                'token' => $reset_token // Chỉ để test, production không trả về token
            ];
            
        } catch (PDOException $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi hệ thống. Vui lòng thử lại sau.'];
        }
    }
    
    /**
     * Reset mật khẩu với token
     */
    public function resetPassword($token, $new_password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT pr.user_id, u.email 
                FROM password_resets pr 
                JOIN users u ON pr.user_id = u.id 
                WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
            ");
            $stmt->execute([$token]);
            $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reset_request) {
                return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn.'];
            }
            
            // Validate mật khẩu mới
            if (strlen($new_password) < 6) {
                return ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'];
            }
            
            // Cập nhật mật khẩu
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashed_password, $reset_request['user_id']]);
            
            // Đánh dấu token đã được sử dụng
            $stmt = $this->pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $this->logActivity($reset_request['user_id'], 'password_reset', 'Password reset successfully');
            
            return ['success' => true, 'message' => 'Reset mật khẩu thành công!'];
            
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi hệ thống. Vui lòng thử lại sau.'];
        }
    }
    
    public function getUserById($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    // =============== PRIVATE METHODS ===============
    
    private function isEmailExists($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }
    
    private function isUsernameExists($username) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->rowCount() > 0;
    }
    
    private function validateUserData($username, $email, $password, $full_name) {
        if (strlen($username) < 3) {
            return ['valid' => false, 'message' => 'Tên đăng nhập phải có ít nhất 3 ký tự.'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Email không hợp lệ.'];
        }
        
        if (strlen($password) < 6) {
            return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'];
        }
        
        if (strlen($full_name) < 2) {
            return ['valid' => false, 'message' => 'Họ tên phải có ít nhất 2 ký tự.'];
        }
        
        return ['valid' => true];
    }
    
    private function startUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_address'] = $user['address'];
        $_SESSION['login_time'] = time();
    }
    
    private function updateLastLogin($user_id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    private function setRememberMeToken($user_id) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        try {
            // Lưu token vào database
            $stmt = $this->pdo->prepare("
                INSERT INTO remember_tokens (user_id, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, hash('sha256', $token), $expires_at]);
            
            // Set cookie
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            
        } catch (PDOException $e) {
            error_log("Remember me token error: " . $e->getMessage());
        }
    }
    
    private function checkRememberMeToken($token) {
        try {
            $hashed_token = hash('sha256', $token);
            $stmt = $this->pdo->prepare("
                SELECT rt.user_id, u.email, u.full_name, u.username, u.status
                FROM remember_tokens rt
                JOIN users u ON rt.user_id = u.id
                WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status = 'active'
            ");
            $stmt->execute([$hashed_token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->startUserSession($result);
                $this->updateLastLogin($result['user_id']);
                return true;
            }
            
        } catch (PDOException $e) {
            error_log("Check remember token error: " . $e->getMessage());
        }
        
        return false;
    }
    
    private function clearRememberMeToken($user_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("Clear remember token error: " . $e->getMessage());
        }
    }
    
    private function logActivity($user_id, $action, $description = '') {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_activities (user_id, action, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user_id,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
}

// =============== HELPER FUNCTIONS ===============

/**
 * Helper functions đã được chuyển sang auth_helper.php
 */

/**
 * Kiểm tra đăng nhập và redirect nếu cần
 */
if (!function_exists('requireLogin')) {
    function requireLogin($redirect_url = null) {
        global $pdo;
        $auth = new Auth($pdo);
        
        if (!$auth->isLoggedIn()) {
            if ($redirect_url) {
                $_SESSION['login_redirect_url'] = $redirect_url;
            }
            header('Location: ' . BASE_URL . 'app/View/user/login.php');
            exit();
        }
        
        return $auth->getCurrentUser();
    }
}

/**
 * Kiểm tra quyền admin
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        $user = requireLogin();
        
        if (!$user || $user['role'] !== 'admin') {
            header('Location: ' . BASE_URL . 'public/TrangChu.php');
            exit();
        }
        
        return $user;
    }
}

/**
 * Escape output để tránh XSS
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate CSRF token
 */
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Validate CSRF token
 */
if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
