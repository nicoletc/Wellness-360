<?php
/**
 * Process Checkout Action
 * Handles the backend processing of the checkout flow after payment confirmation
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';
require_once __DIR__ . '/../Controllers/order_controller.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

header('Content-Type: application/json');

// Check if user is logged in (required for checkout)
if (!is_logged_in()) {
    json_response([
        'status' => 'error',
        'message' => 'Please log in to complete checkout.'
    ], 401);
}

$customer_id = current_user_id();

if (!$customer_id) {
    json_response([
        'status' => 'error',
        'message' => 'User not found. Please log in again.'
    ], 401);
}

try {
    $cart_controller = new cart_controller();
    $order_controller = new order_controller();
    $product_controller = new product_controller();
    
    // Get cart items
    $cart_items = $cart_controller->get_user_cart_ctr($customer_id);
    
    if (empty($cart_items)) {
        json_response([
            'status' => 'error',
            'message' => 'Your cart is empty.'
        ], 400);
    }
    
    // Calculate total
    $total = $cart_controller->get_cart_total_ctr($customer_id);
    
    // Create order (status is 'paid' since user confirmed payment)
    $order_result = $order_controller->create_order_ctr([
        'customer_id' => $customer_id,
        'order_status' => 'paid'
    ]);
    
    if (!$order_result['status']) {
        json_response([
            'status' => 'error',
            'message' => 'Failed to create order: ' . $order_result['message']
        ], 500);
    }
    
    $order_id = $order_result['order_id'];
    $invoice_no = $order_result['invoice_no'];
    
    // Add order details for each cart item
    $errors = [];
    foreach ($cart_items as $item) {
        // Verify product still exists and get current price
        $product = $product_controller->get_product_ctr($item['product_id']);
        
        if (!$product) {
            $errors[] = "Product '{$item['product_title']}' no longer exists.";
            continue;
        }
        
        // Check stock
        if (isset($product['stock']) && $product['stock'] < $item['quantity']) {
            $errors[] = "Insufficient stock for '{$item['product_title']}'. Available: {$product['stock']}, Requested: {$item['quantity']}";
            continue;
        }
        
        $detail_result = $order_controller->add_order_details_ctr([
            'order_id' => $order_id,
            'product_id' => $item['product_id'],
            'qty' => $item['quantity']
        ]);
        
        if (!$detail_result['status']) {
            $errors[] = "Failed to add '{$item['product_title']}' to order: " . $detail_result['message'];
        }
    }
    
    // If there were errors adding items, rollback order
    if (!empty($errors)) {
        // Note: In production, you might want to delete the order here
        json_response([
            'status' => 'error',
            'message' => 'Some items could not be added to the order.',
            'errors' => $errors
        ], 400);
    }
    
    // Record payment
    $payment_result = $order_controller->record_payment_ctr([
        'customer_id' => $customer_id,
        'order_id' => $order_id,
        'amt' => $total,
        'currency' => 'GHS'
    ]);
    
    if (!$payment_result['status']) {
        // Order created but payment failed - still return success but note the issue
        json_response([
            'status' => 'partial',
            'message' => 'Order created but payment recording failed: ' . $payment_result['message'],
            'order_id' => $order_id,
            'invoice_no' => $invoice_no
        ], 200);
    }
    
    // Empty the cart
    $empty_result = $cart_controller->empty_cart_ctr($customer_id);
    
    // Return success response
    json_response([
        'status' => 'success',
        'message' => 'Order processed successfully.',
        'order_id' => $order_id,
        'invoice_no' => $invoice_no,
        'total' => $total,
        'payment_id' => $payment_result['payment_id'] ?? null
    ]);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ], 500);
}
?>

