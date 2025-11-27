<?php
/**
 * Community Model
 * Handles data operations for the Community page
 */

require_once __DIR__ . '/../settings/db_class.php';

class CommunityModel extends db_connection {
    private $dataFile;
    
    public function __construct() {
        $this->dataFile = __DIR__ . '/../data/community_data.php';
    }
    
    /**
     * Get community statistics from database
     * @return array Statistics with activeMembers, discussions, and events (workshops)
     */
    public function getCommunityStats() {
        if (!$this->db_connect()) {
            error_log("CommunityModel: Failed to connect to database in getCommunityStats");
            return [
                'activeMembers' => 0,
                'discussions' => 0,
                'events' => 0,
            ];
        }
        
        $stats = [
            'activeMembers' => 0,
            'discussions' => 0,
            'events' => 0,
        ];
        
        // Get active members count (total customers)
        $members_sql = "SELECT COUNT(*) as count FROM customers";
        $members_result = $this->db_fetch_one($members_sql);
        if ($members_result && isset($members_result['count'])) {
            $stats['activeMembers'] = (int)$members_result['count'];
        }
        
        // Get discussions count
        $discussions_sql = "SELECT COUNT(*) as count FROM community";
        $discussions_result = $this->db_fetch_one($discussions_sql);
        if ($discussions_result && isset($discussions_result['count'])) {
            $stats['discussions'] = (int)$discussions_result['count'];
        }
        
        // Get workshops/events count
        $workshops_sql = "SELECT COUNT(*) as count FROM workshops";
        $workshops_result = $this->db_fetch_one($workshops_sql);
        if ($workshops_result && isset($workshops_result['count'])) {
            $stats['events'] = (int)$workshops_result['count'];
        }
        
        error_log("CommunityModel: getCommunityStats - Members: " . $stats['activeMembers'] . ", Discussions: " . $stats['discussions'] . ", Events: " . $stats['events']);
        
        return $stats;
    }

    public function loadData() {
        // Use require (not require_once) to ensure variables are always available
        // Reset variables to avoid conflicts
        $stats = [];
        $discussionCategories = [];
        $discussions = [];
        $challenges = [];
        $workshops = [];
        $placeholderImage = '../../uploads/placeholder.jpg';
        
        require $this->dataFile;
        
        return [
            'stats' => $stats ?? [],
            'discussionCategories' => $discussionCategories ?? [],
            'discussions' => $discussions ?? [],
            'challenges' => $challenges ?? [],
            'workshops' => $workshops ?? [],
            'placeholderImage' => $placeholderImage ?? '../../uploads/placeholder.jpg',
        ];
    }
    
    /**
     * Get discussions by category
     */
    public function getDiscussionsByCategory($category = 'all') {
        $data = $this->loadData();
        $discussions = $data['discussions'] ?? [];
        
        if ($category === 'all') {
            return $discussions;
        }
        
        if (empty($discussions) || !is_array($discussions)) {
            return [];
        }
        
        return array_filter($discussions, function($discussion) use ($category) {
            return isset($discussion['category']) && $discussion['category'] === $category;
        });
    }
    
    /**
     * Get all challenges
     */
    public function getAllChallenges() {
        $data = $this->loadData();
        return $data['challenges'];
    }
    
    /**
     * Get all workshops from database
     * @param int|null $customer_id Current user's customer ID (for checking registration status)
     * @return array Formatted workshops for display
     */
    public function getAllWorkshopsFromDB($customer_id = null) {
        // Get all workshops with registration count
        $sql = "SELECT w.*, 
                       c.customer_name,
                       c.customer_email,
                       COUNT(r.registration_id) AS registered_count";
        
        // Add check for current user's registration status
        if ($customer_id) {
            $customer_id = (int)$customer_id;
            $sql .= ",
                       CASE WHEN EXISTS (
                           SELECT 1 FROM workshop_registrations wr 
                           WHERE wr.workshop_id = w.workshop_id 
                           AND wr.customer_id = $customer_id 
                           AND wr.status = 'registered'
                       ) THEN 1 ELSE 0 END AS is_user_registered";
        } else {
            $sql .= ", 0 AS is_user_registered";
        }
        
        $sql .= " FROM workshops w
                LEFT JOIN customers c ON w.customer_id = c.customer_id
                LEFT JOIN workshop_registrations r ON w.workshop_id = r.workshop_id AND r.status = 'registered'
                GROUP BY w.workshop_id, w.customer_id, w.workshop_title, w.workshop_image, w.workshop_desc, 
                         w.workshop_leader, w.workshop_date, w.workshop_time, w.workshop_type, w.location, 
                         w.max_participants, c.customer_name, c.customer_email
                ORDER BY w.workshop_date ASC, w.workshop_time ASC";
        
        $workshops = $this->db_fetch_all($sql);
        
        if (!$workshops || !is_array($workshops)) {
            return [];
        }
        
        // Format workshops for display
        $formatted = [];
        foreach ($workshops as $workshop) {
            // Format date
            $dateObj = null;
            if (!empty($workshop['workshop_date'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d', $workshop['workshop_date']);
            }
            $formattedDate = $dateObj ? $dateObj->format('M d, Y') : ($workshop['workshop_date'] ?? 'TBA');
            
            // Format time
            $formattedTime = 'TBA';
            if (!empty($workshop['workshop_time'])) {
                // Try different time formats
                $timeObj = DateTime::createFromFormat('H:i:s', $workshop['workshop_time']);
                if (!$timeObj) {
                    $timeObj = DateTime::createFromFormat('H:i', $workshop['workshop_time']);
                }
                if ($timeObj) {
                    $formattedTime = $timeObj->format('g:i A');
                } else {
                    $formattedTime = $workshop['workshop_time'];
                }
            }
            
            // Get image path - ensure it's relative to the View folder
            $image = '../../uploads/placeholder.jpg';
            if (!empty($workshop['workshop_image'])) {
                // If image path doesn't start with ../uploads/, add it
                if (strpos($workshop['workshop_image'], '../uploads/') === 0 || strpos($workshop['workshop_image'], '../../uploads/') === 0) {
                    // Already has correct prefix
                    $image = $workshop['workshop_image'];
                } else if (strpos($workshop['workshop_image'], 'uploads/') === 0) {
                    // Has old uploads/ prefix, convert to ../../uploads/
                    $image = '../../uploads/' . substr($workshop['workshop_image'], 8);
                } else {
                    $image = '../../uploads/' . ltrim($workshop['workshop_image'], '/');
                }
            }
            
            $formatted[] = [
                'id' => $workshop['workshop_id'],
                'title' => $workshop['workshop_title'] ?? 'Untitled Workshop',
                'description' => $workshop['workshop_desc'] ?? '',
                'image' => $image,
                'type' => $workshop['workshop_type'] ?? 'virtual',
                'host' => $workshop['workshop_leader'] ?? ($workshop['customer_name'] ?? 'TBA'),
                'date' => $formattedDate,
                'time' => $formattedTime,
                'location' => !empty($workshop['location']) ? $workshop['location'] : null,
                'registered' => (int)($workshop['registered_count'] ?? 0),
                'capacity' => (int)($workshop['max_participants'] ?? 0),
                'is_registered' => (bool)($workshop['is_user_registered'] ?? false),
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get all workshops (legacy method - uses data file)
     */
    public function getAllWorkshops() {
        $data = $this->loadData();
        return $data['workshops'] ?? [];
    }
    
    /**
     * Get all categories from database for discussions
     * Note: This method is deprecated. Community categories are free-form text, not from product category table.
     * Use getDiscussionCategories() instead to get unique category names from existing discussions.
     * 
     * @return array Empty array (kept for backward compatibility)
     * @deprecated Community categories are not stored in a separate table
     */
    public function getCategoriesFromDB() {
        // Community categories are free-form text stored in comm_cat field
        // They are NOT references to the product category table
        return [];
    }
    
    /**
     * Get unique category names from existing discussions
     * Note: Community categories are free-form text stored in comm_cat field
     * They are NOT references to the product category table
     * 
     * @return array Unique category names (strings)
     */
    public function getDiscussionCategories() {
        if (!$this->db_connect()) {
            error_log("CommunityModel: Failed to connect to database in getDiscussionCategories");
            return [];
        }
        
        // Get all distinct category names (all are free-form text)
        // Simple check - MySQL will handle empty strings
        $sql = "SELECT DISTINCT comm_cat 
                FROM community 
                WHERE comm_cat IS NOT NULL AND comm_cat != ''
                ORDER BY comm_cat ASC";
        
        error_log("CommunityModel: getDiscussionCategories SQL: " . $sql);
        $categories = $this->db_fetch_all($sql);
        
        if ($categories === false) {
            $error = mysqli_error($this->db);
            error_log("Error fetching discussion categories: " . $error . " | SQL: " . $sql);
            return [];
        }
        
        // mysqli_fetch_all returns empty array [] when no rows found, not false
        if (!is_array($categories)) {
            error_log("CommunityModel: getDiscussionCategories returned non-array result. Type: " . gettype($categories) . " | SQL: " . $sql);
            return [];
        }
        
        error_log("CommunityModel: getDiscussionCategories raw result count: " . count($categories));
        
        $unique = [];
        foreach ($categories as $cat) {
            if (isset($cat['comm_cat']) && !empty($cat['comm_cat']) && is_string($cat['comm_cat'])) {
                $trimmed = trim($cat['comm_cat']);
                if (!empty($trimmed)) {
                    $unique[] = $trimmed;
                }
            }
        }
        
        // Remove duplicates and return
        $result = array_values(array_unique($unique));
        error_log("CommunityModel: getDiscussionCategories found " . count($result) . " unique categories: " . implode(', ', $result));
        return $result;
    }
    
    /**
     * Get all discussions from database with reply counts
     * 
     * Table structure:
     * - comm_id: PRIMARY KEY, AUTO_INCREMENT
     * - customer_id: NOT NULL, references customer table (INDEX)
     * - comm_cat: varchar(255), NULL (free-form text category name, NOT a FK to product category table)
     * - comm_title: varchar(255), NOT NULL
     * - comm_desc: text, NOT NULL
     * - created_at: timestamp, NOT NULL, DEFAULT current_timestamp()
     * 
     * @param string $category Category filter ('all' for all categories)
     * @return array Formatted discussions
     */
    public function getDiscussionsFromDB($category = 'all') {
        if (!$this->db_connect()) {
            error_log("Failed to connect to database in getDiscussionsFromDB");
            return [];
        }
        
        // Query to get all discussions - comm_id is PRIMARY KEY, created_at always exists
        // Note: We don't fetch customer_name or customer_image for anonymity
        $sql = "SELECT c.comm_id, c.customer_id, c.comm_cat, c.comm_title, c.comm_desc, c.created_at
                FROM community c
                WHERE 1=1";
        
        // Filter by category if not 'all'
        // comm_cat is free-form text, not a FK to product category table
        if ($category !== 'all') {
            $category = $this->escape_string($category);
            $sql .= " AND c.comm_cat = '$category'";
        }
        
        // Order by created_at (always exists) descending
        $sql .= " ORDER BY c.created_at DESC";
        
        error_log("CommunityModel: getDiscussionsFromDB SQL: " . $sql);
        $discussions = $this->db_fetch_all($sql);
        
        // Check for database errors
        if ($discussions === false) {
            $error = mysqli_error($this->db);
            error_log("Error fetching discussions: " . $error . " | SQL: " . $sql);
            return [];
        }
        
        // mysqli_fetch_all returns empty array [] when no rows found, not false
        if (!is_array($discussions)) {
            error_log("CommunityModel: getDiscussionsFromDB returned non-array result. Type: " . gettype($discussions) . " | SQL: " . $sql);
            return [];
        }
        
        error_log("CommunityModel: getDiscussionsFromDB raw result count: " . count($discussions));
        
        // Empty array is valid (no discussions yet)
        if (empty($discussions)) {
            error_log("CommunityModel: getDiscussionsFromDB found 0 discussions (this is OK if no discussions exist yet)");
            return [];
        }
        
        error_log("CommunityModel: getDiscussionsFromDB processing " . count($discussions) . " discussions");
        
        // Get reply counts separately for each discussion using comm_id (PRIMARY KEY)
        $reply_counts = [];
        $discussion_ids = [];
        foreach ($discussions as $disc) {
            if (isset($disc['comm_id'])) {
                $discussion_ids[] = (int)$disc['comm_id'];
            }
        }
        
        if (!empty($discussion_ids)) {
            $ids_str = implode(',', array_unique($discussion_ids));
            $reply_sql = "SELECT comm_id, COUNT(*) AS reply_count 
                         FROM community_replies 
                         WHERE comm_id IN ($ids_str)
                         GROUP BY comm_id";
            $reply_results = $this->db_fetch_all($reply_sql);
            if ($reply_results && is_array($reply_results)) {
                foreach ($reply_results as $reply) {
                    $reply_counts[$reply['comm_id']] = (int)$reply['reply_count'];
                }
            }
        }
        
        // Format discussions for display
        $formatted = [];
        foreach ($discussions as $discussion) {
            // Format timestamp
            $timestamp = 'Just now';
            if (!empty($discussion['created_at'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $discussion['created_at']);
                if ($dateObj) {
                    $now = new DateTime();
                    $diff = $now->diff($dateObj);
                    
                    if ($diff->days > 0) {
                        $timestamp = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                    } elseif ($diff->h > 0) {
                        $timestamp = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                    } elseif ($diff->i > 0) {
                        $timestamp = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                    }
                } else {
                    $timestamp = $discussion['created_at'];
                }
            }
            
            // Get category name - comm_cat is free-form text, not a FK to product category table
            $categoryName = !empty($discussion['comm_cat']) ? $discussion['comm_cat'] : 'Uncategorized';
            
            // Get reply count
            $replyCount = isset($reply_counts[$discussion['comm_id']]) ? $reply_counts[$discussion['comm_id']] : 0;
            
            // All posts are anonymous - use generic placeholder
            $authorImage = '../../uploads/placeholder.jpg';
            
            $formatted[] = [
                'id' => $discussion['comm_id'],
                'author' => 'Anonymous', // Always anonymous
                'authorImage' => $authorImage, // Generic placeholder
                'timestamp' => $timestamp,
                'title' => $discussion['comm_title'] ?? '',
                'content' => $discussion['comm_desc'] ?? '',
                'category' => $discussion['comm_cat'],
                'categoryName' => $categoryName,
                'replies' => $replyCount,
            ];
        }
        
        error_log("CommunityModel: getDiscussionsFromDB returning " . count($formatted) . " formatted discussions");
        if (!empty($formatted)) {
            error_log("CommunityModel: First formatted discussion: " . print_r($formatted[0], true));
        }
        
        return $formatted;
    }
    
    /**
     * Create a new discussion
     * 
     * Table structure:
     * - comm_id: PRIMARY KEY, AUTO_INCREMENT (auto-generated)
     * - customer_id: NOT NULL, references customer table
     * - comm_cat: varchar(255), NULL
     * - comm_title: varchar(255), NOT NULL
     * - comm_desc: text, NOT NULL
     * - created_at: timestamp, NOT NULL, DEFAULT current_timestamp() (auto-generated)
     * 
     * @param int $customer_id Customer ID
     * @param string $category Category ID or name
     * @param string $title Discussion title
     * @param string $description Discussion description
     * @return array Response with status, message, and discussion_id
     */
    public function createDiscussion($customer_id, $category, $title, $description) {
        if (!$this->db_connect()) {
            return ['status' => false, 'message' => 'Database connection failed.'];
        }
        
        $customer_id = (int)$customer_id;
        
        // Escape strings - check if escape_string returns false
        $category_escaped = $this->escape_string($category);
        if ($category_escaped === false) {
            return ['status' => false, 'message' => 'Failed to escape category. Database connection issue.'];
        }
        $category = $category_escaped;
        
        $title_escaped = $this->escape_string(trim($title));
        if ($title_escaped === false) {
            return ['status' => false, 'message' => 'Failed to escape title. Database connection issue.'];
        }
        $title = $title_escaped;
        
        $description_escaped = $this->escape_string(trim($description));
        if ($description_escaped === false) {
            return ['status' => false, 'message' => 'Failed to escape description. Database connection issue.'];
        }
        $description = $description_escaped;
        
        if (empty($title) || empty($description)) {
            return ['status' => false, 'message' => 'Title and description are required.'];
        }
        
        if (empty($category)) {
            return ['status' => false, 'message' => 'Category is required.'];
        }
        
        $sql = "INSERT INTO community (customer_id, comm_cat, comm_title, comm_desc) 
                VALUES ($customer_id, '$category', '$title', '$description')";
        
        if ($this->db_query($sql)) {
            $insert_id = mysqli_insert_id($this->db);
            return ['status' => true, 'message' => 'Discussion created successfully.', 'discussion_id' => $insert_id];
        } else {
            // Get the actual database error
            $error = mysqli_error($this->db);
            return ['status' => false, 'message' => 'Database error: ' . ($error ? $error : 'Unknown error')];
        }
    }
    
    /**
     * Get replies for a specific discussion
     * @param int $comm_id Discussion ID
     * @return array Formatted replies
     */
    public function getRepliesForDiscussion($comm_id) {
        $this->db_connect();
        
        $comm_id = (int)$comm_id;
        
        // Note: We don't fetch customer_name or customer_image for anonymity
        $sql = "SELECT cr.reply_id, cr.comm_id, cr.customer_id, cr.reply_content, cr.created_at
                FROM community_replies cr
                WHERE cr.comm_id = $comm_id
                ORDER BY cr.created_at ASC";
        
        $replies = $this->db_fetch_all($sql);
        
        if (!$replies || !is_array($replies)) {
            return [];
        }
        
        // Format replies for display
        $formatted = [];
        foreach ($replies as $reply) {
            // Format timestamp
            $timestamp = 'Just now';
            if (!empty($reply['created_at'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $reply['created_at']);
                if ($dateObj) {
                    $now = new DateTime();
                    $diff = $now->diff($dateObj);
                    
                    if ($diff->days > 0) {
                        $timestamp = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                    } elseif ($diff->h > 0) {
                        $timestamp = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                    } elseif ($diff->i > 0) {
                        $timestamp = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                    }
                } else {
                    $timestamp = $reply['created_at'];
                }
            }
            
            // All replies are anonymous - use generic placeholder
            $authorImage = '../../uploads/placeholder.jpg';
            
            $formatted[] = [
                'id' => $reply['reply_id'],
                'author' => 'Anonymous', // Always anonymous
                'authorImage' => $authorImage, // Generic placeholder
                'timestamp' => $timestamp,
                'content' => $reply['reply_content'] ?? '',
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Add a reply to a discussion
     * @param int $comm_id Discussion ID
     * @param int $customer_id Customer ID
     * @param string $content Reply content
     * @return int|false Reply ID on success, false on failure
     */
    public function addReply($comm_id, $customer_id, $content) {
        $this->db_connect();
        
        $comm_id = (int)$comm_id;
        $customer_id = (int)$customer_id;
        $content = $this->escape_string(trim($content));
        
        if (empty($content)) {
            return false;
        }
        
        $sql = "INSERT INTO community_replies (comm_id, customer_id, reply_content) 
                VALUES ($comm_id, $customer_id, '$content')";
        
        if ($this->db_query($sql)) {
            return mysqli_insert_id($this->db);
        }
        
        return false;
    }
}
?>
