<?php
/**
 * Cart Class
 * Extends database connection and contains cart methods
 */

require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/../settings/core.php';

class cart_class extends db_connection
{
    /**
     * Get user identifier (customer_id if logged in, IP address if guest)
     * @return array ['customer_id' => int|null, 'ip_address' => string]
     */
    public function getUserIdentifier()
    {
        $customer_id = null;
        
        // Get IP address - handle both IPv4 and IPv6
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // If IPv6 localhost (::1), convert to IPv4 localhost for consistency
        if ($ip_address === '::1') {
            $ip_address = '127.0.0.1';
        }
        
        // Normalize IP address (remove any port numbers if present)
        if (strpos($ip_address, ':') !== false && strpos($ip_address, '::') === false) {
            // IPv6 with port, extract IP part
            $parts = explode(':', $ip_address);
            if (count($parts) > 1) {
                $ip_address = implode(':', array_slice($parts, 0, -1));
            }
        }
        
        if (is_logged_in()) {
            $customer_id = current_user_id();
        }
        
        error_log("Cart getUserIdentifier - customer_id: " . ($customer_id ?? 'null') . ", ip: $ip_address");
        
        return [
            'customer_id' => $customer_id,
            'ip_address' => $ip_address
        ];
    }
    
    /**
     * Check if a product already exists in the cart
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string $ip_address IP address for guest
     * @return array|false Cart item if exists, false otherwise
     */
    public function productExistsInCart($product_id, $customer_id, $ip_address)
    {
        $product_id = (int)$product_id;
        
        if (!$this->db_connect()) {
            return false;
        }
        
        $ip_address_escaped = $this->escape_string($ip_address);
        
        if ($customer_id) {
            // For logged-in users, check by customer_id (preferred) OR by primary key (p_id, ip_add) with matching c_id
            // This handles cases where item was added with IP but user is now logged in
            $sql = "SELECT * FROM carts 
                    WHERE p_id = $product_id 
                    AND (
                        c_id = " . (int)$customer_id . " 
                        OR (ip_add = '$ip_address_escaped' AND c_id = " . (int)$customer_id . ")
                    )
                    LIMIT 1";
        } else {
            // For guests, check by IP address and ensure c_id is NULL or 0
            // Primary key is (p_id, ip_add), so this query is efficient
            // Use (c_id IS NULL OR c_id = 0) to handle both cases
            $sql = "SELECT * FROM carts WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' AND (c_id IS NULL OR c_id = 0) LIMIT 1";
        }
        
        $result = $this->db_fetch_one($sql);
        
        // If multiple results exist (shouldn't happen but just in case), return the first one
        if ($result && is_array($result)) {
            return $result;
        }
        
        return false;
    }
    
    /**
     * Add a product to the cart
     * @param array $data Product data (product_id, quantity)
     * @return array Result with status and message
     */
    public function add($data)
    {
        if (empty($data['product_id'])) {
            return [
                'status' => false,
                'message' => 'Product ID is required.'
            ];
        }
        
        $product_id = (int)$data['product_id'];
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
        
        if ($quantity <= 0) {
            return [
                'status' => false,
                'message' => 'Quantity must be greater than 0.'
            ];
        }
        
        // Verify product exists
        $product_sql = "SELECT product_id, product_price, stock FROM customer_products WHERE product_id = $product_id";
        $product = $this->db_fetch_one($product_sql);
        
        if (!$product) {
            return [
                'status' => false,
                'message' => 'Product not found.'
            ];
        }
        
        // Check stock availability
        if (isset($product['stock']) && $product['stock'] < $quantity) {
            return [
                'status' => false,
                'message' => 'Insufficient stock. Available: ' . $product['stock']
            ];
        }
        
        $user = $this->getUserIdentifier();
        $customer_id = $user['customer_id'];
        $ip_address = $user['ip_address'];
        
        // Check if product already exists in cart
        $existing = $this->productExistsInCart($product_id, $customer_id, $ip_address);
        
        if ($existing) {
            // Update quantity
            $new_quantity = (int)$existing['qty'] + $quantity;
            
            // Check stock again with new quantity
            if (isset($product['stock']) && $product['stock'] < $new_quantity) {
                return [
                    'status' => false,
                    'message' => 'Insufficient stock. Available: ' . $product['stock'] . ', Requested: ' . $new_quantity
                ];
            }
            
            return $this->updateQuantity($existing, $new_quantity);
        }
        
        // Add new item to cart
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        $ip_address_escaped = $this->escape_string($ip_address);
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle race conditions
        // This prevents duplicate key errors if the product was added between the check and insert
        // Note: Primary key is (p_id, ip_add), so ON DUPLICATE KEY UPDATE will trigger on same product+IP combination
        if ($customer_id) {
            // For logged-in users, ensure c_id is set correctly
            // If a row exists with same (p_id, ip_add) but different c_id, update both qty and c_id
            $sql = "INSERT INTO carts (p_id, ip_add, c_id, qty) 
                    VALUES ($product_id, '$ip_address_escaped', " . (int)$customer_id . ", $quantity)
                    ON DUPLICATE KEY UPDATE 
                        qty = qty + $quantity,
                        c_id = " . (int)$customer_id;
        } else {
            // For guest users, insert without c_id (NULL)
            // Explicitly set c_id to NULL to ensure it's not 0 or empty
            $sql = "INSERT INTO carts (p_id, ip_add, c_id, qty) 
                    VALUES ($product_id, '$ip_address_escaped', NULL, $quantity)
                    ON DUPLICATE KEY UPDATE 
                        qty = qty + $quantity,
                        c_id = NULL";
        }
        
        // Execute the query
        $query_result = $this->db_query($sql);
        $db_error = mysqli_error($this->db);
        $affected_rows = mysqli_affected_rows($this->db);
        
        // Log for debugging
        error_log("Cart add query - product_id: $product_id, customer_id: " . ($customer_id ?? 'null') . ", ip: $ip_address");
        error_log("Cart add SQL: " . $sql);
        error_log("Cart add query result: " . ($query_result ? 'true' : 'false'));
        error_log("Cart add affected_rows: $affected_rows");
        error_log("Cart add db_error: " . ($db_error ? $db_error : 'none'));
        
        // For guests, also log the escaped IP to verify it's correct
        if (!$customer_id) {
            error_log("Cart add: Guest user - escaped IP: '$ip_address_escaped', original IP: '$ip_address'");
        }
        
        if ($query_result) {
            // Verify the item was actually added/updated by checking if it exists now
            // Use a small delay to ensure database consistency
            usleep(100000); // 100ms delay
            
            $verify_item = $this->productExistsInCart($product_id, $customer_id, $ip_address);
            
            error_log("Cart add verification - found: " . ($verify_item ? 'yes' : 'no'));
            if ($verify_item) {
                error_log("Cart add verification - qty: " . $verify_item['qty'] . ", c_id: " . ($verify_item['c_id'] ?? 'null'));
            }
            
            if (!$verify_item) {
                // Item was not added - this shouldn't happen but handle it
                error_log("Cart add: Item not found after insert/update. product_id: $product_id, customer_id: " . ($customer_id ?? 'null') . ", ip: $ip_address");
                
                // Try one more time with a direct query - try multiple variations
                $direct_results = [];
                
                if ($customer_id) {
                    // For logged-in users, try multiple queries
                    $direct_check1 = "SELECT * FROM carts WHERE p_id = $product_id AND c_id = " . (int)$customer_id . " LIMIT 1";
                    $direct_check2 = "SELECT * FROM carts WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' AND c_id = " . (int)$customer_id . " LIMIT 1";
                    $direct_result1 = $this->db_fetch_one($direct_check1);
                    $direct_result2 = $this->db_fetch_one($direct_check2);
                    if ($direct_result1) $direct_results[] = $direct_result1;
                    if ($direct_result2 && $direct_result2 !== $direct_result1) $direct_results[] = $direct_result2;
                } else {
                    // For guests, try multiple query variations
                    $direct_check1 = "SELECT * FROM carts WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' AND (c_id IS NULL OR c_id = 0) LIMIT 1";
                    $direct_check2 = "SELECT * FROM carts WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' LIMIT 1";
                    $direct_result1 = $this->db_fetch_one($direct_check1);
                    $direct_result2 = $this->db_fetch_one($direct_check2);
                    if ($direct_result1) $direct_results[] = $direct_result1;
                    if ($direct_result2 && $direct_result2 !== $direct_result1) $direct_results[] = $direct_result2;
                    
                    // Also check what's actually in the database
                    $debug_check = "SELECT * FROM carts WHERE p_id = $product_id AND ip_add = '$ip_address_escaped'";
                    $debug_results = $this->db_fetch_all($debug_check);
                    error_log("Cart add: Debug query found " . count($debug_results) . " rows for p_id=$product_id, ip=$ip_address_escaped");
                    if ($debug_results) {
                        foreach ($debug_results as $row) {
                            error_log("Cart add: Found row - p_id: " . $row['p_id'] . ", ip_add: " . $row['ip_add'] . ", c_id: " . ($row['c_id'] ?? 'NULL') . ", qty: " . $row['qty']);
                        }
                    }
                }
                
                if (!empty($direct_results)) {
                    error_log("Cart add: Direct query found item, using it");
                    $verify_item = $direct_results[0];
                } else {
                    error_log("Cart add: Direct query also failed - no items found");
                    error_log("Cart add: Query was: " . ($customer_id ? "c_id = " . (int)$customer_id : "ip_add = '$ip_address_escaped' AND c_id IS NULL"));
                    
                    // Return more detailed error message
                    $error_msg = 'Failed to add product to cart. ';
                    if (!$customer_id) {
                        $error_msg .= 'Please try logging in or contact support if the problem persists.';
                    } else {
                        $error_msg .= 'Please try again.';
                    }
                    
                    return [
                        'status' => false,
                        'message' => $error_msg
                    ];
                }
            }
            
            // Verify stock with updated quantity
            if (isset($product['stock']) && $product['stock'] < $verify_item['qty']) {
                // Revert the quantity if it exceeds stock
                $revert_quantity = $verify_item['qty'] - $quantity;
                if ($revert_quantity > 0) {
                    $this->updateQuantity($verify_item, $product['stock']);
                } else {
                    $this->remove($product_id, $customer_id, $ip_address);
                }
                return [
                    'status' => false,
                    'message' => 'Insufficient stock. Available: ' . $product['stock']
                ];
            }
            
            // Log successful addition for debugging
            error_log("Cart add: Success. product_id: $product_id, customer_id: " . ($customer_id ?? 'null') . ", ip: $ip_address, qty: " . $verify_item['qty']);
            
            return [
                'status' => true,
                'message' => 'Product added to cart successfully.'
            ];
        } else {
            $error = mysqli_error($this->db);
            
            // If it's a duplicate key error, try to update instead
            if (strpos($error, 'Duplicate entry') !== false) {
                // Product exists, update quantity
                $existing = $this->productExistsInCart($product_id, $customer_id, $ip_address);
                if ($existing) {
                    $new_quantity = (int)$existing['qty'] + $quantity;
                    if (isset($product['stock']) && $product['stock'] < $new_quantity) {
                        return [
                            'status' => false,
                            'message' => 'Insufficient stock. Available: ' . $product['stock'] . ', Requested: ' . $new_quantity
                        ];
                    }
                    return $this->updateQuantity($existing, $new_quantity);
                }
            }
            
            return [
                'status' => false,
                'message' => 'Failed to add product to cart: ' . $error
            ];
        }
    }
    
    /**
     * Update quantity of a cart item
     * @param array|int $cart_item Cart item array or cart identifier
     * @param int $quantity New quantity
     * @return array Result with status and message
     */
    public function updateQuantity($cart_item, $quantity)
    {
        if (is_array($cart_item)) {
            // Use existing cart item data
            $product_id = (int)$cart_item['p_id'];
            $customer_id = isset($cart_item['c_id']) ? (int)$cart_item['c_id'] : null;
            $ip_address = $cart_item['ip_add'] ?? '';
        } else {
            // cart_item is an identifier, need to fetch
            return [
                'status' => false,
                'message' => 'Invalid cart item identifier.'
            ];
        }
        
        $quantity = (int)$quantity;
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            return $this->remove($product_id, $customer_id, $ip_address);
        }
        
        // Verify product exists and check stock
        $product_sql = "SELECT stock FROM customer_products WHERE product_id = $product_id";
        $product = $this->db_fetch_one($product_sql);
        
        if (!$product) {
            return [
                'status' => false,
                'message' => 'Product not found.'
            ];
        }
        
        if (isset($product['stock']) && $product['stock'] < $quantity) {
            return [
                'status' => false,
                'message' => 'Insufficient stock. Available: ' . $product['stock']
            ];
        }
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        $ip_address_escaped = $this->escape_string($ip_address);
        
        if ($customer_id) {
            $sql = "UPDATE carts SET qty = $quantity 
                    WHERE p_id = $product_id AND c_id = " . (int)$customer_id;
        } else {
            // For guests, update by primary key (p_id, ip_add) where c_id IS NULL or 0
            $sql = "UPDATE carts SET qty = $quantity 
                    WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' AND (c_id IS NULL OR c_id = 0)";
        }
        
        if ($this->db_query($sql)) {
            return [
                'status' => true,
                'message' => 'Cart updated successfully.'
            ];
        } else {
            $error = mysqli_error($this->db);
            return [
                'status' => false,
                'message' => 'Failed to update cart: ' . $error
            ];
        }
    }
    
    /**
     * Remove a product from the cart
     * @param int $product_id Product ID
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return array Result with status and message
     */
    public function remove($product_id, $customer_id = null, $ip_address = null)
    {
        $product_id = (int)$product_id;
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        if ($customer_id === null && $ip_address === null) {
            $user = $this->getUserIdentifier();
            $customer_id = $user['customer_id'];
            $ip_address = $user['ip_address'];
        }
        
        if ($customer_id) {
            $sql = "DELETE FROM carts WHERE p_id = $product_id AND c_id = " . (int)$customer_id;
        } else {
            // For guests, delete by primary key (p_id, ip_add) where c_id IS NULL or 0
            $ip_address_escaped = $this->escape_string($ip_address);
            $sql = "DELETE FROM carts WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' AND (c_id IS NULL OR c_id = 0)";
        }
        
        if ($this->db_query($sql)) {
            return [
                'status' => true,
                'message' => 'Product removed from cart successfully.'
            ];
        } else {
            $error = mysqli_error($this->db);
            return [
                'status' => false,
                'message' => 'Failed to remove product from cart: ' . $error
            ];
        }
    }
    
    /**
     * Get all cart items for a user
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return array Cart items with product details
     */
    public function getCartItems($customer_id = null, $ip_address = null)
    {
        if ($customer_id === null && $ip_address === null) {
            $user = $this->getUserIdentifier();
            $customer_id = $user['customer_id'];
            $ip_address = $user['ip_address'];
        }
        
        if (!$this->db_connect()) {
            return [];
        }
        
        if ($customer_id) {
            $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image, p.stock
                    FROM carts c
                    INNER JOIN customer_products p ON c.p_id = p.product_id
                    WHERE c.c_id = " . (int)$customer_id . "
                    ORDER BY c.p_id ASC";
        } else {
            // For guests, get items by IP address where c_id IS NULL
            $ip_address_escaped = $this->escape_string($ip_address);
            $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image, p.stock
                    FROM carts c
                    INNER JOIN customer_products p ON c.p_id = p.product_id
                    WHERE c.ip_add = '$ip_address_escaped' AND (c.c_id IS NULL OR c.c_id = 0)
                    ORDER BY c.p_id ASC";
        }
        
        $items = $this->db_fetch_all($sql);
        
        if (!$items || !is_array($items)) {
            return [];
        }
        
        // Format cart items
        $formatted = [];
        foreach ($items as $item) {
            $subtotal = floatval($item['product_price']) * (int)$item['qty'];
            $formatted[] = [
                'product_id' => (int)$item['p_id'],
                'product_title' => $item['product_title'],
                'product_price' => floatval($item['product_price']),
                'product_image' => $item['product_image'],
                'quantity' => (int)$item['qty'],
                'stock' => isset($item['stock']) ? (int)$item['stock'] : null,
                'subtotal' => $subtotal,
                'ip_address' => $item['ip_add'],
                'customer_id' => isset($item['c_id']) ? (int)$item['c_id'] : null,
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Empty the cart
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return array Result with status and message
     */
    public function emptyCart($customer_id = null, $ip_address = null)
    {
        if ($customer_id === null && $ip_address === null) {
            $user = $this->getUserIdentifier();
            $customer_id = $user['customer_id'];
            $ip_address = $user['ip_address'];
        }
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        if ($customer_id) {
            $sql = "DELETE FROM carts WHERE c_id = " . (int)$customer_id;
        } else {
            // For guests, delete all items by IP address where c_id IS NULL or 0
            $ip_address_escaped = $this->escape_string($ip_address);
            $sql = "DELETE FROM carts WHERE ip_add = '$ip_address_escaped' AND (c_id IS NULL OR c_id = 0)";
        }
        
        if ($this->db_query($sql)) {
            return [
                'status' => true,
                'message' => 'Cart emptied successfully.'
            ];
        } else {
            $error = mysqli_error($this->db);
            return [
                'status' => false,
                'message' => 'Failed to empty cart: ' . $error
            ];
        }
    }
    
    /**
     * Get cart total
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return float Total amount
     */
    public function getCartTotal($customer_id = null, $ip_address = null)
    {
        $items = $this->getCartItems($customer_id, $ip_address);
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }
    
    /**
     * Get cart item count
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return int Item count
     */
    public function getCartItemCount($customer_id = null, $ip_address = null)
    {
        $items = $this->getCartItems($customer_id, $ip_address);
        $count = 0;
        
        foreach ($items as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
    
    /**
     * Transfer cart items from IP address to customer_id
     * This is called when a guest user logs in or registers
     * Since the primary key is (p_id, ip_add), we UPDATE c_id instead of deleting/re-inserting
     * @param string $ip_address IP address of the guest
     * @param int $customer_id Customer ID to transfer items to
     * @return array Result with status and message
     */
    public function transferCartFromIP($ip_address, $customer_id)
    {
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        // Normalize IP address (convert IPv6 localhost to IPv4)
        if ($ip_address === '::1') {
            $ip_address = '127.0.0.1';
        }
        
        $ip_address_escaped = $this->escape_string($ip_address);
        $customer_id = (int)$customer_id;
        
        if ($customer_id <= 0) {
            return [
                'status' => false,
                'message' => 'Invalid customer ID.'
            ];
        }
        
        error_log("Cart transfer: IP=$ip_address, Customer ID=$customer_id");
        
        // Get guest cart items (items with this IP and NULL c_id)
        // Try both the normalized IP and original IP if different
        $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image, p.stock
                FROM carts c
                INNER JOIN customer_products p ON c.p_id = p.product_id
                WHERE c.ip_add = '$ip_address_escaped' AND (c.c_id IS NULL OR c.c_id = 0)
                ORDER BY c.p_id ASC";
        
        $guest_items = $this->db_fetch_all($sql);
        
        error_log("Cart transfer: Found " . count($guest_items) . " guest items for IP=$ip_address_escaped");
        
        if (!$guest_items || empty($guest_items)) {
            // Try alternative IP formats (IPv6 vs IPv4)
            $alt_ip = ($ip_address === '127.0.0.1') ? '::1' : '127.0.0.1';
            $alt_ip_escaped = $this->escape_string($alt_ip);
            $alt_sql = "SELECT c.*, p.product_title, p.product_price, p.product_image, p.stock
                        FROM carts c
                        INNER JOIN customer_products p ON c.p_id = p.product_id
                        WHERE c.ip_add = '$alt_ip_escaped' AND (c.c_id IS NULL OR c.c_id = 0)
                        ORDER BY c.p_id ASC";
            $alt_items = $this->db_fetch_all($alt_sql);
            
            if ($alt_items && !empty($alt_items)) {
                error_log("Cart transfer: Found " . count($alt_items) . " items with alternative IP=$alt_ip_escaped");
                $guest_items = $alt_items;
                $ip_address_escaped = $alt_ip_escaped; // Use the alternative IP for updates
            } else {
                return [
                    'status' => true,
                    'message' => 'No items to transfer.',
                    'transferred' => 0
                ];
            }
        }
        
        // Get existing customer cart items
        $customer_items = $this->getCartItems($customer_id, null);
        $customer_cart_map = [];
        foreach ($customer_items as $item) {
            $customer_cart_map[$item['product_id']] = [
                'quantity' => $item['quantity'],
                'ip_address' => $item['ip_address']
            ];
        }
        
        $transferred = 0;
        $merged = 0;
        
        // Transfer each guest item
        foreach ($guest_items as $item) {
            $product_id = (int)$item['p_id'];
            $quantity = (int)$item['qty'];
            
            // Check if customer already has this product in cart
            if (isset($customer_cart_map[$product_id])) {
                // Merge quantities - update the existing customer cart item
                $new_quantity = $customer_cart_map[$product_id]['quantity'] + $quantity;
                $existing_ip = $this->escape_string($customer_cart_map[$product_id]['ip_address']);
                
                // Update the existing customer cart item (identified by p_id and its ip_add)
                $update_sql = "UPDATE carts SET qty = $new_quantity 
                               WHERE p_id = $product_id 
                               AND ip_add = '$existing_ip' 
                               AND c_id = $customer_id";
                
                if ($this->db_query($update_sql)) {
                    $merged++;
                }
                
                // Delete the guest cart item (since we merged it)
                $delete_sql = "DELETE FROM carts 
                               WHERE p_id = $product_id 
                               AND ip_add = '$ip_address_escaped' 
                               AND (c_id IS NULL OR c_id = 0)";
                $this->db_query($delete_sql);
            } else {
                // Simply update c_id for the guest cart item (primary key stays the same: p_id, ip_add)
                // This is more efficient than delete + insert
                $update_sql = "UPDATE carts SET c_id = $customer_id 
                               WHERE p_id = $product_id 
                               AND ip_add = '$ip_address_escaped' 
                               AND (c_id IS NULL OR c_id = 0)";
                
                if ($this->db_query($update_sql)) {
                    $transferred++;
                }
            }
        }
        
        return [
            'status' => true,
            'message' => 'Cart items transferred successfully.',
            'transferred' => $transferred,
            'merged' => $merged
        ];
    }
}
?>

