<?php
/**
 * Wishlist Controller
 * Creates an instance of the wishlist class and wraps its methods
 */

require_once __DIR__ . '/../Classes/wishlist_class.php';

class wishlist_controller
{
    private $wishlist;

    public function __construct()
    {
        $this->wishlist = new wishlist_class();
    }

    /**
     * Add to wishlist
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return array Result with status and message
     */
    public function add_to_wishlist_ctr($customer_id, $product_id)
    {
        return $this->wishlist->addToWishlist($customer_id, $product_id);
    }

    /**
     * Remove from wishlist
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return array Result with status and message
     */
    public function remove_from_wishlist_ctr($customer_id, $product_id)
    {
        return $this->wishlist->removeFromWishlist($customer_id, $product_id);
    }

    /**
     * Check if product is in wishlist
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return bool True if in wishlist
     */
    public function is_in_wishlist_ctr($customer_id, $product_id)
    {
        return $this->wishlist->isInWishlist($customer_id, $product_id);
    }

    /**
     * Get user wishlist
     * @param int $customer_id Customer ID
     * @return array Wishlist items
     */
    public function get_user_wishlist_ctr($customer_id)
    {
        return $this->wishlist->getUserWishlist($customer_id);
    }

    /**
     * Get wishlist count
     * @param int $customer_id Customer ID
     * @return int Count of items
     */
    public function get_wishlist_count_ctr($customer_id)
    {
        return $this->wishlist->getWishlistCount($customer_id);
    }
}
?>

