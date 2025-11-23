<?php
/**
 * Update Product Action
 * Receives data from product update form and invokes the product controller
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

$data = [
    'product_id' => (int)($_POST['product_id'] ?? 0),
    'product_title' => sanitize_input($_POST['product_title'] ?? ''),
    'product_cat' => (int)($_POST['product_cat'] ?? 0),
    'product_vendor' => (int)($_POST['product_vendor'] ?? 0),
    'product_price' => floatval($_POST['product_price'] ?? 0),
    'product_desc' => sanitize_input($_POST['product_desc'] ?? ''),
    'product_image' => sanitize_input($_POST['product_image'] ?? ''),
    'product_keywords' => sanitize_input($_POST['product_keywords'] ?? ''),
    'stock' => (int)($_POST['stock'] ?? 0)
];

$controller = new product_controller();
$result = $controller->update_product_ctr($data);

json_response($result, $result['status'] ? 200 : 400);

?>

