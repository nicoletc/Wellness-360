<?php
/**
 * Register Workshop Action
 * Handles workshop registration for customers
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/workshop_registration_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Please log in to register for workshops.']);
    exit;
}

// Get workshop ID
$workshop_id = isset($_POST['workshop_id']) ? (int)$_POST['workshop_id'] : 0;

if ($workshop_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid workshop ID.']);
    exit;
}

$customer_id = current_user_id();

if (!$customer_id) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'User not found. Please log in again.']);
    exit;
}

try {
    $controller = new workshop_registration_controller();
    $result = $controller->register_ctr($workshop_id, $customer_id);
    
    // Get updated registration count
    if ($result['status']) {
        $registration_count = $controller->get_registration_count_ctr($workshop_id);
        $result['registration_count'] = $registration_count;
    }
    
    if ($result['status']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

