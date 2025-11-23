<?php
/**
 * Delete Workshop Action
 * Handles deleting a workshop
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/workshop_controller.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get workshop ID
$workshop_id = isset($_POST['workshop_id']) ? (int)$_POST['workshop_id'] : (isset($_GET['workshop_id']) ? (int)$_GET['workshop_id'] : 0);

if ($workshop_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid workshop ID.']);
    exit;
}

// Create controller instance and delete workshop
$controller = new workshop_controller();
$result = $controller->delete_workshop_ctr($workshop_id);

if ($result['status']) {
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
}
?>

