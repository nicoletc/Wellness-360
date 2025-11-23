<?php
/**
 * Product Actions
 * Handles all product-related operations and search functionality
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/ShopController.php';

header('Content-Type: application/json');

// Get action type
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $controller = new ShopController();
    
    switch ($action) {
        case 'get_all_products':
            // Get all products with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $products = $controller->get_all_products($limit, $offset);
            $total = $controller->get_product_count();
            
            json_response([
                'status' => true,
                'products' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'search_products':
            // Search products
            $query = $_GET['query'] ?? $_POST['query'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            if (empty($query)) {
                json_response([
                    'status' => false,
                    'message' => 'Search query is required.'
                ], 400);
            }
            
            $products = $controller->search_products($query, $limit, $offset);
            $total = $controller->get_product_count(['search' => $query]);
            
            json_response([
                'status' => true,
                'products' => $products,
                'query' => $query,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'filter_by_category':
            // Filter products by category
            $cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            if ($cat_id <= 0) {
                json_response([
                    'status' => false,
                    'message' => 'Invalid category ID.'
                ], 400);
            }
            
            $products = $controller->filter_by_category($cat_id, $limit, $offset);
            $total = $controller->get_product_count(['category' => $cat_id]);
            
            json_response([
                'status' => true,
                'products' => $products,
                'category_id' => $cat_id,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'filter_by_vendor':
            // Filter products by vendor
            $vendor_id = isset($_GET['vendor_id']) ? (int)$_GET['vendor_id'] : 0;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            if ($vendor_id <= 0) {
                json_response([
                    'status' => false,
                    'message' => 'Invalid vendor ID.'
                ], 400);
            }
            
            $products = $controller->filter_by_vendor($vendor_id, $limit, $offset);
            $total = $controller->get_product_count(['vendor' => $vendor_id]);
            
            json_response([
                'status' => true,
                'products' => $products,
                'vendor_id' => $vendor_id,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'composite_search':
            // Composite search with multiple filters
            $filters = [
                'category' => $_GET['category'] ?? $_POST['category'] ?? 'all',
                'vendor' => $_GET['vendor'] ?? $_POST['vendor'] ?? 'all',
                'min_price' => isset($_GET['min_price']) ? floatval($_GET['min_price']) : null,
                'max_price' => isset($_GET['max_price']) ? floatval($_GET['max_price']) : null,
                'search' => $_GET['search'] ?? $_POST['search'] ?? '',
                'keyword' => $_GET['keyword'] ?? $_POST['keyword'] ?? '',
                'sort' => $_GET['sort'] ?? $_POST['sort'] ?? 'date'
            ];
            
            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== null && $value !== 'all';
            });
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $products = $controller->composite_search($filters, $limit, $offset);
            $total = $controller->get_product_count($filters);
            
            json_response([
                'status' => true,
                'products' => $products,
                'filters' => $filters,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'get_product':
            // Get single product
            $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($product_id <= 0) {
                json_response([
                    'status' => false,
                    'message' => 'Invalid product ID.'
                ], 400);
            }
            
            $product = $controller->get_product($product_id);
            
            if ($product) {
                json_response([
                    'status' => true,
                    'product' => $product
                ]);
            } else {
                json_response([
                    'status' => false,
                    'message' => 'Product not found.'
                ], 404);
            }
            break;
            
        case 'get_categories':
            // Get all categories
            $categories = $controller->get_categories();
            json_response([
                'status' => true,
                'categories' => $categories
            ]);
            break;
            
        case 'get_vendors':
            // Get all vendors
            $vendors = $controller->get_vendors();
            json_response([
                'status' => true,
                'vendors' => $vendors
            ]);
            break;
            
        default:
            json_response([
                'status' => false,
                'message' => 'Invalid action.'
            ], 400);
    }
    
} catch (Exception $e) {
    json_response([
        'status' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
?>

