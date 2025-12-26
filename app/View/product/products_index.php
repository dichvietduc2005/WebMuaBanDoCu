<?php
/**
 * products_index.php - Entry point for product listing page
 * Routes to FrontendProductController and displays products.php view
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Core/Autoloader.php';

// Initialize database
$db = Database::getInstance();
$pdo = $db->getConnection();

// Load controller and execute
require_once __DIR__ . '/../../Controllers/product/FrontendProductController.php';

try {
    $controller = new FrontendProductController();
    $controller->index();
} catch (Exception $e) {
    // Fallback to products.php with empty data
    $products = [];
    $total_products = 0;
    $total_pages = 1;
    $page = 1;
    $search = '';
    $category = 0;
    
    require_once __DIR__ . '/../../Components/header/Header.php';
    require_once __DIR__ . '/../../Components/footer/Footer.php';
    require_once __DIR__ . '/products.php';
}
?>





