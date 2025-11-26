<?php
/**
 * Shop Model
 * Handles data operations for the Shop page using database
 */

require_once __DIR__ . '/../settings/db_class.php';

class ShopModel extends db_connection
{
    /**
     * View all products with category and vendor information
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return array All products
     */
    public function view_all_products($limit = null, $offset = 0)
    {
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM customer_products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                ORDER BY p.date_added DESC";
        
        if ($limit !== null) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Search products by title, description, or keywords
     * Uses efficient LIKE queries with proper indexing considerations
     * @param string $query Search query
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return array Matching products
     */
    public function search_products($query, $limit = null, $offset = 0)
    {
        $query = $this->escape_string(trim($query));
        
        if (empty($query)) {
            return [];
        }
        
        // Search in title, description, and keywords
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM customer_products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                WHERE p.product_title LIKE '%$query%' 
                   OR p.product_desc LIKE '%$query%' 
                   OR p.product_keywords LIKE '%$query%'
                ORDER BY 
                    CASE 
                        WHEN p.product_title LIKE '%$query%' THEN 1
                        WHEN p.product_keywords LIKE '%$query%' THEN 2
                        ELSE 3
                    END,
                    p.date_added DESC";
        
        if ($limit !== null) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Filter products by category
     * @param int $cat_id Category ID
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return array Filtered products
     */
    public function filter_products_by_category($cat_id, $limit = null, $offset = 0)
    {
        $cat_id = (int)$cat_id;
        
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM customer_products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                WHERE p.product_cat = $cat_id
                ORDER BY p.date_added DESC";
        
        if ($limit !== null) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Filter products by vendor
     * @param int $vendor_id Vendor ID
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return array Filtered products
     */
    public function filter_products_by_vendors($vendor_id, $limit = null, $offset = 0)
    {
        $vendor_id = (int)$vendor_id;
        
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM customer_products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                WHERE p.product_vendor = $vendor_id
                ORDER BY p.date_added DESC";
        
        if ($limit !== null) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * View a single product by ID
     * @param int $id Product ID
     * @return array|false Product data or false if not found
     */
    public function view_single_product($id)
    {
        $id = (int)$id;
        
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM customer_products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                WHERE p.product_id = $id";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Composite search with multiple filters
     * Supports filtering by category, vendor, price range, and search query
     * @param array $filters Filter options
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return array Filtered products
     */
    public function composite_search($filters = [], $limit = null, $offset = 0)
    {
        $where = [];
        
        // Category filter
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $cat_id = (int)$filters['category'];
            $where[] = "p.product_cat = $cat_id";
        }
        
        // Vendor filter
        if (!empty($filters['vendor']) && $filters['vendor'] !== 'all') {
            $vendor_id = (int)$filters['vendor'];
            $where[] = "p.product_vendor = $vendor_id";
        }
        
        // Price range filter
        if (!empty($filters['min_price'])) {
            $min_price = floatval($filters['min_price']);
            $where[] = "p.product_price >= $min_price";
        }
        
        if (!empty($filters['max_price'])) {
            $max_price = floatval($filters['max_price']);
            $where[] = "p.product_price <= $max_price";
        }
        
        // Search query
        if (!empty($filters['search'])) {
            $search = $this->escape_string(trim($filters['search']));
            $where[] = "(p.product_title LIKE '%$search%' OR p.product_desc LIKE '%$search%' OR p.product_keywords LIKE '%$search%')";
        }
        
        // Keyword search
        if (!empty($filters['keyword'])) {
            $keyword = $this->escape_string(trim($filters['keyword']));
            $where[] = "p.product_keywords LIKE '%$keyword%'";
        }
        
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM customer_products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Sorting
        $sortBy = $filters['sort'] ?? 'date';
        switch ($sortBy) {
            case 'price_low':
                $sql .= " ORDER BY p.product_price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY p.product_price DESC";
                break;
            case 'name':
                $sql .= " ORDER BY p.product_title ASC";
                break;
            default:
                $sql .= " ORDER BY p.date_added DESC";
        }
        
        if ($limit !== null) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Get total count of products (for pagination)
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function get_product_count($filters = [])
    {
        $where = [];
        
        // Apply same filters as composite_search
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $cat_id = (int)$filters['category'];
            $where[] = "p.product_cat = $cat_id";
        }
        
        if (!empty($filters['vendor']) && $filters['vendor'] !== 'all') {
            $vendor_id = (int)$filters['vendor'];
            $where[] = "p.product_vendor = $vendor_id";
        }
        
        if (!empty($filters['min_price'])) {
            $min_price = floatval($filters['min_price']);
            $where[] = "p.product_price >= $min_price";
        }
        
        if (!empty($filters['max_price'])) {
            $max_price = floatval($filters['max_price']);
            $where[] = "p.product_price <= $max_price";
        }
        
        if (!empty($filters['search'])) {
            $search = $this->escape_string(trim($filters['search']));
            $where[] = "(p.product_title LIKE '%$search%' OR p.product_desc LIKE '%$search%' OR p.product_keywords LIKE '%$search%')";
        }
        
        if (!empty($filters['keyword'])) {
            $keyword = $this->escape_string(trim($filters['keyword']));
            $where[] = "p.product_keywords LIKE '%$keyword%'";
        }
        
        $sql = "SELECT COUNT(*) as total FROM customer_products p";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $result = $this->db_fetch_one($sql);
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Get all categories
     * @return array All categories
     */
    public function get_categories()
    {
        $sql = "SELECT * FROM category ORDER BY cat_name ASC";
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Get all vendors
     * @return array All vendors
     */
    public function get_vendors()
    {
        $sql = "SELECT * FROM vendors ORDER BY vendor_name ASC";
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Get price range (min and max) for all products
     * @return array Price range
     */
    public function get_price_range()
    {
        $sql = "SELECT MIN(product_price) as min_price, MAX(product_price) as max_price FROM customer_products";
        $result = $this->db_fetch_one($sql);
        
        return [
            'min' => $result ? (float)$result['min_price'] : 0,
            'max' => $result ? (float)$result['max_price'] : 500
        ];
    }
}
?>
