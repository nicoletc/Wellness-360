<?php
/**
 * Product Controller
 * Creates an instance of the product class and runs the methods
 */

require_once __DIR__ . '/../Classes/product_class.php';

class product_controller
{
    private $product;

    public function __construct()
    {
        $this->product = new product_class();
    }

    /**
     * Add a new product
     * @param array $kwargs Product data
     * @return array Result with status and message
     */
    public function add_product_ctr($kwargs)
    {
        return $this->product->add($kwargs);
    }

    /**
     * Get all products
     * @return array All products
     */
    public function get_all_products_ctr()
    {
        return $this->product->get_all();
    }

    /**
     * Get a single product by ID
     * @param int $product_id Product ID
     * @return array|false Product data or false if not found
     */
    public function get_product_ctr($product_id)
    {
        return $this->product->get_one($product_id);
    }

    /**
     * Update a product
     * @param array $kwargs Product data
     * @return array Result with status and message
     */
    public function update_product_ctr($kwargs)
    {
        return $this->product->update($kwargs);
    }

    /**
     * Delete a product
     * @param int $product_id Product ID
     * @return array Result with status and message
     */
    public function delete_product_ctr($product_id)
    {
        return $this->product->delete($product_id);
    }
}

?>

