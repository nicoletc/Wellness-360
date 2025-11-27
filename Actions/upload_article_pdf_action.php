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
    
    // Use prepared statement for binary data
    // First, set article_body to NULL to ensure clean state
    $null_stmt = mysqli_prepare($db->db, "UPDATE articles SET article_body = NULL WHERE article_id = ?");
    if ($null_stmt) {
        mysqli_stmt_bind_param($null_stmt, "i", $articleIdEscaped);
        mysqli_stmt_execute($null_stmt);
        mysqli_stmt_close($null_stmt);
        error_log("Upload PDF: Set article_body to NULL before upload");
    }
    
    // Now prepare the update statement
    $stmt = mysqli_prepare($db->db, "UPDATE articles SET article_body = ? WHERE article_id = ?");
    if (!$stmt) {
        http_response_code(500);
        $error = mysqli_error($db->db);
        error_log("Upload PDF: Failed to prepare statement - " . $error);
        echo json_encode(['status'=>'error','message'=>'Failed to prepare statement: ' . $error]);
        exit;
    }
    
    // Bind parameters: Bind as string/blob type ('s')
    // For send_long_data to work, we need to bind an empty string, not NULL
    // MySQLi will recognize it as a blob when we use send_long_data
    $blob_param = '';
    if (!mysqli_stmt_bind_param($stmt, "si", $blob_param, $articleIdEscaped)) {
        http_response_code(500);
        $error = mysqli_stmt_error($stmt);
        error_log("Upload PDF: Failed to bind parameters - " . $error);
        echo json_encode(['status'=>'error','message'=>'Failed to bind parameters: ' . $error]);
        mysqli_stmt_close($stmt);
        exit;
    }
    
    // Send the binary data in chunks (parameter index 0 is the first ? which is article_body)
    // Send data in 8192 byte chunks to avoid memory issues
    $chunkSize = 8192;
    $offset = 0;
    $totalLength = strlen($pdfContent);
    
    error_log("Upload PDF: Starting to send $totalLength bytes in chunks of $chunkSize");
    
    // Send all chunks
    while ($offset < $totalLength) {
        $chunk = substr($pdfContent, $offset, $chunkSize);
        $chunkLength = strlen($chunk);
        
        if ($chunkLength > 0) {
            // Use send_long_data to send chunk (parameter index 0 = first ? placeholder)
            $send_result = mysqli_stmt_send_long_data($stmt, 0, $chunk);
            if (!$send_result) {
                http_response_code(500);
                $error = mysqli_stmt_error($stmt);
                error_log("Upload PDF: Failed to send binary data chunk at offset $offset (length: $chunkLength) - " . $error);
                error_log("Upload PDF: MySQL error: " . mysqli_error($db->db));
                echo json_encode(['status'=>'error','message'=>'Failed to send binary data: ' . $error]);
                mysqli_stmt_close($stmt);
                exit;
            }
        }
        $offset += $chunkSize;
    }
    
    error_log("Upload PDF: Finished sending all chunks. Total sent: $offset bytes");
    
    // Execute the statement
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        $error = mysqli_stmt_error($stmt);
        error_log("Upload PDF: Failed to execute statement - " . $error);
        error_log("Upload PDF: SQL Error: " . mysqli_error($db->db));
        echo json_encode(['status'=>'error','message'=>'Failed to execute statement: ' . $error]);
        mysqli_stmt_close($stmt);
        exit;
    }
    
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
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
        
        // Try alternative approach: Set to NULL first, then update
        error_log("Upload PDF: Attempting alternative update method...");
        $alt_stmt = mysqli_prepare($db->db, "UPDATE articles SET article_body = NULL WHERE article_id = ?");
        if ($alt_stmt) {
            mysqli_stmt_bind_param($alt_stmt, "i", $articleIdEscaped);
            mysqli_stmt_execute($alt_stmt);
            mysqli_stmt_close($alt_stmt);
            error_log("Upload PDF: Set article_body to NULL");
        }
        
        // Now try the update again
        $retry_stmt = mysqli_prepare($db->db, "UPDATE articles SET article_body = ? WHERE article_id = ?");
        if ($retry_stmt) {
            $retry_blob = '';
            mysqli_stmt_bind_param($retry_stmt, "si", $retry_blob, $articleIdEscaped);
            
            // Send data in chunks again
            $retry_offset = 0;
            while ($retry_offset < $totalLength) {
                $retry_chunk = substr($pdfContent, $retry_offset, $chunkSize);
                mysqli_stmt_send_long_data($retry_stmt, 0, $retry_chunk);
                $retry_offset += $chunkSize;
            }
            
            if (mysqli_stmt_execute($retry_stmt)) {
                // Re-verify
                $retry_verify = $db->db_fetch_one($verify_sql);
                if ($retry_verify && isset($retry_verify['pdf_size']) && $retry_verify['pdf_size'] > 0) {
                    error_log("Upload PDF: Retry SUCCESS - PDF stored. Size: " . $retry_verify['pdf_size'] . " bytes");
                    echo json_encode([
                        'status'=>'success',
                        'message'=>'PDF uploaded and stored in database (retry succeeded).',
                        'article_id'=>$articleId, 
                        'pdf_size'=>$retry_verify['pdf_size'],
                        'affected_rows'=>mysqli_stmt_affected_rows($retry_stmt)
                    ]);
                    mysqli_stmt_close($retry_stmt);
                    exit;
                }
            }
            mysqli_stmt_close($retry_stmt);
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
