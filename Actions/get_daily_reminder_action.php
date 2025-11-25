<?php
/**
 * Get Daily Reminder Action
 * Returns daily reminder/motivational quote for logged-in user
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/ReminderModel.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => false,
        'message' => 'Please login to view reminders.'
    ], 401);
}

try {
    $reminderModel = new ReminderModel();
    $customer_id = current_user_id();
    
    // Get today's reminder
    $reminder = $reminderModel->getDailyReminder($customer_id);
    
    if ($reminder) {
        json_response([
            'status' => true,
            'reminder' => [
                'id' => (int)$reminder['reminder_id'],
                'title' => $reminder['title'],
                'message' => $reminder['message'],
                'type' => $reminder['reminder_type'],
                'content_id' => isset($reminder['content_id']) ? (int)$reminder['content_id'] : null,
                'is_read' => (bool)$reminder['is_read']
            ]
        ]);
    } else {
        json_response([
            'status' => false,
            'message' => 'No reminder available.'
        ], 404);
    }
    
} catch (Exception $e) {
    error_log("Error getting daily reminder: " . $e->getMessage());
    
    json_response([
        'status' => false,
        'message' => 'An error occurred while fetching reminder.'
    ], 500);
}

?>

