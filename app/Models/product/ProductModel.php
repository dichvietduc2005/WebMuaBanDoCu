<?php
/**
 * ProductModel - Thực sự là Model để tương tác với database
 * Tách biệt logic database khỏi controller logic
 */

class ProductModel 
{
    private $db;
    
    public function __construct($database = null) 
    {
        $this->db = $database ?: Database::getInstance();
    }
    
    /**
     * Tạo sản phẩm mới
     */
    public function createProduct($data) 
    {
        try {
                    $sql = "INSERT INTO products (
                    title, description, price, category_id, condition_status, 
                    user_id, stock_quantity, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())";
                
        $stmt = $this->db->query($sql, [
            $data['title'],
                $data['description'],
                $data['price'],
                $data['category_id'],
                $data['condition_status'],
                $data['user_id'],
                $data['stock_quantity'] ?? 1
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error creating product: " . $e->getMessage());
            throw new Exception("Không thể tạo sản phẩm mới.");
        }
    }
    
    /**
     * Lấy sản phẩm theo ID
     */
    public function getById($id) 
    {
        try {
            $sql = "SELECT p.*, u.username, u.full_name, c.name as category_name 
                    FROM products p
                    LEFT JOIN users u ON p.user_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?";
                    
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Error getting product by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Lấy sản phẩm theo user ID
     */
    public function getByUserId($user_id, $page = 1, $limit = 12) 
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.user_id = ?
                    ORDER BY p.created_at DESC
                    LIMIT ? OFFSET ?";
                    
            $stmt = $this->db->query($sql, [$user_id, $limit, $offset]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error getting products by user ID: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm theo category
     */
    public function getByCategory($category_id, $page = 1, $limit = 12, $filters = []) 
    {
        try {
            $offset = ($page - 1) * $limit;
            $params = [$category_id];
            
            $sql = "SELECT p.*, u.username, c.name as category_name 
                    FROM products p
                    JOIN users u ON p.user_id = u.id
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.category_id = ? AND p.status = 'active'";
            
            // Apply filters
            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $filters['max_price'];
            }
            
            if (!empty($filters['condition'])) {
                $sql .= " AND p.condition_status = ?";
                $params[] = $filters['condition'];
            }
            
            $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error getting products by category: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search sản phẩm
     */
    public function search($keyword, $page = 1, $limit = 12, $filters = []) 
    {
        try {
            $offset = ($page - 1) * $limit;
            $searchTerm = "%{$keyword}%";
            $params = [$searchTerm, $searchTerm];
            
            $sql = "SELECT p.*, u.username, c.name as category_name 
                    FROM products p
                    JOIN users u ON p.user_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE (p.title LIKE ? OR p.description LIKE ?) 
                    AND p.status = 'active'";
            
            // Apply filters
            if (!empty($filters['category_id'])) {
                $sql .= " AND p.category_id = ?";
                $params[] = $filters['category_id'];
            }
            
            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $filters['max_price'];
            }
            
            if (!empty($filters['condition'])) {
                $sql .= " AND p.condition_status = ?";
                $params[] = $filters['condition'];
            }
            
            $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error searching products: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cập nhật sản phẩm
     */
    public function update($id, $data) 
    {
        try {
                    $sql = "UPDATE products SET 
                    title = ?, description = ?, price = ?, category_id = ?,
                    condition_status = ?, stock_quantity = ?, updated_at = NOW()
                WHERE id = ?";
                
        $stmt = $this->db->query($sql, [
            $data['title'],
                $data['description'],
                $data['price'],
                $data['category_id'],
                $data['condition_status'],
                $data['stock_quantity'] ?? 1,
                $id
            ]);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Error updating product: " . $e->getMessage());
            throw new Exception("Không thể cập nhật sản phẩm.");
        }
    }
    
    /**
     * Cập nhật trạng thái sản phẩm
     */
    public function updateStatus($id, $status) 
    {
        try {
            $sql = "UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->query($sql, [$status, $id]);
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Error updating product status: " . $e->getMessage());
            throw new Exception("Không thể cập nhật trạng thái sản phẩm.");
        }
    }
    
    /**
     * Cập nhật stock quantity
     */
    public function updateStock($id, $quantity) 
    {
        try {
            $sql = "UPDATE products SET stock_quantity = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->query($sql, [$quantity, $id]);
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Error updating product stock: " . $e->getMessage());
            throw new Exception("Không thể cập nhật số lượng tồn kho.");
        }
    }
    
    /**
     * Xóa sản phẩm (soft delete)
     */
    public function softDelete($id) 
    {
        try {
            $sql = "UPDATE products SET status = 'deleted', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Error soft deleting product: " . $e->getMessage());
            throw new Exception("Không thể xóa sản phẩm.");
        }
    }
    
    /**
     * Xóa sản phẩm hoàn toàn
     */
    public function hardDelete($id) 
    {
        try {
            $this->db->beginTransaction();
            
            // Xóa product images trước
            $deleteImagesSql = "DELETE FROM product_images WHERE product_id = ?";
            $this->db->query($deleteImagesSql, [$id]);
            
            // Xóa product
            $deleteProductSql = "DELETE FROM products WHERE id = ?";
            $stmt = $this->db->query($deleteProductSql, [$id]);
            
            $this->db->commit();
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error hard deleting product: " . $e->getMessage());
            throw new Exception("Không thể xóa sản phẩm.");
        }
    }
    
    /**
     * Lấy sản phẩm featured/trending
     */
    public function getFeatured($limit = 8) 
    {
        try {
            $sql = "SELECT p.*, u.username, c.name as category_name 
                    FROM products p
                    JOIN users u ON p.user_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'
                    ORDER BY p.created_at DESC
                    LIMIT ?";
                    
            $stmt = $this->db->query($sql, [$limit]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error getting featured products: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Đếm số lượng sản phẩm theo điều kiện
     */
    public function countByCondition($conditions = []) 
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
            $params = [];
            
            if (!empty($conditions['status'])) {
                $sql .= " AND status = ?";
                $params[] = $conditions['status'];
            }
            
            if (!empty($conditions['user_id'])) {
                $sql .= " AND user_id = ?";
                $params[] = $conditions['user_id'];
            }
            
            if (!empty($conditions['category_id'])) {
                $sql .= " AND category_id = ?";
                $params[] = $conditions['category_id'];
            }
            
            $stmt = $this->db->query($sql, $params);
            $result = $stmt->fetch();
            return $result['total'];
            
        } catch (Exception $e) {
            error_log("Error counting products: " . $e->getMessage());
            return 0;
        }
    }
}