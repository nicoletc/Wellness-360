<?php
/**
 * Remove from Cart Action
 * Processes Remove from Cart actions
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';

header('Content-Type: application/json');

// Get product ID
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Validate input
if ($product_id <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Invalid product ID.'
    ], 400);
}

try {
    $cart_controller = new cart_controller();
    $result = $cart_controller->remove_from_cart_ctr($product_id);
    
    if ($result['status']) {
        json_response([
            'status' => 'success',
            'message' => $result['message']
        ]);
    } else {
        json_response([
            'status' => 'error',
            'message' => $result['message']
        ], 400);
    }
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ], 500);
}
?>

