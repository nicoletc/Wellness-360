<?php
/**
 * Admin Model
 * Handles data operations for the Admin dashboard
 */

require_once __DIR__ . '/../settings/db_class.php';

class AdminModel extends db_connection
{
    /**
     * Get dashboard statistics
     * @return array Dashboard statistics
     */
    public function getDashboardStats()
    {
        if (!$this->db_connect()) {
            return [
                'totalUsers' => 0,
                'totalProducts' => 0,
                'activeVendors' => 0,
                'publishedArticles' => 0,
            ];
        }
        
        $stats = [
            'totalUsers' => 0,
            'totalProducts' => 0,
            'activeVendors' => 0,
            'publishedArticles' => 0,
        ];
        
        // Get total users count
        $users_sql = "SELECT COUNT(*) as count FROM customers";
        $users_result = $this->db_fetch_one($users_sql);
        if ($users_result && isset($users_result['count'])) {
            $stats['totalUsers'] = (int)$users_result['count'];
        }
        
        // Get total products count
        $products_sql = "SELECT COUNT(*) as count FROM customer_products";
        $products_result = $this->db_fetch_one($products_sql);
        if ($products_result && isset($products_result['count'])) {
            $stats['totalProducts'] = (int)$products_result['count'];
        }
        
        // Get active vendors count
        $vendors_sql = "SELECT COUNT(*) as count FROM vendors";
        $vendors_result = $this->db_fetch_one($vendors_sql);
        if ($vendors_result && isset($vendors_result['count'])) {
            $stats['activeVendors'] = (int)$vendors_result['count'];
        }
        
        // Get published articles count
        $articles_sql = "SELECT COUNT(*) as count FROM articles";
        $articles_result = $this->db_fetch_one($articles_sql);
        if ($articles_result && isset($articles_result['count'])) {
            $stats['publishedArticles'] = (int)$articles_result['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get recent activity
     * @param int $limit Number of activities to fetch
     * @return array Recent activities
     */
    public function getRecentActivity($limit = 10)
    {
        if (!$this->db_connect()) {
            return [];
        }
        
        $activities = [];
        
        // Get recent user registrations
        $users_sql = "SELECT customer_id, customer_name, date_joined 
                      FROM customers 
                      ORDER BY date_joined DESC 
                      LIMIT " . (int)$limit;
        $users = $this->db_fetch_all($users_sql);
        
        if ($users && is_array($users)) {
            foreach ($users as $user) {
                $activities[] = [
                    'type' => 'user',
                    'icon' => 'user-plus',
                    'title' => 'New user registered: ' . htmlspecialchars($user['customer_name']),
                    'time' => $this->getTimeAgo($user['date_joined']),
                    'timestamp' => strtotime($user['date_joined'])
                ];
            }
        }
        
        // Get recent products added
        $products_sql = "SELECT product_id, product_title, date_added 
                        FROM customer_products 
                        ORDER BY date_added DESC 
                        LIMIT " . (int)$limit;
        $products = $this->db_fetch_all($products_sql);
        
        if ($products && is_array($products)) {
            foreach ($products as $product) {
                $activities[] = [
                    'type' => 'product',
                    'icon' => 'box',
                    'title' => 'New product added: ' . htmlspecialchars($product['product_title']),
                    'time' => $this->getTimeAgo($product['date_added']),
                    'timestamp' => strtotime($product['date_added'])
                ];
            }
        }
        
        // Get recent articles published
        $articles_sql = "SELECT article_id, article_title, date_added 
                        FROM articles 
                        ORDER BY date_added DESC 
                        LIMIT " . (int)$limit;
        $articles = $this->db_fetch_all($articles_sql);
        
        if ($articles && is_array($articles)) {
            foreach ($articles as $article) {
                $activities[] = [
                    'type' => 'article',
                    'icon' => 'file-alt',
                    'title' => 'Article published: ' . htmlspecialchars($article['article_title']),
                    'time' => $this->getTimeAgo($article['date_added']),
                    'timestamp' => strtotime($article['date_added'])
                ];
            }
        }
        
        // Get recent orders
        $orders_sql = "SELECT o.order_id, o.order_date, c.customer_name 
                      FROM customer_orders o
                      LEFT JOIN customers c ON o.customer_id = c.customer_id
                      ORDER BY o.order_date DESC 
                      LIMIT " . (int)$limit;
        $orders = $this->db_fetch_all($orders_sql);
        
        if ($orders && is_array($orders)) {
            foreach ($orders as $order) {
                $activities[] = [
                    'type' => 'order',
                    'icon' => 'shopping-cart',
                    'title' => 'New order placed' . ($order['customer_name'] ? ' by ' . htmlspecialchars($order['customer_name']) : ''),
                    'time' => $this->getTimeAgo($order['order_date']),
                    'timestamp' => strtotime($order['order_date'])
                ];
            }
        }
        
        // Get recent contact messages
        $messages_sql = "SELECT message_id, first_name, last_name, subject, created_at 
                        FROM contact_messages 
                        ORDER BY created_at DESC 
                        LIMIT " . (int)$limit;
        $messages = $this->db_fetch_all($messages_sql);
        
        if ($messages && is_array($messages)) {
            foreach ($messages as $message) {
                $activities[] = [
                    'type' => 'message',
                    'icon' => 'envelope',
                    'title' => 'New message from ' . htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) . ': ' . htmlspecialchars($message['subject']),
                    'time' => $this->getTimeAgo($message['created_at']),
                    'timestamp' => strtotime($message['created_at'])
                ];
            }
        }
        
        // Sort all activities by timestamp (most recent first)
        usort($activities, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        // Return only the requested limit
        return array_slice($activities, 0, $limit);
    }
    
    /**
     * Get time ago string
     * @param string $datetime Datetime string
     * @return string Time ago string
     */
    private function getTimeAgo($datetime)
    {
        if (empty($datetime)) {
            return 'Unknown time';
        }
        
        $timestamp = strtotime($datetime);
        $current = time();
        $diff = $current - $timestamp;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($diff / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }
}
?>

