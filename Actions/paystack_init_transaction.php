<?php
/**
 * Paystack Initialize Transaction Action
 * Handles payment initialization requests and returns Paystack authorization URL
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/paystack_config.php';

// Set content type to JSON
header('Content-Type: application/json');

error_log("=== PAYSTACK INITIALIZE TRANSACTION ===");

// Check if user is logged in
if (!is_logged_in()) {
    json_response([
        'status' => 'error',
        'message' => 'Please login to complete payment'
    ], 401);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ], 405);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? floatval($input['amount']) : 0;
$customer_email = isset($input['email']) ? trim($input['email']) : '';
$payment_channel = isset($input['payment_channel']) ? trim($input['payment_channel']) : 'card';

// Validate amount
if (!$amount || $amount <= 0) {
    json_response([
        'status' => 'error',
        'message' => 'Amount must be greater than 0'
    ], 400);
}

// Validate email
if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'status' => 'error',
        'message' => 'Invalid email address'
    ], 400);
}

try {
    // Generate unique reference (W360 = Wellness 360)
    $customer_id = current_user_id();
    $reference = 'W360-' . $customer_id . '-' . time();
    
    error_log("Initializing transaction - Customer: $customer_id, Amount: $amount GHS, Email: $customer_email");
    
    // Initialize Paystack transaction
    $paystack_response = paystack_initialize_transaction($amount, $customer_email, $reference);
    
    if (!$paystack_response) {
        throw new Exception("No response from Paystack API");
    }
    
    if (isset($paystack_response['status']) && $paystack_response['status'] === true) {
        // Store transaction reference and payment channel in session for verification later
        $_SESSION['paystack_ref'] = $reference;
        $_SESSION['paystack_amount'] = $amount;
        $_SESSION['paystack_timestamp'] = time();
        $_SESSION['paystack_payment_channel'] = $payment_channel;
        
        error_log("Paystack transaction initialized successfully - Reference: $reference");
        
        json_response([
            'status' => 'success',
            'authorization_url' => $paystack_response['data']['authorization_url'],
            'reference' => $reference,
            'access_code' => $paystack_response['data']['access_code'],
            'message' => 'Redirecting to payment gateway...'
        ]);
    } else {
        error_log("Paystack API error: " . json_encode($paystack_response));
        
        $error_message = $paystack_response['message'] ?? 'Payment gateway error';
        throw new Exception($error_message);
    }
    
} catch (Exception $e) {
    error_log("Error initializing Paystack transaction: " . $e->getMessage());
    
    json_response([
        'status' => 'error',
        'message' => 'Failed to initialize payment: ' . $e->getMessage()
    ], 500);
}

?>

