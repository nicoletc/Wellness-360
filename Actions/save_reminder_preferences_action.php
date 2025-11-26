<?php
/**
 * Save Reminder Preferences Action
 * Saves user's reminder preferences
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/ReminderPreferencesModel.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => false,
        'message' => 'Please login to save preferences.'
    ], 401);
}

$customer_id = current_user_id();

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST; // Fallback to POST if JSON not available
}

// Handle reminder_time - ensure it's in HH:MM:SS format
$reminderTime = sanitize_input($input['reminder_time'] ?? '09:00:00');
// If it's in HH:MM format, add :00
if (preg_match('/^\d{2}:\d{2}$/', $reminderTime)) {
    $reminderTime = $reminderTime . ':00';
}
// If it's longer than HH:MM:SS, truncate it
if (strlen($reminderTime) > 8) {
    $reminderTime = substr($reminderTime, 0, 8);
}
// Validate time format
if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $reminderTime)) {
    $reminderTime = '09:00:00'; // Default to 9 AM if invalid
}

$preferences = [
    'reminder_frequency' => sanitize_input($input['reminder_frequency'] ?? 'daily'),
    'preferred_categories' => isset($input['preferred_categories']) && is_array($input['preferred_categories']) 
        ? array_map('intval', $input['preferred_categories']) 
        : null,
    'email_reminders_enabled' => isset($input['email_reminders_enabled']) ? (int)$input['email_reminders_enabled'] : 0,
    'reminder_time' => $reminderTime
];

// Validate frequency
if (!in_array($preferences['reminder_frequency'], ['daily', 'weekly', 'never'])) {
    json_response([
        'status' => false,
        'message' => 'Invalid reminder frequency.'
    ], 400);
}

try {
    $prefsModel = new ReminderPreferencesModel();
    $result = $prefsModel->savePreferences($customer_id, $preferences);
    
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
    error_log("Error saving reminder preferences: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    json_response([
        'status' => false,
        'message' => 'An error occurred while saving preferences: ' . $e->getMessage()
    ], 500);
}
?>

