<?php
/**
 * Record Article View Action
 * Records a view for an article in the article_views table
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/WellnessHubController.php';

header('Content-Type: application/json');

// Get article ID from request
$article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : (isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0);

if ($article_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid article ID.']);
    exit;
}

try {
    $controller = new WellnessHubController();
    
    // Record the view
    $result = $controller->record_view($article_id);
    
    if ($result) {
        // Get updated view count
        $article = $controller->get_article($article_id);
        echo json_encode([
            'status' => true,
            'message' => 'View recorded successfully.',
            'view_count' => $article['views'] ?? 0
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Failed to record view.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

