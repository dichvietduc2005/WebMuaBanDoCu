<?php
/**
 * SecurityHelper - Tăng cường bảo mật cho ứng dụng
 * Chống các lỗ hổng bảo mật phổ biến
 */

class SecurityHelper 
{
    /**
     * Generate secure random token
     */
    public static function generateSecureToken($length = 32) 
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) 
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,      // 4 iterations
            'threads' => 3,        // 3 threads
        ]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash) 
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate and validate CSRF tokens
     */
    public static function generateCSRFToken() 
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = self::generateSecureToken();
        $_SESSION[CSRF_TOKEN_NAME] = $token;
        $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
        
        return $token;
    }
    
    public static function validateCSRFToken($token) 
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        
        // Check token expiry
        $tokenTime = $_SESSION[CSRF_TOKEN_NAME . '_time'] ?? 0;
        if (time() - $tokenTime > CSRF_TOKEN_EXPIRE) {
            unset($_SESSION[CSRF_TOKEN_NAME]);
            unset($_SESSION[CSRF_TOKEN_NAME . '_time']);
            return false;
        }
        
        // Validate token
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Prevent XSS attacks
     */
    public static function escapeHtml($string) 
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Clean input for database (prevent injection)
     */
    public static function cleanInput($input) 
    {
        if (is_array($input)) {
            return array_map([self::class, 'cleanInput'], $input);
        }
        
        return trim(strip_tags($input));
    }
    
    /**
     * Rate limiting implementation
     */
    public static function checkRateLimit($action, $identifier, $maxAttempts = 5, $timeWindow = 3600) 
    {
        $db = Database::getInstance();
        
        try {
            // Clean old attempts
            $cleanSql = "DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)";
            $db->query($cleanSql, [$timeWindow]);
            
            // Count recent attempts
            $countSql = "SELECT COUNT(*) as attempts FROM rate_limits 
                         WHERE action = ? AND identifier = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";
            $stmt = $db->query($countSql, [$action, $identifier, $timeWindow]);
            $result = $stmt->fetch();
            
            if ($result['attempts'] >= $maxAttempts) {
                return false;
            }
            
            // Log this attempt
            $logSql = "INSERT INTO rate_limits (action, identifier, ip_address, created_at) VALUES (?, ?, ?, NOW())";
            $db->query($logSql, [$action, $identifier, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return true; // Allow on error to prevent blocking legitimate users
        }
    }
    
    /**
     * Validate file upload security
     */
    public static function validateFileUpload($file, $allowedExtensions = [], $maxSize = null) 
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'File upload không hợp lệ.'];
        }
        
        // Check file size
        $maxSize = $maxSize ?: MAX_UPLOAD_SIZE;
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'File quá lớn.'];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = ALLOWED_IMAGE_TYPES;
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'message' => 'Loại file không được phép.'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = $allowedExtensions ?: ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Phần mở rộng file không được phép.'];
        }
        
        // Additional security checks for images
        if (strpos($mimeType, 'image/') === 0) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['valid' => false, 'message' => 'File không phải là hình ảnh hợp lệ.'];
            }
            
            // Check for embedded PHP code
            $content = file_get_contents($file['tmp_name']);
            if (preg_match('/<\?php|<script|javascript:/i', $content)) {
                return ['valid' => false, 'message' => 'File chứa nội dung không an toàn.'];
            }
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Secure file upload with rename
     */
    public static function secureFileUpload($file, $uploadDir, $prefix = '') 
    {
        $validation = self::validateFileUpload($file);
        if (!$validation['valid']) {
            return $validation;
        }
        
        // Generate secure filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $prefix . uniqid() . '_' . time() . '.' . $extension;
        $filepath = rtrim($uploadDir, '/') . '/' . $filename;
        
        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Set secure permissions
            chmod($filepath, 0644);
            
            return [
                'valid' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'message' => 'Upload thành công.'
            ];
        } else {
            return ['valid' => false, 'message' => 'Không thể lưu file.'];
        }
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encrypt($data, $key = null) 
    {
        $key = $key ?: ($_ENV['SECRET_KEY'] ?? 'default-secret-key');
        $cipher = 'AES-256-GCM';
        $iv = random_bytes(12); // 12 bytes for GCM
        
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decrypt($encryptedData, $key = null) 
    {
        $key = $key ?: ($_ENV['SECRET_KEY'] ?? 'default-secret-key');
        $cipher = 'AES-256-GCM';
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $encrypted = substr($data, 28);
        
        return openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event, $details = [], $level = 'INFO') 
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details
        ];
        
        $logMessage = json_encode($logEntry, JSON_UNESCAPED_UNICODE);
        error_log("SECURITY [$level]: $logMessage");
    }
    
    /**
     * Check for suspicious activity patterns
     */
    public static function detectSuspiciousActivity($userId = null) 
    {
        $suspicious = false;
        $reasons = [];
        
        // Check for rapid requests
        $requestCount = $_SESSION['request_count'] ?? 0;
        $_SESSION['request_count'] = $requestCount + 1;
        $_SESSION['last_request_time'] = $_SESSION['last_request_time'] ?? time();
        
        if (time() - $_SESSION['last_request_time'] < 1 && $requestCount > 10) {
            $suspicious = true;
            $reasons[] = 'Too many rapid requests';
        }
        
        // Check for unusual User-Agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($userAgent) || preg_match('/bot|crawler|spider/i', $userAgent)) {
            $suspicious = true;
            $reasons[] = 'Suspicious User-Agent';
        }
        
        // Check for common attack patterns in request
        $requestData = json_encode($_REQUEST);
        $attackPatterns = [
            '/script.*alert/i',
            '/union.*select/i', 
            '/<script/i',
            '/javascript:/i',
            '/onload=/i'
        ];
        
        foreach ($attackPatterns as $pattern) {
            if (preg_match($pattern, $requestData)) {
                $suspicious = true;
                $reasons[] = 'Attack pattern detected';
                break;
            }
        }
        
        if ($suspicious) {
            self::logSecurityEvent('suspicious_activity', [
                'user_id' => $userId,
                'reasons' => $reasons,
                'request_data' => $_REQUEST
            ], 'WARNING');
        }
        
        return $suspicious;
    }
    
    /**
     * Generate secure session ID
     */
    public static function regenerateSecureSession() 
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Update session security flags
        $sessionParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $sessionParams['lifetime'],
            'path' => $sessionParams['path'],
            'domain' => $sessionParams['domain'],
            'secure' => isset($_SERVER['HTTPS']), // Only over HTTPS
            'httponly' => true, // Prevent XSS
            'samesite' => 'Strict' // CSRF protection
        ]);
    }
    
    /**
     * Validate input against common injection patterns
     */
    public static function validateInput($input) 
    {
        $dangerousPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/\bUNION\b.*\bSELECT\b/i',
            '/\bINSERT\b.*\bINTO\b/i',
            '/\bDELETE\b.*\bFROM\b/i',
            '/\bDROP\b.*\bTABLE\b/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::logSecurityEvent('injection_attempt', [
                    'input' => $input,
                    'pattern' => $pattern
                ], 'ERROR');
                return false;
            }
        }
        
        return true;
    }
} 