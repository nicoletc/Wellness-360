<?php
/**
 * Update Profile Action
 * Updates the customer's name in the database and session
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/customer_class.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => 'error',
        'message' => 'Please log in to update your profile.'
    ], 401);
}

$customer_id = current_user_id();

if (!$customer_id) {
    json_response([
        'status' => 'error',
        'message' => 'User not found. Please log in again.'
    ], 401);
}

// Get and validate the new name
$new_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';

if (empty($new_name)) {
    json_response([
        'status' => 'error',
        'message' => 'Name is required.'
    ], 400);
}

// Validate name length
if (strlen($new_name) < 2) {
    json_response([
        'status' => 'error',
        'message' => 'Name must be at least 2 characters long.'
    ], 400);
}

if (strlen($new_name) > 100) {
    json_response([
        'status' => 'error',
        'message' => 'Name must not exceed 100 characters.'
    ], 400);
}

try {
    $customer_class = new customer_class();
    
    // Update customer name in database
    $result = $customer_class->update($customer_id, [
        'customer_name' => $new_name
    ]);
    
    if ($result['status']) {
        // Update session with new name
        $_SESSION[SESS_USER_NAME] = $new_name;
        
        json_response([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'customer_name' => $new_name
        ]);
    } else {
        json_response([
            'status' => 'error',
            'message' => $result['message'] || 'Failed to update profile.'
        ], 500);
    }
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ], 500);
}
?>

