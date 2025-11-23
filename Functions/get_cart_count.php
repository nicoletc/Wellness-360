<?php
/**
 * Get Cart Count Helper Function
 * Returns the cart item count for the current user
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';

function get_cart_count() {
    try {
        $cart_controller = new cart_controller();
        return $cart_controller->get_cart_item_count_ctr();
    } catch (Exception $e) {
        return 0;
    }
}
?>

