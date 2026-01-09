<?php
/**
 * ProfileUserController - Quản lý chức năng trang cá nhân
 */
class ProfileUserController 
{
    private $db;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Hiển thị trang profile (index)
     */
    public function index()
    {
        // Check login
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'public/index.php?page=login');
            exit;
        }

        // Dependencies for Header
        // Assuming CategoryModel is needed for Header
        $db = $this->db->getConnection();
        
        // Fetch Categories
        $categories = [];
        try {
            $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error loading categories: " . $e->getMessage());
        }

        // Fetch Cart & Notifications
        $cart_count = 0;
        $unread_notifications = 0;
        
        try {
            // Cart count
            $stmt = $db->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $cart_count = (int)$stmt->fetchColumn();

            // Notifications
            $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$_SESSION['user_id']]);
            $unread_notifications = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
             error_log("Error loading user data: " . $e->getMessage());
        }

        // Global $pdo is often used in Views in this legacy app, pass it just in case
        global $pdo;
        $pdo = $db;

        // Render View
        require_once APP_PATH . '/View/user/ProfileUserView.php';
    }

    /**
     * API cập nhật profile (update)
     */
    public function update()
    {
        // Check login
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Unauthorized";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo "Invalid Request Method";
            return;
        }

        $rawData = file_get_contents("php://input");
        $data = json_decode($rawData, true);

        if (!$data || !isset($data['user_id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo "Invalid Data";
            return;
        }

        // Security check: Ensure user can only update their own profile
        if ($data['user_id'] != $_SESSION['user_id']) {
             header('HTTP/1.1 403 Forbidden');
             echo "Forbidden";
             return;
        }

        try {
            $conn = $this->db->getConnection();
            $sql = 'UPDATE users SET 
                full_name = ?, 
                email = ?, 
                phone = ?, 
                address = ? 
                WHERE id = ?';
            
            // Note: Username is typically not changeable. Legacy allowed it, but for security/integrity usually we restrict it.
            // If strictly following legacy: include username update.
            // Let's check logic: Legacy script updated username. 
            // BUT `UserController` logic I wrote earlier only updated full_name, phone, address in `updateProfile`.
            // The `apiUpdateProfile` in `UserController` DID update username.
            // Let's sticky to `apiUpdateProfile` logic which mimics legacy script but within class.
            
            // Re-adding username update to match legacy behavior exactly if requested, 
            // but normally we shouldn't. Let's assume we do for now to minimize regression.
            
            $sql = 'UPDATE users SET 
                username = ?, 
                full_name = ?, 
                email = ?, 
                phone = ?, 
                address = ? 
                WHERE id = ?';
                
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data['user_name'],
                $data['user_full_name'],
                $data['user_email'],
                $data['user_phone'],
                $data['user_address'],
                $data['user_id']
            ]);

            // Update Session
            $_SESSION['user_name'] = $data['user_name'];
            $_SESSION['user_full_name'] = $data['user_full_name'];
            $_SESSION['user_email'] = $data['user_email'];
            $_SESSION['user_phone'] = $data['user_phone'];
            $_SESSION['user_address'] = $data['user_address'];

            echo "success";
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
            echo $e->getMessage();
        }
    }
}