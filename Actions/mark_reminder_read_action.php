<?php
/**
 * Mark Reminder as Read Action
 * Marks a daily reminder as read
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/ReminderModel.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => false,
        'message' => 'Please login to manage reminders.'
    ], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
$reminder_id = isset($input['reminder_id']) ? (int)$input['reminder_id'] : 0;
$customer_id = current_user_id();

if ($reminder_id <= 0) {
    json_response([
        'status' => false,
        'message' => 'Invalid reminder ID.'
    ], 400);
}

try {
    $reminderModel = new ReminderModel();
    $result = $reminderModel->markReminderAsRead($reminder_id, $customer_id);
    
    if ($result) {
        json_response([
            'status' => true,
            'message' => 'Reminder marked as read.'
        ]);
    } else {
        json_response([
            'status' => false,
            'message' => 'Failed to mark reminder as read.'
        ], 500);
    }
} catch (Exception $e) {
    error_log("Error marking reminder as read: " . $e->getMessage());
    
    json_response([
        'status' => false,
        'message' => 'An error occurred.'
    ], 500);
}
?>

