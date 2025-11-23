<?php
/**
 * About Model
 * Handles data operations for the About page
 */

class AboutModel {
    private $dataFile;
    
    public function __construct() {
        $this->dataFile = __DIR__ . '/../data/about_data.php';
    }
    
    /**
     * Load all about page data
     */
    public function loadData() {
        require_once $this->dataFile;
        
        return [
            'ourStory' => $ourStory ?? [],
            'mission' => $mission ?? [],
            'vision' => $vision ?? [],
            'storyBehind' => $storyBehind ?? [],
            'coreValues' => $coreValues ?? [],
            'cta' => $cta ?? [],
            'placeholderImage' => $placeholderImage ?? 'uploads/placeholder.jpg',
        ];
    }
}

?>

