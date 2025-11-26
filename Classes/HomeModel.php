<?php
/**
 * Home Model
 * Handles data operations for the Home/Index page
 */

require_once __DIR__ . '/../settings/db_class.php';

class HomeModel extends db_connection
{
    /**
     * Get featured wellness tips (articles)
     * @param int $limit Number of articles to fetch
     * @return array Featured articles
     */
    public function getFeaturedArticles($limit = 3)
    {
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT a.article_id, a.article_title, a.article_author, a.date_added,
                       c.cat_name as category_name
                FROM articles a
                LEFT JOIN category c ON a.article_cat = c.cat_id
                ORDER BY a.date_added DESC
                LIMIT " . (int)$limit;
        
        $articles = $this->db_fetch_all($sql);
        
        error_log("HomeModel getFeaturedArticles - SQL: " . $sql);
        error_log("HomeModel getFeaturedArticles - Result count: " . (is_array($articles) ? count($articles) : 'not array'));
        
        if (!$articles || !is_array($articles)) {
            error_log("HomeModel getFeaturedArticles - No articles found or query failed");
            return [];
        }
        
        $formatted = [];
        foreach ($articles as $article) {
            // Calculate read time (estimate: 200 words per minute)
            $readTime = '5 min read'; // Default, can be calculated from content if available
            
            $formatted[] = [
                'title' => $article['article_title'],
                'category' => $article['category_name'] ?? 'Uncategorized',
                'author' => $article['article_author'],
                'image' => 'uploads/placeholder.jpg', // Default placeholder
                'readTime' => $readTime,
                'article_id' => (int)$article['article_id']
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get top wellness products based on quantity sold
     * @param int $limit Number of products to fetch
     * @return array Top products
     */
    public function getTopProducts($limit = 4)
    {
        if (!$this->db_connect()) {
            return [];
        }
        
        // Get top products by total quantity sold from orderdetails
        $sql = "SELECT p.product_id, p.product_title, p.product_price, p.product_image,
                       v.vendor_name,
                       SUM(od.qty) as total_quantity_sold
                FROM customer_products p
                LEFT JOIN order_details od ON p.product_id = od.product_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                GROUP BY p.product_id, p.product_title, p.product_price, p.product_image, v.vendor_name
                HAVING total_quantity_sold > 0
                ORDER BY total_quantity_sold DESC
                LIMIT " . (int)$limit;
        
        $products = $this->db_fetch_all($sql);
        
        error_log("HomeModel getTopProducts - SQL: " . $sql);
        error_log("HomeModel getTopProducts - Result count: " . (is_array($products) ? count($products) : 'not array'));
        
        if (!$products || !is_array($products)) {
            error_log("HomeModel getTopProducts - No products found, trying fallback");
            // If no products have been sold yet, get products by date_added
            $fallback_sql = "SELECT p.product_id, p.product_title, p.product_price, p.product_image,
                                    v.vendor_name
                             FROM customer_products p
                             LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                             ORDER BY p.date_added DESC
                             LIMIT " . (int)$limit;
            $products = $this->db_fetch_all($fallback_sql);
        }
        
        if (!$products || !is_array($products)) {
            return [];
        }
        
        $formatted = [];
        foreach ($products as $product) {
            $formatted[] = [
                'name' => $product['product_title'],
                'vendor' => $product['vendor_name'] ?? 'Unknown Vendor',
                'price' => '₵' . number_format((float)$product['product_price'], 2),
                'rating' => 4.5, // Default rating (can be calculated from reviews if available)
                'image' => !empty($product['product_image']) ? $product['product_image'] : 'uploads/placeholder.jpg',
                'verified' => true, // All products are considered verified
                'product_id' => (int)$product['product_id']
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get upcoming events (workshops)
     * @param int $limit Number of workshops to fetch
     * @return array Upcoming workshops
     */
    public function getUpcomingWorkshops($limit = 3)
    {
        if (!$this->db_connect()) {
            return [];
        }
        
        // Get upcoming workshops (workshop_date >= today)
        $sql = "SELECT workshop_id, workshop_title, workshop_date, workshop_time, 
                       workshop_type, location
                FROM workshops
                WHERE workshop_date >= CURDATE()
                ORDER BY workshop_date ASC, workshop_time ASC
                LIMIT " . (int)$limit;
        
        $workshops = $this->db_fetch_all($sql);
        
        error_log("HomeModel getUpcomingWorkshops - SQL: " . $sql);
        error_log("HomeModel getUpcomingWorkshops - Result count: " . (is_array($workshops) ? count($workshops) : 'not array'));
        
        if (!$workshops || !is_array($workshops)) {
            error_log("HomeModel getUpcomingWorkshops - No workshops found or query failed");
            return [];
        }
        
        $formatted = [];
        foreach ($workshops as $workshop) {
            // Count attendees (from workshop_registrations)
            $attendees_sql = "SELECT COUNT(*) as count 
                             FROM workshop_registrations 
                             WHERE workshop_id = " . (int)$workshop['workshop_id'];
            $attendees_result = $this->db_fetch_one($attendees_sql);
            $attendees = $attendees_result && isset($attendees_result['count']) ? (int)$attendees_result['count'] : 0;
            
            // Format date
            $date = date('M d, Y', strtotime($workshop['workshop_date']));
            
            // Format time
            $time = date('g:i A', strtotime($workshop['workshop_time']));
            
            // Determine type display
            $type = ucfirst($workshop['workshop_type'] ?? 'Workshop');
            
            $formatted[] = [
                'title' => $workshop['workshop_title'],
                'date' => $date,
                'time' => $time,
                'type' => $type,
                'attendees' => $attendees,
                'workshop_id' => (int)$workshop['workshop_id']
            ];
        }
        
        return $formatted;
    }
}
?>

