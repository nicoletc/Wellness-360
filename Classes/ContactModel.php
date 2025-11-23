<?php
/**
 * Contact Model
 * Handles data operations for the Contact page
 */

class ContactModel {
    private $dataFile;
    
    public function __construct() {
        $this->dataFile = __DIR__ . '/../data/contact_data.php';
    }
    
    /**
     * Load all contact page data
     */
    public function loadData() {
        require_once $this->dataFile;
        
        return [
            'contactMethods' => $contactMethods ?? [],
            'supportHours' => $supportHours ?? [],
            'faqs' => $faqs ?? [],
            'chatMessages' => $chatMessages ?? [],
            'quickActions' => $quickActions ?? [],
        ];
    }
}

?>

