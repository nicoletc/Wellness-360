<?php
/**
 * Create Discussion Action
 * Handles creation of new discussions
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/CommunityController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please log in to create a discussion.']);
    exit;
}

// Get form data
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['content']) ? trim($_POST['content']) : '';

// Validate input
if (empty($category) || empty($title) || empty($description)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
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
    $result = $controller->createDiscussion([
        'customer_id' => $customer_id,
        'category' => $category,
        'title' => $title,
        'description' => $description
    ]);
    
    if ($result['status'] === 'success') {
        echo json_encode($result);
    } else {
        http_response_code(400);
        // Include detailed error message
        echo json_encode([
            'status' => 'error', 
            'message' => $result['message'] ?? 'Failed to create discussion. Please try again.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Fatal Error: ' . $e->getMessage()]);
}
?>

