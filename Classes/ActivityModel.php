<?php
/**
 * Activity Model
 * Handles user activity tracking for recommendations
 */

require_once __DIR__ . '/../settings/db_class.php';

class ActivityModel extends db_connection
{
    /**
     * Record user activity
     * @param array $data Activity data (customer_id, activity_type, content_type, content_id, category_id, time_spent_seconds)
     * @return array Result with status and message
     */
    public function recordActivity($data)
    {
        $customer_id = isset($data['customer_id']) && $data['customer_id'] ? (int)$data['customer_id'] : null;
        $ip_address = $this->escape_string($data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $activity_type_raw = $data['activity_type'] ?? 'page_view';
        $content_type_raw = $data['content_type'] ?? 'page';
        $content_id = (int)($data['content_id'] ?? 0);
        $category_id = isset($data['category_id']) && $data['category_id'] ? (int)$data['category_id'] : null;
        $time_spent_seconds = (int)($data['time_spent_seconds'] ?? 0);
        
        // Validate and normalize activity_type (must match ENUM values)
        $valid_activity_types = ['article_view', 'product_view', 'page_view', 'time_spent'];
        if (!in_array($activity_type_raw, $valid_activity_types)) {
            // Map common variations to valid types
            if (strpos($activity_type_raw, 'article') !== false) {
                $activity_type = 'article_view';
            } elseif (strpos($activity_type_raw, 'product') !== false) {
                $activity_type = 'product_view';
            } elseif (strpos($activity_type_raw, 'time') !== false || strpos($activity_type_raw, 'spent') !== false) {
                $activity_type = 'time_spent';
            } else {
                $activity_type = 'page_view'; // Default fallback
            }
        } else {
            $activity_type = $activity_type_raw;
        }
        $activity_type = $this->escape_string($activity_type);
        
        // Validate and normalize content_type (must match ENUM values)
        $valid_content_types = ['article', 'product', 'page'];
        if (!in_array($content_type_raw, $valid_content_types)) {
            // Map common variations to valid types
            if (strpos($content_type_raw, 'article') !== false) {
                $content_type = 'article';
            } elseif (strpos($content_type_raw, 'product') !== false) {
                $content_type = 'product';
            } else {
                $content_type = 'page'; // Default fallback
            }
        } else {
            $content_type = $content_type_raw;
        }
        $content_type = $this->escape_string($content_type);
        
        error_log("ActivityModel: Normalized activity_type: '$activity_type' (from '$activity_type_raw')");
        error_log("ActivityModel: Normalized content_type: '$content_type' (from '$content_type_raw')");
        
        if (!$this->db_connect()) {
            error_log("ActivityModel: Database connection failed");
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        // Get category_id from content if not provided
        if (!$category_id && $content_id > 0) {
            $category_id = $this->getCategoryFromContent($content_type, $content_id);
        }
        
        $customer_sql = $customer_id ? (int)$customer_id : 'NULL';
        $category_sql = $category_id ? (int)$category_id : 'NULL';
        
        $sql = "INSERT INTO user_activity (customer_id, ip_address, activity_type, content_type, content_id, category_id, time_spent_seconds, viewed_at) 
                VALUES ($customer_sql, '$ip_address', '$activity_type', '$content_type', $content_id, $category_sql, $time_spent_seconds, NOW())";
        
        error_log("ActivityModel: Executing SQL: " . $sql);
        
        if ($this->db_query($sql)) {
            // Update user interests based on this activity
            if ($customer_id && $category_id) {
                try {
                    $this->updateUserInterest($customer_id, $category_id, $time_spent_seconds);
                } catch (Exception $e) {
                    error_log("ActivityModel: Error updating user interest - " . $e->getMessage());
                    // Don't fail the whole operation if interest update fails
                }
            }
            
            return [
                'status' => true,
                'message' => 'Activity recorded successfully.',
                'category_id' => $category_id
            ];
        } else {
            $error = mysqli_error($this->db);
            error_log("ActivityModel: SQL query failed - " . $error);
            error_log("ActivityModel: SQL was - " . $sql);
            return [
                'status' => false,
                'message' => 'Failed to record activity: ' . $error
            ];
        }
    }
    
    /**
     * Get category ID from content (article or product)
     * @param string $content_type 'article' or 'product'
     * @param int $content_id Content ID
     * @return int|null Category ID or null
     */
    private function getCategoryFromContent($content_type, $content_id)
    {
        if (!$this->db_connect()) {
            error_log("ActivityModel: Database connection failed in getCategoryFromContent");
            return null;
        }
        
        try {
            if ($content_type === 'article') {
                $sql = "SELECT article_cat FROM articles WHERE article_id = " . (int)$content_id;
                $result = $this->db_fetch_one($sql);
                return ($result && isset($result['article_cat'])) ? (int)$result['article_cat'] : null;
            } elseif ($content_type === 'product') {
                $sql = "SELECT product_cat FROM customer_products WHERE product_id = " . (int)$content_id;
                $result = $this->db_fetch_one($sql);
                return ($result && isset($result['product_cat'])) ? (int)$result['product_cat'] : null;
            } else {
                return null;
            }
        } catch (Exception $e) {
            error_log("ActivityModel: Error in getCategoryFromContent - " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user interest score based on activity
     * @param int $customer_id Customer ID
     * @param int $category_id Category ID
     * @param int $time_spent Time spent in seconds
     */
    private function updateUserInterest($customer_id, $category_id, $time_spent)
    {
        // Calculate interest score (time spent contributes to score)
        // Base score: 1 point per view, bonus: 0.1 point per second spent (max 10 points per activity)
        $score_increment = 1 + min($time_spent * 0.1, 10);
        
        // Check if interest already exists
        $check_sql = "SELECT interest_id, interest_score FROM user_interests 
                     WHERE customer_id = " . (int)$customer_id . " 
                     AND category_id = " . (int)$category_id;
        $existing = $this->db_fetch_one($check_sql);
        
        if ($existing) {
            // Update existing interest score
            $new_score = (float)$existing['interest_score'] + $score_increment;
            $sql = "UPDATE user_interests 
                    SET interest_score = $new_score, last_updated = NOW() 
                    WHERE interest_id = " . (int)$existing['interest_id'];
        } else {
            // Create new interest record
            $sql = "INSERT INTO user_interests (customer_id, category_id, interest_score, last_updated) 
                    VALUES (" . (int)$customer_id . ", " . (int)$category_id . ", $score_increment, NOW())";
        }
        
        $this->db_query($sql);
    }
    
    /**
     * Get user's top interests
     * @param int $customer_id Customer ID
     * @param int $limit Number of interests to return
     * @return array Top interests with category info
     */
    public function getUserTopInterests($customer_id, $limit = 5)
    {
        $customer_id = (int)$customer_id;
        $limit = (int)$limit;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT ui.category_id, ui.interest_score, c.cat_name 
                FROM user_interests ui
                INNER JOIN category c ON ui.category_id = c.cat_id
                WHERE ui.customer_id = $customer_id
                ORDER BY ui.interest_score DESC
                LIMIT $limit";
        
        $results = $this->db_fetch_all($sql);
        
        if (!$results) {
            return [];
        }
        
        return $results;
    }
    
    /**
     * Get user activity summary
     * @param int $customer_id Customer ID
     * @param int $days Number of days to look back
     * @return array Activity summary
     */
    public function getUserActivitySummary($customer_id, $days = 30)
    {
        $customer_id = (int)$customer_id;
        $days = (int)$days;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT 
                    content_type,
                    category_id,
                    COUNT(*) as view_count,
                    SUM(time_spent_seconds) as total_time_spent,
                    AVG(time_spent_seconds) as avg_time_spent
                FROM user_activity
                WHERE customer_id = $customer_id
                AND viewed_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
                GROUP BY content_type, category_id
                ORDER BY view_count DESC, total_time_spent DESC";
        
        $results = $this->db_fetch_all($sql);
        
        return $results ?: [];
    }
}

?>

