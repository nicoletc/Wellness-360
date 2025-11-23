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
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        if (is_logged_in()) {
            $customer_id = current_user_id();
        }
        
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
        
        if ($customer_id) {
            // For logged-in users, check by customer_id
            $sql = "SELECT * FROM cart WHERE p_id = $product_id AND c_id = " . (int)$customer_id;
        } else {
            // For guests, check by IP address and ensure c_id is NULL
            // Primary key is (p_id, ip_add), so this query is efficient
            $ip_address = $this->escape_string($ip_address);
            $sql = "SELECT * FROM cart WHERE p_id = $product_id AND ip_add = '$ip_address' AND c_id IS NULL";
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
        $product_sql = "SELECT product_id, product_price, stock FROM products WHERE product_id = $product_id";
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
        
        // Build INSERT query - handle NULL for customer_id properly
        // If c_id doesn't allow NULL, we'll omit it from the INSERT for guest users
        if ($customer_id) {
            $sql = "INSERT INTO cart (p_id, ip_add, c_id, qty) 
                    VALUES ($product_id, '$ip_address_escaped', " . (int)$customer_id . ", $quantity)";
        } else {
            // For guest users, try inserting without c_id first (if column allows NULL)
            // If that fails, we'll use 0 as a fallback
            $sql = "INSERT INTO cart (p_id, ip_add, qty) 
                    VALUES ($product_id, '$ip_address_escaped', $quantity)";
        }
        
        if ($this->db_query($sql)) {
            return [
                'status' => true,
                'message' => 'Product added to cart successfully.'
            ];
        } else {
            $error = mysqli_error($this->db);
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
        $product_sql = "SELECT stock FROM products WHERE product_id = $product_id";
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
            $sql = "UPDATE cart SET qty = $quantity 
                    WHERE p_id = $product_id AND c_id = " . (int)$customer_id;
        } else {
            // For guests, update by primary key (p_id, ip_add) where c_id IS NULL
            $sql = "UPDATE cart SET qty = $quantity 
                    WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' AND c_id IS NULL";
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
            $sql = "DELETE FROM cart WHERE p_id = $product_id AND c_id = " . (int)$customer_id;
        } else {
            // For guests, delete by primary key (p_id, ip_add) where c_id IS NULL
            $ip_address_escaped = $this->escape_string($ip_address);
            $sql = "DELETE FROM cart WHERE p_id = $product_id AND ip_add = '$ip_address_escaped' AND c_id IS NULL";
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
                    FROM cart c
                    INNER JOIN products p ON c.p_id = p.product_id
                    WHERE c.c_id = " . (int)$customer_id . "
                    ORDER BY c.p_id ASC";
        } else {
            // For guests, get items by IP address where c_id IS NULL
            $ip_address_escaped = $this->escape_string($ip_address);
            $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image, p.stock
                    FROM cart c
                    INNER JOIN products p ON c.p_id = p.product_id
                    WHERE c.ip_add = '$ip_address_escaped' AND c.c_id IS NULL
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
            $sql = "DELETE FROM cart WHERE c_id = " . (int)$customer_id;
        } else {
            // For guests, delete all items by IP address where c_id IS NULL
            $ip_address_escaped = $this->escape_string($ip_address);
            $sql = "DELETE FROM cart WHERE ip_add = '$ip_address_escaped' AND c_id IS NULL";
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
        
        $ip_address_escaped = $this->escape_string($ip_address);
        $customer_id = (int)$customer_id;
        
        if ($customer_id <= 0) {
            return [
                'status' => false,
                'message' => 'Invalid customer ID.'
            ];
        }
        
        // Get guest cart items (items with this IP and NULL c_id)
        $ip_address_escaped = $this->escape_string($ip_address);
        $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image, p.stock
                FROM cart c
                INNER JOIN products p ON c.p_id = p.product_id
                WHERE c.ip_add = '$ip_address_escaped' AND (c.c_id IS NULL OR c.c_id = 0)
                ORDER BY c.p_id ASC";
        
        $guest_items = $this->db_fetch_all($sql);
        
        if (!$guest_items || empty($guest_items)) {
            return [
                'status' => true,
                'message' => 'No items to transfer.',
                'transferred' => 0
            ];
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
                $update_sql = "UPDATE cart SET qty = $new_quantity 
                               WHERE p_id = $product_id 
                               AND ip_add = '$existing_ip' 
                               AND c_id = $customer_id";
                
                if ($this->db_query($update_sql)) {
                    $merged++;
                }
                
                // Delete the guest cart item (since we merged it)
                $delete_sql = "DELETE FROM cart 
                               WHERE p_id = $product_id 
                               AND ip_add = '$ip_address_escaped' 
                               AND c_id IS NULL";
                $this->db_query($delete_sql);
            } else {
                // Simply update c_id for the guest cart item (primary key stays the same: p_id, ip_add)
                // This is more efficient than delete + insert
                $update_sql = "UPDATE cart SET c_id = $customer_id 
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

