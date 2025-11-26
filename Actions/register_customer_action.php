<?php
/**
 * Register Customer Action
 * Receives data from registration form and invokes customer controller
 * Also handles email availability check
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/customer_controller.php';
require_once __DIR__ . '/../Controllers/cart_controller.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

// Check if this is an email availability check
if (isset($_POST['check_email']) && $_POST['check_email'] === 'true') {
    // Get email
    $email = sanitize_input($_POST['email'] ?? '');
    
    if (empty($email)) {
        json_response([
            'status' => false,
            'message' => 'Email is required.'
        ], 400);
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response([
            'status' => false,
            'message' => 'Invalid email format.'
        ], 400);
    }
    
    // Create controller instance
    $controller = new customer_controller();
    
    // Check email availability
    $result = $controller->check_email_availability_ctr($email);
    
    // Return JSON response (status true means email exists)
    json_response($result, 200);
    exit;
}

// Get and sanitize input data
$data = [
    'customer_name' => sanitize_input($_POST['customer_name'] ?? ''),
    'customer_email' => sanitize_input($_POST['customer_email'] ?? ''),
    'customer_pass' => $_POST['customer_pass'] ?? '', // Don't sanitize password
    'customer_contact' => sanitize_input($_POST['customer_contact'] ?? ''),
    'user_role' => 2 // Default to customer role
];

// Create controller instance
$controller = new customer_controller();

// Register customer
$result = $controller->register_customer_ctr($data);

// If registration successful, transfer cart and handle redirect
if ($result['status'] && isset($result['customer_id'])) {
    // Normalize IP address (convert IPv6 localhost to IPv4)
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    
    // Also check if there's a stored IP in session (from before registration)
    $stored_ip = isset($_SESSION['guest_ip_address']) ? $_SESSION['guest_ip_address'] : $ip_address;
    
    // Transfer cart items from IP to customer_id immediately after registration
    require_once __DIR__ . '/../Controllers/cart_controller.php';
    $cart_controller = new cart_controller();
    
    // Try transferring from stored IP first (this is the IP they had when they added items)
    $transfer_result = $cart_controller->transfer_cart_from_ip_ctr($stored_ip, $result['customer_id']);
    
    // If no items transferred from stored IP, try current IP
    if ($transfer_result['transferred'] == 0 && $transfer_result['merged'] == 0 && $stored_ip !== $ip_address) {
        $transfer_result = $cart_controller->transfer_cart_from_ip_ctr($ip_address, $result['customer_id']);
    }
    
    // Clear stored IP after transfer
    unset($_SESSION['guest_ip_address']);
    
    error_log("Cart transfer on registration - Stored IP: $stored_ip, Current IP: $ip_address, Transferred: " . ($transfer_result['transferred'] ?? 0) . ", Merged: " . ($transfer_result['merged'] ?? 0));
    
    // Handle redirect
    $redirect_url = null;
    if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
        $redirect_url = sanitize_input($_POST['redirect']);
        // Ensure it's a relative path for security
        if (strpos($redirect_url, 'http') === 0) {
            $redirect_url = null; // Reject absolute URLs
        }
    }
    
    // Check if redirect contains checkout - if so, auto-login and redirect to checkout
    if ($redirect_url && strpos($redirect_url, 'checkout') !== false) {
        // Get customer data for auto-login using the customer_id from registration result
        $customer_data = $controller->get_customer_by_id_ctr($result['customer_id']);
        
        if ($customer_data) {
            // Auto-login the user since they came from checkout
            $_SESSION[SESS_USER_ID] = $customer_data['customer_id'];
            $_SESSION[SESS_USER_NAME] = $customer_data['customer_name'];
            $_SESSION[SESS_USER_EMAIL] = $customer_data['customer_email'];
            $_SESSION[SESS_USER_ROLE] = $customer_data['user_role'] ?? 2; // Customer role
            
            // Add customer contact and image to session if available
            if (isset($customer_data['customer_contact'])) {
                $_SESSION['customer_contact'] = $customer_data['customer_contact'];
            }
            if (isset($customer_data['customer_image'])) {
                $_SESSION['customer_image'] = $customer_data['customer_image'];
            }
            
            // Redirect directly to checkout (they're now logged in)
            $result['redirect'] = app_url($redirect_url);
            $result['auto_logged_in'] = true;
            $result['message'] = 'Account created successfully! You have been automatically logged in.';
        } else {
            // Fallback if customer data not found - still redirect but they'll need to log in
            $result['redirect'] = app_url($redirect_url);
        }
    } elseif ($redirect_url) {
        // Other redirects - go to the specified page (they'll need to log in)
        $result['redirect'] = app_url($redirect_url);
    } else {
        // No redirect - default to login page
        $result['redirect'] = app_url(PATH_LOGIN);
    }
}

// Return JSON response
json_response($result, $result['status'] ? 200 : 400);

?>

