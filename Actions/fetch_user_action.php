<?php
/**
 * Fetch User Action
 * Invokes the relevant function from the user controller to fetch all users
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

try {
    $controller = new user_controller();
    $users = $controller->get_all_users_ctr();

    json_response([
        'status' => true,
        'message' => 'Users fetched successfully.',
        'users' => $users
    ], 200);
} catch (Exception $e) {
    json_response([
        'status' => false,
        'message' => 'Error fetching users: ' . $e->getMessage()
    ], 500);
}

?>

