<?php
// SearchAPI.php - Xử lý AJAX requests cho search suggestions
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bắt đầu output buffering ngay từ đầu
ob_start();

try {
    $configPath = __DIR__ . '/../../../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception("Config file not found at: " . $configPath);
    }
    require_once $configPath;
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Initialization error: ' . $e->getMessage()
    ]);
    exit;
}

class SearchAPI
{
    public function handleRequest()
    {
        if (ob_get_level()) ob_clean();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        switch ($action) {
            case 'search_suggestions':
                $this->getSearchSuggestions();
                break;
            default:
                $this->sendError('Invalid action', 400);
        }
    }

    public function getSearchSuggestions()
    {
        error_log("getSearchSuggestions called");
        if (ob_get_level()) ob_clean();
        try {
            $keyword = $_GET['keyword'] ?? '';
            $limit = (int)($_GET['limit'] ?? 8);
            if (strlen($keyword) < 2) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'suggestions' => []
                    ]
                ]);
                exit;
            }
            // Test với dữ liệu tĩnh trước
            $testSuggestions = [
                'Điện thoại ' . $keyword,
                'Laptop ' . $keyword, 
                'Máy tính ' . $keyword,
                'Tai nghe ' . $keyword,
                'Đồng hồ ' . $keyword
            ];
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [
                    'suggestions' => $testSuggestions
                ]
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function sendError($message, $code = 400)
    {
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

if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    $api = new SearchAPI();
    $api->handleRequest();
} 