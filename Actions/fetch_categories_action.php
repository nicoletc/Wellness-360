<?php
/**
 * Fetch Categories Action
 * Fetches all categories for dropdown population
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

try {
    $controller = new category_controller();
    $categories = $controller->get_all_categories_ctr();
    
    json_response([
        'status' => true,
        'categories' => $categories
    ], 200);
} catch (Exception $e) {
    json_response([
        'status' => false,
        'message' => 'Error fetching categories: ' . $e->getMessage()
    ], 500);
}

?>

