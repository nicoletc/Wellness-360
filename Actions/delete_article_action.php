<?php
/**
 * Delete Article Action
 * Receives article ID and invokes the article controller to delete the article
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/article_controller.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    json_response([
        'status' => false,
        'message' => 'Unauthorized access.'
    ], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

$article_id = (int)($_POST['article_id'] ?? 0);

if ($article_id <= 0) {
    json_response([
        'status' => false,
        'message' => 'Invalid article ID.'
    ], 400);
}

$controller = new article_controller();
$result = $controller->delete_article_ctr($article_id);

json_response($result, $result['status'] ? 200 : 400);

?>

