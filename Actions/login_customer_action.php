<?php
/**
 * Login Customer Action
 * Receives data from login form, verifies credentials, and sets session
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

// Get and sanitize input data
$data = [
    'customer_email' => sanitize_input($_POST['customer_email'] ?? ''),
    'customer_pass' => $_POST['customer_pass'] ?? '' // Don't sanitize password
];

// Create controller instance
$controller = new customer_controller();

// Login customer
$result = $controller->login_customer_ctr($data);

if ($result['status']) {
    // Set session variables
    $customer = $result['customer'];
    $_SESSION[SESS_USER_ID] = $customer['customer_id'];
    $_SESSION[SESS_USER_NAME] = $customer['customer_name'];
    $_SESSION[SESS_USER_EMAIL] = $customer['customer_email'];
    $_SESSION[SESS_USER_ROLE] = $customer['user_role'];
    
    // Add customer contact and image to session if available
    if (isset($customer['customer_contact'])) {
        $_SESSION['customer_contact'] = $customer['customer_contact'];
    }
    if (isset($customer['customer_image'])) {
        $_SESSION['customer_image'] = $customer['customer_image'];
    }
    
    // Transfer cart items from IP to customer_id (if guest had items in cart)
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cart_controller = new cart_controller();
    $transfer_result = $cart_controller->transfer_cart_from_ip_ctr($ip_address, $customer['customer_id']);
    
    // Determine redirect path
    // Check if there's a redirect parameter in the request
    $redirect_url = null;
    if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
        $redirect_url = sanitize_input($_POST['redirect']);
        // Ensure it's a relative path for security
        if (strpos($redirect_url, 'http') === 0) {
            $redirect_url = null; // Reject absolute URLs
        }
    }
    
    if (!$redirect_url) {
        // Default redirect based on role
        $redirect_path = ($customer['user_role'] == ROLE_ADMIN) ? PATH_ADMIN : PATH_HOME;
        $redirect_url = app_url($redirect_path);
    } else {
        // Use the redirect parameter
        $redirect_url = app_url($redirect_url);
    }
    
    $result['redirect'] = $redirect_url;
}

// Return JSON response
json_response($result, $result['status'] ? 200 : 401);

?>

