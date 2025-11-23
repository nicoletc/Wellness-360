<?php
/**
 * Vendor Class
 * Extends database connection and contains vendor methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class vendor_class extends db_connection
{
    /**
     * Add a new vendor to the database
     * @param array $data Vendor data (vendor_name, vendor_email, vendor_contact, product_stock)
     * @return array Result with status and message
     */
    public function add($data)
    {
        // Validate required fields
        if (empty($data['vendor_name'])) {
            return [
                'status' => false,
                'message' => 'Vendor name is required.'
            ];
        }

        if (empty($data['vendor_email'])) {
            return [
                'status' => false,
                'message' => 'Vendor email is required.'
            ];
        }

        $vendor_name = $this->escape_string(trim($data['vendor_name']));
        $vendor_email = $this->escape_string(trim($data['vendor_email']));
        $vendor_contact = $this->escape_string(trim($data['vendor_contact'] ?? ''));
        $product_stock = isset($data['product_stock']) ? (int)$data['product_stock'] : 0;

        // Validate email format
        if (!filter_var($vendor_email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Invalid email format.'
            ];
        }

        // Check if vendor email already exists
        $check_sql = "SELECT vendor_id FROM vendors WHERE vendor_email = '$vendor_email'";
        $existing = $this->db_fetch_one($check_sql);

        if ($existing) {
            return [
                'status' => false,
                'message' => 'Vendor email already exists. Please use a different email.'
            ];
        }

        // Validate vendor name length
        if (strlen($vendor_name) < 2) {
            return [
                'status' => false,
                'message' => 'Vendor name must be at least 2 characters long.'
            ];
        }

        if (strlen($vendor_name) > 100) {
            return [
                'status' => false,
                'message' => 'Vendor name must not exceed 100 characters.'
            ];
        }

        // Validate email length
        if (strlen($vendor_email) > 100) {
            return [
                'status' => false,
                'message' => 'Email must not exceed 100 characters.'
            ];
        }

        // Validate product stock (must be non-negative)
        if ($product_stock < 0) {
            return [
                'status' => false,
                'message' => 'Product stock cannot be negative.'
            ];
        }

        // Insert vendor
        $sql = "INSERT INTO vendors (vendor_name, vendor_email, vendor_contact, product_stock) 
                VALUES ('$vendor_name', '$vendor_email', '$vendor_contact', $product_stock)";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Vendor added successfully.',
                'vendor_id' => mysqli_insert_id($this->db)
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to add vendor. Please try again.'
            ];
        }
    }

    /**
     * Get all vendors
     * @return array All vendors
     */
    public function get_all()
    {
        $sql = "SELECT * FROM vendors ORDER BY vendor_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get a single vendor by ID
     * @param int $vendor_id Vendor ID
     * @return array|false Vendor data or false if not found
     */
    public function get_one($vendor_id)
    {
        $vendor_id = (int)$vendor_id;
        $sql = "SELECT * FROM vendors WHERE vendor_id = $vendor_id";
        return $this->db_fetch_one($sql);
    }

    /**
     * Get a single vendor by email
     * @param string $vendor_email Vendor email
     * @return array|false Vendor data or false if not found
     */
    public function get_by_email($vendor_email)
    {
        $vendor_email = $this->escape_string(trim($vendor_email));
        $sql = "SELECT * FROM vendors WHERE vendor_email = '$vendor_email'";
        return $this->db_fetch_one($sql);
    }

    /**
     * Update a vendor
     * @param array $data Vendor data (vendor_id, vendor_name, vendor_email, vendor_contact, product_stock)
     * @return array Result with status and message
     */
    public function update($data)
    {
        // Validate required fields
        if (empty($data['vendor_id'])) {
            return [
                'status' => false,
                'message' => 'Vendor ID is required.'
            ];
        }

        if (empty($data['vendor_name'])) {
            return [
                'status' => false,
                'message' => 'Vendor name is required.'
            ];
        }

        if (empty($data['vendor_email'])) {
            return [
                'status' => false,
                'message' => 'Vendor email is required.'
            ];
        }

        $vendor_id = (int)$data['vendor_id'];
        $vendor_name = $this->escape_string(trim($data['vendor_name']));
        $vendor_email = $this->escape_string(trim($data['vendor_email']));
        $vendor_contact = $this->escape_string(trim($data['vendor_contact'] ?? ''));
        $product_stock = isset($data['product_stock']) ? (int)$data['product_stock'] : 0;

        // Check if vendor exists
        $existing = $this->get_one($vendor_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Vendor not found.'
            ];
        }

        // Validate email format
        if (!filter_var($vendor_email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Invalid email format.'
            ];
        }

        // Check if another vendor with the same email exists (excluding current vendor)
        $check_sql = "SELECT vendor_id FROM vendors WHERE vendor_email = '$vendor_email' AND vendor_id != $vendor_id";
        $duplicate = $this->db_fetch_one($check_sql);

        if ($duplicate) {
            return [
                'status' => false,
                'message' => 'Vendor email already exists. Please use a different email.'
            ];
        }

        // Validate vendor name length
        if (strlen($vendor_name) < 2) {
            return [
                'status' => false,
                'message' => 'Vendor name must be at least 2 characters long.'
            ];
        }

        if (strlen($vendor_name) > 100) {
            return [
                'status' => false,
                'message' => 'Vendor name must not exceed 100 characters.'
            ];
        }

        // Validate email length
        if (strlen($vendor_email) > 100) {
            return [
                'status' => false,
                'message' => 'Email must not exceed 100 characters.'
            ];
        }

        // Validate product stock (must be non-negative)
        if ($product_stock < 0) {
            return [
                'status' => false,
                'message' => 'Product stock cannot be negative.'
            ];
        }

        // Update vendor
        $sql = "UPDATE vendors 
                SET vendor_name = '$vendor_name', 
                    vendor_email = '$vendor_email', 
                    vendor_contact = '$vendor_contact', 
                    product_stock = $product_stock 
                WHERE vendor_id = $vendor_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Vendor updated successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to update vendor. Please try again.'
            ];
        }
    }

    /**
     * Delete a vendor
     * @param int $vendor_id Vendor ID
     * @return array Result with status and message
     */
    public function delete($vendor_id)
    {
        $vendor_id = (int)$vendor_id;

        // Check if vendor exists
        $existing = $this->get_one($vendor_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Vendor not found.'
            ];
        }

        // Check if vendor is being used by products
        $check_products = "SELECT COUNT(*) as count FROM products WHERE product_vendor = $vendor_id";
        $product_count = $this->db_fetch_one($check_products);
        
        if ($product_count && $product_count['count'] > 0) {
            return [
                'status' => false,
                'message' => 'Cannot delete vendor. Vendor is associated with ' . $product_count['count'] . ' product(s).'
            ];
        }

        // Delete vendor
        $sql = "DELETE FROM vendors WHERE vendor_id = $vendor_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Vendor deleted successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to delete vendor. Please try again.'
            ];
        }
    }
}

?>

