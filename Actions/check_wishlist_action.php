<?php
/**
 * Check Wishlist Action
 * Checks if a product is in the user's wishlist
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/wishlist_controller.php';

header('Content-Type: application/json');

$customer_id = is_logged_in() ? current_user_id() : null;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Invalid product ID.',
        'in_wishlist' => false
    ], 400);
}

if (!$customer_id) {
    json_response([
        'status' => 'success',
        'in_wishlist' => false
    ]);
}

try {
    $wishlist_controller = new wishlist_controller();
    $in_wishlist = $wishlist_controller->is_in_wishlist_ctr($customer_id, $product_id);
    
    json_response([
        'status' => 'success',
        'in_wishlist' => $in_wishlist
    ]);
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage(),
        'in_wishlist' => false
    ], 500);
}
?>

