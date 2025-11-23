<?php
/**
 * Fetch Workshops Action
 * Returns all workshops as JSON
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

try {
    $controller = new workshop_controller();
    $workshops = $controller->get_all_workshops_ctr();
    
    if ($workshops === false) {
        echo json_encode(['status' => false, 'message' => 'Error fetching workshops.']);
    } else {
        echo json_encode(['status' => true, 'data' => $workshops]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

