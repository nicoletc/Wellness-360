<?php
/**
 * Paystack Configuration
 * Contains API keys, configuration, and helper functions for Paystack integration
 */

// Include core settings
require_once __DIR__ . '/core.php';

// Environment configuration
define('APP_ENVIRONMENT', 'test'); // Change to 'live' for production

// Dynamically determine base URL
function get_app_base_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    $base_path = get_base_path();
    return $protocol . '://' . $host . $base_path . APP_BASE;
}

define('APP_BASE_URL', get_app_base_url());

// Paystack API Keys
define('PAYSTACK_SECRET_KEY', 'sk_test_f78af66703a0345d4a49cb465ca9846ae9e7a8d6'); // Your secret key
define('PAYSTACK_PUBLIC_KEY', 'pk_test_6cd6fe692309339fd6b31e5a56e1b872c6be590f'); // Your public key (optional, for client-side)

// Paystack API URLs
define('PAYSTACK_API_URL', 'https://api.paystack.co');
define('PAYSTACK_INITIALIZE_URL', PAYSTACK_API_URL . '/transaction/initialize');
define('PAYSTACK_VERIFY_URL', PAYSTACK_API_URL . '/transaction/verify/');

// Callback URL
define('PAYSTACK_CALLBACK_URL', APP_BASE_URL . '/View/paystack_callback.php');

// Currency
define('PAYSTACK_CURRENCY', 'GHS'); // Ghana Cedis

/**
 * Initialize Paystack transaction
 * @param float $amount Amount in GHS
 * @param string $email Customer email
 * @param string $reference Unique transaction reference
 * @return array|false Paystack response or false on failure
 */
function paystack_initialize_transaction($amount, $email, $reference)
{
    // Convert GHS to pesewas (smallest currency unit)
    $amount_in_pesewas = (int)($amount * 100);
    
    if ($amount_in_pesewas <= 0) {
        error_log("Paystack: Invalid amount - $amount GHS");
        return false;
    }
    
    $data = [
        'email' => $email,
        'amount' => $amount_in_pesewas,
        'reference' => $reference,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'currency' => PAYSTACK_CURRENCY
    ];
    
    $response = paystack_api_call(PAYSTACK_INITIALIZE_URL, $data);
    
    if ($response && isset($response['status']) && $response['status'] === true) {
        error_log("Paystack: Transaction initialized successfully - Reference: $reference");
        return $response;
    } else {
        $error_msg = isset($response['message']) ? $response['message'] : 'Unknown error';
        error_log("Paystack: Failed to initialize transaction - $error_msg");
        return false;
    }
}

/**
 * Verify Paystack transaction
 * @param string $reference Transaction reference
 * @return array|false Transaction data or false on failure
 */
function paystack_verify_transaction($reference)
{
    if (empty($reference)) {
        error_log("Paystack: Empty reference provided for verification");
        return false;
    }
    
    $url = PAYSTACK_VERIFY_URL . $reference;
    $response = paystack_api_call($url, null, 'GET');
    
    if ($response && isset($response['status']) && $response['status'] === true) {
        error_log("Paystack: Transaction verified successfully - Reference: $reference");
        return $response;
    } else {
        $error_msg = isset($response['message']) ? $response['message'] : 'Unknown error';
        error_log("Paystack: Failed to verify transaction - $error_msg");
        return false;
    }
}

/**
 * Make API call to Paystack
 * @param string $url API endpoint URL
 * @param array|null $data POST data (null for GET requests)
 * @param string $method HTTP method (POST or GET)
 * @return array|false API response or false on failure
 */
function paystack_api_call($url, $data = null, $method = 'POST')
{
    $ch = curl_init();
    
    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    if ($method === 'POST' && $data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        error_log("Paystack API cURL Error: $error");
        return false;
    }
    
    if ($http_code !== 200) {
        error_log("Paystack API HTTP Error: $http_code - Response: $response");
        return false;
    }
    
    $decoded = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Paystack API JSON Error: " . json_last_error_msg());
        return false;
    }
    
    return $decoded;
}

/**
 * Convert pesewas to GHS
 * @param int $pesewas Amount in pesewas
 * @return float Amount in GHS
 */
function pesewas_to_ghs($pesewas)
{
    return (float)($pesewas / 100);
}

/**
 * Get payment channel from Paystack response
 * Maps Paystack channel values to our ENUM values
 * @param array $transaction_data Transaction data from Paystack
 * @return string Payment channel (card, mobile_money, bank)
 */
function get_payment_channel($transaction_data)
{
    if (isset($transaction_data['data']['channel'])) {
        $paystack_channel = strtolower($transaction_data['data']['channel']);
        
        // Map Paystack channel values to our ENUM values
        $channel_map = [
            'card' => 'card',
            'bank' => 'bank',
            'mobile_money' => 'mobile_money',
            'mobilemoney' => 'mobile_money',
            'momo' => 'mobile_money',
            'ussd' => 'bank',
            'qr' => 'bank',
            'bank_transfer' => 'bank',
            'banktransfer' => 'bank'
        ];
        
        // Return mapped value or default to card
        return $channel_map[$paystack_channel] ?? 'card';
    }
    return 'card'; // Default
}

/**
 * Get authorization code from Paystack response
 * @param array $transaction_data Transaction data from Paystack
 * @return string|null Authorization code or null
 */
function get_authorization_code($transaction_data)
{
    if (isset($transaction_data['data']['authorization']['authorization_code'])) {
        return $transaction_data['data']['authorization']['authorization_code'];
    }
    return null;
}

?>

