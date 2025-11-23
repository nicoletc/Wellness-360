<?php
/**
 * Delete Product Action
 * Receives product ID and invokes the product controller to delete the product
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

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

$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    json_response([
        'status' => false,
        'message' => 'Invalid product ID.'
    ], 400);
}

$controller = new product_controller();
$result = $controller->delete_product_ctr($product_id);

json_response($result, $result['status'] ? 200 : 400);

?>

<｜tool▁calls▁begin｜><｜tool▁call▁begin｜>
read_file
