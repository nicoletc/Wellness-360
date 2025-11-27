<?php
/**
 * Wellness Hub Model
 * Handles data operations for the Wellness Hub page, interacting with the database.
 */

require_once __DIR__ . '/../settings/db_class.php';

class WellnessHubModel extends db_connection {
    
    /**
     * Get all categories from database
     * @return array Categories
     */
    public function get_categories() {
        $sql = "SELECT cat_id, cat_name FROM category ORDER BY cat_name ASC";
        $categories = $this->db_fetch_all($sql);
        
        // Format as associative array with 'all' option
        $formatted = ['all' => 'All'];
        if ($categories && is_array($categories)) {
            foreach ($categories as $cat) {
                $formatted[$cat['cat_id']] = $cat['cat_name'];
            }
        }
        
        return $formatted;
    }
    
    /**
     * Get all articles with category information and view counts
     * @param int|null $category_id Category ID to filter by (null for all)
     * @param string $search_query Search query to filter by title/author
     * @return array Articles
     */
    public function get_articles($category_id = null, $search_query = '') {
        $where_clauses = [];
        
        // Category filter
        if ($category_id !== null && $category_id !== 'all' && is_numeric($category_id)) {
            $category_id = (int)$category_id;
            $where_clauses[] = "a.article_cat = $category_id";
        }
        
        // Search filter
        if (!empty($search_query)) {
            // Ensure database connection for escape_string
            if (!$this->db_connect()) {
                return [];
            }
            $search = $this->escape_string("%" . $search_query . "%");
            $where_clauses[] = "(a.article_title LIKE '$search' OR a.article_author LIKE '$search')";
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = " WHERE " . implode(" AND ", $where_clauses);
        }
        
        $sql = "SELECT a.article_id, 
                       a.article_title, 
                       a.article_author, 
                       a.article_cat, 
                       a.article_image,
                       a.date_added,
                       CASE WHEN a.article_body IS NOT NULL AND LENGTH(a.article_body) > 0 THEN 1 ELSE 0 END as has_pdf,
                       COALESCE(COUNT(DISTINCT av.view_id), 0) as view_count,
                       c.cat_name 
                FROM articles a
                LEFT JOIN category c ON a.article_cat = c.cat_id
                LEFT JOIN article_views av ON a.article_id = av.article_id
                $where_sql
                GROUP BY a.article_id, a.article_title, a.article_author, a.article_cat, a.article_image, a.date_added, c.cat_name
                ORDER BY a.date_added DESC";
        
        $results = $this->db_fetch_all($sql);
        return $results ? $results : [];
    }
    
    /**
     * Get a single article by ID
     * @param int $article_id Article ID
     * @return array|false Article data or false if not found
     */
    public function get_article_by_id($article_id) {
        $article_id = (int)$article_id;
        
        $sql = "SELECT a.article_id, 
                       a.article_title, 
                       a.article_author, 
                       a.article_cat, 
                       a.article_image,
                       a.date_added,
                       CASE WHEN a.article_body IS NOT NULL AND LENGTH(a.article_body) > 0 THEN 1 ELSE 0 END as has_pdf,
                       COALESCE(COUNT(DISTINCT av.view_id), 0) as view_count,
                       c.cat_name 
                FROM articles a
                LEFT JOIN category c ON a.article_cat = c.cat_id
                LEFT JOIN article_views av ON a.article_id = av.article_id
                WHERE a.article_id = $article_id
                GROUP BY a.article_id, a.article_title, a.article_author, a.article_cat, a.article_image, a.date_added, c.cat_name";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get article PDF binary data
     * @param int $article_id Article ID
     * @return string|false PDF binary data or false if not found
     */
    public function get_article_pdf($article_id) {
        $article_id = (int)$article_id;
        $sql = "SELECT article_body FROM articles WHERE article_id = $article_id";
        $result = $this->db_fetch_one($sql);
        
        if ($result && !empty($result['article_body'])) {
            return $result['article_body'];
        }
        
        return false;
    }
    
    /**
     * Record an article view
     * @param int $article_id Article ID
     * @param int|null $user_id User ID (null for guests)
     * @param string $ip_address IP address of viewer
     * @return bool Success status
     */
    public function record_article_view($article_id, $user_id = null, $ip_address = '') {
        $article_id = (int)$article_id;
        
        // Ensure database connection for escape_string
        if (!$this->db_connect()) {
            return false;
        }
        $ip_address = $this->escape_string($ip_address);
        
        // Check if this view already exists (same user/IP within last hour to prevent spam)
        $check_sql = "SELECT view_id FROM article_views 
                      WHERE article_id = $article_id 
                      AND (user_id " . ($user_id ? "= " . (int)$user_id : "IS NULL") . ")
                      AND ip_address = '$ip_address'
                      AND viewed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                      LIMIT 1";
        
        $existing = $this->db_fetch_one($check_sql);
        
        if ($existing) {
            // View already recorded recently, skip
            return true;
        }
        
        // Insert new view
        $user_id_sql = $user_id ? (int)$user_id : 'NULL';
        $sql = "INSERT INTO article_views (article_id, user_id, ip_address, viewed_at) 
                VALUES ($article_id, $user_id_sql, '$ip_address', NOW())";
        
        return $this->db_query($sql);
    }
    
    /**
     * Get view count for an article
     * @param int $article_id Article ID
     * @return int View count
     */
    public function get_article_view_count($article_id) {
        $article_id = (int)$article_id;
        $sql = "SELECT COUNT(view_id) as view_count FROM article_views WHERE article_id = $article_id";
        $result = $this->db_fetch_one($sql);
        return (int)($result['view_count'] ?? 0);
    }
}
?>
