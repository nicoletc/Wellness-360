<?php
/**
 * View Article PDF Action
 * Serves the PDF content from the database
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/WellnessHubController.php';

// Get article ID from request
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    http_response_code(400);
    die('Invalid article ID.');
}

try {
    $controller = new WellnessHubController();
    
    // Get PDF binary data
    $pdf_data = $controller->get_article_pdf($article_id);
    
    if ($pdf_data === false) {
        http_response_code(404);
        die('PDF not found for this article.');
    }
    
    // Record the view (if not already recorded in this session)
    if (!isset($_SESSION['viewed_articles']) || !in_array($article_id, $_SESSION['viewed_articles'])) {
        $controller->record_view($article_id);
        if (!isset($_SESSION['viewed_articles'])) {
            $_SESSION['viewed_articles'] = [];
        }
        $_SESSION['viewed_articles'][] = $article_id;
    }
    
    // Set headers for PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="article_' . $article_id . '.pdf"');
    header('Content-Length: ' . strlen($pdf_data));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output PDF
    echo $pdf_data;
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    die('Error loading PDF: ' . $e->getMessage());
}
?>

