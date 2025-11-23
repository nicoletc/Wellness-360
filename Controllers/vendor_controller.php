<?php
/**
 * Vendor Controller
 * Creates an instance of the vendor class and runs the methods
 */

require_once __DIR__ . '/../Classes/vendor_class.php';

class vendor_controller
{
    private $vendor;

    public function __construct()
    {
        $this->vendor = new vendor_class();
    }

    /**
     * Add a new vendor
     * @param array $kwargs Vendor data (vendor_name, vendor_email, vendor_contact, product_stock)
     * @return array Result with status and message
     */
    public function add_vendor_ctr($kwargs)
    {
        return $this->vendor->add($kwargs);
    }

    /**
     * Get all vendors
     * @return array All vendors
     */
    public function get_all_vendors_ctr()
    {
        return $this->vendor->get_all();
    }

    /**
     * Get a single vendor by ID
     * @param int $vendor_id Vendor ID
     * @return array|false Vendor data or false if not found
     */
    public function get_vendor_ctr($vendor_id)
    {
        return $this->vendor->get_one($vendor_id);
    }

    /**
     * Update a vendor
     * @param array $kwargs Vendor data (vendor_id, vendor_name, vendor_email, vendor_contact, product_stock)
     * @return array Result with status and message
     */
    public function update_vendor_ctr($kwargs)
    {
        return $this->vendor->update($kwargs);
    }

    /**
     * Delete a vendor
     * @param int $vendor_id Vendor ID
     * @return array Result with status and message
     */
    public function delete_vendor_ctr($vendor_id)
    {
        return $this->vendor->delete($vendor_id);
    }
}

?>

