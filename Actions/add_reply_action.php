<?php
/**
 * Add Reply Action
 * Handles adding replies to discussions
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/CommunityController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please log in to reply to discussions.']);
    exit;
}

// Get form data
$comm_id = isset($_POST['comm_id']) ? (int)$_POST['comm_id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

// Validate input
if ($comm_id <= 0 || empty($content)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Discussion ID and reply content are required.']);
    exit;
}

$customer_id = current_user_id();

if (!$customer_id) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not found. Please log in again.']);
    exit;
}

try {
    $controller = new CommunityController();
    $result = $controller->addReply([
        'comm_id' => $comm_id,
        'customer_id' => $customer_id,
        'content' => $content
    ]);
    
    if ($result['status'] === 'success') {
        // Fetch updated replies
        $replies_result = $controller->getReplies($comm_id);
        $result['replies'] = $replies_result['replies'] ?? [];
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>

