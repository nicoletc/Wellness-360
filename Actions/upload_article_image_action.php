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
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit;
}

// Check file size (max 5MB)
if ($_FILES['article_image']['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'File size exceeds 5MB limit.']);
    exit;
}

// Create directory structure: uploads/u{userId}/a{articleId}/
$targetDir = $uploadsRoot . '/u' . $userId . '/a' . $articleId;
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Could not create directory.']);
        exit;
    }
}

// Find next available image number
$next = 1;
while (file_exists($targetDir . '/image_' . $next . '.' . $ext)) {
    $next++;
}

$filename = 'image_' . $next . '.' . $ext;
$targetPath = $targetDir . '/' . $filename;

if (!move_uploaded_file($_FILES['article_image']['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to save image.']);
    exit;
}

// Relative path for database
$relativePath = '../../uploads/u' . $userId . '/a' . $articleId . '/' . $filename;

// Update database
$db = new db_connection();
if (!$db->db_connect()) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
    exit;
}

$relativePathEscaped = $db->escape_string($relativePath);
$sql = "UPDATE articles SET article_image = '$relativePathEscaped' WHERE article_id = $articleId";

if ($db->db_query($sql)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Image uploaded successfully.',
        'image_path' => $relativePath
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to update database.']);
}

