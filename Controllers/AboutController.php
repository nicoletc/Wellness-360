<?php
/**
 * About Controller
 * Handles logic for the About page
 */

require_once __DIR__ . '/../Classes/AboutModel.php';

class AboutController {
    private $model;
    
    public function __construct() {
        $this->model = new AboutModel();
    }
    
    /**
     * Get all data for the about page
     */
    public function index() {
        return $this->model->loadData();
    }
}

?>

