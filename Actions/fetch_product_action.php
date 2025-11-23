<?php
/**
 * Fetch Product Action
 * Fetches all products or a single product and returns them as JSON
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

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
    $controller = new product_controller();
    
    // Check if requesting a single product
    if (isset($_GET['product_id'])) {
        $product_id = (int)$_GET['product_id'];
        $product = $controller->get_product_ctr($product_id);
        
        if ($product) {
            json_response([
                'status' => true,
                'product' => $product
            ], 200);
        } else {
            json_response([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        }
    } else {
        // Fetch all products
        $products = $controller->get_all_products_ctr();
        
        json_response([
            'status' => true,
            'products' => $products
        ], 200);
    }
} catch (Exception $e) {
    json_response([
        'status' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ], 500);
}

?>

