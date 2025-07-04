<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/ExtraController.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pdo = Database::getInstance()->getConnection();
$controller = new ExtraController();

switch ($action) {
    case 'search_suggestions':
        $keyword = $_GET['keyword'] ?? '';
        $limit = $_GET['limit'] ?? 10;
        
        if (strlen($keyword) < 2) {
            echo json_encode(['success' => false, 'message' => 'Keyword too short']);
            exit;
        }

        try {
            $suggestions = $controller->getSearchSuggestions($pdo, $keyword, (int)$limit);
            echo json_encode(['success' => true, 'suggestions' => $suggestions]);
        } catch (Exception $e) {
            error_log("Search suggestion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>