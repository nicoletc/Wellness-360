<?php
/**
 * Reminder Preferences Model
 * Handles user reminder preferences
 */

require_once __DIR__ . '/../settings/db_class.php';

class ReminderPreferencesModel extends db_connection
{
    /**
     * Get user's reminder preferences
     * @param int $customer_id Customer ID
     * @return array Preferences or default values
     */
    public function getPreferences($customer_id)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return $this->getDefaultPreferences();
        }
        
        $sql = "SELECT * FROM reminder_preferences WHERE customer_id = $customer_id LIMIT 1";
        $prefs = $this->db_fetch_one($sql);
        
        if ($prefs) {
            // Decode JSON categories
            if (!empty($prefs['preferred_categories'])) {
                $prefs['preferred_categories'] = json_decode($prefs['preferred_categories'], true);
            } else {
                $prefs['preferred_categories'] = null; // null means all categories
            }
            return $prefs;
        }
        
        return $this->getDefaultPreferences();
    }
    
    /**
     * Get default preferences
     * @return array Default preference values
     */
    private function getDefaultPreferences()
    {
        return [
            'preference_id' => null,
            'customer_id' => null,
            'reminder_frequency' => 'daily',
            'preferred_categories' => null, // null = all categories
            'email_reminders_enabled' => 0,
            'reminder_time' => '09:00:00'
        ];
    }
    
    /**
     * Save or update user preferences
     * @param int $customer_id Customer ID
     * @param array $preferences Preference data
     * @return array Result with status and message
     */
    public function savePreferences($customer_id, $preferences)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        $frequency = $this->escape_string($preferences['reminder_frequency'] ?? 'daily');
        $preferred_categories = isset($preferences['preferred_categories']) && is_array($preferences['preferred_categories']) 
            ? $this->escape_string(json_encode($preferences['preferred_categories'])) 
            : null;
        $email_enabled = isset($preferences['email_reminders_enabled']) ? (int)$preferences['email_reminders_enabled'] : 0;
        $reminder_time = !empty($preferences['reminder_time']) ? $this->escape_string($preferences['reminder_time']) : null;
        
        // Handle NULL values properly for SQL
        $categories_sql = $preferred_categories !== null ? "'$preferred_categories'" : 'NULL';
        $reminder_time_sql = $reminder_time !== null ? "'$reminder_time'" : 'NULL';
        
        $sql = "INSERT INTO reminder_preferences 
                (customer_id, reminder_frequency, preferred_categories, email_reminders_enabled, reminder_time)
                VALUES ($customer_id, '$frequency', $categories_sql, $email_enabled, $reminder_time_sql)
                ON DUPLICATE KEY UPDATE
                reminder_frequency = '$frequency',
                preferred_categories = $categories_sql,
                email_reminders_enabled = $email_enabled,
                reminder_time = $reminder_time_sql,
                updated_at = NOW()";
        
        if ($this->db_query($sql)) {
            return [
                'status' => true,
                'message' => 'Preferences saved successfully.'
            ];
        } else {
            // Get the actual database error
            $error = mysqli_error($this->db) ?? 'Unknown database error';
            error_log("ReminderPreferencesModel::savePreferences SQL Error: " . $error);
            error_log("ReminderPreferencesModel::savePreferences SQL: " . $sql);
            
            return [
                'status' => false,
                'message' => 'Failed to save preferences: ' . $error
            ];
        }
    }
    
    /**
     * Get reminder history for user
     * @param int $customer_id Customer ID
     * @param int $limit Number of reminders to return
     * @return array Reminder history
     */
    public function getReminderHistory($customer_id, $limit = 30)
    {
        $customer_id = (int)$customer_id;
        $limit = (int)$limit;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT dr.*, c.cat_name
                FROM daily_reminders dr
                LEFT JOIN category c ON dr.category_id = c.cat_id
                WHERE dr.customer_id = $customer_id
                ORDER BY dr.sent_date DESC, dr.created_at DESC
                LIMIT $limit";
        
        $reminders = $this->db_fetch_all($sql);
        
        return $reminders ?: [];
    }
    
}

?>

