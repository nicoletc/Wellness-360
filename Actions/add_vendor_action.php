<?php
/**
 * Add Vendor Action
 * Receives data from vendor creation form and invokes the vendor controller
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

$data = [
    'vendor_name' => sanitize_input($_POST['vendor_name'] ?? ''),
    'vendor_email' => sanitize_input($_POST['vendor_email'] ?? ''),
    'vendor_contact' => sanitize_input($_POST['vendor_contact'] ?? ''),
    'product_stock' => (int)($_POST['product_stock'] ?? 0)
];

$controller = new vendor_controller();
$result = $controller->add_vendor_ctr($data);

json_response($result, $result['status'] ? 200 : 400);

?>

