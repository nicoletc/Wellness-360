<?php
/**
 * Upload Profile Image Action
 * Handles profile image uploads and stores them in uploads/u{user_id}/profile/ directory
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

$userId = (int)($_SESSION[SESS_USER_ID] ?? 0);

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid user id.']);
    exit;
}

if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
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

$ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
$ok  = ['jpg','jpeg','png','gif','webp'];
if (!in_array($ext, $ok, true)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Unsupported file type.']);
    exit;
}

// Check file size (max 5MB)
if ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'File size exceeds 5MB limit.']);
    exit;
}

// Create user/profile folders
$uDirAbs = $uploadsRoot . "/u{$userId}";
$profileDirAbs = $uDirAbs . "/profile";
if (!is_dir($uDirAbs) && !mkdir($uDirAbs, 0777, true)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to create user folder.']);
    exit;
}
if (!is_dir($profileDirAbs) && !mkdir($profileDirAbs, 0777, true)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to create profile folder.']);
    exit;
}

// Delete old profile images (keep only the latest)
$oldFiles = glob($profileDirAbs.'/image_*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
foreach ($oldFiles as $oldFile) {
    @unlink($oldFile);
}

// Use image_1 for profile (only one profile image per user)
$destAbs = $profileDirAbs . "/image_1.{$ext}";

// Final guard: ensure destination stays inside /uploads
$realDest = realpath(dirname($destAbs));
if ($realDest === false || strpos($realDest, $uploadsRoot) !== 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid destination path.']);
    exit;
}

if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $destAbs)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Could not store file.']);
    exit;
}

// Store RELATIVE path in DB (same pattern as products)
$relative = "../../uploads/u{$userId}/profile/image_1.{$ext}";

try {
    $db = new db_connection();
    
    if (!$db->db_connect()) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
        exit;
    }
    
    $userIdEscaped = (int)$userId;
    $relativeEscaped = $db->escape_string($relative);
    
    $sql = "UPDATE customers SET customer_image = '$relativeEscaped' WHERE customer_id = $userIdEscaped";
    
    $result = $db->db_query($sql);
    
    if ($result) {
        // Update session with new image path
        $_SESSION['customer_image'] = $relative;
        
        echo json_encode(['status'=>'success','path'=>$relative,'image_path'=>$relative]);
    } else {
        http_response_code(500);
        $error = mysqli_error($db->db);
        echo json_encode(['status'=>'error','message'=>'DB update failed: ' . ($error ?: 'Unknown error')]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB update failed: ' . $e->getMessage()]);
}

?>

