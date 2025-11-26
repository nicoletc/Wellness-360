<?php
/**
 * Reminder Model
 * Handles daily reminders and motivational quotes based on user interests
 */

require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/ActivityModel.php';

class ReminderModel extends db_connection
{
    /**
     * Get or create daily reminder for user
     * @param int $customer_id Customer ID
     * @return array Reminder data
     */
    public function getDailyReminder($customer_id)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return null;
        }
        
        // Check user preferences
        require_once __DIR__ . '/ReminderPreferencesModel.php';
        $prefsModel = new ReminderPreferencesModel();
        $preferences = $prefsModel->getPreferences($customer_id);
        
        // If user has disabled reminders, return null
        if ($preferences['reminder_frequency'] === 'never') {
            return null;
        }
        
        // Check frequency (weekly reminders only on specific days)
        if ($preferences['reminder_frequency'] === 'weekly') {
            $dayOfWeek = date('w'); // 0 = Sunday, 1 = Monday, etc.
            // Only show weekly reminders on Monday (1)
            if ($dayOfWeek !== 1) {
                return null;
            }
        }
        
        $today = date('Y-m-d');
        
        // Check if reminder already exists for today
        $sql = "SELECT * FROM daily_reminders 
                WHERE customer_id = $customer_id 
                AND sent_date = '$today'
                AND reminder_type = 'motivational_quote'
                ORDER BY created_at DESC
                LIMIT 1";
        
        $existing = $this->db_fetch_one($sql);
        
        if ($existing) {
            return $existing;
        }
        
        // Generate new reminder based on user interests and preferences
        return $this->generateDailyReminder($customer_id, $preferences);
    }
    
    /**
     * Generate daily reminder based on user interests and preferences
     * @param int $customer_id Customer ID
     * @param array $preferences User preferences
     * @return array Reminder data
     */
    private function generateDailyReminder($customer_id, $preferences = null)
    {
        $activityModel = new ActivityModel();
        $topInterests = $activityModel->getUserTopInterests($customer_id, 3); // Get top 3 interests
        
        // Filter interests based on user preferences
        if ($preferences && !empty($preferences['preferred_categories']) && is_array($preferences['preferred_categories'])) {
            $preferredCategoryIds = array_map('intval', $preferences['preferred_categories']);
            $topInterests = array_filter($topInterests, function($interest) use ($preferredCategoryIds) {
                return in_array((int)$interest['category_id'], $preferredCategoryIds);
            });
            $topInterests = array_values($topInterests); // Re-index array
        }
        
        $category_id = null;
        $category_name = null;
        
        if (!empty($topInterests)) {
            // Use the top interest category
            $category_id = (int)$topInterests[0]['category_id'];
            $category_name = $topInterests[0]['cat_name'] ?? null;
        }
        
        // Get a motivational quote for this category (or random if no interests)
        $quote = $this->getMotivationalQuote($category_id);
        
        if (!$quote) {
            return null;
        }
        
        // Create reminder record with category context
        $category_sql = $category_id ? (int)$category_id : 'NULL';
        $title = $category_name ? "Daily {$category_name} Reminder" : 'Daily Wellness Reminder';
        
        // Build message with category context
        $message = $quote['quote_text'];
        if ($quote['author']) {
            $message .= "\n\n— " . $this->escape_string($quote['author']);
        }
        
        // Add category-specific encouragement if available
        if ($category_name) {
            $encouragement = $this->getCategoryEncouragement($category_name);
            if ($encouragement) {
                $message = $encouragement . "\n\n" . $message;
            }
        }
        
        $sql = "INSERT INTO daily_reminders (customer_id, reminder_type, category_id, title, message, sent_date, is_read, created_at) 
                VALUES ($customer_id, 'motivational_quote', $category_sql, '" . $this->escape_string($title) . "', '" . $this->escape_string($message) . "', CURDATE(), 0, NOW())";
        
        if ($this->db_query($sql)) {
            $reminder_id = mysqli_insert_id($this->db);
            
            // Return the created reminder with category info
            $sql = "SELECT dr.*, c.cat_name 
                    FROM daily_reminders dr
                    LEFT JOIN category c ON dr.category_id = c.cat_id
                    WHERE dr.reminder_id = $reminder_id";
            return $this->db_fetch_one($sql);
        }
        
        return null;
    }
    
    /**
     * Get category-specific encouragement message
     * @param string $category_name Category name
     * @return string|null Encouragement message
     */
    private function getCategoryEncouragement($category_name)
    {
        $encouragements = [
            'Mental Health' => "Your mental wellness matters. Take a moment today to check in with yourself.",
            'Nutrition' => "Every meal is a chance to nourish your body. Make today count!",
            'Fitness' => "Your body is capable of amazing things. Keep moving forward!",
            'Wellness' => "Wellness is a journey, not a destination. You're doing great!",
        ];
        
        // Try exact match first
        if (isset($encouragements[$category_name])) {
            return $encouragements[$category_name];
        }
        
        // Try partial match
        foreach ($encouragements as $key => $value) {
            if (stripos($category_name, $key) !== false || stripos($key, $category_name) !== false) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Get motivational quote for category (or random)
     * @param int|null $category_id Category ID
     * @return array|false Quote data or false
     */
    public function getMotivationalQuote($category_id = null)
    {
        if (!$this->db_connect()) {
            return false;
        }
        
        if ($category_id) {
            // Try to get quote for specific category
            $sql = "SELECT * FROM motivational_quotes 
                    WHERE (category_id = " . (int)$category_id . " OR category_id IS NULL)
                    AND is_active = 1
                    ORDER BY RAND()
                    LIMIT 1";
        } else {
            // Get random quote
            $sql = "SELECT * FROM motivational_quotes 
                    WHERE is_active = 1
                    ORDER BY RAND()
                    LIMIT 1";
        }
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get unread reminders for user
     * @param int $customer_id Customer ID
     * @return array Reminders
     */
    public function getUnreadReminders($customer_id)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT * FROM daily_reminders 
                WHERE customer_id = $customer_id 
                AND is_read = 0
                ORDER BY created_at DESC
                LIMIT 10";
        
        $reminders = $this->db_fetch_all($sql);
        
        return $reminders ?: [];
    }
    
    /**
     * Mark reminder as read
     * @param int $reminder_id Reminder ID
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function markReminderAsRead($reminder_id, $customer_id)
    {
        $reminder_id = (int)$reminder_id;
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return false;
        }
        
        $sql = "UPDATE daily_reminders 
                SET is_read = 1 
                WHERE reminder_id = $reminder_id 
                AND customer_id = $customer_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Generate product reminder based on viewed but not purchased products
     * @param int $customer_id Customer ID
     * @return array|false Reminder data or false
     */
    public function generateProductReminder($customer_id)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return false;
        }
        
        // Get products user viewed but didn't purchase (in last 7 days)
        $sql = "SELECT DISTINCT ua.content_id as product_id, p.product_title, c.cat_name
                FROM user_activity ua
                INNER JOIN customer_products p ON ua.content_id = p.product_id
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN customer_orders o ON o.customer_id = $customer_id
                LEFT JOIN order_details od ON o.order_id = od.order_id AND od.product_id = p.product_id
                WHERE ua.customer_id = $customer_id
                AND ua.content_type = 'product'
                AND ua.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND od.product_id IS NULL
                AND p.stock > 0
                ORDER BY ua.viewed_at DESC
                LIMIT 1";
        
        $product = $this->db_fetch_one($sql);
        
        if (!$product) {
            return false;
        }
        
        $title = "Remember this product?";
        $message = "You recently viewed '" . $this->escape_string($product['product_title']) . "'. Still interested?";
        
        $sql = "INSERT INTO daily_reminders (customer_id, reminder_type, category_id, content_id, title, message, sent_date, is_read, created_at) 
                VALUES ($customer_id, 'product_reminder', " . ($product['cat_name'] ? "NULL" : "NULL") . ", " . (int)$product['product_id'] . ", 
                '$title', '" . $this->escape_string($message) . "', CURDATE(), 0, NOW())";
        
        if ($this->db_query($sql)) {
            $reminder_id = mysqli_insert_id($this->db);
            $sql = "SELECT * FROM daily_reminders WHERE reminder_id = $reminder_id";
            return $this->db_fetch_one($sql);
        }
        
        return false;
    }
}

?>

