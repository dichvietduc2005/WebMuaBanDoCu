<?php
/**
 * UserController - Quản lý các chức năng user
 * Cải thiện bảo mật và hiệu suất
 */

class UserController 
{
    private $db;
    private $auth;
    
    public function __construct($database = null) 
    {
        $this->db = $database ?: Database::getInstance();
        $this->auth = new Auth($this->db->getConnection());
    }
    
    /**
     * Đăng ký user mới với validation nâng cao
     */
    public function register($data) 
    {
        // Validate CSRF token
        if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
            return [
                'success' => false,
                'message' => 'Token bảo mật không hợp lệ.'
            ];
        }
        
        // Sanitize input data
        $cleanData = $this->sanitizeUserData($data);
        
        // Validation nâng cao
        $validation = $this->validateRegistrationData($cleanData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'errors' => $validation['errors'] ?? []
            ];
        }
        
        // Rate limiting check
        if (!$this->checkRateLimit('register', $_SERVER['REMOTE_ADDR'])) {
            return [
                'success' => false,
                'message' => 'Quá nhiều lần đăng ký. Vui lòng thử lại sau.'
            ];
        }
        
        return $this->auth->register(
            $cleanData['username'],
            $cleanData['email'], 
            $cleanData['password'],
            $cleanData['full_name'],
            $cleanData['phone'] ?? null
        );
    }
    
    /**
     * Đăng nhập với bảo mật nâng cao
     */
    public function login($data) 
    {
        // Validate CSRF token
        if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
            return [
                'success' => false,
                'message' => 'Token bảo mật không hợp lệ.'
            ];
        }
        
        // Rate limiting check
        if (!$this->checkRateLimit('login', $_SERVER['REMOTE_ADDR'])) {
            return [
                'success' => false,
                'message' => 'Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau.'
            ];
        }
        
        $email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $data['password'] ?? '';
        $remember_me = !empty($data['remember_me']);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Email không hợp lệ.'
            ];
        }
        
        return $this->auth->login($email, $password, $remember_me);
    }
    
    /**
     * Đăng xuất
     */
    public function logout() 
    {
        return $this->auth->logout();
    }
    
    /**
     * Cập nhật profile user
     */
    public function updateProfile($user_id, $data) 
    {
        // Validate CSRF token
        if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
            return [
                'success' => false,
                'message' => 'Token bảo mật không hợp lệ.'
            ];
        }
        
        // Sanitize data
        $cleanData = $this->sanitizeUserData($data);
        
        // Validation
        $validation = $this->validateProfileData($cleanData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'errors' => $validation['errors'] ?? []
            ];
        }
        
        return $this->auth->updateProfile(
            $user_id,
            $cleanData['full_name'],
            $cleanData['phone'] ?? null,
            $cleanData['address'] ?? null
        );
    }
    
    /**
     * Đổi mật khẩu
     */
    public function changePassword($user_id, $data) 
    {
        // Validate CSRF token
        if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
            return [
                'success' => false,
                'message' => 'Token bảo mật không hợp lệ.'
            ];
        }
        
        $old_password = $data['old_password'] ?? '';
        $new_password = $data['new_password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';
        
        // Validation
        if (empty($old_password) || empty($new_password)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin.'
            ];
        }
        
        if ($new_password !== $confirm_password) {
            return [
                'success' => false,
                'message' => 'Mật khẩu xác nhận không khớp.'
            ];
        }
        
        if (!$this->validatePassword($new_password)) {
            return [
                'success' => false,
                'message' => 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số.'
            ];
        }
        
        return $this->auth->changePassword($user_id, $old_password, $new_password);
    }
    
    /**
     * Lấy danh sách users với pagination (cho admin)
     */
    public function getUsers($page = 1, $limit = 20, $search = '', $status = '') 
    {
        try {
            $offset = ($page - 1) * $limit;
            $params = [];
            
            // Build query với conditions
            $sql = "SELECT id, username, email, full_name, phone, status, created_at, last_login 
                    FROM users WHERE 1=1";
            
            if (!empty($search)) {
                $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (!empty($status)) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params = array_merge($params, [$limit, $offset]);
            
            $stmt = $this->db->query($sql, $params);
            
            // Count total
            $countSql = str_replace("SELECT id, username, email, full_name, phone, status, created_at, last_login", "SELECT COUNT(*)", $sql);
            $countSql = str_replace("ORDER BY created_at DESC LIMIT ? OFFSET ?", "", $countSql);
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            
            $countStmt = $this->db->query($countSql, $countParams);
            $total = $countStmt->fetch()['COUNT(*)'];
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(),
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => $total,
                    'items_per_page' => $limit
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể lấy danh sách người dùng.'
            ];
        }
    }
    
    /**
     * Cập nhật trạng thái user (cho admin)
     */
    public function updateUserStatus($user_id, $status) 
    {
        $allowedStatuses = ['active', 'inactive', 'banned'];
        if (!in_array($status, $allowedStatuses)) {
            return [
                'success' => false,
                'message' => 'Trạng thái không hợp lệ.'
            ];
        }
        
        try {
            $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->query($sql, [$status, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Người dùng không tồn tại.'
                ];
            }
        } catch (Exception $e) {
            error_log("Error updating user status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái.'
            ];
        }
    }
    
    /**
     * Sanitize user input data
     */
    private function sanitizeUserData($data) 
    {
        return [
            'username' => trim(htmlspecialchars($data['username'] ?? '', ENT_QUOTES, 'UTF-8')),
            'email' => filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'password' => $data['password'] ?? '',
            'full_name' => trim(htmlspecialchars($data['full_name'] ?? '', ENT_QUOTES, 'UTF-8')),
            'phone' => trim(htmlspecialchars($data['phone'] ?? '', ENT_QUOTES, 'UTF-8')),
            'address' => trim(htmlspecialchars($data['address'] ?? '', ENT_QUOTES, 'UTF-8'))
        ];
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) 
    {
        $errors = [];
        
        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Tên đăng nhập là bắt buộc.';
        } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
            $errors['username'] = 'Tên đăng nhập phải từ 3-50 ký tự.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới.';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email là bắt buộc.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        
        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Mật khẩu là bắt buộc.';
        } elseif (!$this->validatePassword($data['password'])) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số.';
        }
        
        // Full name validation
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Họ tên là bắt buộc.';
        } elseif (strlen($data['full_name']) < 2 || strlen($data['full_name']) > 100) {
            $errors['full_name'] = 'Họ tên phải từ 2-100 ký tự.';
        }
        
        // Phone validation (optional)
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : 'Dữ liệu không hợp lệ.'
        ];
    }
    
    /**
     * Validate profile data
     */
    private function validateProfileData($data) 
    {
        $errors = [];
        
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Họ tên là bắt buộc.';
        } elseif (strlen($data['full_name']) < 2 || strlen($data['full_name']) > 100) {
            $errors['full_name'] = 'Họ tên phải từ 2-100 ký tự.';
        }
        
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : 'Dữ liệu không hợp lệ.'
        ];
    }
    
    /**
     * Validate password strength
     */
    private function validatePassword($password) 
    {
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    
    /**
     * Validate CSRF token
     */
    private function validateCSRF($token) 
    {
        return validateCSRF($token); // Sử dụng function global từ config
    }
    
    /**
     * Rate limiting check
     */
    private function checkRateLimit($action, $ip) 
    {
        try {
            $key = "rate_limit_{$action}_{$ip}";
            
            // Check in database hoặc cache (Redis/Memcached)
            // Tạm thời sử dụng database
            $sql = "SELECT COUNT(*) as attempts FROM rate_limits 
                    WHERE action = ? AND ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $stmt = $this->db->query($sql, [$action, $ip]);
            $result = $stmt->fetch();
            
            $maxAttempts = ($action === 'login') ? 5 : 3; // 5 login attempts, 3 register attempts per hour
            
            if ($result['attempts'] >= $maxAttempts) {
                return false;
            }
            
            // Log attempt
            $logSql = "INSERT INTO rate_limits (action, ip_address, created_at) VALUES (?, ?, NOW())";
            $this->db->query($logSql, [$action, $ip]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return true; // Allow on error để không block user
        }
    }
}
