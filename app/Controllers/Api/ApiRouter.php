<?php
/**
 * ApiRouter - Unified API Router
 * Tập trung xử lý tất cả API requests
 * 
 * Usage:
 *   GET /api.php?module=search&action=suggestions&keyword=...
 *   GET /api.php?module=notification&action=get
 *   POST /api.php?module=notification&action=mark_read
 */

// Suppress errors and handle them as JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

class ApiRouter
{
    private $pdo;
    private $handlers = [];
    
    public function __construct()
    {
        // Load config
        $configPath = __DIR__ . '/../../../config/config.php';
        if (!file_exists($configPath)) {
            $this->sendError('Configuration not found', 500);
        }
        require_once $configPath;
        
        global $pdo;
        $this->pdo = $pdo;
        
        // Register handlers
        $this->registerHandlers();
    }
    
    /**
     * Register all available API handlers
     */
    private function registerHandlers()
    {
        $this->handlers = [
            'search' => [
                'class' => 'SearchAPI',
                'file' => __DIR__ . '/../extra/SearchAPI.php',
                'actions' => ['suggestions', 'search_suggestions']
            ],
            'notification' => [
                'class' => 'NotificationAPI', 
                'file' => __DIR__ . '/../extra/NotificationAPI.php',
                'actions' => ['get', 'mark_read', 'get_notifications', 'mark_as_read']
            ],
            'extra' => [
                'class' => 'ExtraController',
                'file' => __DIR__ . '/../extra/ExtraController.php',
                'actions' => ['search_suggestions']
            ]
        ];
    }
    
    /**
     * Handle incoming API request
     */
    public function handleRequest()
    {
        // Clear any output
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');
        
        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        try {
            $module = $_GET['module'] ?? $this->detectModule();
            $action = $_GET['action'] ?? $_POST['action'] ?? '';
            
            if (empty($module) || empty($action)) {
                $this->sendError('Missing module or action parameter', 400);
            }
            
            $this->routeRequest($module, $action);
            
        } catch (Exception $e) {
            error_log("ApiRouter Error: " . $e->getMessage());
            $this->sendError('Server error: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Detect module from action name (backward compatibility)
     */
    private function detectModule()
    {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        // Map old actions to modules
        $actionToModule = [
            'search_suggestions' => 'search',
            'suggestions' => 'search',
            'get_notifications' => 'notification',
            'mark_as_read' => 'notification',
            'get' => 'notification',
            'mark_read' => 'notification'
        ];
        
        return $actionToModule[$action] ?? 'extra';
    }
    
    /**
     * Route request to appropriate handler
     */
    private function routeRequest($module, $action)
    {
        if (!isset($this->handlers[$module])) {
            $this->sendError("Unknown module: {$module}", 404);
        }
        
        $handler = $this->handlers[$module];
        
        // Load handler file
        if (!file_exists($handler['file'])) {
            $this->sendError("Handler file not found: {$module}", 500);
        }
        
        require_once $handler['file'];
        
        // Call handler
        $className = $handler['class'];
        
        if ($className === 'ExtraController') {
            $this->handleExtraController($action);
        } elseif ($className === 'SearchAPI') {
            $this->handleSearchAPI($action);
        } elseif ($className === 'NotificationAPI') {
            $this->handleNotificationAPI($action);
        } else {
            $this->sendError("Unknown handler: {$className}", 500);
        }
    }
    
    /**
     * Handle ExtraController requests
     */
    private function handleExtraController($action)
    {
        $controller = new ExtraController();
        
        switch ($action) {
            case 'search_suggestions':
                $keyword = $_GET['keyword'] ?? '';
                $limit = $_GET['limit'] ?? 10;
                
                if (strlen($keyword) < 2) {
                    $this->sendSuccess(['suggestions' => []]);
                    return;
                }
                
                $suggestions = $controller->getSearchSuggestions($this->pdo, $keyword, (int)$limit);
                $this->sendSuccess(['suggestions' => $suggestions]);
                break;
                
            default:
                $this->sendError("Unknown action: {$action}", 400);
        }
    }
    
    /**
     * Handle SearchAPI requests
     */
    private function handleSearchAPI($action)
    {
        $api = new SearchAPI();
        
        switch ($action) {
            case 'suggestions':
            case 'search_suggestions':
                $api->getSearchSuggestions();
                break;
                
            default:
                $this->sendError("Unknown search action: {$action}", 400);
        }
    }
    
    /**
     * Handle NotificationAPI requests  
     */
    private function handleNotificationAPI($action)
    {
        $api = new NotificationAPI();
        
        switch ($action) {
            case 'get':
            case 'get_notifications':
                $api->getNotifications();
                break;
                
            case 'mark_read':
            case 'mark_as_read':
                $api->markAsRead();
                break;
                
            default:
                $this->sendError("Unknown notification action: {$action}", 400);
        }
    }
    
    /**
     * Send success response
     */
    private function sendSuccess($data)
    {
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send error response
     */
    private function sendError($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Auto-execute if called directly
if (basename($_SERVER['SCRIPT_FILENAME']) === 'ApiRouter.php' || 
    basename($_SERVER['SCRIPT_FILENAME']) === 'api.php') {
    $router = new ApiRouter();
    $router->handleRequest();
}
