<?php
/**
 * Upload Article Image Action
 * Handles article image uploads and stores them in uploads/u{user_id}/a{article_id}/ directory
 * Updates the database directly with the image path
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

$userId    = (int)($_SESSION[SESS_USER_ID] ?? 0);
$articleId = (int)($_POST['article_id'] ?? 0);

if ($userId <= 0 || $articleId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid user or article id.']);
    exit;
}

if (!isset($_FILES['article_image']) || $_FILES['article_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'No image uploaded.']);
    exit;
}

// ---- Paths & security: only inside /uploads ----
$uploadsRoot = realpath(__DIR__ . '/../../uploads');   // folder already exists (per assignment)
if ($uploadsRoot === false) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Uploads folder missing.']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['article_image']['name'], PATHINFO_EXTENSION));
$ok  = ['jpg','jpeg','png','gif','webp'];
if (!in_array($ext, $ok, true)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Unsupported file type.']);
    exit;
}

// Check file size (max 5MB)
if ($_FILES['article_image']['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'File size exceeds 5MB limit.']);
    exit;
}

// Create user/article folders (same pattern as products)
$uDirAbs = $uploadsRoot . "/u{$userId}";
$aDirAbs = $uDirAbs . "/a{$articleId}";
if (!is_dir($uDirAbs) && !mkdir($uDirAbs, 0777, true)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to create user folder.']);
    exit;
}
if (!is_dir($aDirAbs) && !mkdir($aDirAbs, 0777, true)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to create article folder.']);
    exit;
}

// Next index (image_1, image_2…)
$files = glob($aDirAbs.'/image_*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
$next  = count($files) + 1;

$destAbs = $aDirAbs . "/image_{$next}.{$ext}";

// Final guard: ensure destination stays inside /uploads
$realDest = realpath(dirname($destAbs));
if ($realDest === false || strpos($realDest, $uploadsRoot) !== 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid destination path.']);
    exit;
}

if (!move_uploaded_file($_FILES['article_image']['tmp_name'], $destAbs)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Could not store file.']);
    exit;
}

// Store RELATIVE path in DB (same pattern as products)
$relative = "../../uploads/u{$userId}/a{$articleId}/image_{$next}.{$ext}";

try {
    $db = new db_connection();
    
    // Ensure database connection is established
    if (!$db->db_connect()) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
        exit;
    }
    
    $articleIdEscaped = (int)$articleId; // Article ID is already an integer
    $relativeEscaped = $db->escape_string($relative);
    
    // Verify the escaped string is not empty
    if (empty($relativeEscaped)) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Failed to escape image path.']);
        exit;
    }
    
    $sql = "UPDATE articles SET article_image = '$relativeEscaped' WHERE article_id = $articleIdEscaped";
    
    // Log the SQL for debugging
    error_log("Article image upload SQL: " . $sql);
    error_log("Article ID: " . $articleIdEscaped);
    error_log("Image path: " . $relativeEscaped);
    
    $result = $db->db_query($sql);
    
    if ($result) {
        // Verify the update worked
        $verifySql = "SELECT article_image FROM articles WHERE article_id = $articleIdEscaped";
        $verifyResult = $db->db_fetch_one($verifySql);
        error_log("Article image after update: " . ($verifyResult['article_image'] ?? 'NULL'));
        
        echo json_encode(['status'=>'success','path'=>$relative,'image_path'=>$relative,'article_id'=>$articleId]);
    } else {
        http_response_code(500);
        $error = mysqli_error($db->db);
        error_log("Article image DB update failed: " . ($error ?: 'Unknown error'));
        echo json_encode(['status'=>'error','message'=>'DB update failed: ' . ($error ?: 'Unknown error')]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB update failed: ' . $e->getMessage()]);
}

