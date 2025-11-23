<?php
/**
 * Delete User Action
 * Receives user ID and invokes the user controller to delete the user
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/user_controller.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    json_response([
        'status' => false,
        'message' => 'Unauthorized access.'
    ], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

$customer_id = (int)($_POST['customer_id'] ?? 0);

if ($customer_id <= 0) {
    json_response([
        'status' => false,
        'message' => 'Invalid user ID.'
    ], 400);
}

// Prevent deleting yourself
if ($customer_id == current_user_id()) {
    json_response([
        'status' => false,
        'message' => 'You cannot delete your own account.'
    ], 400);
}

$controller = new user_controller();
$result = $controller->delete_user_ctr($customer_id);

json_response($result, $result['status'] ? 200 : 400);

?>

