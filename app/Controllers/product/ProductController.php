<?php
/**
 * ProductController - Cải thiện để có validation, error handling và hiệu suất tốt hơn
 */

class ProductController 
{
    private $db;
    
    public function __construct($database = null) 
    {
        $this->db = $database ?: Database::getInstance();
    }
    
    /**
     * Lấy danh sách sản phẩm pending với pagination
     */
    public function getPendingProducts($page = 1, $limit = 20) 
    {
        try {
            $offset = ($page - 1) * $limit;
            
            // Chỉ select các cột cần thiết thay vì SELECT *
            $sql = "SELECT 
                        p.id, p.title, p.price, p.condition_status, p.created_at, p.status,
                        u.username, u.full_name
                    FROM products p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.status = ? 
                    ORDER BY p.created_at DESC
                    LIMIT ? OFFSET ?";
                    
            $stmt = $this->db->query($sql, ['pending', $limit, $offset]);
            
            // Đếm tổng số records để pagination
            $countSql = "SELECT COUNT(*) as total FROM products WHERE status = ?";
            $countStmt = $this->db->query($countSql, ['pending']);
            $total = $countStmt->fetch()['total'];
            
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
            error_log("Error getting pending products: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể lấy danh sách sản phẩm.'
            ];
        }
    }
    
    /**
     * Cập nhật trạng thái sản phẩm với validation
     */
    public function updateProductStatus($id, $status) 
    {
        // Validate input
        $validation = $this->validateStatusUpdate($id, $status);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Kiểm tra product tồn tại
            $checkSql = "SELECT id, status, user_id FROM products WHERE id = ?";
            $checkStmt = $this->db->query($checkSql, [$id]);
            $product = $checkStmt->fetch();
            
            if (!$product) {
                throw new Exception("Sản phẩm không tồn tại.");
            }
            
            // Cập nhật status
            $updateSql = "UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->query($updateSql, [$status, $id]);
            
            // Log activity nếu cần
            $this->logProductStatusChange($product['user_id'], $id, $product['status'], $status);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Cập nhật trạng thái sản phẩm thành công.'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating product status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái sản phẩm.'
            ];
        }
    }
    
    /**
     * Xóa sản phẩm với validation
     */
    public function deleteProduct($product_id) 
    {
        // Validate input
        if (!is_numeric($product_id) || $product_id <= 0) {
            return [
                'success' => false,
                'message' => 'ID sản phẩm không hợp lệ.'
            ];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Kiểm tra sản phẩm tồn tại và có thể xóa không
            $checkSql = "SELECT id, name, status FROM products WHERE id = ?";
            $checkStmt = $this->db->query($checkSql, [$product_id]);
            $product = $checkStmt->fetch();
            
            if (!$product) {
                throw new Exception("Sản phẩm không tồn tại.");
            }
            
            // Kiểm tra xem có order liên quan không
            $orderCheckSql = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
            $orderStmt = $this->db->query($orderCheckSql, [$product_id]);
            $orderCount = $orderStmt->fetch()['count'];
            
            if ($orderCount > 0) {
                throw new Exception("Không thể xóa sản phẩm này vì đã có đơn hàng liên quan.");
            }
            
            // Xóa product images trước
            $deleteImagesSql = "DELETE FROM product_images WHERE product_id = ?";
            $this->db->query($deleteImagesSql, [$product_id]);
            
            // Xóa product
            $deleteSql = "DELETE FROM products WHERE id = ?";
            $deleteStmt = $this->db->query($deleteSql, [$product_id]);
            
            if ($deleteStmt->rowCount() === 0) {
                throw new Exception("Không thể xóa sản phẩm.");
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Xóa sản phẩm thành công.'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error deleting product: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Lấy thông tin chi tiết sản phẩm
     */
    public function getProductById($id) 
    {
        try {
            $sql = "SELECT 
                        p.*, 
                        u.username, u.full_name,
                        c.name as category_name
                    FROM products p
                    LEFT JOIN users u ON p.user_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?";
                    
            $stmt = $this->db->query($sql, [$id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại.'
                ];
            }
            
            return [
                'success' => true,
                'data' => $product
            ];
            
        } catch (Exception $e) {
            error_log("Error getting product by ID: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể lấy thông tin sản phẩm.'
            ];
        }
    }
    
    /**
     * Toggle featured status của sản phẩm
     */
    public function toggleFeaturedStatus($id) 
    {
        // Validate input
        if (!is_numeric($id) || $id <= 0) {
            return [
                'success' => false,
                'message' => 'ID sản phẩm không hợp lệ.'
            ];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Kiểm tra product tồn tại
            $checkSql = "SELECT id, featured, status FROM products WHERE id = ?";
            $checkStmt = $this->db->query($checkSql, [$id]);
            $product = $checkStmt->fetch();
            
            if (!$product) {
                throw new Exception("Sản phẩm không tồn tại.");
            }
            
            // Chỉ cho phép toggle featured với sản phẩm active
            if ($product['status'] !== 'active') {
                throw new Exception("Chỉ có thể đặt nổi bật cho sản phẩm đã được duyệt.");
            }
            
            // Toggle featured status
            $newFeatured = $product['featured'] ? 0 : 1;
            $updateSql = "UPDATE products SET featured = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->query($updateSql, [$newFeatured, $id]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Không thể cập nhật trạng thái nổi bật.");
            }
            
            $this->db->commit();
            
            $statusText = $newFeatured ? 'đã được đặt làm sản phẩm nổi bật' : 'đã được bỏ khỏi danh sách nổi bật';
            
            return [
                'success' => true,
                'message' => "Sản phẩm $statusText thành công.",
                'new_featured_status' => $newFeatured
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error toggling featured status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate dữ liệu update status
     */
    private function validateStatusUpdate($id, $status) 
    {
        if (!is_numeric($id) || $id <= 0) {
            return [
                'valid' => false,
                'message' => 'ID sản phẩm không hợp lệ.'
            ];
        }
        
        $allowedStatuses = ['pending', 'active', 'rejected', 'sold', 'inactive'];
        if (!in_array($status, $allowedStatuses)) {
            return [
                'valid' => false,
                'message' => 'Trạng thái không hợp lệ.'
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Log thay đổi trạng thái sản phẩm
     */
    private function logProductStatusChange($user_id, $product_id, $old_status, $new_status) 
    {
        try {
            $logSql = "INSERT INTO product_status_logs (user_id, product_id, old_status, new_status, changed_at) 
                       VALUES (?, ?, ?, ?, NOW())";
            $this->db->query($logSql, [$user_id, $product_id, $old_status, $new_status]);
        } catch (Exception $e) {
            // Log error nhưng không throw exception để không làm fail main operation
            error_log("Error logging product status change: " . $e->getMessage());
        }
    }
}

// Legacy function wrappers để backward compatibility
function getPendingProducts($pdo) {
    $controller = new ProductController();
    $result = $controller->getPendingProducts();
    return $result['success'] ? $result['data'] : [];
}

function updateProductStatus($pdo, $id, $status) {
    $controller = new ProductController();
    $result = $controller->updateProductStatus($id, $status);
    return $result['success'];
}

function deleteProduct($pdo, $product_id) {
    $controller = new ProductController();
    $result = $controller->deleteProduct($product_id);
    return $result['success'];
}

function toggleFeaturedStatus($pdo, $id) {
    $controller = new ProductController();
    $result = $controller->toggleFeaturedStatus($id);
    return $result['success'];
}
?>