<?php
/**
 * Upload Helpers
 * Helper functions for file upload validation and path management
 */

require_once __DIR__ . '/../settings/core.php';

/**
 * Ensures a path is inside /uploads by comparing realpaths.
 */
function assert_inside_uploads(string $absPath): bool {
    $uploadsRoot = realpath(__DIR__ . '/../uploads');
    $destReal    = realpath(dirname($absPath));
    // If destination doesn't exist yet, create and then check
    if (!$destReal) {
        @mkdir(dirname($absPath), 0775, true);
        $destReal = realpath(dirname($absPath));
    }
    return $uploadsRoot && $destReal && str_starts_with($destReal, $uploadsRoot);
}

/**
 * Save a $_FILES image to /uploads/u{uid}/p{id}/filename.ext
 */
function save_uploaded_image_strict(string $field, int $user_id, int $product_id): array {
    if (empty($_FILES[$field]['name'])) return ['ok'=>false,'error'=>'No file'];

    // Basic checks
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return ['ok'=>false,'error'=>'Upload error'];
    $tmp  = $_FILES[$field]['tmp_name'];
    $name = basename($_FILES[$field]['name']);
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp', 'avif'])) return ['ok'=>false,'error'=>'Unsupported type'];

    $baseDir = __DIR__ . '/../uploads/u'.$user_id.'/p'.$product_id;
    @mkdir($baseDir, 0775, true);

    $destAbs  = $baseDir . '/main.' . $ext; // normalize name
    if (!assert_inside_uploads($destAbs)) return ['ok'=>false,'error'=>'Invalid path'];

    if (!move_uploaded_file($tmp, $destAbs)) return ['ok'=>false,'error'=>'Cannot move file'];

    $rel = 'uploads/u'.$user_id.'/p'.$product_id.'/main.'.$ext;
    return ['ok'=>true,'relative'=>$rel];
}

/**
 * Move an existing file path (used by bulk zip) into the strict uploads tree.
 */
function save_uploaded_image_from_path(string $srcAbs, int $user_id, int $product_id, string $origName): array {
    if (!file_exists($srcAbs)) return ['ok'=>false,'error'=>'Source missing'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return ['ok'=>false,'error'=>'Unsupported type'];

    $baseDir = __DIR__ . '/../uploads/u'.$user_id.'/p'.$product_id;
    @mkdir($baseDir, 0775, true);
    $destAbs = $baseDir . '/main.'.$ext;
    if (!assert_inside_uploads($destAbs)) return ['ok'=>false,'error'=>'Invalid path'];

    if (!rename($srcAbs, $destAbs)) {
        if (!copy($srcAbs, $destAbs)) return ['ok'=>false,'error'=>'Cannot move image'];
        @unlink($srcAbs);
    }
    $rel = 'uploads/u'.$user_id.'/p'.$product_id.'/main.'.$ext;
    return ['ok'=>true,'relative'=>$rel];
}

?>

