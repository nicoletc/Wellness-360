<?php
/**
 * Community Controller
 * Handles logic for the Community page
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/CommunityModel.php';

class CommunityController {
    private $model;
    
    public function __construct() {
        $this->model = new CommunityModel();
    }
    
    /**
     * Get all data for the community page
     */
    public function index() {
        try {
            $data = $this->model->loadData();
            
            // Get real statistics from database
            $data['stats'] = $this->model->getCommunityStats();
            
            // Get selected tab from query parameters
            $selectedTab = $_GET['tab'] ?? 'discussions';
            
            // Redirect challenges tab to discussions (challenges removed)
            if ($selectedTab === 'challenges') {
                $selectedTab = 'discussions';
            }
            
            // Get selected category for discussions
            $selectedCategory = $_GET['category'] ?? 'all';
            
            // Get discussion categories (free-form text from existing discussions)
            // Note: Community categories are NOT from product category table
            $data['discussionCategories'] = $this->model->getDiscussionCategories();
            
            // Ensure it's always an array
            if (!is_array($data['discussionCategories'])) {
                $data['discussionCategories'] = [];
            }
            
            error_log("CommunityController: discussionCategories count: " . count($data['discussionCategories']));
            
            // Filter discussions by category if needed
            if ($selectedTab === 'discussions') {
                // Get discussions from database
                $data['discussions'] = $this->model->getDiscussionsFromDB($selectedCategory);
                
                // Ensure discussions is always an array
                if (!is_array($data['discussions'])) {
                    $data['discussions'] = [];
                }
                
                error_log("CommunityController: discussions count: " . count($data['discussions']));
            } else {
                // Initialize empty array for discussions when not on discussions tab
                $data['discussions'] = [];
            }
            
            // Get workshops from database
            $customer_id = null;
            if (is_logged_in()) {
                $customer_id = current_user_id();
            }
            
            if ($selectedTab === 'workshops') {
                $data['workshops'] = $this->model->getAllWorkshopsFromDB($customer_id);
            } else {
                // Ensure workshops are always available (for stats or other uses)
                if (!isset($data['workshops']) || empty($data['workshops'])) {
                    $data['workshops'] = $this->model->getAllWorkshopsFromDB($customer_id);
                }
            }
            
            $data['selectedTab'] = $selectedTab;
            $data['selectedCategory'] = $selectedCategory;
            
            return $data;
        } catch (Exception $e) {
            error_log("CommunityController error: " . $e->getMessage());
            // Return empty data structure on error with default stats
            return [
                'stats' => [
                    'activeMembers' => 0,
                    'discussions' => 0,
                    'events' => 0,
                ],
                'discussionCategories' => [],
                'discussions' => [],
                'workshops' => [],
                'selectedTab' => 'discussions',
                'selectedCategory' => 'all',
                'placeholderImage' => '../../uploads/placeholder.jpg',
            ];
        }
    }
    
    /**
     * Create a new discussion
     * @param array $kwargs Discussion data (customer_id, category, title, description)
     * @return array Response with status and message
     */
    public function createDiscussion($kwargs) {
        try {
            $customer_id = $kwargs['customer_id'] ?? null;
            $category = $kwargs['category'] ?? '';
            $title = $kwargs['title'] ?? '';
            $description = $kwargs['description'] ?? '';
            
            if (empty($customer_id)) {
                return ['status' => 'error', 'message' => 'User must be logged in to create a discussion.'];
            }
            
            if (empty($category) || empty($title) || empty($description)) {
                return ['status' => 'error', 'message' => 'All fields are required.'];
            }
            
            $result = $this->model->createDiscussion($customer_id, $category, $title, $description);
            
            if (is_array($result)) {
                // Model now returns array with status and message
                if ($result['status'] === true || $result['status'] === 'success') {
                    return ['status' => 'success', 'message' => $result['message'] ?? 'Discussion created successfully.', 'discussion_id' => $result['discussion_id'] ?? null];
                } else {
                    return ['status' => 'error', 'message' => $result['message'] ?? 'Failed to create discussion. Please try again.'];
                }
            } else {
                // Legacy format (integer ID or false)
                if ($result) {
                    return ['status' => 'success', 'message' => 'Discussion created successfully.', 'discussion_id' => $result];
                } else {
                    return ['status' => 'error', 'message' => 'Failed to create discussion. Please try again.'];
                }
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get replies for a discussion
     * @param int $comm_id Discussion ID
     * @return array Response with status and replies
     */
    public function getReplies($comm_id) {
        try {
            $comm_id = (int)$comm_id;
            
            if ($comm_id <= 0) {
                return ['status' => 'error', 'message' => 'Invalid discussion ID.', 'replies' => []];
            }
            
            $replies = $this->model->getRepliesForDiscussion($comm_id);
            
            return ['status' => 'success', 'replies' => $replies];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage(), 'replies' => []];
        }
    }
    
    /**
     * Add a reply to a discussion
     * @param array $kwargs Reply data (comm_id, customer_id, content)
     * @return array Response with status and message
     */
    public function addReply($kwargs) {
        try {
            $comm_id = $kwargs['comm_id'] ?? null;
            $customer_id = $kwargs['customer_id'] ?? null;
            $content = $kwargs['content'] ?? '';
            
            if (empty($customer_id)) {
                return ['status' => 'error', 'message' => 'User must be logged in to reply.'];
            }
            
            if (empty($comm_id) || empty($content)) {
                return ['status' => 'error', 'message' => 'Discussion ID and reply content are required.'];
            }
            
            $reply_id = $this->model->addReply($comm_id, $customer_id, $content);
            
            if ($reply_id) {
                return ['status' => 'success', 'message' => 'Reply added successfully.', 'reply_id' => $reply_id];
            } else {
                return ['status' => 'error', 'message' => 'Failed to add reply. Please try again.'];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
}
?>
