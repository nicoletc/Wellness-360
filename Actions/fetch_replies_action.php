<?php
/**
 * Fetch Replies Action
 * Handles fetching replies for a discussion
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/CommunityController.php';

header('Content-Type: application/json');

// Get discussion ID
$comm_id = isset($_GET['comm_id']) ? (int)$_GET['comm_id'] : 0;

if ($comm_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid discussion ID.', 'replies' => []]);
    exit;
}

try {
    $controller = new CommunityController();
    $result = $controller->getReplies($comm_id);
    
    if ($result['status'] === 'success') {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage(), 'replies' => []]);
}
?>

