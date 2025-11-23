<?php
/**
 * Shop Controller
 * Handles logic for the Shop page
 */

require_once __DIR__ . '/../Classes/ShopModel.php';

class ShopController {
    private $model;
    
    public function __construct() {
        $this->model = new ShopModel();
    }
    
    /**
     * Get all data for the shop page
     */
    public function index() {
        // Get filter parameters from query
        $selectedCategory = $_GET['category'] ?? 'all';
        $selectedVendor = $_GET['vendor'] ?? 'all';
        $minPrice = isset($_GET['minPrice']) ? floatval($_GET['minPrice']) : null;
        $maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : null;
        $searchQuery = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? 'date';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10; // Products per page
        $offset = ($page - 1) * $limit;
        
        // Build filters
        $filters = [];
        if ($selectedCategory !== 'all') {
            $filters['category'] = $selectedCategory;
        }
        if ($selectedVendor !== 'all') {
            $filters['vendor'] = $selectedVendor;
        }
        if ($minPrice !== null) {
            $filters['min_price'] = $minPrice;
        }
        if ($maxPrice !== null) {
            $filters['max_price'] = $maxPrice;
        }
        if (!empty($searchQuery)) {
            $filters['search'] = $searchQuery;
        }
        $filters['sort'] = $sortBy;
        
        // Get products using composite search
        $products = $this->model->composite_search($filters, $limit, $offset);
        $total = $this->model->get_product_count($filters);
        
        // Get categories and vendors for filters
        $categories = $this->model->get_categories();
        $vendors = $this->model->get_vendors();
        $priceRange = $this->model->get_price_range();
        
        return [
            'products' => $products,
            'categories' => $categories,
            'vendors' => $vendors,
            'priceRange' => $priceRange,
            'selectedCategory' => $selectedCategory,
            'selectedVendor' => $selectedVendor,
            'minPrice' => $minPrice ?? $priceRange['min'],
            'maxPrice' => $maxPrice ?? $priceRange['max'],
            'searchQuery' => $searchQuery,
            'sortBy' => $sortBy,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ],
            'placeholderImage' => 'uploads/placeholder.jpg'
        ];
    }
    
    /**
     * Get all products
     */
    public function get_all_products($limit = null, $offset = 0) {
        return $this->model->view_all_products($limit, $offset);
    }
    
    /**
     * Search products
     */
    public function search_products($query, $limit = null, $offset = 0) {
        return $this->model->search_products($query, $limit, $offset);
    }
    
    /**
     * Filter products by category
     */
    public function filter_by_category($cat_id, $limit = null, $offset = 0) {
        return $this->model->filter_products_by_category($cat_id, $limit, $offset);
    }
    
    /**
     * Filter products by vendor
     */
    public function filter_by_vendor($vendor_id, $limit = null, $offset = 0) {
        return $this->model->filter_products_by_vendors($vendor_id, $limit, $offset);
    }
    
    /**
     * Composite search with multiple filters
     */
    public function composite_search($filters = [], $limit = null, $offset = 0) {
        return $this->model->composite_search($filters, $limit, $offset);
    }
    
    /**
     * Get product count
     */
    public function get_product_count($filters = []) {
        return $this->model->get_product_count($filters);
    }
    
    /**
     * Get product details
     */
    public function get_product($id) {
        return $this->model->view_single_product($id);
    }
    
    /**
     * Get all categories
     */
    public function get_categories() {
        return $this->model->get_categories();
    }
    
    /**
     * Get all vendors
     */
    public function get_vendors() {
        return $this->model->get_vendors();
    }
    
    /**
     * Get price range
     */
    public function get_price_range() {
        return $this->model->get_price_range();
    }
}
?>
