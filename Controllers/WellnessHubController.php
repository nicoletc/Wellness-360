<?php
/**
 * Wellness Hub Controller
 * Handles logic for the Wellness Hub page
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/WellnessHubModel.php';

class WellnessHubController {
    private $model;
    
    public function __construct() {
        $this->model = new WellnessHubModel();
    }
    
    /**
     * Get all data for the wellness hub page
     */
    public function index() {
        try {
            // Get selected category from query parameters (only category filter now)
            $selectedCategory = $_GET['category'] ?? 'all';
            
            // Get search query if provided
            $searchQuery = $_GET['search'] ?? '';
            
            // Get categories from database
            $categories = $this->model->get_categories();
            
            // Get articles based on category and search
            $category_id = ($selectedCategory === 'all') ? null : (int)$selectedCategory;
            $articles = $this->model->get_articles($category_id, $searchQuery);
            
            // Format articles for display
            $formatted_articles = [];
            if ($articles && is_array($articles)) {
                foreach ($articles as $article) {
                    $formatted_articles[] = [
                        'id' => $article['article_id'],
                        'title' => $article['article_title'],
                        'author' => $article['article_author'],
                        'category' => $article['cat_name'] ?? 'Uncategorized',
                        'category_id' => $article['article_cat'],
                        'date' => date('M d, Y', strtotime($article['date_added'])),
                        'views' => (int)($article['view_count'] ?? 0),
                        'has_pdf' => (bool)($article['has_pdf'] ?? false),
                        'image' => 'uploads/placeholder.jpg' // Placeholder for now
                    ];
                }
            }
            
            return [
                'categories' => $categories,
                'articles' => $formatted_articles,
                'selectedCategory' => $selectedCategory,
                'searchQuery' => $searchQuery,
                'placeholderImage' => 'uploads/placeholder.jpg'
            ];
        } catch (Exception $e) {
            // Return empty data on error
            return [
                'categories' => ['all' => 'All'],
                'articles' => [],
                'selectedCategory' => 'all',
                'searchQuery' => '',
                'placeholderImage' => 'uploads/placeholder.jpg',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get article details
     * @param int $id Article ID
     * @return array|false Article data or false if not found
     */
    public function get_article($id) {
        $article = $this->model->get_article_by_id($id);
        
        if (!$article) {
            return false;
        }
        
        return [
            'id' => $article['article_id'],
            'title' => $article['article_title'],
            'author' => $article['article_author'],
            'category' => $article['cat_name'],
            'category_id' => $article['article_cat'],
            'date' => date('M d, Y', strtotime($article['date_added'])),
            'views' => (int)$article['view_count'],
            'has_pdf' => (bool)$article['has_pdf']
        ];
    }
    
    /**
     * Record an article view
     * @param int $article_id Article ID
     * @return bool Success status
     */
    public function record_view($article_id) {
        $user_id = null;
        if (is_logged_in()) {
            $user_id = current_user_id();
        }
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        return $this->model->record_article_view($article_id, $user_id, $ip_address);
    }
    
    /**
     * Get article PDF
     * @param int $article_id Article ID
     * @return string|false PDF binary data or false if not found
     */
    public function get_article_pdf($article_id) {
        return $this->model->get_article_pdf($article_id);
    }
}
?>
