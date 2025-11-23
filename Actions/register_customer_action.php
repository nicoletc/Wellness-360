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

// If registration successful, transfer cart items and set session
if ($result['status'] && isset($result['customer_id'])) {
    $customer_id = $result['customer_id'];
    
    // Fetch customer data for session
    $customer = $controller->get_customer_by_id_ctr($customer_id);
    
    if ($customer && is_array($customer)) {
        
        // Set session variables for auto-login after registration
        $_SESSION[SESS_USER_ID] = $customer['customer_id'];
        $_SESSION[SESS_USER_NAME] = $customer['customer_name'];
        $_SESSION[SESS_USER_EMAIL] = $customer['customer_email'];
        $_SESSION[SESS_USER_ROLE] = $customer['user_role'] ?? 2;
        
        if (isset($customer['customer_contact'])) {
            $_SESSION['customer_contact'] = $customer['customer_contact'];
        }
        if (isset($customer['customer_image'])) {
            $_SESSION['customer_image'] = $customer['customer_image'];
        }
    }
    
    // Transfer cart items from IP to customer_id (if guest had items in cart)
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cart_controller = new cart_controller();
    $transfer_result = $cart_controller->transfer_cart_from_ip_ctr($ip_address, $customer_id);
    
    // Handle redirect
    $redirect_url = null;
    if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
        $redirect_url = sanitize_input($_POST['redirect']);
        // Ensure it's a relative path for security
        if (strpos($redirect_url, 'http') === 0) {
            $redirect_url = null; // Reject absolute URLs
        }
    }
    
    if ($redirect_url) {
        $result['redirect'] = app_url($redirect_url);
    } else {
        // Default to home page
        $result['redirect'] = app_url(PATH_HOME);
    }
}

// Return JSON response
json_response($result, $result['status'] ? 200 : 400);

?>

