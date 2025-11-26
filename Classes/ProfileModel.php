<?php
/**
 * Profile Model
 * Handles data operations for the Profile page
 */

require_once __DIR__ . '/../settings/db_class.php';

class ProfileModel extends db_connection {
    private $dataFile;
    
    public function __construct() {
        $this->dataFile = __DIR__ . '/../data/profile_data.php';
    }
    
    /**
     * Load profile page data
     */
    public function loadData() {
        require_once $this->dataFile;
        
        return [
            'userStats' => $userStats ?? [],
            'userBadges' => $userBadges ?? [],
            'activeChallenges' => $activeChallenges ?? [],
            'recommendedContent' => $recommendedContent ?? [],
            'orders' => $orders ?? [],
            'favorites' => $favorites ?? [],
            'placeholderImage' => $placeholderImage ?? 'uploads/placeholder.jpg',
            'placeholderAvatar' => $placeholderAvatar ?? 'uploads/placeholder_avatar.jpg',
        ];
    }
    
    /**
     * Get user profile data from database
     * @param int $customer_id Customer ID
     * @return array|false Customer data or false
     */
    public function getUserProfile($customer_id) {
        try {
            $customer_id = (int)$customer_id;
            $sql = "SELECT customer_id, customer_name, customer_email, customer_contact, customer_image, user_role, date_joined 
                    FROM customers WHERE customer_id = $customer_id";
            
            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get user statistics from database
     * @param int $customer_id Customer ID
     * @return array User statistics
     */
    public function getUserStats($customer_id) {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [
                'articlesRead' => 0,
                'ordersPlaced' => 0,
                'favorites' => 0,
            ];
        }
        
        $stats = [
            'articlesRead' => 0,
            'ordersPlaced' => 0,
            'favorites' => 0,
        ];
        
        // Get articles read count (from article_views)
        $articles_sql = "SELECT COUNT(DISTINCT article_id) as count 
                        FROM article_views 
                        WHERE user_id = $customer_id";
        $articles_result = $this->db_fetch_one($articles_sql);
        if ($articles_result && isset($articles_result['count'])) {
            $stats['articlesRead'] = (int)$articles_result['count'];
        }
        
        // Get orders placed count
        $orders_sql = "SELECT COUNT(*) as count 
                      FROM customer_orders 
                      WHERE customer_id = $customer_id";
        $orders_result = $this->db_fetch_one($orders_sql);
        if ($orders_result && isset($orders_result['count'])) {
            $stats['ordersPlaced'] = (int)$orders_result['count'];
        }
        
        // Get wishlist count (from product_likes)
        $wishlist_sql = "SELECT COUNT(*) as count 
                         FROM product_likes 
                         WHERE customer_id = $customer_id";
        $wishlist_result = $this->db_fetch_one($wishlist_sql);
        if ($wishlist_result && isset($wishlist_result['count'])) {
            $stats['wishlist'] = (int)$wishlist_result['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get user orders from database
     * @param int $customer_id Customer ID
     * @return array Orders
     */
    public function getUserOrders($customer_id) {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT o.*, 
                       COUNT(od.product_id) as item_count,
                       SUM(p.product_price * od.qty) as total_amount
                FROM customer_orders o
                LEFT JOIN order_details od ON o.order_id = od.order_id
                LEFT JOIN customer_products p ON od.product_id = p.product_id
                WHERE o.customer_id = $customer_id
                GROUP BY o.order_id
                ORDER BY o.order_date DESC";
        
        $orders = $this->db_fetch_all($sql);
        
        if (!$orders || !is_array($orders)) {
            return [];
        }
        
        // Format orders for display
        $formatted = [];
        foreach ($orders as $order) {
            $formatted[] = [
                'order_id' => (int)$order['order_id'],
                'orderNumber' => $order['invoice_no'],
                'date' => date('F j, Y', strtotime($order['order_date'])),
                'status' => ucfirst($order['order_status']),
                'items' => (int)$order['item_count'],
                'total' => floatval($order['total_amount'] ?? 0),
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get user wishlist from database
     * @param int $customer_id Customer ID
     * @return array Wishlist (liked products)
     */
    public function getUserWishlist($customer_id) {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT pl.product_id, pl.created_at,
                       p.product_title, p.product_price, p.product_image
                FROM product_likes pl
                INNER JOIN customer_products p ON pl.product_id = p.product_id
                WHERE pl.customer_id = $customer_id
                ORDER BY pl.created_at DESC";
        
        $wishlist = $this->db_fetch_all($sql);
        
        if (!$wishlist || !is_array($wishlist)) {
            return [];
        }
        
        // Format wishlist for display
        $formatted = [];
        foreach ($wishlist as $item) {
            $formatted[] = [
                'product_id' => (int)$item['product_id'],
                'title' => $item['product_title'],
                'price' => floatval($item['product_price']),
                'image' => $item['product_image'] ?: 'uploads/placeholder.jpg',
                'date' => date('F j, Y', strtotime($item['created_at'])),
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get articles read by user from database
     * @param int $customer_id Customer ID
     * @return array Articles read
     */
    public function getUserArticlesRead($customer_id) {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT a.article_id,
                       a.article_title, 
                       a.article_author, 
                       a.date_added,
                       c.cat_name,
                       COUNT(av.view_id) as read_count,
                       MAX(av.viewed_at) as last_viewed_at
                FROM article_views av
                INNER JOIN articles a ON av.article_id = a.article_id
                LEFT JOIN category c ON a.article_cat = c.cat_id
                WHERE av.user_id = $customer_id
                GROUP BY a.article_id, a.article_title, a.article_author, a.date_added, c.cat_name
                ORDER BY last_viewed_at DESC";
        
        $articles = $this->db_fetch_all($sql);
        
        if (!$articles || !is_array($articles)) {
            return [];
        }
        
        // Format articles for display
        $formatted = [];
        foreach ($articles as $article) {
            $formatted[] = [
                'article_id' => (int)$article['article_id'],
                'title' => $article['article_title'],
                'author' => $article['article_author'],
                'category' => $article['cat_name'] ?? 'Uncategorized',
                'date_added' => date('F j, Y', strtotime($article['date_added'])),
                'read_count' => (int)$article['read_count'],
                'last_viewed_at' => date('F j, Y', strtotime($article['last_viewed_at'])),
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get recommended content based on user behavior
     * @param int $customer_id Customer ID
     * @return array Recommended items (products, articles, workshops)
     */
    public function getRecommendedContent($customer_id) {
        $customer_id = (int)$customer_id;
        $recommendations = [];
        
        if (!$this->db_connect()) {
            return [];
        }
        
        // Get user's top interests
        require_once __DIR__ . '/ActivityModel.php';
        $activityModel = new ActivityModel();
        $topInterests = $activityModel->getUserTopInterests($customer_id, 3);
        
        // If no interests yet, return empty (user needs to browse first)
        if (empty($topInterests)) {
            return [];
        }
        
        // Extract category IDs
        $category_ids = array_column($topInterests, 'category_id');
        $category_ids_str = implode(',', array_map('intval', $category_ids));
        
        // Get user's activity summary to understand preferences
        $activitySummary = $activityModel->getUserActivitySummary($customer_id, 30);
        
        // Get products user has viewed but not purchased
        $viewed_products_sql = "SELECT DISTINCT ua.content_id as product_id
                               FROM user_activity ua
                               LEFT JOIN customer_orders o ON o.customer_id = $customer_id
                               LEFT JOIN order_details od ON o.order_id = od.order_id AND od.product_id = ua.content_id
                               WHERE ua.customer_id = $customer_id
                               AND ua.content_type = 'product'
                               AND ua.category_id IN ($category_ids_str)
                               AND od.product_id IS NULL
                               ORDER BY ua.viewed_at DESC
                               LIMIT 3";
        $viewed_products = $this->db_fetch_all($viewed_products_sql);
        $viewed_product_ids = $viewed_products ? array_column($viewed_products, 'product_id') : [];
        
        // Recommend products from top interest categories (excluding already viewed)
        $products_sql = "SELECT p.product_id, p.product_title, p.product_price, p.product_image, p.product_desc,
                               c.cat_name
                        FROM customer_products p
                        INNER JOIN category c ON p.product_cat = c.cat_id
                        WHERE p.product_cat IN ($category_ids_str)
                        " . (!empty($viewed_product_ids) ? "AND p.product_id NOT IN (" . implode(',', array_map('intval', $viewed_product_ids)) . ")" : "") . "
                        AND p.stock > 0
                        ORDER BY p.date_added DESC
                        LIMIT 3";
        $products = $this->db_fetch_all($products_sql);
        
        // Format products
        foreach ($products as $product) {
            $recommendations[] = [
                'id' => (int)$product['product_id'],
                'title' => $product['product_title'],
                'description' => substr($product['product_desc'] ?? 'Quality wellness product', 0, 100) . '...',
                'price' => floatval($product['product_price']),
                'image' => $product['product_image'] ?: 'uploads/placeholder.jpg',
                'category' => $product['cat_name'] ?? 'Products',
                'type' => 'product'
            ];
        }
        
        // Get articles from top interest categories (excluding already read)
        $read_articles_sql = "SELECT DISTINCT article_id 
                             FROM article_views 
                             WHERE user_id = $customer_id";
        $read_articles = $this->db_fetch_all($read_articles_sql);
        $read_article_ids = $read_articles ? array_column($read_articles, 'article_id') : [];
        
        $articles_sql = "SELECT a.article_id, a.article_title, a.article_author, a.date_added,
                               c.cat_name
                        FROM articles a
                        INNER JOIN category c ON a.article_cat = c.cat_id
                        WHERE a.article_cat IN ($category_ids_str)
                        " . (!empty($read_article_ids) ? "AND a.article_id NOT IN (" . implode(',', array_map('intval', $read_article_ids)) . ")" : "") . "
                        ORDER BY a.date_added DESC
                        LIMIT 2";
        $articles = $this->db_fetch_all($articles_sql);
        
        // Format articles
        foreach ($articles as $article) {
            $recommendations[] = [
                'id' => (int)$article['article_id'],
                'title' => $article['article_title'],
                'description' => 'Based on your interest in ' . ($article['cat_name'] ?? 'wellness') . ' topics',
                'date' => date('Y-m-d', strtotime($article['date_added'])),
                'image' => 'uploads/placeholder.jpg',
                'category' => $article['cat_name'] ?? 'Articles',
                'type' => 'article'
            ];
        }
        
        // Shuffle recommendations for variety
        shuffle($recommendations);
        
        // Limit to 6 recommendations
        return array_slice($recommendations, 0, 6);
    }
    
    /**
     * Get reminder history for user
     * @param int $customer_id Customer ID
     * @param int $limit Number of reminders to return
     * @return array Reminder history
     */
    public function getReminderHistory($customer_id, $limit = 30)
    {
        require_once __DIR__ . '/ReminderPreferencesModel.php';
        $prefsModel = new ReminderPreferencesModel();
        return $prefsModel->getReminderHistory($customer_id, $limit);
    }
}

?>

