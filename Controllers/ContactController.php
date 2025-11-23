<?php
/**
 * Contact Controller
 * Handles logic for the Contact page
 */

require_once __DIR__ . '/../Classes/ContactModel.php';

class ContactController {
    private $model;
    
    public function __construct() {
        $this->model = new ContactModel();
    }
    
    /**
     * Get all data for the contact page
     */
    public function index() {
        return $this->model->loadData();
    }
}

?>

