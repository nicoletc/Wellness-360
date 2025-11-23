<?php
/**
 * Fetch Vendor Action
 * Invokes the relevant function from the vendor controller to fetch all vendors
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response([
        'status' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

try {
    $controller = new vendor_controller();
    $vendors = $controller->get_all_vendors_ctr();

    json_response([
        'status' => true,
        'message' => 'Vendors fetched successfully.',
        'vendors' => $vendors
    ], 200);
} catch (Exception $e) {
    json_response([
        'status' => false,
        'message' => 'Error fetching vendors: ' . $e->getMessage()
    ], 500);
}

?>

