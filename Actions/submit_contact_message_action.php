<?php
/**
 * Submit Contact Message Action
 * Handles contact form submissions (calls controller)
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/ContactController.php';

header('Content-Type: application/json');

try {
    $controller = new ContactController();
    $result = $controller->submitMessage($_POST);
    
    if ($result['status']) {
        json_response([
            'status' => true,
            'message' => $result['message']
        ]);
    } else {
        json_response([
            'status' => false,
            'message' => $result['message']
        ], 500);
    }
    
} catch (Exception $e) {
    error_log("Error submitting contact message: " . $e->getMessage());
    
    json_response([
        'status' => false,
        'message' => 'An error occurred while sending your message. Please try again.'
    ], 500);
}
?>

