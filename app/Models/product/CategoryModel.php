<?php
/**
 * CategoryModel - Interact with categories table
 */

class CategoryModel 
{
    private $db;
    private $pdo;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    /**
     * Get all categories sorted by name
     */
    public function getAll() 
    {
        $sql = "SELECT * FROM categories ORDER BY name";
        return $this->db->query($sql);
    }

    /**
     * Get all active categories sorted by name
     * Fallback to all categories if status filter returns empty
     */
    public function getAllActive() 
    {
        try {
            // First try with status filter
            $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
            $result = $this->db->query($sql);
            
            // If no results, try without status filter (table might not have status column)
            if (empty($result)) {
                $sql = "SELECT * FROM categories ORDER BY name";
                $result = $this->db->query($sql);
            }
            
            return $result;
        } catch (Exception $e) {
            // If query fails (e.g., status column doesn't exist), get all
            error_log("CategoryModel::getAllActive error: " . $e->getMessage());
            try {
                $sql = "SELECT * FROM categories ORDER BY name";
                return $this->db->query($sql);
            } catch (Exception $e2) {
                error_log("CategoryModel::getAllActive fallback error: " . $e2->getMessage());
                return [];
            }
        }
    }

    /**
     * Get category by slug
     */
    public function getBySlug($slug)
    {
        $sql = "SELECT * FROM categories WHERE slug = ? AND status = 'active' LIMIT 1";
        return $this->db->queryOne($sql, [$slug]);
    }
}
