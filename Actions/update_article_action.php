<?php
/**
 * Update Article Action
 * Receives data from article update form and invokes the article controller
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

$data = [
    'article_id' => (int)($_POST['article_id'] ?? 0),
    'article_title' => sanitize_input($_POST['article_title'] ?? ''),
    'article_author' => sanitize_input($_POST['article_author'] ?? ''),
    'article_cat' => (int)($_POST['article_cat'] ?? 0),
    // article_body is now LONGBLOB and handled separately via PDF upload
    'article_body' => null
];

$controller = new article_controller();
$result = $controller->update_article_ctr($data);

json_response($result, $result['status'] ? 200 : 400);

?>

