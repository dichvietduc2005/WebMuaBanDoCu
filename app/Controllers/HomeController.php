<?php
/**
 * HomeController - Handle home page logic
 */

require_once __DIR__ . '/../Models/product/ProductModel.php';
require_once __DIR__ . '/../Models/product/CategoryModel.php';
require_once __DIR__ . '/../Models/order/OrderModel.php';

class HomeController
{
    private $productModel;
    private $categoryModel;
    private $orderModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->orderModel = new OrderModel();
    }

    public function index()
    {
        // Debug connection (legacy, can be removed if sure)
        global $pdo;
        if (!isset($pdo)) {
             // Re-initialize if needed or rely on Models which use Database singleton
             $db = Database::getInstance();
             $pdo = $db->getConnection();
        }

        // Debug trace check
        // file_put_contents($logFile, "HomeController::index() START\n");
        
        try {
            // Get Featured Products (18 items)
            $featured_products = $this->productModel->getProducts(18, 0, true);

            // Get Regular Products (24 items)
            $regular_products = $this->productModel->getProducts(24, 0, false);

            // Get Categories
            $categories = $this->categoryModel->getAllActive();
            
            // var_dump($categories[0] ?? 'Null cat'); 

        } catch (Exception $e) {
            error_log("Error in HomeController: " . $e->getMessage());
            // echo "Error: " . $e->getMessage() . "<br>";
            $featured_products = [];
            $regular_products = [];
            $categories = [];
        }

        // Get User Specific Data
        $recent_orders = [];
        $cart_count = 0;
        $unread_notifications = 0;

        if (isset($_SESSION['user_id'])) {
            try {
                $userId = $_SESSION['user_id'];
                $recent_orders = $this->orderModel->getRecentOrders($userId, 6);
                $cart_count = $this->orderModel->getCartItemCount($userId);
                $unread_notifications = $this->orderModel->countUnreadNotifications($userId);
                // echo "User Data Loaded<br>";
            } catch (Exception $e) {
                error_log("Error getting user data in HomeController: " . $e->getMessage());
            }
        }

        // Include View and pass variables
        // echo "Loading View...<br>";
        require __DIR__ . '/../View/Home.php';
    }
}
