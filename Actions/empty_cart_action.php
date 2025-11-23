<?php
/**
 * Empty Cart Action
 * Processes empty cart actions
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';

header('Content-Type: application/json');

try {
    $cart_controller = new cart_controller();
    $result = $cart_controller->empty_cart_ctr();
    
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

