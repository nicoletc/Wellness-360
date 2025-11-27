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

error_log("Upload PDF: Starting upload. Article ID: $articleId");

if ($articleId <= 0) {
    http_response_code(400);
    error_log("Upload PDF: Invalid article ID: $articleId");
    echo json_encode(['status'=>'error','message'=>'Invalid article id.']);
    exit;
}

// Verify article exists before attempting upload
try {
    $db_check = new db_connection();
    if ($db_check->db_connect()) {
        $check_sql = "SELECT article_id FROM articles WHERE article_id = $articleId";
        $article_check = $db_check->db_fetch_one($check_sql);
        if (!$article_check) {
            http_response_code(404);
            error_log("Upload PDF: Article $articleId does not exist in database");
            echo json_encode(['status'=>'error','message'=>'Article not found. Please create the article first.']);
            exit;
        }
        error_log("Upload PDF: Article $articleId exists in database");
    }
} catch (Exception $e) {
    error_log("Upload PDF: Error checking article existence: " . $e->getMessage());
    // Continue anyway - the main upload will fail if article doesn't exist
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
$file_size = $_FILES['article_pdf']['size'];
if ($file_size > $max_size) {
    http_response_code(400);
    $file_size_mb = round($file_size / 1024 / 1024, 2);
    echo json_encode([
        'status'=>'error',
        'message'=>"File size ($file_size_mb MB) exceeds maximum allowed size of 10MB. Please upload a smaller file."
    ]);
    exit;
}

// Read PDF file content directly from uploaded file
$pdfContent = file_get_contents($_FILES['article_pdf']['tmp_name']);

if ($pdfContent === false || empty($pdfContent)) {
    http_response_code(500);
    error_log("Upload PDF: Failed to read PDF file. File error: " . $_FILES['article_pdf']['error']);
    echo json_encode(['status'=>'error','message'=>'Failed to read PDF file content.']);
    exit;
}

// Verify we have actual content
$pdfSize = strlen($pdfContent);
if ($pdfSize === 0) {
    http_response_code(500);
    error_log("Upload PDF: PDF file is empty");
    echo json_encode(['status'=>'error','message'=>'PDF file is empty.']);
    exit;
}

error_log("Upload PDF: File read successfully. Size: $pdfSize bytes, Article ID: $articleId");

try {
    $db = new db_connection();
    
    // Ensure database connection is established
    if (!$db->db_connect()) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
        exit;
    }
    
    $articleIdEscaped = (int)$articleId;
    
    // Check column type first to ensure it's LONGBLOB
    $column_check = "SHOW COLUMNS FROM articles WHERE Field = 'article_body'";
    $column_info = $db->db_fetch_one($column_check);
    $column_type = $column_info['Type'] ?? 'NOT FOUND';
    error_log("Upload PDF: Column type for article_body: " . $column_type);
    
    // Verify it's a BLOB type
    if (stripos($column_type, 'blob') === false && stripos($column_type, 'binary') === false) {
        error_log("Upload PDF: WARNING - article_body column type is '$column_type', expected LONGBLOB or similar BLOB type");
        // Continue anyway - might still work
    }
    
    // Use hex encoding approach for LONGBLOB - more reliable than send_long_data
    // First, set article_body to NULL to ensure clean state
    $null_sql = "UPDATE articles SET article_body = NULL WHERE article_id = $articleIdEscaped";
    $db->db_query($null_sql);
    error_log("Upload PDF: Set article_body to NULL before upload");
    
    // Convert binary data to hex string for safe storage
    $hexData = bin2hex($pdfContent);
    $hexDataEscaped = $db->escape_string($hexData);
    
    error_log("Upload PDF: Converted PDF to hex. Original size: $pdfSize bytes, Hex size: " . strlen($hexData) . " bytes");
    
    // Use UNHEX() function to convert hex back to binary when storing
    // This avoids the send_long_data issue entirely
    $sql = "UPDATE articles SET article_body = UNHEX('$hexDataEscaped') WHERE article_id = $articleIdEscaped";
    
    error_log("Upload PDF: Executing UPDATE with UNHEX()");
    $result = $db->db_query($sql);
    
    if (!$result) {
        http_response_code(500);
        $error = mysqli_error($db->db);
        error_log("Upload PDF: UPDATE failed - " . $error);
        echo json_encode(['status'=>'error','message'=>'Failed to update database: ' . $error]);
        exit;
    }
    
    $affected_rows = mysqli_affected_rows($db->db);
    error_log("Upload PDF: UPDATE executed. Affected rows: $affected_rows");
    
    error_log("Upload PDF: Statement executed. Affected rows: $affected_rows, Article ID: $articleIdEscaped, Expected PDF size: $pdfSize bytes");
    
    // Note: mysqli_stmt_affected_rows() might return 0 even if the update succeeded
    // if MySQL optimizes the query or if the data appears identical
    // So we always verify by checking the actual data in the database
    
    // Small delay to ensure database commit
    usleep(100000); // 100ms delay
    
    // Verify the data was stored
    $verify_sql = "SELECT LENGTH(article_body) as pdf_size, article_body IS NULL as is_null FROM articles WHERE article_id = $articleIdEscaped";
    $verify_result = $db->db_fetch_one($verify_sql);
    
    error_log("Upload PDF: Verification result - " . print_r($verify_result, true));
    
    // Also check if article exists
    $check_exists = $db->db_fetch_one("SELECT article_id FROM articles WHERE article_id = $articleIdEscaped");
    if (!$check_exists) {
        error_log("Upload PDF: ERROR - Article does not exist after update attempt!");
        http_response_code(404);
        echo json_encode([
            'status'=>'error',
            'message'=>'Article not found. Cannot update PDF.'
        ]);
        exit;
    }
    
    if ($verify_result && isset($verify_result['pdf_size']) && $verify_result['pdf_size'] > 0 && $verify_result['is_null'] == 0) {
        // Success - PDF is stored in database
        error_log("Upload PDF: SUCCESS - PDF stored. Size: " . $verify_result['pdf_size'] . " bytes");
        echo json_encode([
            'status'=>'success',
            'message'=>'PDF uploaded and stored in database.',
            'article_id'=>$articleId, 
            'pdf_size'=>$verify_result['pdf_size'],
            'affected_rows'=>$affected_rows
        ]);
    } else {
        // Verification failed - check what went wrong
        $pdf_size = $verify_result['pdf_size'] ?? 0;
        $is_null = $verify_result['is_null'] ?? 1;
        
        error_log("Upload PDF: VERIFICATION FAILED");
        error_log("  - PDF size: $pdf_size");
        error_log("  - Is NULL: $is_null");
        error_log("  - Affected rows: $affected_rows");
        error_log("  - Article ID: $articleIdEscaped");
        
        // Article exists (we already checked above), so the update must have failed
        http_response_code(500);
        $error_msg = 'PDF upload completed but verification failed. ';
        $error_msg .= 'PDF size in DB: ' . $pdf_size . ' bytes (expected: ' . $pdfSize . ' bytes), ';
        $error_msg .= 'Is NULL: ' . ($is_null ? 'YES' : 'NO') . ', ';
        $error_msg .= 'Affected rows: ' . $affected_rows;
        
        error_log("Upload PDF: Final error - " . $error_msg);
        
        // Try alternative approach: Use hex encoding again (in case first attempt had issues)
        error_log("Upload PDF: Attempting alternative update method with hex encoding...");
        
        // Set to NULL first
        $db->db_query("UPDATE articles SET article_body = NULL WHERE article_id = $articleIdEscaped");
        
        // Try with hex encoding again
        $retry_hex = bin2hex($pdfContent);
        $retry_hex_escaped = $db->escape_string($retry_hex);
        $retry_sql = "UPDATE articles SET article_body = UNHEX('$retry_hex_escaped') WHERE article_id = $articleIdEscaped";
        
        if ($db->db_query($retry_sql)) {
            // Re-verify
            $retry_verify = $db->db_fetch_one($verify_sql);
            if ($retry_verify && isset($retry_verify['pdf_size']) && $retry_verify['pdf_size'] > 0) {
                error_log("Upload PDF: Retry SUCCESS - PDF stored. Size: " . $retry_verify['pdf_size'] . " bytes");
                echo json_encode([
                    'status'=>'success',
                    'message'=>'PDF uploaded and stored in database (retry succeeded).',
                    'article_id'=>$articleId, 
                    'pdf_size'=>$retry_verify['pdf_size'],
                    'affected_rows'=>mysqli_affected_rows($db->db)
                ]);
                exit;
            }
        }
        
        echo json_encode([
            'status'=>'error',
            'message'=>$error_msg,
            'affected_rows'=>$affected_rows,
            'pdf_size'=>$pdf_size,
            'is_null'=>$is_null
        ]);
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB update failed: ' . $e->getMessage()]);
}

?>
