<?php
/**
 * Get Order Details Action
 * Returns detailed information about a specific order including items
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/order_class.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => 'error',
        'message' => 'Please log in to view order details.'
    ], 401);
}

$customer_id = current_user_id();
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Invalid order ID.'
    ], 400);
}

try {
    $order_class = new order_class();
    $order_details = $order_class->getOrderDetails($order_id);
    
    if (!$order_details) {
        json_response([
            'status' => 'error',
            'message' => 'Order not found.'
        ], 404);
    }
    
    // Verify the order belongs to the current user
    if ($order_details['order']['customer_id'] != $customer_id) {
        json_response([
            'status' => 'error',
            'message' => 'You do not have permission to view this order.'
        ], 403);
    }
    
    // Format the response
    $response = [
        'status' => 'success',
        'order' => [
            'order_id' => (int)$order_details['order']['order_id'],
            'invoice_no' => $order_details['order']['invoice_no'],
            'order_date' => date('F j, Y', strtotime($order_details['order']['order_date'])),
            'order_status' => ucfirst($order_details['order']['order_status']),
        ],
        'items' => [],
        'payment' => null
    ];
    
    // Format items
    foreach ($order_details['items'] as $item) {
        $response['items'][] = [
            'product_id' => (int)$item['product_id'],
            'product_title' => $item['product_title'],
            'product_image' => $item['product_image'] ?: 'uploads/placeholder.jpg',
            'product_price' => floatval($item['product_price']),
            'quantity' => (int)$item['qty'],
            'subtotal' => floatval($item['product_price']) * (int)$item['qty']
        ];
    }
    
    // Format payment if exists
    if ($order_details['payment']) {
        $response['payment'] = [
            'amount' => floatval($order_details['payment']['amt']),
            'currency' => $order_details['payment']['currency'],
            'payment_date' => date('F j, Y', strtotime($order_details['payment']['payment_date']))
        ];
    }
    
    // Calculate total
    $total = 0;
    foreach ($response['items'] as $item) {
        $total += $item['subtotal'];
    }
    $response['total'] = $total;
    
    json_response($response);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ], 500);
}
?>

