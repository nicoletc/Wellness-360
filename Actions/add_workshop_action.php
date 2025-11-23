<?php
/**
 * Add Workshop Action
 * Handles adding a new workshop
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

// Get POST data
$workshop_title = $_POST['workshop_title'] ?? '';
$workshop_desc = $_POST['workshop_desc'] ?? '';
$workshop_leader = $_POST['workshop_leader'] ?? '';
$workshop_date = $_POST['workshop_date'] ?? '';
$workshop_time = $_POST['workshop_time'] ?? '';
$workshop_type = $_POST['workshop_type'] ?? '';
$location = $_POST['location'] ?? '';
$max_participants = $_POST['max_participants'] ?? 0;
$customer_id = current_user_id() ?? 0;

// Prepare data array
$data = [
    'workshop_title' => $workshop_title,
    'workshop_desc' => $workshop_desc,
    'workshop_leader' => $workshop_leader,
    'workshop_date' => $workshop_date,
    'workshop_time' => $workshop_time,
    'workshop_type' => $workshop_type,
    'location' => $location,
    'max_participants' => $max_participants,
    'customer_id' => $customer_id,
    'workshop_image' => '' // Will be handled separately if image upload is needed
];

// Create controller instance and add workshop
$controller = new workshop_controller();
$result = $controller->add_workshop_ctr($data);

if ($result['status']) {
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
}
?>

