<?php
/**
 * Product Class
 * Extends database connection and contains product methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class product_class extends db_connection
{
    /**
     * Add a new product to the database
     * @param array $data Product data
     * @return array Result with status and message
     */
    public function add($data)
    {
        // Validate required fields
        if (empty($data['product_title'])) {
            return [
                'status' => false,
                'message' => 'Product title is required.'
            ];
        }

        if (empty($data['product_cat']) || $data['product_cat'] <= 0) {
            return [
                'status' => false,
                'message' => 'Product category is required.'
            ];
        }

        if (empty($data['product_vendor']) || $data['product_vendor'] <= 0) {
            return [
                'status' => false,
                'message' => 'Product vendor is required.'
            ];
        }

        if (empty($data['product_price']) || $data['product_price'] < 0) {
            return [
                'status' => false,
                'message' => 'Product price is required and must be 0 or greater.'
            ];
        }

        $product_title = $this->escape_string(trim($data['product_title']));
        $product_cat = (int)$data['product_cat'];
        $product_vendor = (int)$data['product_vendor'];
        $product_price = floatval($data['product_price']);
        $product_desc = $this->escape_string(trim($data['product_desc'] ?? ''));
        $product_image = $this->escape_string(trim($data['product_image'] ?? ''));
        $product_keywords = $this->escape_string(trim($data['product_keywords'] ?? ''));
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;

        // Validate title length
        if (strlen($product_title) < 3) {
            return [
                'status' => false,
                'message' => 'Product title must be at least 3 characters long.'
            ];
        }

        if (strlen($product_title) > 200) {
            return [
                'status' => false,
                'message' => 'Product title must not exceed 200 characters.'
            ];
        }

        // Validate stock (must be non-negative)
        if ($stock < 0) {
            return [
                'status' => false,
                'message' => 'Stock cannot be negative.'
            ];
        }

        // Check if category exists
        $check_cat = "SELECT cat_id FROM category WHERE cat_id = $product_cat";
        if (!$this->db_fetch_one($check_cat)) {
            return [
                'status' => false,
                'message' => 'Selected category does not exist.'
            ];
        }

        // Check if vendor exists
        $check_vendor = "SELECT vendor_id FROM vendors WHERE vendor_id = $product_vendor";
        if (!$this->db_fetch_one($check_vendor)) {
            return [
                'status' => false,
                'message' => 'Selected vendor does not exist.'
            ];
        }

        // Insert product
        $sql = "INSERT INTO products (product_cat, product_vendor, product_title, product_price, product_desc, product_image, product_keywords, stock) 
                VALUES ($product_cat, $product_vendor, '$product_title', $product_price, '$product_desc', '$product_image', '$product_keywords', $stock)";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Product added successfully.',
                'product_id' => mysqli_insert_id($this->db)
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to add product. Please try again.'
            ];
        }
    }

    /**
     * Get all products with category and vendor information
     * @return array All products
     */
    public function get_all()
    {
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                ORDER BY p.date_added DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get a single product by ID
     * @param int $product_id Product ID
     * @return array|false Product data or false if not found
     */
    public function get_one($product_id)
    {
        $product_id = (int)$product_id;
        $sql = "SELECT p.*, c.cat_name, v.vendor_name 
                FROM products p
                LEFT JOIN category c ON p.product_cat = c.cat_id
                LEFT JOIN vendors v ON p.product_vendor = v.vendor_id
                WHERE p.product_id = $product_id";
        return $this->db_fetch_one($sql);
    }

    /**
     * Update a product
     * @param array $data Product data
     * @return array Result with status and message
     */
    public function update($data)
    {
        // Validate required fields
        if (empty($data['product_id']) || $data['product_id'] <= 0) {
            return [
                'status' => false,
                'message' => 'Product ID is required.'
            ];
        }

        if (empty($data['product_title'])) {
            return [
                'status' => false,
                'message' => 'Product title is required.'
            ];
        }

        if (empty($data['product_cat']) || $data['product_cat'] <= 0) {
            return [
                'status' => false,
                'message' => 'Product category is required.'
            ];
        }

        if (empty($data['product_vendor']) || $data['product_vendor'] <= 0) {
            return [
                'status' => false,
                'message' => 'Product vendor is required.'
            ];
        }

        if (empty($data['product_price']) || $data['product_price'] < 0) {
            return [
                'status' => false,
                'message' => 'Product price is required and must be 0 or greater.'
            ];
        }

        $product_id = (int)$data['product_id'];
        $product_title = $this->escape_string(trim($data['product_title']));
        $product_cat = (int)$data['product_cat'];
        $product_vendor = (int)$data['product_vendor'];
        $product_price = floatval($data['product_price']);
        $product_desc = $this->escape_string(trim($data['product_desc'] ?? ''));
        $product_image = $this->escape_string(trim($data['product_image'] ?? ''));
        $product_keywords = $this->escape_string(trim($data['product_keywords'] ?? ''));
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;

        // Check if product exists
        $existing = $this->get_one($product_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Product not found.'
            ];
        }

        // Validate title length
        if (strlen($product_title) < 3) {
            return [
                'status' => false,
                'message' => 'Product title must be at least 3 characters long.'
            ];
        }

        if (strlen($product_title) > 200) {
            return [
                'status' => false,
                'message' => 'Product title must not exceed 200 characters.'
            ];
        }

        // Validate stock (must be non-negative)
        if ($stock < 0) {
            return [
                'status' => false,
                'message' => 'Stock cannot be negative.'
            ];
        }

        // Check if category exists
        $check_cat = "SELECT cat_id FROM category WHERE cat_id = $product_cat";
        if (!$this->db_fetch_one($check_cat)) {
            return [
                'status' => false,
                'message' => 'Selected category does not exist.'
            ];
        }

        // Check if vendor exists
        $check_vendor = "SELECT vendor_id FROM vendors WHERE vendor_id = $product_vendor";
        if (!$this->db_fetch_one($check_vendor)) {
            return [
                'status' => false,
                'message' => 'Selected vendor does not exist.'
            ];
        }

        // Update product (always update image if provided, even if empty string to clear it)
        $sql = "UPDATE products 
                SET product_cat = $product_cat, 
                    product_vendor = $product_vendor, 
                    product_title = '$product_title', 
                    product_price = $product_price, 
                    product_desc = '$product_desc', 
                    product_image = '$product_image', 
                    product_keywords = '$product_keywords', 
                    stock = $stock 
                WHERE product_id = $product_id";

        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Product updated successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to update product. Please try again.'
            ];
        }
    }

    /**
     * Delete a product
     * @param int $product_id Product ID
     * @return array Result with status and message
     */
    public function delete($product_id)
    {
        $product_id = (int)$product_id;

        // Check if product exists
        $existing = $this->get_one($product_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Product not found.'
            ];
        }

        // Check if product is in any orders
        $check_orders = "SELECT COUNT(*) as count FROM orderdetails WHERE product_id = $product_id";
        $order_count = $this->db_fetch_one($check_orders);
        
        if ($order_count && $order_count['count'] > 0) {
            return [
                'status' => false,
                'message' => 'Cannot delete product. Product is associated with ' . $order_count['count'] . ' order(s).'
            ];
        }

        // Check if product is in any carts
        $check_cart = "SELECT COUNT(*) as count FROM cart WHERE p_id = $product_id";
        $cart_count = $this->db_fetch_one($check_cart);
        
        if ($cart_count && $cart_count['count'] > 0) {
            return [
                'status' => false,
                'message' => 'Cannot delete product. Product is in ' . $cart_count['count'] . ' cart(s).'
            ];
        }

        // Delete product
        $sql = "DELETE FROM products WHERE product_id = $product_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Product deleted successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to delete product. Please try again.'
            ];
        }
    }
}

?>

