<?php
/**
 * Record Activity Action
 * Receives activity data from frontend and records it in the database
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/ActivityModel.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_response([
        'status' => false,
        'message' => 'Invalid JSON data.'
    ], 400);
}

try {
    $activityModel = new ActivityModel();
    
    // Prepare activity data
    $activityData = [
        'customer_id' => is_logged_in() ? current_user_id() : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'activity_type' => $input['activity_type'] ?? 'page_view',
        'content_type' => $input['content_type'] ?? 'page',
        'content_id' => (int)($input['content_id'] ?? 0),
        'category_id' => isset($input['category_id']) ? (int)$input['category_id'] : null,
        'time_spent_seconds' => (int)($input['time_spent_seconds'] ?? 0)
    ];
    
    // Record activity
    $result = $activityModel->recordActivity($activityData);
    
    if ($result['status']) {
        json_response([
            'status' => true,
            'message' => 'Activity recorded successfully.',
            'category_id' => $result['category_id'] ?? null
        ]);
    } else {
        json_response([
            'status' => false,
            'message' => $result['message']
        ], 500);
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    $error_trace = $e->getTraceAsString();
    
    error_log("Error recording activity: " . $error_message);
    error_log("Stack trace: " . $error_trace);
    error_log("Input data: " . print_r($input ?? [], true));
    
    json_response([
        'status' => false,
        'message' => 'An error occurred while recording activity: ' . $error_message
    ], 500);
}

?>

