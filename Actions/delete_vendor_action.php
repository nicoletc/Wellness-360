<?php
/**
 * Delete Vendor Action
 * Receives vendor ID and invokes the vendor controller to delete the vendor
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/vendor_controller.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    json_response([
        'status' => false,
        'message' => 'Unauthorized access.'
    ], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

$vendor_id = (int)($_POST['vendor_id'] ?? 0);

if ($vendor_id <= 0) {
    json_response([
        'status' => false,
        'message' => 'Invalid vendor ID.'
    ], 400);
}

$controller = new vendor_controller();
$result = $controller->delete_vendor_ctr($vendor_id);

json_response($result, $result['status'] ? 200 : 400);

?>

