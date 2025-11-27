<?php
/**
 * Upload Workshop Image Action
 * Handles workshop image uploads and stores them in uploads/u{user_id}/w{workshop_id}/ directory
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

// Check if admin (for workshop uploads)
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized access.']);
    exit;
}

$userId = (int)($_SESSION[SESS_USER_ID] ?? 0);
$workshopId = (int)($_POST['workshop_id'] ?? 0);

if ($userId <= 0 || $workshopId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid user or workshop id.']);
    exit;
}

if (!isset($_FILES['workshop_image']) || $_FILES['workshop_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'No image uploaded.']);
    exit;
}

// ---- Paths & security: only inside /uploads ----
$uploadsRoot = realpath(__DIR__ . '/../../uploads');
if ($uploadsRoot === false) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Uploads folder missing.']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['workshop_image']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit;
}

// Check file size (5MB max)
if ($_FILES['workshop_image']['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'File size exceeds 5MB limit.']);
    exit;
}

$userDir = $uploadsRoot . '/u' . $userId;
$workshopDir = $userDir . '/w' . $workshopId;

// Create directories if they don't exist
if (!is_dir($userDir)) {
    if (!mkdir($userDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Failed to create user directory.']);
        exit;
    }
}

if (!is_dir($workshopDir)) {
    if (!mkdir($workshopDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Failed to create workshop directory.']);
        exit;
    }
}

// Generate unique filename
$filename = 'workshop_image_' . time() . '_' . uniqid() . '.' . $ext;
$targetPath = $workshopDir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($_FILES['workshop_image']['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to save image.']);
    exit;
}

// Relative path for database
$relativePath = '../../uploads/u' . $userId . '/w' . $workshopId . '/' . $filename;

// Update database
$db = new db_connection();
if (!$db->db_connect()) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
    exit;
}

$relativePathEscaped = $db->escape_string($relativePath);
$updateSql = "UPDATE workshops SET workshop_image = '$relativePathEscaped' WHERE workshop_id = $workshopId";
if (!$db->db_query($updateSql)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to update database.']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Image uploaded successfully.',
    'path' => $relativePath
]);
?>

