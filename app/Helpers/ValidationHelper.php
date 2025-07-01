<?php
/**
 * ValidationHelper - Tập trung tất cả validation logic
 * Giảm thiểu code duplication và cải thiện consistency
 */

class ValidationHelper 
{
    /**
     * Validate email format
     */
    public static function validateEmail($email) 
    {
        return [
            'valid' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
            'message' => 'Email không hợp lệ.'
        ];
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password, $minLength = 8) 
    {
        $errors = [];
        
        if (strlen($password) < $minLength) {
            $errors[] = "Mật khẩu phải có ít nhất {$minLength} ký tự.";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 số.';
        }
        
        // Check for special characters (recommended)
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Mật khẩu nên có ít nhất 1 ký tự đặc biệt.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : implode(' ', $errors)
        ];
    }
    
    /**
     * Validate username
     */
    public static function validateUsername($username) 
    {
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Tên đăng nhập là bắt buộc.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Tên đăng nhập phải từ 3-50 ký tự.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : implode(' ', $errors)
        ];
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone, $required = false) 
    {
        if (!$required && empty($phone)) {
            return ['valid' => true, 'message' => ''];
        }
        
        if ($required && empty($phone)) {
            return ['valid' => false, 'message' => 'Số điện thoại là bắt buộc.'];
        }
        
        // Vietnamese phone number pattern
        $pattern = '/^(0|\+84)[1-9][0-9]{8,9}$/';
        
        return [
            'valid' => preg_match($pattern, $phone),
            'message' => preg_match($pattern, $phone) ? '' : 'Số điện thoại không hợp lệ.'
        ];
    }
    
    /**
     * Validate price
     */
    public static function validatePrice($price, $min = 0, $max = null) 
    {
        $errors = [];
        
        if (!is_numeric($price)) {
            $errors[] = 'Giá phải là số.';
        } else {
            $price = floatval($price);
            
            if ($price < $min) {
                $errors[] = "Giá phải lớn hơn hoặc bằng " . number_format($min) . " VNĐ.";
            }
            
            if ($max !== null && $price > $max) {
                $errors[] = "Giá không được vượt quá " . number_format($max) . " VNĐ.";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : implode(' ', $errors)
        ];
    }
    
    /**
     * Validate product data
     */
    public static function validateProductData($data) 
    {
        $errors = [];
        
        // Title validation
        if (empty($data['title'])) {
            $errors['title'] = 'Tên sản phẩm là bắt buộc.';
        } elseif (strlen($data['title']) < 3 || strlen($data['title']) > 255) {
            $errors['title'] = 'Tên sản phẩm phải từ 3-255 ký tự.';
        }
        
        // Description validation
        if (empty($data['description'])) {
            $errors['description'] = 'Mô tả sản phẩm là bắt buộc.';
        } elseif (strlen($data['description']) < 10) {
            $errors['description'] = 'Mô tả phải có ít nhất 10 ký tự.';
        }
        
        // Price validation
        $priceValidation = self::validatePrice($data['price'] ?? 0, 1000, 1000000000);
        if (!$priceValidation['valid']) {
            $errors['price'] = $priceValidation['message'];
        }
        
        // Category validation
        if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
            $errors['category_id'] = 'Vui lòng chọn danh mục sản phẩm.';
        }
        
        // Condition validation
        $allowedConditions = ['new', 'like_new', 'good', 'fair', 'poor'];
        if (empty($data['condition_status']) || !in_array($data['condition_status'], $allowedConditions)) {
            $errors['condition_status'] = 'Vui lòng chọn tình trạng sản phẩm hợp lệ.';
        }
        
        // Stock quantity validation
        if (isset($data['stock_quantity'])) {
            if (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 1) {
                $errors['stock_quantity'] = 'Số lượng phải là số nguyên dương.';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : 'Dữ liệu sản phẩm không hợp lệ.'
        ];
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = null) 
    {
        $errors = [];
        
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'Không có file được upload.'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Có lỗi xảy ra khi upload file.'];
        }
        
        // Check file size
        $maxSize = $maxSize ?: MAX_UPLOAD_SIZE;
        if ($file['size'] > $maxSize) {
            $errors[] = 'File quá lớn. Kích thước tối đa: ' . formatBytes($maxSize);
        }
        
        // Check file type
        $allowedTypes = $allowedTypes ?: ALLOWED_IMAGE_TYPES;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'Loại file không được phép. Cho phép: ' . implode(', ', $allowedTypes);
        }
        
        // Check if file is actually an image (for security)
        if (strpos($mimeType, 'image/') === 0) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $errors[] = 'File không phải là hình ảnh hợp lệ.';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : implode(' ', $errors)
        ];
    }
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input, $maxLength = null) 
    {
        $cleaned = trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
        
        if ($maxLength && strlen($cleaned) > $maxLength) {
            $cleaned = substr($cleaned, 0, $maxLength);
        }
        
        return $cleaned;
    }
    
    /**
     * Sanitize HTML input (for rich text)
     */
    public static function sanitizeHtml($input, $allowedTags = []) 
    {
        $defaultAllowedTags = '<p><br><strong><em><u><ol><ul><li>';
        $allowedTags = empty($allowedTags) ? $defaultAllowedTags : implode('', $allowedTags);
        
        return strip_tags($input, $allowedTags);
    }
    
    /**
     * Validate and sanitize array of data
     */
    public static function sanitizeArray($data, $rules = []) 
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                
                switch ($rule['type']) {
                    case 'string':
                        $sanitized[$key] = self::sanitizeString($value, $rule['max_length'] ?? null);
                        break;
                        
                    case 'html':
                        $sanitized[$key] = self::sanitizeHtml($value, $rule['allowed_tags'] ?? []);
                        break;
                        
                    case 'email':
                        $sanitized[$key] = filter_var(trim($value), FILTER_SANITIZE_EMAIL);
                        break;
                        
                    case 'int':
                        $sanitized[$key] = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                        break;
                        
                    case 'float':
                        $sanitized[$key] = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        break;
                        
                    default:
                        $sanitized[$key] = self::sanitizeString($value);
                }
            } else {
                $sanitized[$key] = self::sanitizeString($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Check if value is in allowed list
     */
    public static function validateInArray($value, $allowedValues, $errorMessage = 'Giá trị không hợp lệ.') 
    {
        return [
            'valid' => in_array($value, $allowedValues),
            'message' => in_array($value, $allowedValues) ? '' : $errorMessage
        ];
    }
    
    /**
     * Validate date format
     */
    public static function validateDate($date, $format = 'Y-m-d') 
    {
        $dateTime = DateTime::createFromFormat($format, $date);
        $valid = $dateTime && $dateTime->format($format) === $date;
        
        return [
            'valid' => $valid,
            'message' => $valid ? '' : 'Định dạng ngày không hợp lệ.'
        ];
    }
}

/**
 * Helper function để format bytes
 */
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 