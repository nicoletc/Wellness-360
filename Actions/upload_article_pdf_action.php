<?php
/**
 * Upload Article PDF Action
 * Handles article PDF uploads and stores them directly in the database as LONGBLOB
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';

header('Content-Type: application/json');

// Must be logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'You must be logged in to upload.']);
    exit;
}

// Check if admin (for article uploads)
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized access.']);
    exit;
}

// Get article ID
$articleId = (int)($_POST['article_id'] ?? 0);

if ($articleId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid article id.']);
    exit;
}

if (!isset($_FILES['article_pdf']) || $_FILES['article_pdf']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'No PDF uploaded.']);
    exit;
}

// Validate file extension
$ext = strtolower(pathinfo($_FILES['article_pdf']['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Only PDF files are allowed.']);
    exit;
}

// Validate file size (max 10MB for PDFs)
$max_size = 10 * 1024 * 1024; // 10MB in bytes
if ($_FILES['article_pdf']['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'File size exceeds maximum allowed size of 10MB.']);
    exit;
}

// Read PDF file content directly from uploaded file
$pdfContent = file_get_contents($_FILES['article_pdf']['tmp_name']);

if ($pdfContent === false || empty($pdfContent)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to read PDF file content.']);
    exit;
}

// Verify we have actual content
if (strlen($pdfContent) === 0) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'PDF file is empty.']);
    exit;
}

try {
    $db = new db_connection();
    
    // Ensure database connection is established
    if (!$db->db_connect()) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
        exit;
    }
    
    $articleIdEscaped = (int)$articleId;
    
    // Use prepared statement for binary data
    $stmt = mysqli_prepare($db->db, "UPDATE articles SET article_body = ? WHERE article_id = ?");
    if (!$stmt) {
        http_response_code(500);
        $error = mysqli_error($db->db);
        echo json_encode(['status'=>'error','message'=>'Failed to prepare statement: ' . $error]);
        exit;
    }
    
    // Bind parameters: 'b' for blob, 'i' for integer
    // For blob, we bind a null variable first, then send the data
    $blob = null;
    if (!mysqli_stmt_bind_param($stmt, "bi", $blob, $articleIdEscaped)) {
        http_response_code(500);
        $error = mysqli_stmt_error($stmt);
        echo json_encode(['status'=>'error','message'=>'Failed to bind parameters: ' . $error]);
        mysqli_stmt_close($stmt);
        exit;
    }
    
    // Send the binary data (parameter index 0 is the first ? which is article_body)
    if (!mysqli_stmt_send_long_data($stmt, 0, $pdfContent)) {
        http_response_code(500);
        $error = mysqli_stmt_error($stmt);
        echo json_encode(['status'=>'error','message'=>'Failed to send binary data: ' . $error]);
        mysqli_stmt_close($stmt);
        exit;
    }
    
    // Execute the statement
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        $error = mysqli_stmt_error($stmt);
        echo json_encode(['status'=>'error','message'=>'Failed to execute statement: ' . $error]);
        mysqli_stmt_close($stmt);
        exit;
    }
    
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    // Verify the data was stored
    $verify_sql = "SELECT LENGTH(article_body) as pdf_size FROM articles WHERE article_id = $articleIdEscaped";
    $verify_result = $db->db_fetch_one($verify_sql);
    
    if ($verify_result && isset($verify_result['pdf_size']) && $verify_result['pdf_size'] > 0) {
        echo json_encode([
            'status'=>'success',
            'message'=>'PDF uploaded and stored in database.',
            'article_id'=>$articleId, 
            'pdf_size'=>$verify_result['pdf_size'],
            'affected_rows'=>$affected_rows
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status'=>'error',
            'message'=>'PDF upload completed but verification failed. PDF size: ' . ($verify_result['pdf_size'] ?? 'NULL'),
            'affected_rows'=>$affected_rows
        ]);
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB update failed: ' . $e->getMessage()]);
}

?>
