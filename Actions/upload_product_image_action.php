<?php
/**
 * Upload Product Image Action
 * Handles product image uploads and stores them in uploads/u{user_id}/p{product_id}/ directory
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

// Check if admin (for product uploads)
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized access.']);
    exit;
}

$userId    = (int)($_SESSION[SESS_USER_ID] ?? 0);
$productId = (int)($_POST['product_id'] ?? 0);

if ($userId <= 0 || $productId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid user or product id.']);
    exit;
}

if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'No image uploaded.']);
    exit;
}

// ---- Paths & security: only inside /uploads ----
$uploadsRoot = realpath(__DIR__ . '/../uploads');   // folder already exists (per assignment)
if ($uploadsRoot === false) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Uploads folder missing.']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
$ok  = ['jpg','jpeg','png','gif','webp'];
if (!in_array($ext, $ok, true)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Unsupported file type.']);
    exit;
}

// Create user/product folders
$uDirAbs = $uploadsRoot . "/u{$userId}";
$pDirAbs = $uDirAbs    . "/p{$productId}";
if (!is_dir($uDirAbs) && !mkdir($uDirAbs, 0777, true)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to create user folder.']);
    exit;
}
if (!is_dir($pDirAbs) && !mkdir($pDirAbs, 0777, true)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Failed to create product folder.']);
    exit;
}

// Next index (image_1, image_2…)
$files = glob($pDirAbs.'/image_*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
$next  = count($files) + 1;

$destAbs = $pDirAbs . "/image_{$next}.{$ext}";

// Final guard: ensure destination stays inside /uploads
$realDest = realpath(dirname($destAbs));
if ($realDest === false || strpos($realDest, $uploadsRoot) !== 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid destination path.']);
    exit;
}

if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $destAbs)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Could not store file.']);
    exit;
}

// Store RELATIVE path in DB
$relative = "uploads/u{$userId}/p{$productId}/image_{$next}.{$ext}";

try {
    $db = new db_connection();
    
    // Ensure database connection is established
    if (!$db->db_connect()) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
        exit;
    }
    
    $productIdEscaped = (int)$productId; // Product ID is already an integer
    $relativeEscaped = $db->escape_string($relative);
    
    // Verify the escaped string is not empty
    if (empty($relativeEscaped)) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Failed to escape image path.']);
        exit;
    }
    
    $sql = "UPDATE products SET product_image = '$relativeEscaped' WHERE product_id = $productIdEscaped";
    $result = $db->db_query($sql);
    
    if ($result) {
        echo json_encode(['status'=>'success','path'=>$relative,'product_id'=>$productId]);
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
