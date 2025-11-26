<?php
/**
 * Profile Controller
 * Handles logic for the Profile page
 */

require_once __DIR__ . '/../Classes/ProfileModel.php';
require_once __DIR__ . '/../settings/core.php';

class ProfileController {
    private $model;
    
    public function __construct() {
        $this->model = new ProfileModel();
    }
    
    public function index() {
        $data = $this->model->loadData();
        
        // Get active tab from query parameters (default to orders if not set)
        $activeTab = $_GET['tab'] ?? 'orders';
        
        // If user is logged in, get their profile data
        if (is_logged_in()) {
            $customer_id = current_user_id();
            $userProfile = $this->model->getUserProfile($customer_id);
            
            if ($userProfile) {
                $data['userProfile'] = $userProfile;
                
                // Get user statistics from database
                $userStats = $this->model->getUserStats($customer_id);
                $data['userStats'] = $userStats;
                
                // Get articles read by user from database
                $data['articlesRead'] = $this->model->getUserArticlesRead($customer_id);
                
                // Get user orders from database
                $data['orders'] = $this->model->getUserOrders($customer_id);
                
                // Get user wishlist from database
                $data['wishlist'] = $this->model->getUserWishlist($customer_id);
                
                // Get recommended content (dummy data for now)
                $data['recommendedContent'] = $this->model->getRecommendedContent($customer_id);
                
                // Get reminder history (always load it, not just on reminders tab)
                $data['reminderHistory'] = $this->model->getReminderHistory($customer_id, 30);
                
                // Get reminder preferences if on settings tab
                if ($activeTab === 'settings') {
                    require_once __DIR__ . '/../Classes/ReminderPreferencesModel.php';
                    $prefsModel = new ReminderPreferencesModel();
                    $data['reminderPreferences'] = $prefsModel->getPreferences($customer_id);
                    
                    // Get all categories for preference selection
                    require_once __DIR__ . '/../Classes/WellnessHubModel.php';
                    $wellnessModel = new WellnessHubModel();
                    $data['categories'] = $wellnessModel->get_categories();
                }
                
                // Format member since date
                if (isset($userProfile['date_joined'])) {
                    $date = new DateTime($userProfile['date_joined']);
                    $data['memberSince'] = $date->format('F Y');
                } else {
                    $data['memberSince'] = 'Recently';
                }
            }
        }
        
        $data['activeTab'] = $activeTab;
        
        return $data;
    }
}

?>

