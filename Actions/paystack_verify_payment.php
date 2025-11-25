<?php
/**
 * Paystack Verify Payment Action
 * Verifies payment with Paystack, creates order, records payment, and empties cart
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/paystack_config.php';
require_once __DIR__ . '/../Controllers/order_controller.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

// Set content type to JSON
header('Content-Type: application/json');

error_log("=== PAYSTACK VERIFY PAYMENT ===");

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => 'error',
        'verified' => false,
        'message' => 'Please login to verify payment'
    ], 401);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => 'error',
        'verified' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : '';
$total_amount = isset($input['total_amount']) ? floatval($input['total_amount']) : 0;

// Validate reference
if (empty($reference)) {
    json_response([
        'status' => 'error',
        'verified' => false,
        'message' => 'Transaction reference is required.'
    ], 400);
}

// Validate amount
if ($total_amount <= 0) {
    json_response([
        'status' => 'error',
        'verified' => false,
        'message' => 'Invalid amount.'
    ], 400);
}

try {
    // Verify transaction with Paystack
    $paystack_response = paystack_verify_transaction($reference);
    
    if (!$paystack_response) {
        throw new Exception("Failed to verify transaction with Paystack");
    }
    
    // Check if transaction was successful
    if (!isset($paystack_response['data']['status']) || $paystack_response['data']['status'] !== 'success') {
        $status = $paystack_response['data']['status'] ?? 'unknown';
        throw new Exception("Payment status is $status. Only successful payments are accepted.");
    }
    
    // Get transaction data
    $transaction_data = $paystack_response['data'];
    $paid_amount_pesewas = (int)$transaction_data['amount'];
    $paid_amount_ghs = pesewas_to_ghs($paid_amount_pesewas);
    
    // Validate amount matches (allow 1 pesewa tolerance for rounding)
    $amount_difference = abs($paid_amount_ghs - $total_amount);
    if ($amount_difference > 0.01) {
        error_log("Amount mismatch - Expected: $total_amount GHS, Paid: $paid_amount_ghs GHS");
        throw new Exception("Payment amount mismatch. Expected: ₵" . number_format($total_amount, 2) . ", Paid: ₵" . number_format($paid_amount_ghs, 2));
    }
    
    // Get customer ID
    $customer_id = current_user_id();
    
    // Get cart items
    $cart_controller = new cart_controller();
    $cart_items = $cart_controller->get_user_cart_ctr($customer_id);
    
    if (empty($cart_items)) {
        throw new Exception("Cart is empty. Cannot create order.");
    }
    
    // Start database transaction (using manual transaction handling)
    // Create order
    $order_controller = new order_controller();
    $order_result = $order_controller->create_order_ctr([
        'customer_id' => $customer_id,
        'order_status' => 'paid'
    ]);
    
    if (!$order_result['status']) {
        throw new Exception("Failed to create order: " . $order_result['message']);
    }
    
    $order_id = $order_result['order_id'];
    $invoice_no = $order_result['invoice_no'];
    
    // Add order details for each cart item
    $product_controller = new product_controller();
    $errors = [];
    $item_count = 0;
    
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
        } else {
            $item_count++;
        }
    }
    
    // If there were errors adding items, we still proceed but log them
    if (!empty($errors)) {
        error_log("Order creation warnings: " . implode(', ', $errors));
    }
    
    // Get Paystack payment details
    // Payment method is always 'paystack' for Paystack payments
    $payment_method = 'paystack';
    $transaction_ref = $reference;
    $authorization_code = get_authorization_code($paystack_response);
    
    // Get payment channel from Paystack response or use default
    $payment_channel = get_payment_channel($transaction_data);
    
    // If payment channel is not available from Paystack, try to get from session
    if (empty($payment_channel) && isset($_SESSION['paystack_payment_channel'])) {
        $payment_channel = $_SESSION['paystack_payment_channel'];
    }
    
    // Record payment with Paystack details
    $payment_result = $order_controller->record_payment_ctr([
        'customer_id' => $customer_id,
        'order_id' => $order_id,
        'amt' => $paid_amount_ghs,
        'currency' => PAYSTACK_CURRENCY,
        'payment_method' => $payment_method,
        'transaction_ref' => $transaction_ref,
        'authorization_code' => $authorization_code,
        'payment_channel' => $payment_channel
    ]);
    
    if (!$payment_result['status']) {
        error_log("Payment recording failed: " . $payment_result['message']);
        // Order is created but payment recording failed - still return success but note the issue
    }
    
    // Empty the cart
    $empty_result = $cart_controller->empty_cart_ctr($customer_id);
    
    if (!$empty_result['status']) {
        error_log("Failed to empty cart: " . $empty_result['message']);
        // Cart emptying failed but order is created - log but don't fail
    }
    
    // Get customer name
    $customer_name = current_user_name();
    
    // Format order date
    $order_date = date('F j, Y');
    
    // Clear session variables
    unset($_SESSION['paystack_ref'], $_SESSION['paystack_amount'], $_SESSION['paystack_timestamp']);
    
    error_log("Payment verified successfully - Order ID: $order_id, Invoice: $invoice_no, Reference: $reference");
    
    // Return success response
    json_response([
        'status' => 'success',
        'verified' => true,
        'message' => 'Payment successful! Order confirmed.',
        'order_id' => $order_id,
        'invoice_no' => $invoice_no,
        'total_amount' => number_format($paid_amount_ghs, 2),
        'currency' => PAYSTACK_CURRENCY,
        'order_date' => $order_date,
        'customer_name' => $customer_name,
        'item_count' => $item_count,
        'payment_reference' => $reference,
        'payment_method' => ucfirst($payment_channel),
        'customer_email' => $transaction_data['customer']['email'] ?? current_user_email()
    ]);
    
} catch (Exception $e) {
    error_log("Error verifying Paystack payment: " . $e->getMessage());
    
    json_response([
        'status' => 'error',
        'verified' => false,
        'message' => 'Payment verification failed: ' . $e->getMessage()
    ], 400);
}

?>

