<?php
// NotificationAPI.php - Xử lý AJAX requests cho notifications
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Core/Database.php';

class NotificationAPI
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function handleRequest()
    {
        // Clean any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Thiết lập CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
        header('Content-Type: application/json; charset=utf-8');

        // Xử lý preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'get_notifications':
                    $this->getNotifications();
                    break;
                case 'mark_read':
                    $this->markAsRead();
                    break;
                default:
                    $this->sendError('Invalid action', 400);
            }
        } catch (Exception $e) {
            error_log("NotificationAPI Error: " . $e->getMessage());
            $this->sendError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function getNotifications()
    {
        try {
            // Debug session - add more detailed logging
            error_log("=== NotificationAPI Debug ===");
            error_log("Session Status: " . session_status());
            error_log("Session ID: " . session_id());
            error_log("Session data: " . print_r($_SESSION, true));
            error_log("Cookie data: " . print_r($_COOKIE, true));
            error_log("User ID from session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
            
            // Nếu user chưa đăng nhập, trả về empty data với tin tức hệ thống
            if (!isset($_SESSION['user_id'])) {
                error_log("User not logged in - returning news only");
                $news = [
                    [
                        'id' => 1,
                        'message' => 'Chào mừng bạn đến với HIHand Shop!',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                        'is_read' => 0,
                        'type' => 'news'
                    ],
                    [
                        'id' => 2,
                        'message' => 'Tính năng chat mới đã ra mắt',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                        'is_read' => 0,
                        'type' => 'news'
                    ],
                    [
                        'id' => 3,
                        'message' => 'Khuyến mãi đặc biệt cuối tuần',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                        'is_read' => 0,
                        'type' => 'news'
                    ]
                ];

                $this->successResponse([
                    'activities' => [],
                    'news' => $news,
                    'unreadCount' => 0,
                    'isLoggedIn' => false,
                    'debug' => [
                        'session_status' => session_status(),
                        'session_id' => session_id(),
                        'has_user_id' => isset($_SESSION['user_id'])
                    ]
                ]);
                return;
            }

            $user_id = $_SESSION['user_id'];
            error_log("User logged in with ID: " . $user_id);
            
            // Lấy thông báo hoạt động (notifications)
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    message,
                    created_at,
                    is_read,
                    'activity' as type
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $stmt->execute([$user_id]);
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($activities) . " activities for user " . $user_id);

            // Lấy tin tức hệ thống
            $news = [
                [
                    'id' => 1,
                    'message' => 'Chào mừng bạn đến với HIHand Shop!',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                    'is_read' => 0,
                    'type' => 'news'
                ],
                [
                    'id' => 2,
                    'message' => 'Tính năng chat mới đã ra mắt',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'is_read' => 0,
                    'type' => 'news'
                ],
                [
                    'id' => 3,
                    'message' => 'Khuyến mãi đặc biệt cuối tuần',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'is_read' => 0,
                    'type' => 'news'
                ]
            ];

            // Đếm thông báo chưa đọc
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            $unreadCount = (int) $stmt->fetchColumn();
            error_log("Unread count: " . $unreadCount);

            $response_data = [
                'activities' => $activities,
                'news' => $news,
                'unreadCount' => $unreadCount,
                'isLoggedIn' => true,
                'debug' => [
                    'session_status' => session_status(),
                    'session_id' => session_id(),
                    'user_id' => $user_id,
                    'activities_count' => count($activities)
                ]
            ];
            
            error_log("Sending response: " . json_encode($response_data));
            $this->successResponse($response_data);

        } catch (Exception $e) {
            error_log("Exception in getNotifications: " . $e->getMessage());
            $this->errorResponse('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function markAsRead()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        try {
            $user_id = $_SESSION['user_id'];
            $notification_id = $_POST['notification_id'] ?? null;

            if ($notification_id) {
                // Đánh dấu một thông báo cụ thể đã đọc
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$notification_id, $user_id]);
            } else {
                // Đánh dấu tất cả thông báo đã đọc
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
                $stmt->execute([$user_id]);
            }

            $this->successResponse(['message' => 'Notifications marked as read']);

        } catch (Exception $e) {
            $this->errorResponse('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function successResponse($data)
    {
        // Xóa bất kỳ output nào trước đó
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    public function errorResponse($message, $code = 400)
    {
        // Xóa bất kỳ output nào trước đó
        if (ob_get_length()) ob_clean();
        
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }

    public function sendError($message, $code = 400)
    {
        // Xóa bất kỳ output nào trước đó
        if (ob_get_length()) ob_clean();
        
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}

// Xử lý request nếu file được gọi trực tiếp
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    try {
        $api = new NotificationAPI();
        $api->handleRequest();
    } catch (Exception $e) {
        error_log("NotificationAPI Error: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Server error'
        ]);
    }
}
?> 