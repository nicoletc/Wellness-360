<?php
/**
 * Get Cart Count Action
 * Returns the current cart item count for the user
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';

header('Content-Type: application/json');

try {
    $cart_controller = new cart_controller();
    $count = $cart_controller->get_cart_item_count_ctr();
    
    json_response([
        'status' => 'success',
        'count' => $count
    ]);
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage(),
        'count' => 0
    ], 500);
}
?>

