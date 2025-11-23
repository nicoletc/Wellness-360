<?php
/**
 * Add to Wishlist Action
 * Adds a product to the user's wishlist
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/wishlist_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => 'error',
        'message' => 'Please log in or sign up to add items to your wishlist.',
        'requires_login' => true
    ], 401);
}

$customer_id = current_user_id();

if (!$customer_id) {
    json_response([
        'status' => 'error',
        'message' => 'User not found. Please log in again.'
    ], 401);
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Debug logging
error_log("Add to wishlist - customer_id: $customer_id, product_id: $product_id");

if ($product_id <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Invalid product ID.'
    ], 400);
}

try {
    $wishlist_controller = new wishlist_controller();
    $result = $wishlist_controller->add_to_wishlist_ctr($customer_id, $product_id);
    
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

