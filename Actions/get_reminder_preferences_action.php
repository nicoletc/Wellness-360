<?php
/**
 * Get Reminder Preferences Action
 * Returns user's reminder preferences (for client-side time checking)
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/ReminderPreferencesModel.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => false,
        'message' => 'Please login to view preferences.'
    ], 401);
}

try {
    $prefsModel = new ReminderPreferencesModel();
    $customer_id = current_user_id();
    
    $preferences = $prefsModel->getPreferences($customer_id);
    
    json_response([
        'status' => true,
        'preferences' => [
            'reminder_frequency' => $preferences['reminder_frequency'],
            'reminder_time' => $preferences['reminder_time'],
            'preferred_categories' => $preferences['preferred_categories']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error getting reminder preferences: " . $e->getMessage());
    
    json_response([
        'status' => false,
        'message' => 'An error occurred while fetching preferences.'
    ], 500);
}
?>

