<?php
/**
 * Wishlist Class
 * Extends database connection and contains wishlist (product_likes) methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class wishlist_class extends db_connection
{
    /**
     * Add product to wishlist
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return array Result with status and message
     */
    public function addToWishlist($customer_id, $product_id)
    {
        $customer_id = (int)$customer_id;
        $product_id = (int)$product_id;
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        // Check if product exists
        $product_sql = "SELECT product_id FROM customer_products WHERE product_id = $product_id LIMIT 1";
        $product = $this->db_fetch_one($product_sql);
        
        if (!$product || !is_array($product) || empty($product['product_id'])) {
            return [
                'status' => false,
                'message' => 'Product not found.'
            ];
        }
        
        // Try to insert directly - let the database handle duplicates
        // This is more reliable than checking first, as it avoids race conditions
        error_log("=== WISHLIST ADD ===");
        error_log("customer_id: $customer_id, product_id: $product_id");
        
        // Add to wishlist
        $sql = "INSERT INTO product_likes (customer_id, product_id, created_at) 
                VALUES ($customer_id, $product_id, NOW())";
        
        $insert_result = $this->db_write_query($sql);
        $db_error = mysqli_error($this->db);
        
        error_log("Wishlist insert result: " . ($insert_result ? 'true' : 'false'));
        error_log("Wishlist insert error: " . $db_error);
        
        if ($insert_result) {
            // Verify the insert was successful by checking for the new record
            $verify_sql = "SELECT like_id FROM product_likes WHERE customer_id = $customer_id AND product_id = $product_id LIMIT 1";
            $verify_result = $this->db_fetch_one($verify_sql);
            
            error_log("Wishlist verify result: " . print_r($verify_result, true));
            
            if ($verify_result && is_array($verify_result) && isset($verify_result['like_id']) && !empty($verify_result['like_id'])) {
                error_log("Wishlist: Successfully added with like_id: " . $verify_result['like_id']);
                return [
                    'status' => true,
                    'message' => 'Product added to wishlist successfully.'
                ];
            } else {
                error_log("Wishlist: Insert reported success but verification failed");
                return [
                    'status' => false,
                    'message' => 'Failed to verify wishlist addition. Please try again.'
                ];
            }
        } else {
            // Insert failed - check why
            error_log("Wishlist: Insert failed with error: " . $db_error);
            
            // Check if error is due to duplicate entry (MySQL error code 1062)
            // But first, verify if the record actually exists now
            $verify_sql = "SELECT like_id FROM product_likes WHERE customer_id = $customer_id AND product_id = $product_id LIMIT 1";
            $verify_result = $this->db_fetch_one($verify_sql);
            
            if ($verify_result && is_array($verify_result) && isset($verify_result['like_id']) && !empty($verify_result['like_id'])) {
                // Record exists - it was already in wishlist
                error_log("Wishlist: Record exists after failed insert - was already in wishlist");
                return [
                    'status' => false,
                    'message' => 'Product is already in your wishlist.'
                ];
            }
            
            // Check if error is due to duplicate entry (MySQL error code 1062)
            if (!empty($db_error) && (strpos($db_error, 'Duplicate') !== false || strpos($db_error, '1062') !== false || strpos($db_error, 'UNIQUE') !== false)) {
                error_log("Wishlist: Duplicate entry detected via MySQL error");
                return [
                    'status' => false,
                    'message' => 'Product is already in your wishlist.'
                ];
            }
            
            // Some other error occurred
            error_log("Wishlist: Insert failed with unknown error");
            return [
                'status' => false,
                'message' => 'Failed to add to wishlist. Please try again.'
            ];
        }
    }
    
    /**
     * Remove product from wishlist
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return array Result with status and message
     */
    public function removeFromWishlist($customer_id, $product_id)
    {
        $customer_id = (int)$customer_id;
        $product_id = (int)$product_id;
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        error_log("=== WISHLIST REMOVE ===");
        error_log("customer_id: $customer_id, product_id: $product_id");
        
        // Try to delete directly - check affected rows to determine if it existed
        $sql = "DELETE FROM product_likes WHERE customer_id = $customer_id AND product_id = $product_id";
        $delete_result = $this->db_write_query($sql);
        
        // Check affected rows immediately after delete
        $affected_rows = mysqli_affected_rows($this->db);
        $db_error = mysqli_error($this->db);
        
        error_log("Wishlist delete result: " . ($delete_result ? 'true' : 'false'));
        error_log("Wishlist affected_rows: $affected_rows");
        error_log("Wishlist delete error: " . ($db_error ? $db_error : 'none'));
        
        if (!$delete_result) {
            // Query execution failed
            error_log("Wishlist: Delete query execution failed");
            return [
                'status' => false,
                'message' => 'Failed to remove from wishlist. Please try again.'
            ];
        }
        
        // Check affected rows to see if a record was actually deleted
        if ($affected_rows > 0) {
            // Successfully deleted
            error_log("Wishlist: Successfully removed - affected_rows: $affected_rows");
            return [
                'status' => true,
                'message' => 'Product removed from wishlist successfully.'
            ];
        } else {
            // No rows affected - product was not in wishlist
            error_log("Wishlist: No rows affected - product not in wishlist");
            return [
                'status' => false,
                'message' => 'Product is not in your wishlist.'
            ];
        }
    }
    
    /**
     * Check if product is in wishlist
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return bool True if in wishlist, false otherwise
     */
    public function isInWishlist($customer_id, $product_id)
    {
        $customer_id = (int)$customer_id;
        $product_id = (int)$product_id;
        
        if (!$this->db_connect()) {
            return false;
        }
        
        $sql = "SELECT like_id FROM product_likes WHERE customer_id = $customer_id AND product_id = $product_id LIMIT 1";
        $result = $this->db_fetch_one($sql);
        
        return ($result && is_array($result) && !empty($result['like_id']));
    }
    
    /**
     * Get user's wishlist
     * @param int $customer_id Customer ID
     * @return array Wishlist items
     */
    public function getUserWishlist($customer_id)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT pl.product_id, pl.created_at,
                       p.product_title, p.product_price, p.product_image
                FROM product_likes pl
                INNER JOIN customer_products p ON pl.product_id = p.product_id
                WHERE pl.customer_id = $customer_id
                ORDER BY pl.created_at DESC";
        
        $wishlist = $this->db_fetch_all($sql);
        
        if (!$wishlist || !is_array($wishlist)) {
            return [];
        }
        
        return $wishlist;
    }
    
    /**
     * Get wishlist count for user
     * @param int $customer_id Customer ID
     * @return int Count of items in wishlist
     */
    public function getWishlistCount($customer_id)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count FROM product_likes WHERE customer_id = $customer_id";
        $result = $this->db_fetch_one($sql);
        
        return (int)($result['count'] ?? 0);
    }
}
?>

