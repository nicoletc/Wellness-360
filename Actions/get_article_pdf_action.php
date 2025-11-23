<?php
/**
 * Get Article PDF Action
 * Returns the PDF binary data for an article
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/article_class.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error','message'=>'You must be logged in.']);
    exit;
}

$article_id = (int)($_GET['article_id'] ?? 0);

if ($article_id <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error','message'=>'Invalid article ID.']);
    exit;
}

try {
    $article = new article_class();
    $pdf_data = $article->get_article_pdf($article_id);
    
    if ($pdf_data === false) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status'=>'error','message'=>'PDF not found.']);
        exit;
    }
    
    // Set headers for PDF download/view
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="article_' . $article_id . '.pdf"');
    header('Content-Length: ' . strlen($pdf_data));
    
    // Output PDF binary data
    echo $pdf_data;
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error','message'=>'Error retrieving PDF: ' . $e->getMessage()]);
    exit;
}

?>

