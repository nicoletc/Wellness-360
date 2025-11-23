<?php
/**
 * Add to Cart Action
 * Processes Add to Cart actions
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';

header('Content-Type: application/json');

// Get product details
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate input
if ($product_id <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Invalid product ID.'
    ], 400);
}

if ($quantity <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Quantity must be greater than 0.'
    ], 400);
}

try {
    $cart_controller = new cart_controller();
    $result = $cart_controller->add_to_cart_ctr([
        'product_id' => $product_id,
        'quantity' => $quantity
    ]);
    
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

