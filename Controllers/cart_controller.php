<?php
/**
 * Cart Controller
 * Creates an instance of the cart class and wraps its methods for use by action scripts
 */

require_once __DIR__ . '/../Classes/cart_class.php';

class cart_controller
{
    private $cart;

    public function __construct()
    {
        $this->cart = new cart_class();
    }

    /**
     * Add to cart
     * @param array $params Product data (product_id, quantity)
     * @return array Result with status and message
     */
    public function add_to_cart_ctr($params)
    {
        return $this->cart->add($params);
    }

    /**
     * Update cart item quantity
     * @param array $params Cart item data (product_id, quantity, customer_id, ip_address)
     * @return array Result with status and message
     */
    public function update_cart_item_ctr($params)
    {
        $product_id = (int)$params['product_id'];
        $quantity = (int)$params['quantity'];
        
        // Get user identifier
        $user = $this->cart->getUserIdentifier();
        $customer_id = $user['customer_id'];
        $ip_address = $user['ip_address'];
        
        // Check if product exists in cart
        $existing = $this->cart->productExistsInCart($product_id, $customer_id, $ip_address);
        
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Product not found in cart.'
            ];
        }
        
        return $this->cart->updateQuantity($existing, $quantity);
    }

    /**
     * Remove from cart
     * @param int $product_id Product ID
     * @param array $params Optional customer_id and ip_address
     * @return array Result with status and message
     */
    public function remove_from_cart_ctr($product_id, $params = [])
    {
        $customer_id = isset($params['customer_id']) ? (int)$params['customer_id'] : null;
        $ip_address = $params['ip_address'] ?? null;
        
        return $this->cart->remove($product_id, $customer_id, $ip_address);
    }

    /**
     * Get user cart
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return array Cart items
     */
    public function get_user_cart_ctr($customer_id = null, $ip_address = null)
    {
        return $this->cart->getCartItems($customer_id, $ip_address);
    }

    /**
     * Empty cart
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return array Result with status and message
     */
    public function empty_cart_ctr($customer_id = null, $ip_address = null)
    {
        return $this->cart->emptyCart($customer_id, $ip_address);
    }

    /**
     * Get cart total
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return float Total amount
     */
    public function get_cart_total_ctr($customer_id = null, $ip_address = null)
    {
        return $this->cart->getCartTotal($customer_id, $ip_address);
    }

    /**
     * Get cart item count
     * @param int|null $customer_id Customer ID (null for guest)
     * @param string|null $ip_address IP address for guest
     * @return int Item count
     */
    public function get_cart_item_count_ctr($customer_id = null, $ip_address = null)
    {
        return $this->cart->getCartItemCount($customer_id, $ip_address);
    }
    
    /**
     * Transfer cart items from IP address to customer_id
     * @param string $ip_address IP address of the guest
     * @param int $customer_id Customer ID to transfer items to
     * @return array Result with status and message
     */
    public function transfer_cart_from_ip_ctr($ip_address, $customer_id)
    {
        return $this->cart->transferCartFromIP($ip_address, $customer_id);
    }
}
?>

