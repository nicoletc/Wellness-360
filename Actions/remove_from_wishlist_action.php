<?php
/**
 * Remove from Wishlist Action
 * Removes a product from the user's wishlist
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/wishlist_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => 'error',
        'message' => 'Please log in to manage your wishlist.'
    ], 401);
}

$customer_id = current_user_id();

if (!$customer_id) {
    json_response([
        'status' => 'error',
        'message' => 'User not found. Please log in again.'
    ], 401);
}

// Get product_id from POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Debug logging
error_log("Remove from wishlist action - POST data: " . print_r($_POST, true));
error_log("Remove from wishlist action - product_id: $product_id");

if ($product_id <= 0) {
    error_log("Remove from wishlist action - Invalid product_id: $product_id");
    json_response([
        'status' => 'error',
        'message' => 'Invalid product ID.'
    ], 400);
}

try {
    $wishlist_controller = new wishlist_controller();
    $result = $wishlist_controller->remove_from_wishlist_ctr($customer_id, $product_id);
    
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

