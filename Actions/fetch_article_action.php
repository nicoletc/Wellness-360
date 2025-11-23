<?php
/**
 * Fetch Article Action
 * Fetches all articles or a single article and returns them as JSON
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

try {
    $controller = new article_controller();
    
    // Check if requesting a single article
    if (isset($_GET['article_id'])) {
        $article_id = (int)$_GET['article_id'];
        $article = $controller->get_article_ctr($article_id);
        
        if ($article) {
            json_response([
                'status' => true,
                'article' => $article
            ], 200);
        } else {
            json_response([
                'status' => false,
                'message' => 'Article not found.'
            ], 404);
        }
    } else {
        // Fetch all articles
        $articles = $controller->get_all_articles_ctr();
        
        json_response([
            'status' => true,
            'articles' => $articles
        ], 200);
    }
} catch (Exception $e) {
    json_response([
        'status' => false,
        'message' => 'Error fetching articles: ' . $e->getMessage()
    ], 500);
}

?>

