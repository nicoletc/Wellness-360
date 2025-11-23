<?php
/**
 * Delete Category Action
 * Receives category ID and invokes the category controller to delete the category
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

$cat_id = (int)($_POST['cat_id'] ?? 0);

if ($cat_id <= 0) {
    json_response([
        'status' => false,
        'message' => 'Invalid category ID.'
    ], 400);
}

$controller = new category_controller();
$result = $controller->delete_category_ctr($cat_id);

json_response($result, $result['status'] ? 200 : 400);

?>

