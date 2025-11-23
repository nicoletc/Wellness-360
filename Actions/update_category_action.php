<?php
/**
 * Update Category Action
 * Receives data from category update form and invokes the category controller
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/category_controller.php';

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

$data = [
    'cat_id' => (int)($_POST['cat_id'] ?? 0),
    'cat_name' => sanitize_input($_POST['cat_name'] ?? '')
];

$controller = new category_controller();
$result = $controller->update_category_ctr($data);

json_response($result, $result['status'] ? 200 : 400);

?>

