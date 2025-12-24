<?php
/**
 * ProductModel - Thực sự là Model để tương tác với database
 * Tách biệt logic database khỏi controller logic
 */

class ProductModel 
{
    private $db;
    private $pdo;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    /**
     * Lấy danh sách sản phẩm với cache
     */
    public function getProducts($limit = 12, $offset = 0, $featured = null, $categoryId = null) 
    {
        $params = [];
        
        // Base query - require active status AND stock_quantity > 0 (hide out of stock items)
        $sql = "SELECT p.*, pi.image_path, c.name as category_name 
                FROM products p 
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.stock_quantity > 0";
        
        if ($featured !== null) {
            $sql .= " AND p.featured = ?";
            $params[] = $featured ? 1 : 0;
        }
        
        if ($categoryId !== null) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        try {
            $result = $this->db->query($sql, $params, false); // Disable cache for fresh data
            
            // If featured query returns empty, fallback to all products
            if ($featured === true && empty($result)) {
                error_log("ProductModel: No featured products, falling back to all products");
                return $this->getProducts($limit, $offset, null, $categoryId);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("ProductModel::getProducts error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy chi tiết sản phẩm với cache
     */
    public function getProductById($id) 
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as seller_name, u.email as seller_email
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = ? AND p.status = 'active'";
                
        return $this->db->queryOne($sql, [$id]);
    }
    
    /**
     * Lấy hình ảnh sản phẩm với cache
     */
    public function getProductImages($productId) 
    {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC";
        return $this->db->query($sql, [$productId]);
    }
    
    /**
     * Lấy sản phẩm liên quan với cache
     */
    public function getRelatedProducts($categoryId, $productId, $limit = 4) 
    {
        $sql = "SELECT p.*, pi.image_path 
                FROM products p 
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' AND p.stock_quantity > 0
                ORDER BY p.created_at DESC 
                LIMIT ?";
                
        return $this->db->query($sql, [$categoryId, $productId, $limit]);
    }
    
    /**
     * Đếm tổng số sản phẩm với cache
     */
    public function countProducts($featured = null, $categoryId = null) 
    {
        $params = [];
        $sql = "SELECT COUNT(*) FROM products WHERE status = 'active' AND stock_quantity > 0";
        
        if ($featured !== null) {
            $sql .= " AND featured = ?";
            $params[] = $featured ? 1 : 0;
        }
        
        if ($categoryId !== null) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        return $this->db->queryValue($sql, $params);
    }
    
    /**
     * Thêm sản phẩm mới (không cache)
     */
    public function createProduct($data) 
    {
        // Xóa cache khi thêm sản phẩm mới
        $this->db->clearCache();
        
        $sql = "INSERT INTO products (user_id, category_id, title, description, price, condition_status, 
                stock_quantity, location, featured, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                
        $this->db->execute($sql, [
            $data['user_id'],
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['price'],
            $data['condition_status'],
            $data['stock_quantity'],
            $data['location'],
            $data['featured'] ?? 0,
            $data['status'] ?? 'active'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Cập nhật sản phẩm (không cache)
     */
    public function updateProduct($id, $data) 
    {
        // Xóa cache khi cập nhật sản phẩm
        $this->db->clearCache();
        
        $sql = "UPDATE products SET 
                category_id = ?, 
                title = ?, 
                description = ?, 
                price = ?, 
                condition_status = ?, 
                stock_quantity = ?, 
                location = ?, 
                featured = ?, 
                status = ?, 
                updated_at = NOW() 
                WHERE id = ?";
                
        return $this->db->execute($sql, [
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['price'],
            $data['condition_status'],
            $data['stock_quantity'],
            $data['location'],
            $data['featured'] ?? 0,
            $data['status'] ?? 'active',
            $id
        ]);
    }
    
    /**
     * Xóa sản phẩm (không cache)
     */
    public function deleteProduct($id) 
    {
        // Xóa cache khi xóa sản phẩm
        $this->db->clearCache();
        
        $sql = "UPDATE products SET status = 'deleted', updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Thêm ảnh sản phẩm (không cache)
     */
    public function addProductImage($productId, $imagePath, $isPrimary = 0) 
    {
        // Xóa cache khi thêm ảnh
        $this->invalidateProductCache($productId);
        
        $sql = "INSERT INTO product_images (product_id, image_path, is_primary, created_at) 
                VALUES (?, ?, ?, NOW())";
                
        return $this->db->execute($sql, [$productId, $imagePath, $isPrimary]);
    }
    
    /**
     * Cập nhật số lượng sản phẩm (không cache)
     */
    public function updateStock($id, $quantity) 
    {
        // Xóa cache khi cập nhật số lượng
        $this->invalidateProductCache($id);
        
        $sql = "UPDATE products SET stock_quantity = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$quantity, $id]);
    }
    
    /**
     * Xóa cache liên quan đến sản phẩm
     */
    private function invalidateProductCache($productId) 
    {
        // Xóa cache của sản phẩm cụ thể
        $this->db->invalidateCache("SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as seller_name, u.email as seller_email
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = ? AND p.status = 'active'", [$productId]);
                
        // Xóa cache của hình ảnh sản phẩm
        $this->db->invalidateCache("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC", [$productId]);
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
    /**
     * Lấy thống kê đánh giá sản phẩm
     */
    public function getReviewStats($productId) 
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_reviews,
                        AVG(rating) as average_rating,
                        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star_5,
                        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star_4,
                        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star_3,
                        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star_2,
                        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star_1
                    FROM review_products
                    WHERE product_id = ?";
                    
            return $this->db->queryOne($sql, [$productId]);
            
        } catch (Exception $e) {
            error_log("Error getting review stats: " . $e->getMessage());
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'star_5' => 0, 'star_4' => 0, 'star_3' => 0, 'star_2' => 0, 'star_1' => 0
            ];
        }
    }
}