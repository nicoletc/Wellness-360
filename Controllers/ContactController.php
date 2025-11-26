<?php
/**
 * Contact Controller
 * Handles logic for the Contact page
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/ContactModel.php';
require_once __DIR__ . '/../Classes/ContactMessageModel.php';

class ContactController {
    private $model;
    private $messageModel;
    
    public function __construct() {
        $this->model = new ContactModel();
        $this->messageModel = new ContactMessageModel();
    }
    
    /**
     * Get all data for the contact page
     */
    public function index() {
        return $this->model->loadData();
    }
    
    /**
     * Submit a contact message
     * @param array $data Form data
     * @return array Result with status and message
     */
    public function submitMessage($data) {
        // Validate required fields
        $required = ['firstName', 'lastName', 'email', 'subject', 'message'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'status' => false,
                    'message' => ucfirst($field) . ' is required.'
                ];
            }
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Invalid email address.'
            ];
        }
        
        // Prepare data for model
        $messageData = [
            'first_name' => sanitize_input($data['firstName']),
            'last_name' => sanitize_input($data['lastName']),
            'email' => sanitize_input($data['email']),
            'phone' => !empty($data['phone']) ? sanitize_input($data['phone']) : null,
            'subject' => sanitize_input($data['subject']),
            'message' => sanitize_input($data['message']),
            'customer_id' => is_logged_in() ? current_user_id() : null
        ];
        
        return $this->messageModel->saveMessage($messageData);
    }
}

?>

